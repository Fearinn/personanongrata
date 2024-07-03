<?php

/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * PersonaNonGrata implementation : © Matheus Gomes matheusgomesforwork@gmail.com
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * personanongrata.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 *
 */


require_once(APP_GAMEMODULE_PATH . 'module/table/table.game.php');


class PersonaNonGrata extends Table
{
    function __construct()
    {
        parent::__construct();

        $this->initGameStateLabels(array(
            "week" => 10,
            "currentCorporation" => 11
        ));

        $this->information_cards = $this->getNew("module.common.deck");
        $this->information_cards->init("information");

        $this->action_cards = $this->getNew("module.common.deck");
        $this->action_cards->init("action");

        $this->corporation_cards = $this->getNew("module.common.deck");
        $this->corporation_cards->init("corporation");

        $this->key_cards = $this->getNew("module.common.deck");
        $this->key_cards->init("corporationKey");
    }

    protected function getGameName()
    {
        // Used for translations and stuff. Please do not modify.
        return "personanongrata";
    }

    protected function setupNewGame($players, $options = array())
    {
        $gameinfos = $this->getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach ($players as $player_id => $player) {
            $color = array_shift($default_colors);
            $values[] = "('" . $player_id . "','$color','" . $player['player_canal'] . "','" . addslashes($player['player_name']) . "','" . addslashes($player['player_avatar']) . "')";
        }
        $sql .= implode(',', $values);
        $this->DbQuery($sql);
        $this->reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
        $this->reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        $this->setGameStateInitialValue("week", 1);
        $this->setGameStateInitialValue("currentCorporation", 1);

        //corporations
        $corporation_cards = array();
        $key_cards = array();

        foreach ($this->corporations as $corporation_id => $corporation) {
            $key_cards[] = array(
                "type" => $corporation_id,
                "type_arg" => 2,
                "nbr" => 1
            );

            for ($value = 0; $value <= 4; $value += 2) {
                $corporation_cards[] = array(
                    "type" => $corporation_id,
                    "type_arg" => $value,
                    "nbr" => 1
                );
            }
        }

        $this->corporation_cards->createCards($corporation_cards, "deck");
        $this->key_cards->createCards($key_cards, "table");

        foreach ($this->corporations as $corporation_id => $corporation) {
            $key_card = $this->getKeyByCorporation($corporation_id);
            $this->key_cards->moveCard($key_card["id"], "table", $corporation_id);

            for ($value = 0; $value <= 4; $value += 2) {
                $corporation_cards = $this->corporation_cards->getCardsOfTypeInLocation($corporation_id, $value, "deck");

                foreach ($corporation_cards as $card_id => $card) {
                    $this->corporation_cards->insertCardOnExtremePosition($card_id, "deck:" . $corporation_id, true);
                }
            }
        }

        $action_cards = array();

        foreach ($this->hackers as $hacker_id => $hacker) {
            foreach ($this->actions as $action_id => $action) {
                $nbr = $action_id === 1 ? 2 : 1;

                $action_cards[] = array(
                    "type" => $hacker_id,
                    "type_arg" => $action_id,
                    "nbr" => $nbr,
                );
            }
        }

        $this->action_cards->createCards($action_cards, "deck");

        foreach ($players as $player_id => $player) {
            foreach ($this->hackers as $hacker_id => $hacker) {
                $player_color = $this->getPlayerColorById($player_id);

                if ($hacker["color"] !== $player_color) {
                    continue;
                }

                $card_ids = array_keys($this->action_cards->getCardsOfTypeInLocation($hacker_id, null, "deck"));
                $this->action_cards->moveCards($card_ids, "hand", $player_id);
            }
        }

        //informations
        $information_cards = array();

        foreach ($this->corporations as $corporation_id => $corporation) {
            foreach ($this->informations as $information_id => $information) {
                $information_cards[] = array(
                    "type" => $corporation_id,
                    "type_arg" => $information_id,
                    "nbr" => $information["nbr"],
                );
            }
        }

        $this->information_cards->createCards($information_cards, "deck");
        $this->information_cards->shuffle("deck");

        foreach ($players as $player_id => $player) {
            $this->information_cards->pickCards(6, "deck", $player_id);
        }

        /************ End of the game initialization *****/
    }

