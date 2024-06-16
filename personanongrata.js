/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * PersonaNonGrata implementation : © Matheus Gomes matheusgomesforwork@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * personanongrata.js
 *
 * PersonaNonGrata user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
  "dojo",
  "dojo/_base/declare",
  "ebg/core/gamegui",
  "ebg/counter",
  g_gamethemeurl + "modules/bga-cards.js",
], function (dojo, declare) {
  return declare("bgagame.personanongrata", ebg.core.gamegui, {
    constructor: function () {
      console.log("personanongrata constructor");

      this.hackerManager = new CardManager(this, {
        getId: (card) => `hacker-${card.id}`,
        setupDiv: (card, div) => {
          div.classList.add("prs_card");
          div.style.width = "180px";
          div.style.height = "280px";
          div.style.position = "relative";
        },
        setupFrontDiv: (card, div) => {
          div.style.background = `url(${g_gamethemeurl}img/hackers.png)`;
          div.style.backgroundPosition = this.calcBackgroundPosition(
            card.type_arg - 1,
            8
          );
          div.classList.add("prs_cardFace");
          div.id = `prs_hackerFront:${card.id}`;
          div.dataset.direction = "backwards";
        },
        setupBackDiv: (card, div) => {
          div.dataset.direction = "forward";
          div.style.backgroundImage = `url(${g_gamethemeurl}img/hackers.png)`;
          div.style.backgroundPosition = this.calcBackgroundPosition(
            card.type_arg + 3,
            8
          );
          div.classList.add("prs_cardFace");
        },
      });
    },

    setup: function (gamedatas) {
      console.log("Starting game setup");

      for (const player_id in gamedatas.players) {
        const player = gamedatas.players[player_id];

        const hackerControlName = `hackerStock$${player_id}`;
        this[hackerControlName] = new LineStock(
          this.hackerManager,
          $(`prs_hacker$${player_id}`)
        );

        const hacker = gamedatas.hackers[player_id];

        this[hackerControlName].addCard(hacker);
      }

      this.setupNotifications();

      console.log("Ending game setup");
    },

    ///////////////////////////////////////////////////
    //// Game & client states

    onEnteringState: function (stateName, args) {
      console.log("Entering state: " + stateName);
    },

    onLeavingState: function (stateName) {
      console.log("Leaving state: " + stateName);
    },

    onUpdateActionButtons: function (stateName, args) {
      console.log("onUpdateActionButtons: " + stateName);

      if (this.isCurrentPlayerActive()) {
      }
    },

    ///////////////////////////////////////////////////
    //// Utility methods

    calcBackgroundPosition: function (spritePosition, itemsPerRow = 10) {
      const xAxis = (spritePosition % itemsPerRow) * 100;
      const yAxis = Math.floor(spritePosition / itemsPerRow) * 100;
      return `-${xAxis}% -${yAxis}%`;
    },

    ///////////////////////////////////////////////////
    //// Player's action

    /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */

    /* Example:
        
        onMyMethodToCall1: function( evt )
        {
            console.log( 'onMyMethodToCall1' );
            
            // Preventing default browser reaction
            dojo.stopEvent( evt );

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'myAction' ) )
            {   return; }

            this.ajaxcall( "/personanongrata/personanongrata/myAction.html", { 
                                                                    lock: true, 
                                                                    myArgument1: arg1, 
                                                                    myArgument2: arg2,
                                                                    ...
                                                                 }, 
                         this, function( result ) {
                            
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)
                            
                         }, function( is_error) {

                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                         } );        
        },        
        
        */

    ///////////////////////////////////////////////////
    //// Reaction to cometD notifications

    /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your personanongrata.game.php file.
        
        */
    setupNotifications: function () {
      console.log("notifications subscriptions setup");

      // TODO: here, associate your game notifications with local methods

      // Example 1: standard notification handling
      // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );

      // Example 2: standard notification handling + tell the user interface to wait
      //            during 3 seconds after calling the method in order to let the players
      //            see what is happening in the game.
      // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
      // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
      //
    },

    // TODO: from this point and below, you can write your game notifications handling methods

    /*
        Example:
        
        notif_cardPlayed: function( notif )
        {
            console.log( 'notif_cardPlayed' );
            console.log( notif );
            
            // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
            
            // TODO: play the card in the user interface.
        },    
        
        */
  });
});
