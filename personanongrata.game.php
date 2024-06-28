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
            "direction" => 11,
        ));

        $this->information_cards = $this->getNew("module.common.deck");
        $this->information_cards->init("information");

        $this->action_cards = $this->getNew("module.common.deck");
        $this->action_cards->init("action");

        $this->corporation_cards = $this->getNew("module.common.deck");
        $this->corporation_cards->init("corporation");
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

        //corporations
        $corporation_cards = array();

        for ($value = 0; $value <= 4; $value++) {
            if ($value === 3) {
                continue;
            }

            foreach ($this->corporations as $corporation_id => $corporation) {
                $corporation_cards[] = array(
                    "type" => $corporation_id,
                    "type_arg" => $value,
                    "nbr" => 1
                );
            }
        }

        $this->corporation_cards->createCards($corporation_cards, "deck");

        $keys = $this->getCardsByTypeArg("corporation", 1);
        $key_ids = array_keys($keys);

        $this->corporation_cards->moveCards($key_ids, "keysontable");

        foreach ($this->corporations as $corporation_id => $corporation) {
            for ($value = 0; $value <= 4; $value++) {
                if ($value === 1 || $value === 3) {
                    continue;
                }

                $cards = $this->getCollectionFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, 
                card_location_arg location_arg from corporation WHERE card_type_arg='$value' AND card_location='deck' AND card_type='$corporation_id'");

                foreach ($cards as $card_id => $card) {
                    $this->corporation_cards->insertCardOnExtremePosition($card_id, "corpdeck:" . $corporation_id, true);
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
        $result["keys"] = $this->getKeys();
        $result["corporationDecks"] = $this->getCorporationDecks();
        $result["actionsInMyHand"] = $this->getActionsInMyHand($current_player_id);
        $result["actionsInOtherHands"] = $this->getActionsInOtherHands($current_player_id);
        $result["deckOfInformations"] = $this->getDeckOfInformations();
        $result["infoInMyHand"] = $this->getInfoInMyHand($current_player_id);
        $result["infoInOtherHands"] = $this->getInfoInOtherHands($current_player_id);
        $result["cardsPlayedByMe"] = $this->getCardsPlayedByMe($current_player_id);
        $result["infoArchivedByMe"] = $this->getInfoArchivedByMe($current_player_id);
        $result["infoArchivedByOthers"] = $this->getInfoArchivedByOthers($current_player_id);
        $result["actionsDiscarded"] = $this->getActionsDiscarded($current_player_id);

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

    function getCardsByTypeArg(string $table, int $type_arg): array | null
    {
        $sql = "SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg 
        from $table WHERE card_type_arg='$type_arg'";

        $result = $this->getCollectionFromDB($sql);

        return $result;
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

        $fake_ids = range(count($cards) * -1, -1);

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

    function getKeys(): array
    {
        $keys = $this->corporation_cards->getCardsInLocation("keysontable");

        return $keys;
    }

    function getCorporationDecks(): array
    {
        $corporation_cards = array();

        foreach ($this->corporations as $corporation_id => $corporation) {
            $corporation_cards[$corporation_id] = $this->corporation_cards->getCardsInLocation("corpdeck:" . $corporation_id);
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

    function getInfoArchivedByMe(int $player_id): array
    {
        $archived_cards = array();

        $visible_cards = $this->information_cards->getCardsInLocation("archived", $player_id);
        $encrypted_cards = $this->information_cards->getCardsInLocation("encrypted", $player_id);

        $archived_cards = $visible_cards + $encrypted_cards;

        return $archived_cards;
    }

    function getInfoArchivedByOthers(int $current_player_id): array
    {
        $archived_cards = array();

        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            if ($player_id == $current_player_id) {
                continue;
            }

            $visible_cards = $this->information_cards->getCardsInLocation("archived", $player_id);
            $encrypted_card = $this->getSingleCardInLocation($this->information_cards, "encrypted", $player_id, false);

            $archived_cards[$player_id]["visible"] = $visible_cards;
            $archived_cards[$player_id]["encrypted"] = $encrypted_card ? $this->hideCard($encrypted_card, true, -1) : null;
        }

        return $archived_cards;
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

    //checkers
    function isClockwise(): bool
    {
        $week = $this->getGameStateValue("week");

        return !($week % 2);
    }

    function cardInHand(array $card, int $player_id): bool
    {
        return $card["location"] === "hand" && $card["location_arg"] == $player_id;
    }

    // action cards
    function download(array $info_card, int $player_id): void
    {
        $this->information_cards->moveCard($info_card["id"], "archived", $player_id);

        $this->notifyAllPlayers(
            "archive",
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

        $this->notifyAllPlayers(
            "archive",
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

        $this->information_cards->moveCard($info_card["id"], "archived", $recipient_id);

        $info_id = $info_card["type_arg"];
        $corp_id = intval($info_card["type"]);

        $this->notifyAllPlayers(
            "archive",
            clienttranslate('${player_name2} sends a ${info_label} of ${corp_label} to ${player_name}'),
            array(
                "player_id" => $recipient_id,
                "player_name" => $this->getPlayerNameById($recipient_id),
                "player_id2" => $player_id,
                "player_name2" => $this->getPlayerNameById($player_id),
                "info_label" => $this->informations[$info_id]["name"],
                "corp_label" => $this->corporations[$corp_id],
                "infoCard" => $info_card,
            )
        );
    }

    function sendToLeft(array $info_card, int $player_id): void
    {
        $recipient_id = $this->getPlayerBefore($player_id);

        $this->information_cards->moveCard($info_card["id"], "archived", $recipient_id);

        $info_id = $info_card["type_arg"];
        $corp_id = intval($info_card["type"]);

        $this->notifyAllPlayers(
            "archive",
            clienttranslate('${player_name2} sends a ${info_label} of ${corp_label} to ${player_name}'),
            array(
                "player_id" => $recipient_id,
                "player_name" => $this->getPlayerNameById($recipient_id),
                "player_id2" => $player_id,
                "player_name2" => $this->getPlayerNameById($player_id),
                "info_label" => $this->informations[$info_id]["name"],
                "corp_label" => $this->corporations[$corp_id],
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
        $corp_id = intval($info_card["type"]);

        $encrypt = $action_id == 2;

        if ($encrypt) {
            $info_card = $this->hideCard($info_card, true, -1);
        }

        $message =  $encrypt ? clienttranslate('${player_name} combines a ${action_label} to an information')
            : clienttranslate('${player_name} combines a ${action_label} to a ${info_label} of ${corp_label}');

        $this->notifyAllPlayers(
            "playCards",
            $message,
            array(
                "i18n" => array("action_label", "info_label", "corp_label"),
                "player_id" => $player_id,
                "player_name" => $this->getPlayerNameById($player_id),
                "action_label" => $this->actions[$action_id],
                "info_label" => $encrypt ? null : $this->informations[$info_id]["name"],
                "corp_label" => $encrypt ? null : $this->corporations[$corp_id],
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

        $this->action_cards->moveCard($action_card["id"], "discard", $player_id);

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
        $corp_id = intval($info_card["type"]);

        $this->action_cards->moveCard($action_card_id, "played", $player_id);
        $this->information_cards->moveCard($info_card_id, "played", $player_id);

        $this->notifyPlayer(
            $player_id,
            "playCards",
            clienttranslate('You combine a ${action_label} to a ${info_label} of ${corp_label}'),
            array(
                "i18n" => array("action_label", "info_label", "corp_label"),
                "player_id" => $player_id,
                "action_label" => $this->actions[$action_id],
                "info_label" => $this->informations[$info_id]["name"],
                "corp_label" => $this->corporations[$corp_id],
                "actionCard" => $action_card,
                "infoCard" => $info_card
            )
        );

        // if ($this->getPlayersNumber() === 2) {
        //     $this->gamestate->nextPrivateState($player_id, "discardCard");
        //     return;
        // }

        $this->gamestate->setPlayerNonMultiactive($player_id, "infoArchiving");
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

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state arguments
    ////////////

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state actions
    ////////////

    function st_day()
    {
        $this->gamestate->setAllPlayersMultiactive();

        $this->gamestate->initializePrivateStateForAllActivePlayers();
    }

    function st_infoArchiving()
    {
        $players = $this->loadPlayersBasicInfos();
        $current_player_id = $this->getCurrentPlayerId();

        $cards_played = array();

        foreach ($players as $player_id => $player) {
            if ($current_player_id != $player_id) {
                $this->revealPlayed($player_id);
            }

            $this->activateActionCard($player_id);
        }

        $this->gamestate->nextState("nextDay");
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
