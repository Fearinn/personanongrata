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
 * states.inc.php
 *
 * PersonaNonGrata game states description
 *
 */

$machinestates = array(
    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array("" => 2)
    ),

    // Note: ID=2 => your first state

    2 => array(
        "name" => "day",
        "description" => clienttranslate('Waiting for other players to end their turn'),
        "descriptionmyturn" => "",
        "type" => "multipleactiveplayer",
        "action" => "st_day",
        "possibleactions" => array("changeMindPlayed", "changeMindDiscarded"),
        "transitions" => array("betweenDays" => 3, "infoArchiving" => 4),
        "initialprivate" => 20,
        "updateGameProgression" => true
    ),

    20 => array(
        "name" => "playCards",
        "description" => clienttranslate('Other player(s) must combine an Action card to an Information card'),
        "descriptionmyturn" => clienttranslate('${you} must combine an Action card to an Information card'),
        "type" => "private",
        "possibleactions" => array("playCards"),
        "transitions" => array(
            "discardInfo" => 21,
            "changeMindDiscarded" => 21,
            "betweenDays" => 3,
        ),
    ),

    21 => array(
        "name" => "discardInfo",
        "description" => clienttranslate('Other player(s) must discard an Information card'),
        "descriptionmyturn" => clienttranslate('${you} must discard an Information card'),
        "type" => "private",
        "possibleactions" => array(
            "discardInfo",
            "changeMindPlayed"
        ),
        "transitions" => array("changeMindPlayed" => 20),
    ),

    3 => array(
        "name" => "betweenDays",
        "description" => clienttranslate("End of day: activating all Action cards..."),
        "type" => "game",
        "action" => "st_betweenDays",
        "transitions" => array(
            "nextDay" => 2,
            "infoArchiving" => 4
        ),
        "updateGameProgression" => true
    ),

    4 => array(
        "name" => "infoArchiving",
        "description" => clienttranslate("End of week: calculating points for each Corporation..."),
        "type" => "game",
        "action" => "st_infoArchiving",
        "transitions" => array(
            "infoArchiving" => 4,
            "stealInfo" => 41,
            "breakFirstTie" => 42,
            "breakSecondTie" => 43,
            "betweenWeeks" => 5
        ),
        "updateGameProgression" => true
    ),

    41 => array(
        "name" => "stealInfo",
        "description" => clienttranslate('${actplayer} must take a card of ${corporation_label} from other player'),
        "descriptionmyturn" => clienttranslate('${you} must take a card of ${corporation_label} from other player'),
        "type" => "activeplayer",
        "possibleactions" => array("stealInfo"),
        "args" => "arg_stealInfo",
        "transitions" => array(
            "infoArchiving" => 4,
            "zombiePass" => 4
        )
    ),

    42 => array(
        "name" => "breakFirstTie",
        "description" => clienttranslate('${actplayer} must pick who obtains the Corporation card of ${corporation_label} this week (click their Character card)'),
        "descriptionmyturn" => clienttranslate('${you} must pick who obtains the Corporation card of ${corporation_label} this week (click their Character card)'),
        "type" => "activeplayer",
        "possibleactions" => array("breakFirstTie"),
        "args" => "arg_breakFirstTie",
        "transitions" => array(
            "infoArchiving" => 4,
        )
    ),

    43 => array(
        "name" => "breakSecondTie",
        "description" => clienttranslate('${actplayer} must pick who obtains the Key of ${corporation_label} this week (click their Character card)'),
        "descriptionmyturn" => clienttranslate('${you} must pick who obtains the Key of ${corporation_label} this week (click their Character card)'),
        "type" => "activeplayer",
        "possibleactions" => array("breakSecondTie"),
        "args" => "arg_breakSecondTie",
        "transitions" => array(
            "infoArchiving" => 4,
        )
    ),

    5 => array(
        "name" => "betweenWeeks",
        "description" => clienttranslate("Setting up the next week..."),
        "type" => "game",
        "action" => "st_betweenWeeks",
        "transitions" => array(
            "nextWeek" => 2,
            "finalScoring" => 6
        ),
    ),

    6 => array(
        "name" => "finalScoring",
        "description" => clienttranslate("Calculating final scores..."),
        "type" => "game",
        "action" => "st_finalScoring",
        "transitions" => array(
            "gameEnd" => 99
        ),
    ),

    // Final state.
    // Please do not modify (and do not overload action/args methods).
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);
