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
 * personanongrata.view.php
 *
 */

require_once(APP_BASE_PATH . "view/common/game.view.php");

class view_personanongrata_personanongrata extends game_view
{
    protected function getGameName()
    {
        // Used for translations and stuff. Please do not modify.
        return "personanongrata";
    }

    function build_page($viewArgs)
    {
        // Get players & players number
        global $g_user;
        $players = $this->game->loadPlayersBasicInfos();
        $current_player = $g_user->get_id();
        $template = "personanongrata_personanongrata";

        $this->tpl["YOU"] = $this->_("You");
        $this->tpl["COMMON_AREA"] = $this->_("Decks");
        $this->tpl["ARCHIVED_CARDS"] = $this->_("Archived cards");
        $this->tpl["PLAY_AREA"] = $this->_("Play area");
        $this->tpl["PLAYED_TODAY"] = $this->_("Played today");
        $this->tpl["ACTION_HAND"] = $this->_("Hand (Actions)");
        $this->tpl["INFO_HAND"] = $this->_("Hand (Information)");

        $this->page->begin_block($template, "playerzone");
        $this->page->begin_block($template, "myzone");

        foreach ($players as $player_id => $player) {
            if ($player_id == $current_player) {
                $this->page->insert_block(
                    "myzone",
                    array(
                        "PLAYER_ID" => $player_id,
                        "PLAYER_COLOR" => $player["player_color"],
                        "PLAYER_NAME" => $player["player_name"]
                    )
                );
                continue;
            }

            $this->page->insert_block(
                "playerzone",
                array(
                    "PLAYER_ID" => $player_id,
                    "PLAYER_COLOR" => $player["player_color"],
                    "PLAYER_NAME" => $player["player_name"]
                )
            );
        }

        /*********** Do not change anything below this line  ************/
    }
}
