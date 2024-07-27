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
            "currentCorporation" => 11,
            "day" => 12,
            "corporationFirst" => 13,
            "corporationSecond" => 14,
        ));

        $this->information_cards = $this->getNew("module.common.deck");
        $this->information_cards->init("information");

        $this->action_cards = $this->getNew("module.common.deck");
        $this->action_cards->init("action");

        $this->corporation_cards = $this->getNew("module.common.deck");
        $this->corporation_cards->init("corporation");

        $this->key_cards = $this->getNew("module.common.deck");
        $this->key_cards->init("corporationKey");

        // EXPERIMENTAL to avoid deadlocks
        $this->bSelectGlobalsForUpdate = true;
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
        $this->setGameStateInitialValue("day", 1);

        $this->initStat("player", "points1", 0);
        $this->initStat("player", "points2", 0);
        $this->initStat("player", "points3", 0);
        $this->initStat("player", "points4", 0);
        $this->initStat("player", "points5", 0);
        $this->initStat("player", "wastedPoints", 0);
        $this->initStat("player", "cardsStolenBy", 0);
        $this->initStat("player", "cardsStolenFrom", 0);
        $this->initStat("player", "corporationFirst", 0);
        $this->initStat("player", "corporationSecond", 0);
        $this->initStat("player", "corporationsActivated", 0);

        if (count($players) == 4) {
            $this->initStat("player", "points6", 0);
        }

        $corporation_cards = array();
        $key_cards = array();

        foreach ($this->corporations($players) as $corporation_id => $corporation) {
            $key_cards[] = array(
                "type" => $corporation_id,
                "type_arg" => 1,
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

        foreach ($this->corporations($players) as $corporation_id => $corporation) {
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

        foreach ($this->corporations($players) as $corporation_id => $corporation) {
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

        $result["gameVersion"] = intval($this->gamestate->table_globals[300]);

        $result["players"] = $this->getCollectionFromDb("SELECT player_id id, player_score score, player_zombie zombie FROM player ");
        $result["clockwise"] = $this->isClockwise();
        $result["sidePlayers"] = $this->getSidePlayers();
        $result["actions"] = $this->actions;
        $result["corporations"] = $this->corporations();
        $result["informations"] = $this->informations;
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
        $result["discardedActions"] = $this->getActionsDiscarded();
        $result["encryptActionUsed"] = $this->getEncryptActionUsed();
        $result["archivedKeys"] = $this->getArchivedKeys();
        $result["archivedCorporations"] = $this->getArchivedCorporations();
        $result["archivedInfo"] = $this->getArchivedInfo();
        $result["storedCounters"] = $this->getStoredCounters();
        return $result;
    }

    function getGameProgression()
    {
        $day = $this->getGameStateValue("day");
        $progression = 100 / 15 * ($day - 1);

        return round($progression);
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    ////////////

    public function checkVersion(int $clientVersion): void
    {
        if ($clientVersion != intval($this->gamestate->table_globals[300])) {
            throw new BgaVisibleSystemException($this->_("A new version of this game is now available. Please reload the page (F5)."));
        }
    }

    //scoring helpers
    function dbGetScore($player_id)
    {
        return $this->getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id='$player_id'");
    }

    function dbSetScore($player_id, $count)
    {
        $this->DbQuery("UPDATE player SET player_score='$count' WHERE player_id='$player_id'");
    }

    function dbSetAuxScore($player_id, $score)
    {
        $this->DbQuery("UPDATE player SET player_score_aux=$score WHERE player_id='$player_id'");
    }

    function dbIncScore($player_id, $inc)
    {
        $count = $this->dbGetScore($player_id);
        if ($inc != 0) {
            $count += $inc;
            $this->dbSetScore($player_id, $count);
        }

        return $count;
    }

    //deck helpers
    function getSingleCardInLocation(object $deck, string $location, int $location_arg = null, $showError = true): ?array
    {
        $location_cards = $deck->getCardsInLocation($location, $location_arg);

        $card = array_shift($location_cards);

        if ($card === null && $showError) {
            throw new BgaVisibleSystemException("Card not found: " . $location);
        }

        return $card;
    }

    ////////////////////////////////////////////////////

    function loadPlayersNoZombies(): array
    {
        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            if ($player["player_zombie"] == 1) {
                unset($players[$player_id]);
            }
        }

        return $players;
    }

    function corporations(array $players = null): array
    {
        if (!key_exists(6, $this->corporations)) {
            return $this->corporations;
        }

        $playersNumber = $players ? count($players) : $this->getPlayersNumber();

        if ($playersNumber < 4) {
            unset($this->corporations[6]);
        }

        return $this->corporations;
    }

    private function getRandomKey(array $array)
    {
        $size = count($array);
        $rand = random_int(0, $size - 1);
        $slice = array_slice($array, $rand, 1, true);
        foreach ($slice as $key => $value) {
            return $key;
        }
    }

    function getPlayerAfterNoZombie($player_id): int
    {
        $player_after = $this->getPlayerAfter($player_id);

        if (!key_exists($player_after, $this->loadPlayersNoZombies())) {
            //recursive
            $player_after = $this->getPlayerAfter($player_after);
        }

        return $player_after;
    }

    function getPlayerBeforeNoZombie($player_id): int
    {
        $player_before = $this->getPlayerBefore($player_id);

        if (!key_exists($player_before, $this->loadPlayersNoZombies())) {
            //recursive
            $player_before = $this->getPlayerBefore($player_before);
        }

        return $player_before;
    }

    function getNextPlayer(int $player_id): int
    {
        return $this->isClockwise() ? $this->getPlayerAfterNoZombie($player_id) : $this->getPlayerBeforeNoZombie($player_id);
    }

    function hideCard(array $card, bool $hideType = false, $fake_id = null, string $fake_location = null): array
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

        if ($fake_location) {
            $hidden_card["location"] = $fake_location;
        }

        return $hidden_card;
    }

    function hideCards(array $cards, bool $hideType = false, bool $hideId = false, string $fake_location = null): array
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

            $hidden_cards[$card_id] = $this->hideCard($card, $hideType, $fake_id, $fake_location);
        }

        return $hidden_cards;
    }

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

    function getCardOfTheWeek(): int
    {
        $week = $this->getGameStateValue("week");

        return $this->cardOfTheWeek[$week];
    }

    //gamedatas getters

    function getSidePlayers(): array
    {
        $players = $this->loadPlayersNoZombies();

        $side_players = array();

        foreach ($players as $player_id => $player) {
            $side_players[$player_id]["left"] = $this->getPlayerBeforeNoZombie($player_id);
            $side_players[$player_id]["right"] = $this->getPlayerAfterNoZombie($player_id);
        }

        return $side_players;
    }

    function getHackers(): array
    {
        $players = $this->loadPlayersBasicInfos();

        $hackers = array();

        foreach ($players as $player_id => $player) {
            $player_color = $this->getPlayerColorById($player_id);

            $hacker = $this->getHackerByColor($player_color);
            $hacker["location_arg"] = $player_id;

            $hackers[$player_id] = $hacker;
        }

        return $hackers;
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

        foreach ($this->corporations() as $corporation_id => $corporation) {
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
                $cards += $this->action_cards->getCardsInLocation("played", $player_id);


                $hands[$player_id] = $this->hideCards($cards, false, false, "hand");
            }
        }

        return $hands;
    }

    function getDeckOfInformations(): array
    {
        $deck = $this->information_cards->getCardsInLocation("deck");

        $hidden_deck = $this->hideCards($deck, true);

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
                $cards += $this->information_cards->getCardsInLocation("played", $player_id);

                $hands[$player_id] = $this->hideCards($cards, true, true, "hand");
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

    function getArchivedKeys(int $player_id = null): array
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

    function getArchivedCorporations(int $player_id = null): array
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

    function getStoredCounters(?int $current_player_id = null): array
    {
        $stored_counters = array();

        foreach ($this->corporations() as $corporation_id => $corporation) {
            foreach ($this->storePoints($corporation_id, false) as $player_id => $points) {
                $stored_counters[$player_id][$corporation_id] = $points;
            };
        }

        if ($current_player_id) {
            return $stored_counters[$current_player_id];
        }

        return $stored_counters;
    }

    //corporation tie break
    function setTiedPlayer(int $player_id, int $tied = 1): void
    {
        $this->DbQuery("UPDATE player SET player_tied=$tied WHERE player_id='$player_id'");
    }

    function getTiedPlayers(): array
    {
        $tied_players = $this->getCollectionFromDB("SELECT player_id id from player WHERE player_tied=1 AND player_zombie=0");

        return $tied_players;
    }

    //player stole info
    function setPlayerStole(int $player_id, int $stole = 1): void
    {
        $this->DbQuery("UPDATE player SET player_stole=$stole WHERE player_id='$player_id'");
    }

    function getPlayerStole($player_id): int
    {
        $stole = $this->getUniqueValueFromDB("SELECT player_stole from player WHERE player_id='$player_id'");

        return $stole;
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

    function canSteal(int $corporation_id, int $current_player_id): array
    {
        $canSteal = array();
        $players = $this->loadPlayersBasicInfos();

        $stole = $this->getPlayerStole($current_player_id);

        if ($stole) {
            return array();
        }

        foreach ($players as $player_id => $player) {
            if ($current_player_id != $player_id) {
                $stored_info = $this->getStoredInfoByCorporation($corporation_id, $player_id);
                if ($stored_info) {
                    $canSteal += $stored_info;
                }
            }
        }

        return $canSteal;
    }

    // action cards
    function download(array $info_card, array $action_card, int $player_id): void
    {
        $this->information_cards->moveCard($info_card["id"], "stored", $player_id);

        $action_id = 1;
        $corporation_id = intval($info_card["type"]);
        $info_id = $info_card["type_arg"];

        $this->notifyAllPlayers(
            "storeInfo",
            clienttranslate('${player_name} combines the ${action_label} to the ${info_label} of ${corporation_label}'),
            array(
                "preserve" => array("corporationId", "informationId", "actionId", "hackerId"),
                "i18n" => array("action_label", "info_label"),
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "action_label" => $this->actions[$action_id],
                "info_label" => $this->informations[$info_id]["name"],
                "corporation_label" => $this->corporations()[$corporation_id],
                "corporationId" => $corporation_id,
                "informationId" => $info_id,
                "actionId" => $action_id,
                "hackerId" => $action_card["type"],
                "actionCard" => $action_card,
                "infoCard" => $info_card,
                "storedCounters" => $this->getStoredCounters($player_id),
            )
        );
    }

    function encrypt(array $info_card, array $action_card, int $player_id): void
    {
        $this->information_cards->moveCard($info_card["id"], "encrypted", $player_id);
        $info_card = $this->getSingleCardInLocation($this->information_cards, "encrypted", $player_id);

        $action_id = 2;
        $corporation_id = intval($info_card["type"]);
        $info_id = $info_card["type_arg"];

        $this->notifyPlayer(
            $player_id,
            "storeInfoPrivate",
            clienttranslate('You combine the ${action_label} to the ${info_label} of ${corporation_label}'),
            array(
                "preserve" => array("corporationId", "informationId", "actionId", "hackerId"),
                "i18n" => array("action_label", "info_label"),
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "action_label" => $this->actions[$action_id],
                "info_label" => $this->informations[$info_id]["name"],
                "corporation_label" => $this->corporations()[$corporation_id],
                "corporationId" => $corporation_id,
                "informationId" => $info_id,
                "actionId" => $action_id,
                "hackerId" => $action_card["type"],
                "actionCard" => $action_card,
                "infoCard" => $info_card,
                "encrypt" => true,
            )
        );

        $this->notifyAllPlayers(
            "storeInfo",
            clienttranslate('${player_name} combines the ${action_label} to an Information card'),
            array(
                "preserve" => array("actionId", "hackerId"),
                "i18n" => array("action_label"),
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "action_label" => $this->actions[$action_id],
                "actionId" => $action_id,
                "hackerId" => $action_card["type"],
                "actionCard" => $action_card,
                "infoCard" => $this->hideCard($info_card, true, -1),
                "encrypt" => true,
                "storedCounters" => $this->getStoredCounters($player_id),
            )
        );
    }
    function sendToRight(array $info_card, array $action_card, int $player_id): void
    {
        $recipient_id = $this->getPlayerAfterNoZombie($player_id);

        $this->information_cards->moveCard($info_card["id"], "stored", $recipient_id);

        $action_id = 3;
        $info_id = $info_card["type_arg"];
        $corporation_id = intval($info_card["type"]);

        $this->notifyAllPlayers(
            "storeInfo",
            clienttranslate('${player_name2} uses the ${action_label} to send the ${info_label} of ${corporation_label} to ${player_name}'),
            array(
                "preserve" => array("corporationId", "informationId", "actionId", "hackerId"),
                "i18n" => array("action_label", "info_label"),
                "player_id" => $recipient_id,
                "player_name" => $this->getPlayerNameById($recipient_id),
                "player_id2" => $player_id,
                "player_name2" => $this->getPlayerNameById($player_id),
                "action_label" => $this->actions[$action_id],
                "info_label" => $this->informations[$info_id]["name"],
                "corporation_label" => $this->corporations()[$corporation_id],
                "corporationId" => $corporation_id,
                "informationId" => $info_id,
                "actionId" => $action_id,
                "hackerId" => $action_card["type"],
                "actionCard" => $action_card,
                "infoCard" => $info_card,
                "storedCounters" => $this->getStoredCounters($recipient_id),
            )
        );
    }

    function sendToLeft(array $info_card, array $action_card, int $player_id): void
    {
        $recipient_id = $this->getPlayerBeforeNoZombie($player_id);

        $this->information_cards->moveCard($info_card["id"], "stored", $recipient_id);

        $action_id = 4;
        $info_id = $info_card["type_arg"];
        $corporation_id = intval($info_card["type"]);

        $this->notifyAllPlayers(
            "storeInfo",
            clienttranslate('${player_name2} uses the ${action_label} to send the ${info_label} of ${corporation_label} to ${player_name}'),
            array(
                "preserve" => array("corporationId", "informationId", "actionId", "hackerId"),
                "i18n" => array("action_label", "info_label"),
                "player_id" => $recipient_id,
                "player_name" => $this->getPlayerNameById($recipient_id),
                "player_id2" => $player_id,
                "player_name2" => $this->getPlayerNameById($player_id),
                "action_label" => $this->actions[$action_id],
                "info_label" => $this->informations[$info_id]["name"],
                "corporation_label" => $this->corporations()[$corporation_id],
                "corporationId" => $corporation_id,
                "informationId" => $info_id,
                "actionId" => $action_id,
                "hackerId" => $action_card["type"],
                "actionCard" => $action_card,
                "infoCard" => $info_card,
                "storedCounters" => $this->getStoredCounters($recipient_id),
            )
        );
    }

    // operations
    // function revealPlayed(int $player_id): array
    // {
    //     $action_card = $this->getSingleCardInLocation($this->action_cards, "played", $player_id);
    //     $info_card = $this->getSingleCardInLocation($this->information_cards, "played", $player_id);

    //     $action_id = $action_card["type_arg"];
    //     $info_id = $info_card["type_arg"];
    //     $corporation_id = intval($info_card["type"]);

    //     $encrypt = $action_id == 2;

    //     if ($encrypt) {
    //         $info_card = $this->hideCard($info_card, true, -1);
    //     }

    //     $message =  $encrypt ? clienttranslate('${player_name} combines the ${action_label} to an Information card')
    //         : clienttranslate('${player_name} combines the ${action_label} to the ${info_label} of ${corporation_label}');

    //     $this->notifyAllPlayers(
    //         "revealPlayed",
    //         $message,
    //         array(
    //             "preserve" => array("corporationId", "informationId", "actionId", "hackerId"),
    //             "i18n" => array("action_label", "info_label"),
    //             "player_id" => $player_id,
    //             "player_name" => $this->getPlayerNameById($player_id),
    //             "action_label" => $this->actions[$action_id],
    //             "info_label" => $encrypt ? null : $this->informations[$info_id]["name"],
    //             "corporation_label" => $encrypt ? null : $this->corporations()[$corporation_id],
    //             "corporationId" => $corporation_id,
    //             "informationId" => $info_id,
    //             "actionId" => $action_id,
    //             "hackerId" => $action_card["type"],
    //             "actionCard" => $action_card,
    //             "infoCard" => $info_card,
    //             "encrypt" => $encrypt
    //         )
    //     );

    //     return array("action" => $action_card, "info" => $info_card);
    // }

    function activateActionCard(int $player_id)
    {
        $action_card = $this->getSingleCardInLocation($this->action_cards, "played", $player_id);
        $info_card = $this->getSingleCardInLocation($this->information_cards, "played", $player_id);

        $action_id = $action_card["type_arg"];

        if ($action_id == 1) {
            $this->download($info_card, $action_card, $player_id);
        }

        if ($action_id == 2) {
            $this->encrypt($info_card, $action_card, $player_id);
        }

        if ($action_id == 3) {
            $this->sendToRight($info_card, $action_card, $player_id);
        }

        if ($action_id == 4) {
            $this->sendToLeft($info_card, $action_card, $player_id);
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

    function passHands()
    {
        $players = $this->loadPlayersNoZombies();

        $senders = array();

        foreach ($players as $player_id => $player) {
            $recipient_id = $this->getNextPlayer($player_id);

            if ($recipient_id == $player_id) {
                return;
            }

            $this->information_cards->moveAllCardsInLocation("hand", "preHand", $player_id, $recipient_id);

            $senders[$recipient_id] = $player_id;
        }

        foreach ($senders as $player_id => $sender_id) {
            $this->information_cards->moveAllCardsInLocation("preHand", "hand", $player_id, $player_id);

            $info_cards = $this->information_cards->getCardsInLocation("hand", $player_id);

            $new_info[$player_id] = $this->hideCards($info_cards, true, true);

            $this->notifyPlayer(
                $player_id,
                "receiveNewInfo",
                "",
                array(
                    "player_id" => $player_id,
                    "player_id2" => $sender_id,
                    "infoCards" => $info_cards,
                    "removeFromSender" => $this->getPlayersNumber() == 2,
                )
            );
        }

        $this->notifyAllPlayers(
            "passHands",
            clienttranslate('All players pass the remaining Information ${direction}'),
            array(
                "i18n" => array("direction"),
                "direction" => $this->isClockwise() ? clienttranslate("clockwise") : clienttranslate("counterclockwise"),
                "senders" => $senders,
                "newInfo" => $new_info,
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
            clienttranslate('${player_name} reveals his encrypted card... It&apos;s the ${info_label} of ${corporation_label}!'),
            array(
                "preserve" => array("corporationId", "informationId"),
                "i18n" => array("info_label"),
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "info_label" => $this->informations[$info_id]["name"],
                "corporation_label" => $this->corporations()[$corporation_id],
                "corporationId" => $corporation_id,
                "informationId" => $info_id,
                "infoCard" => $info_card,
                "storedCounters" => $this->getStoredCounters($player_id)
            )
        );
    }

    function storePoints(int $corporation_id, $notify = true): array
    {
        $players = $this->loadPlayersNoZombies();

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

            if ($notify && $points > 0) {
                $this->notifyAllPlayers(
                    "storePoints",
                    clienttranslate('${player_name} scores ${points} points for ${corporation_label}'),
                    array(
                        "preserve" => array("corporationId"),
                        "player_id" => $player_id,
                        "player_name" => $this->getPlayerNameById($player_id),
                        "corporation_label" => $this->corporations()[$corporation_id],
                        "corporationId" => $corporation_id,
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
                "preserve" => array("corporationId"),
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "card_value" => $card_value,
                "corporation_label" => $this->corporations()[$corporation_id],
                "corporationId" => $corporation_id,
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

        if ($player_id == $key_card["location_arg"]) {
            $this->notifyAllPlayers(
                "keepKey",
                clienttranslate('${player_name} keeps the Key card of ${corporation_label}'),
                array(
                    "preserve" => array("corporationId"),
                    "player_id" => $player_id,
                    "player_name" => $this->getPlayerNameById($player_id),
                    "corporation_label" => $this->corporations()[$corporation_id],
                    "corporationId" => $corporation_id,
                    "keyCard" => $key_card,
                )
            );

            return;
        }

        $this->key_cards->moveCard($key_card["id"], "archived", $player_id);

        $this->notifyAllPlayers(
            "obtainKey",
            clienttranslate('${player_name} obtains the Key card of ${corporation_label}'),
            array(
                "preserve" => array("corporationId"),
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "corporation_label" => $this->corporations()[$corporation_id],
                "corporationId" => $corporation_id,
                "keyCard" => $key_card,
            )
        );
    }

    function discardActivator(int $corporation_id, int $player_id): bool
    {
        $activators = array();

        for ($value = 0; $value <= 4; $value += 2) {
            $corporations_of_value =  $this->corporation_cards->getCardsOfTypeInLocation($corporation_id, $value, "archived", $player_id);
            $activators[$value] = array_shift($corporations_of_value);
        }

        foreach ($activators as $value => $card) {
            if ($value == 4) {
                continue;
            }

            if ($card) {
                $this->corporation_cards->moveCard($card["id"], "discard");

                $this->notifyAllPlayers(
                    "discardActivator",
                    clienttranslate('${player_name} discards the Corporation card of value ${value} to activate ${corporation_label}'),
                    array(
                        "preserve" => array("corporationId"),
                        "player_name" => $this->getPlayerNameById($player_id),
                        "value" => $card["type_arg"],
                        "corporation_label" => $this->corporations()[$corporation_id],
                        "corporationId" => $corporation_id
                    )
                );

                return true;
            }
        }

        $workers = $this->information_cards->getCardsOfTypeInLocation($corporation_id, 2, "archived", $player_id);
        $worker = array_shift($workers);

        if ($worker) {
            $this->information_cards->moveCard($worker["id"], "discard");

            $this->notifyAllPlayers(
                "discardActivator",
                clienttranslate('${player_name} discards the Corporation card of value ${value} to activate ${corporation_label}'),
                array(
                    "preserve" => array("corporationId"),
                    "player_name" => $this->getPlayerNameById($player_id),
                    "value" => $worker["type_arg"],
                    "corporation_label" => $this->corporations()[$corporation_id],
                    "corporationId" => $corporation_id
                )
            );

            return true;
        }

        $last_activator = $activators[4];

        if ($last_activator) {
            $this->corporation_cards->moveCard($last_activator["id"], "discard");

            $this->notifyAllPlayers(
                "discardActivator",
                clienttranslate('${player_name} discards the Corporation card of value ${value} to activate ${corporation_label}'),
                array(
                    "preserve" => array("corporationId"),
                    "player_name" => $this->getPlayerNameById($player_id),
                    "value" => $last_activator["type_arg"],
                    "corporation_label" => $this->corporations()[$corporation_id],
                    "corporationId" => $corporation_id
                )
            );

            return true;
        }

        $wasted_points = $this->computeArchivedPoints($corporation_id, $player_id, false);
        $this->setStat($wasted_points, "wastedPoints", $player_id);

        return false;
    }

    function computeArchivedPoints(int $corporation_id, int $player_id, $updateDb = true): int
    {
        $points = 0;

        $archived_corporations = $this->corporation_cards->getCardsOfTypeInLocation($corporation_id, null, "archived", $player_id);

        foreach ($archived_corporations as $card) {
            $points += $card["type_arg"];
        }

        $archived_info = $this->information_cards->getCardsOfTypeInLocation($corporation_id, null, "archived", $player_id);

        foreach ($archived_info as $card) {
            $points += $card["type_arg"];
        }

        if (!$updateDb) {
            return $points;
        }

        $total_points = $this->dbIncScore($player_id, $points);

        $this->notifyAllPlayers(
            "computeArchivedPoints",
            clienttranslate('${player_name} scores ${points} points with ${corporation_label} from archived cards'),
            array(
                "preserve" => array("corporationId"),
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "player_color" => $this->getPlayerColorById($player_id),
                "corporation_label" => $this->corporations()[$corporation_id],
                "corporationId" => $corporation_id,
                "corporationCards" => $archived_corporations,
                "infoCards" => $archived_info,
                "points" => $points,
                "totalPoints" => $total_points
            )
        );

        $this->incStat($points, "points" . $corporation_id, $player_id);

        return $points;
    }

    function computeKeyPoint(int $corporation_id, int $player_id)
    {
        $archived_keys = $this->key_cards->getCardsOfTypeInLocation($corporation_id, null, "archived", $player_id);

        foreach ($archived_keys as $card) {
            $total_points = $this->dbIncScore($player_id, 1);

            $this->notifyAllPlayers(
                "computeKeyPoint",
                clienttranslate('${player_name} scores 1 point with ${corporation_label} from its Key'),
                array(
                    "preserve" => array("corporationId"),
                    "player_id" => $player_id,
                    "player_name" => $this->getPlayerNameById($player_id),
                    "player_color" => $this->getPlayerColorById($player_id),
                    "corporation_label" => $this->corporations()[$corporation_id],
                    "corporationId" => $corporation_id,
                    "keyCard" => $card,
                    "totalPoints" => $total_points
                )
            );

            $this->incStat(1, "points" . $corporation_id, $player_id);
        }
    }

    function drawSingleNewInfo()
    {
        $players = $this->loadPlayersNoZombies();

        foreach ($players as $player_id => $player) {
            $info_card = $this->information_cards->pickCard("deck", $player_id);

            $info_id = $info_card["type_arg"];
            $corporation_id = intval($info_card["type"]);

            $hand_info_count = $this->information_cards->countCardsInLocation("hand", $player_id);

            $new_info[$player_id] = array($this->hideCard($info_card, true, $hand_info_count - 12));

            $removed_from_deck[$player_id] = $this->hideCards(array($info_card), true);

            $this->notifyPlayer(
                $player_id,
                "drawNewInfoPrivate",
                clienttranslate('You draw the ${info_label} of ${corporation_label} from the deck'),
                array(
                    "preserve" => array("corporationId", "informationId"),
                    "i18n" => array("info_label"),
                    "player_id" => $player_id,
                    "info_label" => $this->informations[$info_id]["name"],
                    "corporation_label" => $this->corporations[$corporation_id],
                    "corporationId" => $corporation_id,
                    "informationId" => $info_id,
                    "infoCards" => array($info_card),
                )
            );
        }

        $this->notifyAllPlayers(
            "drawNewInfo",
            clienttranslate('All other players draw a new Information card from the deck'),
            array(
                "newInfo" => $new_info,
                "removedFromDeck" => $removed_from_deck
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
            throw new BgaVisibleSystemException("Played card not found");
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
            clienttranslate('You combine the ${action_label} to the ${info_label} of ${corporation_label}'),
            array(
                "preserve" => array("corporationId", "informationId", "actionId", "hackerId"),
                "i18n" => array("action_label", "info_label"),
                "player_id" => $player_id,
                "action_label" => $this->actions[$action_id],
                "info_label" => $this->informations[$info_id]["name"],
                "corporation_label" => $this->corporations()[$corporation_id],
                "corporationId" => $corporation_id,
                "informationId" => $info_id,
                "actionId" => $action_id,
                "hackerId" => $action_card["type"],
                "actionCard" => $action_card,
                "infoCard" => $info_card
            )
        );

        if ($this->getPlayersNumber() == 2 && $this->information_cards->countCardsInLocation("hand", $player_id) > 1) {
            $this->gamestate->nextPrivateState($player_id, "discardInfo");
            return;
        }

        $this->gamestate->setPlayerNonMultiactive($player_id, "betweenDays");
    }

    function discardInfo($card_id)
    {
        $this->checkAction("discardInfo");

        $player_id = $this->getCurrentPlayerId();

        $this->information_cards->moveCard($card_id, "pre_discard", $player_id);
        $info_card = $this->information_cards->getCard($card_id);

        $info_id = $info_card["type_arg"];
        $corporation_id = $info_card["type"];

        $this->notifyPlayer(
            $player_id,
            "discardInfoPrivate",
            clienttranslate('You discard the ${info_label} of ${corporation_label}'),
            array(
                "preserve" => array("corporationId", "informationId"),
                "i18n" => array("info_label"),
                "player_id" => $player_id,
                "info_label" => $this->informations[$info_id]["name"],
                "corporation_label" => $this->corporations[$corporation_id],
                "corporationId" => $corporation_id,
                "informationId" => $info_id,
                "infoCard" => $info_card,
            )
        );

        $this->notifyAllPlayers(
            "discardInfo",
            "",
            array(
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id)
            )
        );

        $this->gamestate->setPlayerNonMultiactive($player_id, "betweenDays");
    }

    function changeMindPlayed()
    {
        $this->gamestate->checkPossibleAction("changeMindPlayed");

        $player_id = $this->getCurrentPlayerId();

        $played_cards = $this->getCardsPlayedByMe($player_id);

        $action_card = $played_cards["action"];
        $info_card = $played_cards["info"];

        $this->action_cards->moveCard($action_card["id"], "hand", $player_id);
        $this->information_cards->moveCard($info_card["id"], "hand", $player_id);

        $this->notifyPlayer(
            $player_id,
            "changeMindPlayed",
            clienttranslate("You change your mind and may play cards again"),
            array(
                "player_id" => $player_id,
                "actionCard" => $action_card,
                "infoCard" => $info_card,
            )
        );

        $this->gamestate->setPlayersMultiactive(array($player_id), "error");

        $this->gamestate->initializePrivateState($player_id);
    }

    function changeMindDiscarded()
    {
        $this->gamestate->checkPossibleAction("changeMindDiscarded");

        $player_id = $this->getCurrentPlayerId();

        $info_card = $this->getSingleCardInLocation($this->information_cards, "pre_discard", $player_id);
        $this->information_cards->moveCard($info_card["id"], "hand", $player_id);

        $this->notifyPlayer(
            $player_id,
            "changeMindDiscarded",
            clienttranslate("You change your mind and may discard an Information again"),
            array(
                "player_id" => $player_id,
                "infoCard" => $info_card,
            )
        );

        $this->gamestate->setPlayersMultiactive(array($player_id), "error");

        $this->gamestate->initializePrivateState($player_id);
        $this->gamestate->nextPrivateState($player_id, "changeMindDiscarded");
    }

    function stealInfo($card_id, bool $auto = false)
    {
        if (!$auto) {
            $this->checkAction("stealInfo");
        }

        $player_id = $this->getActivePlayerId();
        $corporation_id = $this->getGameStateValue("currentCorporation");

        $card = $this->information_cards->getCard($card_id);
        $opponent_id = $card["location_arg"];
        $info_id = $card["type_arg"];

        if ($card["location"] !== "stored" || $card["type"] != $corporation_id || $opponent_id == $player_id) {
            throw new BgaVisibleSystemException("You can't take this card");
        }

        $this->information_cards->moveCard($card_id, "archived", $player_id);

        $message = $auto ? clienttranslate('${player_name} automatically takes the ${info_label} of ${corporation_label} from ${player_name2} and archives it')
            : clienttranslate('${player_name} takes the ${info_label} of ${corporation_label} from ${player_name2} and archives it');

        $this->notifyAllPlayers(
            "archiveInfo",
            $message,
            array(
                "preserve" => array("corporationId", "informationId"),
                "i18n" => array("info_label"),
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "player_id2" => $opponent_id,
                "player_name2" => $this->getPlayerNameById($opponent_id),
                "info_label" => $this->informations[$info_id]["name"],
                "corporation_label" => $this->corporations()[$corporation_id],
                "corporationId" => $corporation_id,
                "informationId" => $info_id,
                "infoCards" => array($card_id => $card),
                "storedCounters" => $this->getStoredCounters($opponent_id),
                "isStolen" => true
            )
        );

        $this->setPlayerStole($player_id);

        $this->incStat(1, "cardsStolenBy", $player_id);
        $this->incStat(1, "cardsStolenFrom", $opponent_id);

        $this->gamestate->nextState("infoArchiving");
    }

    function breakFirstTie($tie_winner, $tie_runner, bool $auto = false)
    {
        $player_id = $this->getActivePlayerId();

        if (!$auto) {
            $this->checkAction("breakFirstTie");
        } else {
            $this->notifyAllPlayers(
                "zombieBreakTie",
                clienttranslate('${player_name} is offline. The second-place of this Corporation is randomly decided'),
                array(
                    "player_id" => $player_id,
                    "player_name" => $this->getPlayerNameById($player_id)
                )
            );
        }

        $corporation_id = $this->getGameStateValue("currentCorporation");

        $tied_players = $this->getTiedPlayers($corporation_id);

        if (!key_exists($tie_winner, $tied_players) || !key_exists($tie_runner, $tied_players)) {
            throw new BgaVisibleSystemException("You can't pick this player to obtain the Corporation card or the Key");
        }

        if ($tie_winner == $tie_runner) {
            throw new BgaVisibleSystemException("You can't pick the same player to obtain the Corporation card and the Key");
        }

        $corporation_card = $this->corporation_cards->pickCardForLocation("deck:" . $corporation_id, "archived", $tie_winner);

        $this->notifyAllPlayers(
            "obtainCorporation",
            clienttranslate('${player_name2} picks ${player_name} to obtain the Corporation card of ${corporation_label}'),
            array(
                "preserve" => array("corporationId"),
                "player_id" => $tie_winner,
                "player_name" => $this->getPlayerNameById($tie_winner),
                "player_id2" => $player_id,
                "player_name2" => $this->getPlayerNameById($player_id),
                "corporation_label" => $this->corporations()[$corporation_id],
                "corporationId" => $corporation_id,
                "corporationCard" => $corporation_card
            )
        );

        $key_card = $this->getKeyByCorporation($corporation_id);
        $this->key_cards->moveCard($key_card["id"], "archived", $tie_runner);

        $this->notifyAllPlayers(
            "obtainKey",
            clienttranslate('${player_name2} picks ${player_name} to obtain the Key of ${corporation_label}'),
            array(
                "preserve" => array("corporationId"),
                "player_id" => $tie_runner,
                "player_name" => $this->getPlayerNameById($tie_runner),
                "player_id2" => $player_id,
                "player_name2" => $this->getPlayerNameById($player_id),
                "corporation_label" => $this->corporations()[$corporation_id],
                "corporationId" => $corporation_id,
                "keyCard" => $key_card
            )
        );

        foreach ($tied_players as $player_id => $player) {
            $this->setTiedPlayer($player_id, 0);
        }

        $this->setGameStateValue("corporationFirst", $tie_winner);
        $this->incStat(1, "corporationFirst", $tie_winner);

        $this->setGameStateValue("corporationSecond", $tie_runner);
        $this->incStat(1, "corporationSecond", $tie_runner);

        $this->gamestate->nextState("infoArchiving");
    }

    function breakSecondTie($tie_runner, bool $auto = false)
    {
        $player_id = $this->getActivePlayerId();

        if (!$auto) {
            $this->checkAction("breakSecondTie");
        } else {
            $this->notifyAllPlayers(
                "zombieBreakTie",
                clienttranslate('${player_name} is offline. The first and second-place of this Corporation are randomly decided'),
                array(
                    "player_id" => $player_id,
                    "player_name" => $this->getPlayerNameById($player_id)
                )
            );
        }

        $corporation_id = $this->getGameStateValue("currentCorporation");

        $tied_players = $this->getTiedPlayers($corporation_id);

        if (!key_exists($tie_runner, $tied_players)) {
            throw new BgaVisibleSystemException("You can't pick this player to obtain the Key");
        }

        $key_card = $this->getKeyByCorporation($corporation_id);

        $this->key_cards->moveCard($key_card["id"], "archived", $tie_runner);

        $this->notifyAllPlayers(
            "obtainKey",
            clienttranslate('${player_name2} picks ${player_name} to obtain the Key of ${corporation_label}'),
            array(
                "preserve" => array("corporationId"),
                "player_id" => $tie_runner,
                "player_name" => $this->getPlayerNameById($tie_runner),
                "player_id2" => $player_id,
                "player_name2" => $this->getPlayerNameById($player_id),
                "corporation_label" => $this->corporations()[$corporation_id],
                "corporationId" => $corporation_id,
                "keyCard" => $key_card
            )
        );

        foreach ($tied_players as $player_id => $player) {
            $this->setTiedPlayer($player_id, 0);
        }

        $this->setGameStateValue("corporationSecond", $tie_runner);
        $this->incStat(1, "corporationSecond", $tie_runner);

        $this->gamestate->nextState("infoArchiving");
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state arguments
    ////////////

    function arg_stealInfo()
    {
        $corporation_id = $this->getGameStateValue("currentCorporation");
        $corporation_label = $this->corporations()[$corporation_id];

        return array(
            "corporation_label" => $corporation_label,
            "corporationId" => $corporation_id
        );
    }

    function arg_breakFirstTie()
    {
        $corporation_id = $this->getGameStateValue("currentCorporation");
        $corporation_label = $this->corporations()[$corporation_id];

        $tied_players = $this->getTiedPlayers();

        return array(
            "corporation_label" => $corporation_label,
            "corporationId" => $corporation_id,
            "tiedPlayers" => $tied_players
        );
    }

    function arg_breakSecondTie()
    {
        $corporation_id = $this->getGameStateValue("currentCorporation");
        $corporation_label = $this->corporations()[$corporation_id];

        $tied_players = $this->getTiedPlayers();

        return array(
            "corporation_label" => $corporation_label,
            "corporationId" => $corporation_id,
            "tiedPlayers" => $tied_players
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

    function st_betweenDays()
    {
        $players = $this->loadPlayersNoZombies();

        $this->incGameStateValue("day", 1);

        $this->information_cards->moveAllCardsInLocation("pre_discard", "discard");

        foreach ($players as $player_id => $player) {
            // $this->revealPlayed($player_id);

            $this->activateActionCard($player_id);
        }

        $hand_actions_count = $this->action_cards->countCardsInLocation("hand");

        if ($hand_actions_count == 0) {
            $this->gamestate->nextState("infoArchiving");
            return;
        }

        $this->passHands();

        if ($this->getPlayersNumber() == 2) {
            $this->drawSingleNewInfo();
        }

        $this->gamestate->nextState("nextDay");
    }

    function st_infoArchiving()
    {
        $players = $this->loadPlayersNoZombies();

        $corporation_id = $this->getGameStateValue("currentCorporation");

        if ($corporation_id > count($this->corporations())) {
            $this->gamestate->nextState("betweenWeeks");
            return;
        }

        $corporation_label = $this->corporations()[$corporation_id];

        $first = $this->getGameStateValue("corporationFirst");
        $second = $this->getGameStateValue("corporationSecond");

        if (!$first) {
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
                    clienttranslate('No player scores points for ${corporation_label} this week'),
                    array(
                        "preserve" => array("corporationId"),
                        "corporation_label" => $corporation_label,
                        "corporationId" => $corporation_id,
                    )
                );

                $this->incGameStateValue("currentCorporation", 1);

                $this->setGameStateValue("corporationFirst", 0);
                $this->setGameStateValue("corporationSecond", 0);

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
                        "preserve" => array("corporationId"),
                        "corporation_label" => $corporation_label,
                        "corporationId" => $corporation_id
                    )
                );

                $key_card = $this->getKeyByCorporation($corporation_id);

                if ($key_card["location"] === "archived") {
                    foreach ($winners as $player_id) {
                        $this->setTiedPlayer($player_id);
                    }

                    $this->gamestate->changeActivePlayer($key_card["location_arg"]);
                    $this->gamestate->nextState("breakFirstTie");
                    return;
                }

                $this->incGameStateValue("currentCorporation", 1);

                $this->setGameStateValue("corporationFirst", 0);
                $this->setGameStateValue("corporationSecond", 0);

                $this->gamestate->nextState("infoArchiving");
                return;
            }

            $first = array_shift($winners);

            $this->obtainCorporation($corporation_id, $first);

            $this->setGameStateValue("corporationFirst", $first);
            $this->incStat(1, "corporationFirst", $first);
        }

        if (!$second) {
            $second_most_points = max($corporation_points);
            $runner_ups = array_keys($corporation_points, $second_most_points);

            if (count($runner_ups) >= 2 && $second_most_points) {
                foreach ($runner_ups as $player_id) {
                    $this->setTiedPlayer($player_id);
                }

                $this->notifyAllPlayers(
                    "tie",
                    clienttranslate('Two or more players are tied in the second-place for ${corporation_label}'),
                    array(
                        "preserve" => array("corporationId"),
                        "corporation_label" => $corporation_label,
                        "corporationId" => $corporation_id
                    )
                );

                $this->gamestate->changeActivePlayer($first);
                $this->gamestate->nextState("breakSecondTie");
                return;
            }

            $second = array_shift($runner_ups);

            if (!$second_most_points) {
                $second = $first;
            }

            $this->obtainKey($corporation_id, $second);

            $this->setGameStateValue("corporationSecond", $second);
            $this->incStat(1, "corporationSecond", $second);
        }

        $first_steal = $this->canSteal($corporation_id, $first);
        if ($first_steal) {
            $this->gamestate->changeActivePlayer($first);

            if (count($first_steal) > 1) {
                $this->gamestate->nextState("stealInfo");
                return;
            }

            $stolen_info = array_shift($first_steal);
            $this->stealInfo($stolen_info["id"], true);
        }

        $second_steal = $this->canSteal($corporation_id, $second);
        if ($second_steal) {
            $this->gamestate->changeActivePlayer($second);

            if (count($second_steal) > 1) {
                $this->gamestate->nextState("stealInfo");
                return;
            }

            $stolen_info = array_shift($second_steal);
            $this->stealInfo($stolen_info["id"], true);
        }

        $this->incGameStateValue("currentCorporation", 1);

        $this->setGameStateValue("corporationFirst", 0);
        $this->setGameStateValue("corporationSecond", 0);

        foreach ($players as $player_id => $player) {
            $this->setPlayerStole($player_id, 0);
        }

        $this->gamestate->nextState("infoArchiving");
    }

    function st_betweenWeeks()
    {
        if ($this->getGameStateValue("week") == 3) {
            $this->gamestate->nextState("finalScoring");
            return;
        }

        $this->setGameStateValue("currentCorporation", 1);
        $this->incGameStateValue("week", 1);

        $players = $this->loadPlayersNoZombies();

        foreach ($players as $player_id => $player) {
            $info_card = $this->getSingleCardInLocation($this->information_cards, "hand", $player_id);

            $corporation_id = $info_card["type"];
            $info_id = $info_card["type_arg"];

            $this->notifyAllPlayers(
                "discardLastInfo",
                clienttranslate('${player_name} discards the ${info_label} of ${corporation_label}'),
                array(
                    "i18n" => array("info_label"),
                    "player_id" => $player_id,
                    "player_name" => $this->getPlayerNameById($player_id),
                    "info_label" => $this->informations[$info_id]["name"],
                    "corporation_label" => $this->corporations[$corporation_id],
                    "corporationId" => $corporation_id,
                    "infoCard" => $info_card
                )
            );
        }

        $this->notifyAllPlayers(
            "resetActions",
            clienttranslate('All players take their action cards back to their hands'),
            array()
        );

        $new_info = array();

        foreach ($players as $player_id => $player) {
            $this->action_cards->moveAllCardsInLocation("discard", "hand", $player_id, $player_id);
            $this->information_cards->moveAllCardsInLocation("hand", "discard", $player_id);

            $info_cards = $this->information_cards->pickCards(6, "deck", $player_id);
            $new_info[$player_id] = $this->hideCards($info_cards, true, true);
            $removed_from_deck = $this->hideCards($info_cards, true);

            $this->notifyPlayer(
                $player_id,
                "drawNewInfoPrivate",
                "",
                array(
                    "player_id" => $player_id,
                    "infoCards" => $info_cards,
                )
            );
        }

        $this->notifyAllPlayers(
            "drawNewInfo",
            clienttranslate('Each player draws 6 new Information cards'),
            array(
                "newInfo" => $new_info,
                "removedFromDeck" => $removed_from_deck
            )

        );

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

    function st_finalScoring()
    {
        $players = $this->loadPlayersNoZombies();

        $points = array();

        foreach ($players as $player_id => $player) {
            $information_cards = $this->information_cards->getCardsInLocation("stored", $player_id);
            $this->information_cards->moveAllCardsInLocation("stored", "archived", $player_id, $player_id);

            $this->notifyAllPlayers(
                "archiveInfo",
                clienttranslate('${player_name} archives all cards from his play area'),
                array(
                    "preserve" => array("corporationId"),
                    "i18n" => array("info_label"),
                    "player_id" => $player_id,
                    "player_name" => $this->getPlayerNameById($player_id),
                    "infoCards" => $information_cards,
                    "storedCounters" => $this->getStoredCounters($player_id),
                )
            );

            foreach ($this->corporations() as $corporation_id => $corporation) {
                $points[$player_id][$corporation_id] = 0;

                $this->computeKeyPoint($corporation_id, $player_id);

                if (!$this->discardActivator($corporation_id, $player_id)) {
                    continue;
                };

                $this->incStat(1, "corporationsActivated", $player_id);
                $this->computeArchivedPoints($corporation_id, $player_id);
            }

            $aux_score = 100 * $this->getStat("corporationsActivated", $player_id) + $this->key_cards->countCardsInLocation("archived", $player_id);
            $this->dbSetAuxScore($player_id, $aux_score);
        }

        $this->gamestate->nextState("gameEnd");
    }

    function zombieTurn($state, $active_player)
    {
        $statename = $state['name'];

        $isZombie = $this->information_cards->countCardsInLocation("hand", $active_player) == 0;

        $this->setTiedPlayer($active_player, 0);

        if (!$isZombie) {
            $info_cards = $this->information_cards->getCardsInLocation("hand", $active_player);

            $this->notifyAllPlayers(
                "zombieTurn",
                clienttranslate('All Information cards in the hand of ${player_name} are shuffled back to the deck. His Key cards go back to the table'),
                array(
                    "player_id" => $active_player,
                    "player_name" => $this->getPlayerNameById($active_player),
                    "infoCards" => $this->hideCards($info_cards, true)
                )
            );

            $this->information_cards->moveAllCardsInLocation("hand", "deck", $active_player, $active_player);
            $this->action_cards->moveAllCardsInLocation("hand", "discard", $active_player, $active_player);
            $this->key_cards->moveAllCardsInLocation("archived", "table", $active_player);

            $this->information_cards->shuffle("deck");
        }

        if ($state['type'] === "activeplayer") {
            if ($statename === "breakFirstTie") {
                $tied_players = $this->getTiedPlayers();

                $tie_winner = $this->getRandomKey($tied_players);

                unset($tied_players[$tie_winner]);

                $tie_runner = $this->getRandomKey($tied_players);

                foreach ($tied_players as $player_id => $player) {
                    $this->setTiedPlayer($player_id, 0);
                }

                $this->breakFirstTie($tie_winner, $tie_runner, true);
                return;
            }

            if ($statename === "breakSecondTie") {
                $tied_players = $this->getTiedPlayers();

                $tie_runner = $this->getRandomKey($tied_players);

                foreach ($tied_players as $player_id => $player) {
                    $this->setTiedPlayer($player_id, 0);
                }

                $this->breakSecondTie($tie_runner, true);
                return;
            }

            $this->gamestate->nextState("zombiePass");
            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
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
