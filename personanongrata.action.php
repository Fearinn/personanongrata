<?php

/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * PersonaNonGrata implementation : © Matheus Gomes matheusgomesforwork@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * personanongrata.action.php
 *
 * PersonaNonGrata main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/personanongrata/personanongrata/myAction.html", ...)
 *
 */


class action_personanongrata extends APP_GameAction
{
  // Constructor: please do not modify
  public function __default()
  {
    if ($this->isArg('notifwindow')) {
      $this->view = "common_notifwindow";
      $this->viewArgs['table'] = $this->getArg("table", AT_posint, true);
    } else {
      $this->view = "personanongrata_personanongrata";
      $this->trace("Complete reinitialization of board game");
    }
  }

  public function playCards()
  {
    $this->setAjaxMode();

    $action_card_id = $this->getArg("action_card_id", AT_enum, true, null, range(1, 20));
    $info_card_id = $this->getArg("info_card_id", AT_enum, true, null, range(1, 72));
    $this->game->playCards($action_card_id, $info_card_id);

    $this->ajaxResponse();
  }

  public function changeMind()
  {
    $this->setAjaxMode();
    $this->game->changeMind();
    $this->ajaxResponse();
  }

  public function stealCard()
  {
    $this->setAjaxMode();

    $card_id = $this->getArg("card_id", AT_enum, true, null, range(1, 72));
    $this->game->stealCard($card_id);

    $this->ajaxResponse();
  }
}