    protected function getAllDatas()
    {
        $result = array();

        $current_player_id = $this->getCurrentPlayerId();    // !! We must only return informations visible by this player !!

        $result["players"] = $this->getCollectionFromDb("SELECT player_id id, player_score score FROM player ");
        $result["clockwise"] = $this->isClockwise();
        $result["prevPlayer"] = $this->getPlayerBefore($current_player_id);
        $result["nextPlayer"] = $this->getPlayerAfter($current_player_id);
        $result["corporations"] = $this->corporations;
        $result["hackers"] = $this->getHackers();
        $result["keysOnTable"] = $this->getKeysOnTable();
        $result["corporationDecks"] = $this->getCorporationDecks();
        $result["actionsInMyHand"] = $this->getActionsInMyHand($current_player_id);
        $result["actionsInOtherHands"] = $this->getActionsInOtherHands($current_player_id);
        $result["deckOfInformations"] = $this->getDeckOfInformations();
        $result["infoInMyHand"] = $this->getInfoInMyHand($current_player_id);
        $result["infoInOtherHands"] = $this->getInfoInOtherHands($current_player_id);
        $result["cardsPlayedByMe"] = $this->getCardsPlayedByMe($current_player_id);
        $result["infoStoredByMe"] = $this->getInfoStoredByMe($current_player_id);
        $result["infoStoredByOthers"] = $this->getInfoStoredByOthers($current_player_id);
        $result["actionsDiscarded"] = $this->getActionsDiscarded();
        $result["encryptActionUsed"] = $this->getEncryptActionUsed();
        $result["keysArchived"] = $this->getKeysArchived();
        $result["corporationsArchived"] = $this->getCorporationsArchived();
        $result["archivedInfo"] = $this->getArchivedInfo();
        return $result;
    }

