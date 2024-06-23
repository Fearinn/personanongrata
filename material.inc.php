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
 * material.inc.php
 *
 * PersonaNonGrata game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */

$this->hackers = array(
  1 => array(
    "name" => "_FANA7IC",
    "color" => "e94190",
  ),
  2 => array(
    "name" => "TOX.INA",
    "color" => "ffffff",
  ),
  3 => array(
    "name" => "[AXIOMA]",
    "color" => "72c3b1",
  ),
  4 => array(
    "name" => "#FOSSIL",
    "color" => "f07f16"
  )
);

$this->actions = array(
  1 => clienttranslate("Download"),
  2 => clienttranslate("Encrypt"),
  3 => clienttranslate("Send to Right"),
  4 => clienttranslate("Send to Left"),
);

$this->corporations = array(
  1 => "Biolife",
  2 => "Cosmoco",
  3 => "Jihui",
  4 => "Delacruz",
  5 => "Lumni",
  6 => "SJT"
);

$this->informations = array(
  2 => array(
    "name" => clienttranslate("Worker"),
    "nbr" => 3,
    "activators" => true,
  ),
  3 => array(
    "name" => clienttranslate("Assistant"),
    "nbr" => 4,
    "activators" => false,
  ),
  4 => array(
    "name" => clienttranslate("Manager"),
    "nbr" => 2,
    "activators" => false,
  ),
  5 => array(
    "name" => clienttranslate("Director"),
    "nbr" => 2,
    "activators" => false,
  ),
  6 => array(
    "name" => clienttranslate("President"),
    "nbr" => 1,
    "activactor" => false,
  )
);
