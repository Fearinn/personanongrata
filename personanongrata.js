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
        cardHeight: 280,
        cardWidth: 180,
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

      this.corporationManager = new CardManager(this, {
        cardHeight: 280,
        cardWidth: 180,
        getId: (card) => `corporation-${card.id}`,
        setupDiv: (card, div) => {
          div.classList.add("prs_card");
          div.style.width = "180px";
          div.style.height = "280px";
          div.style.position = "relative";
        },
        setupFrontDiv: (card, div) => {
          div.style.background = `url(${g_gamethemeurl}img/corporations.png)`;

          const type = parseInt(card.type);
          const type_arg = parseInt(card.type_arg);

          const valueShift = type_arg == 4 ? 3 : type_arg;
          const position = (type - 1) * 4 + valueShift;

          div.style.backgroundPosition = this.calcBackgroundPosition(
            position,
            25
          );
          div.classList.add("prs_cardFace");
        },
        setupBackDiv: (card, div) => {
          div.style.backgroundImage = `url(${g_gamethemeurl}img/corporations.png)`;
          div.style.backgroundPosition = this.calcBackgroundPosition(24, 25);
          div.classList.add("prs_cardFace");
        },
      });

      this.actionManager = new CardManager(this, {
        selectedCardClass: "prs_selected",
        getId: (card) => `action-${card.id}`,
        setupDiv: (card, div) => {
          div.classList.add("prs_card");
          div.style.width = "180px";
          div.style.height = "280px";
          div.style.position = "relative";
        },
        setupFrontDiv: (card, div) => {
          div.style.background = `url(${g_gamethemeurl}img/actions.png)`;

          const type = parseInt(card.type);
          const type_arg = parseInt(card.type_arg);

          const position = (type - 1) * 5 + type_arg;

          div.style.backgroundPosition = this.calcBackgroundPosition(
            position,
            20
          );
          div.classList.add("prs_cardFace");
        },
        setupBackDiv: (card, div) => {
          div.style.backgroundImage = `url(${g_gamethemeurl}img/actions.png)`;

          const type = parseInt(card.type);

          div.style.backgroundPosition = this.calcBackgroundPosition(
            (type - 1) * 5,
            20
          );

          div.classList.add("prs_cardFace");
        },
      });
    },

    setup: function (gamedatas) {
      console.log("Starting game setup");

      this.players = gamedatas.players;
      this.corporations = gamedatas.corporations;
      this.hackers = gamedatas.hackers;
      this.keys = gamedatas.keys;
      this.corporationDecks = gamedatas.corporationDecks;
      this.actionsInMyHand = gamedatas.actionsInMyHand;
      this.actionsInOtherHands = gamedatas.actionsInOtherHands;

      for (const player_id in this.players) {
        const player = this.players[player_id];

        const hackerControl = `hackerStock$${player_id}`;
        this[hackerControl] = new LineStock(
          this.hackerManager,
          $(`prs_hacker$${player_id}`)
        );

        const hacker = this.hackers[player_id];

        this[hackerControl].addCard(hacker);

        //actions
        if (this.player_id !== player_id) {
          const actionsInHandControl = `actionInHandStock`;
          this[actionsInHandControl] = new HandStock(
            this.actionManager,
            $(`prs_handOfActions$${player_id}`),
            { cardOverlap: "175px", sort: sortFunction("type", "type_arg") }
          );
          const actionCards = this.actionsInOtherHands[player_id];

          for (const card_id in actionCards) {
            const card = actionCards[card_id];

            this[actionsInHandControl].addCard(card);
            this[actionsInHandControl].setCardVisible(card, false);
          }
        }
      }

      const keyControl = `keyStock`;
      this[keyControl] = new SlotStock(this.corporationManager, $(`prs_keys`), {
        slotsIds: Object.keys(this.corporations),
      });

      for (const key_id in this.keys) {
        const key = this.keys[key_id];
        this[keyControl].addCard(key, {}, { slot: parseInt(key.type) });
      }

      for (const corporation_id in this.corporations) {
        const corpDeck = this.corporationDecks[corporation_id];
        const cards = [];

        for (const card_id in corpDeck) {
          const card = corpDeck[card_id];
          cards.push(card);
        }

        const corpControl = `corpDeck:${corporation_id}`;
        this[corpControl] = new Deck(
          this.corporationManager,
          $(`prs_corpDeck:${corporation_id}`),
          { sort: sortFunction("type", "type_arg") }
        );

        cards.sort((a, b) => {
          return a.location_arg - b.location_arg;
        });

        this[corpControl].addCards(cards, undefined, {
          visible: true,
        });
      }

      //actions
      const actionsInMyHandControl = `actionsInMyHandStock`;
      this[actionsInMyHandControl] = new HandStock(
        this.actionManager,
        $(`prs_handOfActions$${this.player_id}`),
        { cardOverlap: "90px", sort: sortFunction("type", "type_arg") }
      );

      this[actionsInMyHandControl].setSelectionMode("single");

      for (const card_id in this.actionsInMyHand) {
        const card = this.actionsInMyHand[card_id];
        this[actionsInMyHandControl].addCard(card, undefined, {
          visible: true,
        });
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

    calcBackgroundPosition: function (spritePosition, itemsPerRow) {
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