    function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0;
    }


    //////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    ////////////
    function getCustomPlayerAfter(int $player_id): int
    {
        return $this->isClockwise() ? $this->getPlayerAfter($player_id) : $this->getPlayerBefore($player_id);
    }

    function getCustomPlayerBefore(int $player_id): int
    {
        return $this->isClockwise() ? $this->getPlayerBefore($player_id) : $this->getPlayerAfter($player_id);
    }

    function getSingleCardInLocation(object $deck, string $location, int $location_arg = null, $showError = true): ?array
    {
        $location_cards = $deck->getCardsInLocation($location, $location_arg);

        $card = array_shift($location_cards);

        if ($card === null && $showError) {
            throw new BgaVisibleSystemException("Card not found");
        }

        return $card;
    }

    function hideCard(array $card, bool $hideType = false, string | int $fake_id = null): array
    {
        $hidden_card = array(
            "id" => $card["id"],
            "location" => $card["location"],
            "type" => $card["type"],
        );

        if ($hideType) {
            unset($hidden_card["type"]);
        }

        if ($fake_id) {
            $hidden_card["id"] = $fake_id . ":" . $card["location_arg"];
        }

        return $hidden_card;
    }

    function hideCards(array $cards, bool $hideType = false, bool $hideId = false): array
    {
        $hidden_cards = array();

        $fake_ids = range(count($cards) * -1 - 1, -2);

        foreach ($cards as $card_id => $card) {
            $fake_id = null;

            if ($hideId) {
                $random_key = array_rand($fake_ids);
                $fake_id = $fake_ids[$random_key];
                unset($fake_ids[$random_key]);

                $card_id = $fake_id;
            }

            $hidden_cards[$card_id] = $this->hideCard($card, $hideType, $fake_id);
        }

        return $hidden_cards;
    }

    //getters
    function getHackerByColor(string $color): array
    {
        $hacker_card = null;

        foreach ($this->hackers as $hacker_id => $hacker) {
            if ($color === $hacker["color"]) {
                $hacker_card = array(
                    "id" => $hacker_id,
                    "type" => $hacker["name"],
                    "type_arg" => $hacker_id,
                );
            }
        }

        if (!$hacker_card) {
            throw new BgaVisibleSystemException("Unable to get hacker by color");
        }

        return $hacker_card;
    }

    function getHackers(): array
    {
        $players = $this->loadPlayersBasicInfos();

        $hackers = array();

        foreach ($players as $player_id => $player) {
            $player_color = $this->getPlayerColorById($player_id);

            $hackers[$player_id] = $this->getHackerByColor($player_color);
        }

        return $hackers;
    }

    function getCardOfTheWeek(): int
    {
        $week = $this->getGameStateValue("week");

        return $this->getCardOfTheWeek[$week];
    }

    function getKeyByCorporation(int $corporation_id): array
    {
        $sql = "SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg 
        from corporationKey WHERE card_type='$corporation_id'";

        $key_card = $this->getObjectFromDB($sql);

        if ($key_card === null) {
            throw new BgaVisibleSystemException("Key card not found");
        }

        return $key_card;
    }

    function getKeysOnTable(): array
    {
        $key_cards = $this->key_cards->getCardsInLocation("table");

        return $key_cards;
    }

    function getCorporationDecks(): array
    {
        $corporation_cards = array();

        foreach ($this->corporations as $corporation_id => $corporation) {
            $corporation_cards[$corporation_id] = $this->corporation_cards->getCardsInLocation("deck:" . $corporation_id);
        }

        return $corporation_cards;
    }

    function getActionsInMyHand(int $player_id): array
    {
        $hand = $this->action_cards->getCardsInLocation("hand", $player_id);

        return $hand;
    }

    function getActionsInOtherHands(int $current_player_id): array
    {
        $hands = array();
        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            if ($player_id !== $current_player_id) {
                $cards = $this->action_cards->getCardsInLocation("hand", $player_id);

                $hands[$player_id] = $cards;
            }
        }

        return $hands;
    }

    function getDeckOfInformations(): array
    {
        $deck = $this->information_cards->getCardsInLocation("deck");

        $hidden_deck = $this->hideCards($deck);

        return $hidden_deck;
    }

    function getInfoInMyHand(int $player_id): array
    {
        $hand = $this->information_cards->getCardsInLocation("hand", $player_id);

        return $hand;
    }

    function getInfoInOtherHands(int $current_player_id): array
    {
        $hands = array();

        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            if ($player_id !== $current_player_id) {
                $cards = $this->information_cards->getCardsInLocation("hand", $player_id);

                $hands[$player_id] = $this->hideCards($cards, true, true);
            }
        }

        return $hands;
    }

    function getCardsPlayedByMe(int $player_id): array
    {
        $played_cards = array();

        $played_cards["action"] = $this->getSingleCardInLocation($this->action_cards, "played", $player_id, false);
        $played_cards["info"] = $this->getSingleCardInLocation($this->information_cards, "played", $player_id, false);

        return $played_cards;
    }

    function getInfoStoredByMe(int $player_id): array
    {
        $stored_cards = array();

        $visible_cards = $this->information_cards->getCardsInLocation("stored", $player_id);
        $encrypted_cards = $this->information_cards->getCardsInLocation("encrypted", $player_id);

        $stored_cards = $visible_cards + $encrypted_cards;

        return $stored_cards;
    }

    function getInfoStoredByOthers(int $current_player_id): array
    {
        $stored_cards = array();

        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            if ($player_id == $current_player_id) {
                continue;
            }

            $visible_cards = $this->information_cards->getCardsInLocation("stored", $player_id);
            $encrypted_card = $this->getSingleCardInLocation($this->information_cards, "encrypted", $player_id, false);

            $stored_cards[$player_id]["visible"] = $visible_cards;
            $stored_cards[$player_id]["encrypted"] = $encrypted_card ? $this->hideCard($encrypted_card, true, -1) : null;
        }

        return $stored_cards;
    }

    function getStoredInfoByCorporation(int $corporation_id, int $player_id): array
    {
        $stored_cards = $this->information_cards->getCardsOfTypeInLocation($corporation_id, null, "stored", $player_id);

        return $stored_cards;
    }

    function getActionsDiscarded(): array
    {
        $discarded_cards = array();

        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $discarded_cards[$player_id] = $this->action_cards->getCardsInLocation("discard", $player_id);
        }

        return $discarded_cards;
    }

    function getEncryptActionUsed(): array
    {
        $encrypt_cards = array();

        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $encrypt_cards[$player_id] = $this->getSingleCardInLocation($this->action_cards, "encrypted", $player_id, false);
        }

        return $encrypt_cards;
    }

    function getKeysArchived(int $player_id = null): array
    {
        if ($player_id) {
            return $this->key_cards->getCardsInLocation("archived", $player_id);
        }

        $archived_keys = array();

        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $archived_keys[$player_id] = $this->key_cards->getCardsInLocation("archived", $player_id);
        }

        return $archived_keys;
    }

    function getCorporationsArchived(int $player_id = null): array
    {
        if ($player_id) {
            return $this->corporation_cards->getCardsInLocation("archived", $player_id);
        }

        $archived_corporations = array();

        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $archived_corporations[$player_id] = $this->corporation_cards->getCardsInLocation("archived", $player_id);
        }

        return $archived_corporations;
    }

    function getArchivedInfo(int $player_id = null): array
    {
        if ($player_id) {
            return $this->information_cards->getCardsInLocation("archived", $player_id);
        }

        $archived_informations = array();

        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $archived_informations[$player_id] = $this->information_cards->getCardsInLocation("archived", $player_id);
        }

        return $archived_informations;
    }

    //checkers
    function isClockwise(): bool
    {
        $week = $this->getGameStateValue("week");

        return $week != 2;
    }

    function cardInHand(array $card, int $player_id): bool
    {
        return $card["location"] === "hand" && $card["location_arg"] == $player_id;
    }

    function canSteal(int $corporation_id, int $current_player_id): bool
    {
        $canSteal = false;
        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            if ($current_player_id != $player_id) {
                if ($this->getStoredInfoByCorporation($corporation_id, $player_id)) {
                    $canSteal = true;
                    break;
                }
            }
        }

        return $canSteal;
    }

    // action cards
    function download(array $info_card, int $player_id): void
    {
        $this->information_cards->moveCard($info_card["id"], "stored", $player_id);
        $info_card = $this->getSingleCardInLocation($this->information_cards, "stored", $player_id);

        $this->notifyAllPlayers(
            "store",
            "",
            array(
                "player_id" => $player_id,
                "infoCard" => $info_card,
            )
        );
    }

    function encrypt(array $info_card, int $player_id): void
    {
        $this->information_cards->moveCard($info_card["id"], "encrypted", $player_id);
        $info_card = $this->getSingleCardInLocation($this->information_cards, "encrypted", $player_id);

        $this->notifyAllPlayers(
            "store",
            "",
            array(
                "player_id" => $player_id,
                "infoCard" => $this->hideCard($info_card, true, -1),
                "encrypt" => true
            )
        );
    }
    function sendToRight(array $info_card, int $player_id): void
    {
        $recipient_id = $this->getPlayerAfter($player_id);

        $this->information_cards->moveCard($info_card["id"], "stored", $recipient_id);
        $info_card = $this->getSingleCardInLocation($this->information_cards, "stored", $recipient_id);

        $info_id = $info_card["type_arg"];
        $corporation_id = intval($info_card["type"]);

        $this->notifyAllPlayers(
            "store",
            clienttranslate('${player_name2} sends a ${info_label} of ${corporation_label} to ${player_name}'),
            array(
                "player_id" => $recipient_id,
                "player_name" => $this->getPlayerNameById($recipient_id),
                "player_id2" => $player_id,
                "player_name2" => $this->getPlayerNameById($player_id),
                "info_label" => $this->informations[$info_id]["name"],
                "corporation_label" => $this->corporations[$corporation_id],
                "infoCard" => $info_card,
            )
        );
    }

    function sendToLeft(array $info_card, int $player_id): void
    {
        $recipient_id = $this->getPlayerBefore($player_id);

        $this->information_cards->moveCard($info_card["id"], "stored", $recipient_id);
        $info_card = $this->getSingleCardInLocation($this->information_cards, "stored", $recipient_id);

        $info_id = $info_card["type_arg"];
        $corporation_id = intval($info_card["type"]);

        $this->notifyAllPlayers(
            "store",
            clienttranslate('${player_name2} sends a ${info_label} of ${corporation_label} to ${player_name}'),
            array(
                "player_id" => $recipient_id,
                "player_name" => $this->getPlayerNameById($recipient_id),
                "player_id2" => $player_id,
                "player_name2" => $this->getPlayerNameById($player_id),
                "info_label" => $this->informations[$info_id]["name"],
                "corporation_label" => $this->corporations[$corporation_id],
                "infoCard" => $info_card,
            )
        );
    }

    // operations
    function revealPlayed(int $player_id): array
    {
        $action_card = $this->getSingleCardInLocation($this->action_cards, "played", $player_id);
        $info_card = $this->getSingleCardInLocation($this->information_cards, "played", $player_id);

        $action_id = $action_card["type_arg"];
        $info_id = $info_card["type_arg"];
        $corporation_id = intval($info_card["type"]);

        $encrypt = $action_id == 2;

        if ($encrypt) {
            $info_card = $this->hideCard($info_card, true, -1);
        }

        $message =  $encrypt ? clienttranslate('${player_name} combines a ${action_label} to an information')
            : clienttranslate('${player_name} combines a ${action_label} to a ${info_label} of ${corporation_label}');

        $this->notifyAllPlayers(
            "playCards",
            $message,
            array(
                "i18n" => array("action_label", "info_label", "corporation_label"),
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "action_label" => $this->actions[$action_id],
                "info_label" => $encrypt ? null : $this->informations[$info_id]["name"],
                "corporation_label" => $encrypt ? null : $this->corporations[$corporation_id],
                "actionCard" => $action_card,
                "infoCard" => $info_card,
                "encrypt" => $encrypt
            )
        );

        return array("action" => $action_card, "info" => $info_card);
    }

    function activateActionCard(int $player_id)
    {
        $action_card = $this->getSingleCardInLocation($this->action_cards, "played", $player_id);
        $info_card = $this->getSingleCardInLocation($this->information_cards, "played", $player_id);

        $action_id = $action_card["type_arg"];

        if ($action_id == 1) {
            $this->download($info_card, $player_id);
        }

        if ($action_id == 2) {
            $this->encrypt($info_card, $player_id);
        }

        if ($action_id == 3) {
            $this->sendToRight($info_card, $player_id);
        }

        if ($action_id == 4) {
            $this->sendToLeft($info_card, $player_id);
        }

        if ($action_id == 2) {
            $this->action_cards->moveCard($action_card["id"], "encrypted", $player_id);
        } else {
            $this->action_cards->moveCard($action_card["id"], "discard", $player_id);
        }

        $this->notifyAllPlayers(
            "activateActionCard",
            "",
            array(
                "player_id" => $player_id,
                "actionCard" => $action_card,
                "encrypt" => $action_id == 2
            )
        );
    }

    function revealEncrypted(int $player_id): void
    {
        $info_card = $this->getSingleCardInLocation($this->information_cards, "encrypted", $player_id);

        $this->information_cards->moveCard($info_card["id"], "stored", $player_id);
        $this->action_cards->moveAllCardsInLocation("encrypted", "discard", $player_id, $player_id);

        $info_card = $this->information_cards->getCard($info_card["id"]);

        $info_id = $info_card["type_arg"];
        $corporation_id = intval($info_card["type"]);

        $this->notifyAllPlayers(
            "revealEncrypted",
            clienttranslate('${player_name} reveals his encrypted card... It&apos;s a ${info_label} of ${corporation_label}!'),
            array(
                "i18n" => array("info_label", "corporation_label"),
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "info_label" => $this->informations[$info_id]["name"],
                "corporation_label" => $this->corporations[$corporation_id],
                "infoCard" => $info_card
            )
        );
    }

    function storePoints(int $corporation_id): array
    {
        $players = $this->loadPlayersBasicInfos();

        $stored_points = array();

        foreach ($players as $player_id => $player) {
            $stored_cards = $this->information_cards->getCardsInLocation("stored", $player_id);

            $points = 0;

            foreach ($stored_cards as $card_id => $card) {
                if ($card["type"] == $corporation_id) {
                    $points += $card["type_arg"];
                }
            }

            $stored_points[$player_id] = $points;

            if ($points > 0) {
                $this->notifyAllPlayers(
                    "storePoints",
                    clienttranslate('${player_name} scores ${points} points for ${corporation_label}'),
                    array(
                        "i18n" => array("corporation_label"),
                        "player_id" => $player_id,
                        "player_name" => $this->getPlayerNameById($player_id),
                        "corporation_label" => $this->corporations[$corporation_id],
                        "points" => $points
                    )
                );
            }
        }


        return $stored_points;
    }

    function obtainCorporation(int $corporation_id, int $player_id)
    {
        $corporation_card = $this->corporation_cards->pickCardForLocation("deck:" . $corporation_id, "archived", $player_id);
        $card_value = $corporation_card["type_arg"];

        $this->notifyAllPlayers(
            "obtainCorporation",
            clienttranslate('${player_name} obtains the Corporation card of ${corporation_label} with value ${card_value}'),
            array(
                "i18n" => array("corporation_label"),
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "corporation_label" => $this->corporations[$corporation_id],
                "card_value" => $card_value,
                "corporationCard" => $corporation_card,
            )
        );

        if ($card_value != $this->getCardOfTheWeek()) {
            $this->obtainCorporation($corporation_id, $player_id, true);
        }
    }

    function obtainKey(int $corporation_id, int $player_id)
    {
        $key_card = $this->getKeyByCorporation($corporation_id);
        $this->key_cards->moveCard($key_card["id"], "archived", $player_id);

        $this->notifyAllPlayers(
            "obtainKey",
            clienttranslate('${player_name} obtains the Key card of ${corporation_label}'),
            array(
                "i18n" => array("corporation_label"),
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "corporation_label" => $this->corporations[$corporation_id],
                "keyCard" => $key_card,
            )
        );
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Player actions
    //////////// 

    function playCards(int $action_card_id, int $info_card_id)
    {
        $this->checkAction("playCards");

        $player_id = $this->getCurrentPlayerId();

        $action_card = $this->action_cards->getCard($action_card_id);
        $info_card = $this->information_cards->getCard($info_card_id);

        if (!$action_card || !$info_card) {
            throw new BgaVisibleSystemException("Card not found");
        }

        if (!$this->cardInHand($action_card, $player_id) || !$this->cardInHand($info_card, $player_id)) {
            throw new BgaVisibleSystemException("This card is not in your hand");
        }

        $action_id = $action_card["type_arg"];
        $info_id = $info_card["type_arg"];
        $corporation_id = intval($info_card["type"]);

        $this->action_cards->moveCard($action_card_id, "played", $player_id);
        $this->information_cards->moveCard($info_card_id, "played", $player_id);

        $this->notifyPlayer(
            $player_id,
            "playCards",
            clienttranslate('You combine a ${action_label} to a ${info_label} of ${corporation_label}'),
            array(
                "i18n" => array("action_label", "info_label", "corporation_label"),
                "player_id" => $player_id,
                "action_label" => $this->actions[$action_id],
                "info_label" => $this->informations[$info_id]["name"],
                "corporation_label" => $this->corporations[$corporation_id],
                "actionCard" => $action_card,
                "infoCard" => $info_card
            )
        );

        // if ($this->getPlayersNumber() === 2) {
        //     $this->gamestate->nextPrivateState($player_id, "discardCard");
        //     return;
        // }

        $this->gamestate->setPlayerNonMultiactive($player_id, "endOfDay");
    }

    function changeMind()
    {
        $this->gamestate->checkPossibleAction("changeMind");

        $player_id = $this->getCurrentPlayerId();

        $played_cards = $this->getCardsPlayedByMe($player_id);

        $action_card = $played_cards["action"];
        $info_card = $played_cards["info"];

        $this->action_cards->moveCard($action_card["id"], "hand", $player_id);
        $this->information_cards->moveCard($info_card["id"], "hand", $player_id);

        $this->notifyPlayer(
            $player_id,
            "changeMind",
            clienttranslate("You change your mind and become active again"),
            array(
                "player_id" => $player_id,
                "actionCard" => $action_card,
                "infoCard" => $info_card,
            )
        );

        $this->gamestate->setPlayersMultiactive(array($player_id), "error");

        $this->gamestate->initializePrivateState($player_id);
    }

    function stealCard($card_id)
    {
        $this->checkAction("stealCard");

        $player_id = $this->getActivePlayerId();
        $corporation_id = $this->getGameStateValue("currentCorporation") - 1;

        $card = $this->information_cards->getCard($card_id);
        $opponent_id = $card["location_arg"];
        $info_id = $card["type_arg"];

        if ($card["location"] !== "stored" || $card["type"] != $corporation_id || $opponent_id == $player_id) {
            throw new BgaVisibleSystemException("You can't take this card");
        }

        $this->information_cards->moveCard($card_id, "archived", $player_id);

        $this->notifyAllPlayers(
            "stealCard",
            clienttranslate('${player_name} takes a of ${corporation_label} from ${player_name2} '),
            array(
                "i18n" => array("info_label", "corporation_label"),
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "player_id2" => $opponent_id,
                "player_name2" => $this->getPlayerNameById($opponent_id),
                "info_label" => $this->informations[$info_id]["name"],
                "corporation_label" => $this->corporations[$corporation_id],
                "infoCard" => $card
            )
        );

        $this->gamestate->nextState("infoArchiving");
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state arguments
    ////////////

    function arg_stealCard()
    {
        $corporation_id = $this->getGameStateValue("currentCorporation") - 1;
        $corporation_label = $this->corporations[$corporation_id];

        return array(
            "i18n" => array("corporation_label"),
            "corporation_label" => $corporation_label,
            "corporationId" => $corporation_id
        );
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state actions
    ////////////

    function st_day()
    {
        $this->gamestate->setAllPlayersMultiactive();

        $this->gamestate->initializePrivateStateForAllActivePlayers();
    }

    function st_endOfDay()
    {
        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $this->revealPlayed($player_id);

            $this->activateActionCard($player_id);
        }

        //tests
        if (count($this->getActionsInMyHand($player_id)) <= 3) {
            $this->gamestate->nextState("infoArchiving");
            return;
        }

        $this->gamestate->nextState("nextDay");
    }

    function st_infoArchiving()
    {
        $players = $this->loadPlayersBasicInfos();

        $corporation_id = $this->getGameStateValue("currentCorporation");
        $this->incGameStateValue("currentCorporation", 1);

        if ($corporation_id > 6) {
            $this->gamestate->nextState("betweenWeeks");
            return;
        }

        $corporation_label = $this->corporations[$corporation_id];

        if ($corporation_id == 1) {
            foreach ($players as $player_id => $player) {
                $this->revealEncrypted($player_id);
            }
        }

        $corporation_points = $this->storePoints($corporation_id);
        $most_points = max($corporation_points);

        if (!$most_points) {
            $this->notifyAllPlayers(
                "tie",
                clienttranslate('No player scored points with ${corporation_label} this round'),
                array(
                    "i18n" => array("corporation_label"),
                    "corporation_label" => $corporation_label
                )
            );
            $this->gamestate->nextState("infoArchiving");
            return;
        }

        $winners = array_keys($corporation_points, $most_points);

        foreach ($winners as $player_id) {
            unset($corporation_points[$player_id]);
        }

        if (count($winners) >= 2) {
            $this->notifyAllPlayers(
                "tie",
                clienttranslate('Two or more players are tied in the first-place for ${corporation_label}'),
                array(
                    "i18n" => array("corporation_label"),
                    "corporation_label" => $corporation_label
                )
            );
            //tests
            $this->gamestate->nextState("infoArchiving");
            return;
        }

        $first = array_shift($winners);

        $this->obtainCorporation($corporation_id, $first);

        $second_most_points = max($corporation_points);
        $runner_ups = array_keys($corporation_points, $second_most_points);

        if (count($runner_ups) >= 2 && $second_most_points) {
            $this->notifyAllPlayers(
                "tie",
                clienttranslate('Two or more players are tied in the second-place for ${corporation_label}'),
                array(
                    "i18n" => array("corporation_label"),
                    "corporation_label" => $corporation_label
                )
            );

            //tests
            // $this->gamestate->nextState("infoArchiving");
            // return;
        }

        $second = array_shift($runner_ups);

        if (!$second_most_points) {
            $second = $first;
        }

        $this->obtainKey($corporation_id, $second);

        if ($this->canSteal($corporation_id, $first)) {
            $this->gamestate->changeActivePlayer($first);
            $this->gamestate->nextState("stealCard");
            return;
        }

        $this->gamestate->nextState("infoArchiving");
    }

    function st_betweenWeeks()
    {
        $this->setGameStateValue("currentCorporation", 1);
        $this->incGameStateValue("week", 1);

        $this->notifyAllPlayers(
            "flipHackers",
            clienttranslate('A new week starts. The game direction is now ${direction}'),
            array(
                "i18n" => array("direction"),
                "direction" => $this->isClockwise() ? clienttranslate("clockwise") : clienttranslate('counterclockwise')
            )
        );

        $this->gamestate->nextState("nextWeek");
    }

    function zombieTurn($state, $active_player)
    {
        $statename = $state['name'];

        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState("zombiePass");
                    break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive($active_player, '');

            return;
        }

        throw new feException("Zombie mode not supported at this game state: " . $statename);
    }

    ///////////////////////////////////////////////////////////////////////////////////:
    ////////// DB upgrade
    //////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */

    function upgradeTableDb($from_version)
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345

        // Example:
        //        if( $from_version <= 1404301345 )
        //        {
        //            // ! important ! Use DBPREFIX_<table_name> for all tables
        //
        //            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
        //            $this->applyDbUpgradeToAllDB( $sql );
        //        }
        //        if( $from_version <= 1405061421 )
        //        {
        //            // ! important ! Use DBPREFIX_<table_name> for all tables
        //
        //            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
        //            $this->applyDbUpgradeToAllDB( $sql );
        //        }
        //        // Please add your future database scheme changes here
        //
        //


    }
}
