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
            card.type_arg - 1
          );
          div.classList.add("prs_cardFace");
          div.dataset.direction = "backwards";
        },
        setupBackDiv: (card, div) => {
          div.dataset.direction = "forward";
          div.style.backgroundImage = `url(${g_gamethemeurl}img/hackers.png)`;
          div.style.backgroundPosition = this.calcBackgroundPosition(
            card.type_arg + 3
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

          div.style.backgroundPosition = this.calcBackgroundPosition(position);
          div.classList.add("prs_cardFace");
        },
        setupBackDiv: (card, div) => {
          div.style.backgroundImage = `url(${g_gamethemeurl}img/corporations.png)`;
          div.style.backgroundPosition = this.calcBackgroundPosition(24);
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

          div.style.backgroundPosition = this.calcBackgroundPosition(position);
          div.classList.add("prs_cardFace");
        },
        setupBackDiv: (card, div) => {
          div.style.backgroundImage = `url(${g_gamethemeurl}img/actions.png)`;

          const type = parseInt(card.type);

          div.style.backgroundPosition = this.calcBackgroundPosition(
            (type - 1) * 5
          );

          div.classList.add("prs_cardFace");
        },
      });

      this.informationManager = new CardManager(this, {
        cardHeight: 280,
        cardWidth: 180,
        selectedCardClass: "prs_selected",
        getId: (card) => `information-${card.id}`,
        setupDiv: (card, div) => {
          div.classList.add("prs_card");
          div.style.width = "180px";
          div.style.height = "280px";
          div.style.position = "relative";
        },
        setupFrontDiv: (card, div) => {
          div.style.background = `url(${g_gamethemeurl}img/informations.jpg)`;

          const type = parseInt(card.type);
          const type_arg = parseInt(card.type_arg);
          const position = type_arg - 2 + (type - 1) * 5;

          div.style.backgroundPosition = this.calcBackgroundPosition(position);
          div.classList.add("prs_cardFace");
        },
        setupBackDiv: (card, div) => {
          div.style.backgroundImage = `url(${g_gamethemeurl}img/informations.jpg)`;
          div.style.backgroundPosition = this.calcBackgroundPosition(30);
          div.classList.add("prs_cardFace");
        },
      });
    },

    setup: function (gamedatas) {
      console.log("Starting game setup");

      this.players = gamedatas.players;
      this.clockwise = false;
      this.nextPlayer = gamedatas.nextPlayer;
      this.prevPlayer = gamedatas.prevPlayer;

      this.corporations = gamedatas.corporations;
      this.hackers = gamedatas.hackers;
      this.keys = gamedatas.keys;
      this.corporationDecks = gamedatas.corporationDecks;
      this.actionsInMyHand = gamedatas.actionsInMyHand;
      this.actionsInOtherHands = gamedatas.actionsInOtherHands;
      this.deckOfInformations = gamedatas.deckOfInformations;
      this.infoInMyHand = gamedatas.infoInMyHand;
      this.infoInOtherHands = gamedatas.infoInOtherHands;

      this.selectedAction = gamedatas.cardsPlayedByMe["action"];
      this.selectedInfo = gamedatas.cardsPlayedByMe["info"];

      $(`prs_playerZone$${this.player_id}`).style.order = 0;
      $(`prs_playerZone$${this.prevPlayer}`).style.order = -1;
      $(`prs_playerZone$${this.nextPlayer}`).style.order = 1;

      for (const player_id in this.players) {
        const player = this.players[player_id];

        const hackerControl = `hackerStock$${player_id}`;
        this[hackerControl] = new LineStock(
          this.hackerManager,
          $(`prs_hacker$${player_id}`)
        );

        const hackerCard = this.hackers[player_id];

        this[hackerControl].addCard(hackerCard);

        if (this.clockwise) {
          this[hackerControl].flipCard(hackerCard);
        }

        //actions
        if (this.player_id != player_id) {
          const actionsInHandControl = `actionInHandStock$${player_id}`;

          this[actionsInHandControl] = new HandStock(
            this.actionManager,
            $(`prs_handOfActions$${player_id}`),
            { cardOverlap: "160px", sort: sortFunction("type", "type_arg") }
          );

          const actionCards = this.actionsInOtherHands[player_id];

          for (const card_id in actionCards) {
            const card = actionCards[card_id];

            this[actionsInHandControl].addCard(card);
            this[actionsInHandControl].setCardVisible(card, false);
          }

          const infoInHandControl = `infoInHandStock$${player_id}`;
          this[infoInHandControl] = new HandStock(
            this.informationManager,
            $(`prs_handOfInfo$${player_id}`),
            { cardOverlap: "160px", sort: sortFunction("type", "type_arg") }
          );

          const infoCards = this.infoInOtherHands[player_id];
          for (const card_id in infoCards) {
            const card = infoCards[card_id];
            this[infoInHandControl].addCard(card);
            this[infoInHandControl].setCardVisible(card, false);
          }

          const playedActionControl = `playedActionStock$${player_id}`;
          this[playedActionControl] = new LineStock(
            this.actionManager,
            $(`prs_playedAction$${player_id}`),
            {}
          );

          const playedInfoControl = `playedInfoStock$${player_id}`;
          this[playedInfoControl] = new LineStock(
            this.informationManager,
            $(`prs_playedInfo$${player_id}`),
            {}
          );

          const downloadedControl = `downloadedStock$${player_id}`;
          this[downloadedControl] = new LineStock(
            this.informationManager,
            $(`prs_downloaded$${player_id}`),
            {}
          );
        }
      }

      //played
      const myPlayedActionControl = `playedActionStock$${this.player_id}`;
      this[myPlayedActionControl] = new LineStock(
        this.actionManager,
        $(`prs_playedAction$${this.player_id}`),
        {}
      );
      if (this.selectedAction) {
        this[myPlayedActionControl].addCard(this.selectedAction);
      }

      const myPlayedInfoControl = `playedInfoStock$${this.player_id}`;
      this[myPlayedInfoControl] = new LineStock(
        this.informationManager,
        $(`prs_playedInfo$${this.player_id}`),
        {}
      );

      if (this.selectedInfo) {
        this[myPlayedInfoControl].addCard(this.selectedInfo);
      }

      //downloaded
      const myDownloadedControl = `downloadedStock$${this.player_id}`;
      this[myDownloadedControl] = new LineStock(
        this.informationManager,
        $(`prs_downloaded$${this.player_id}`),
        {}
      );

      //discard
      const actionDiscardControl = `actionDiscardStock`;
      this[actionDiscardControl] = new HandStock(
        this.actionManager,
        $("prs_actionDiscard"),
        { cardOverlap: "175px" }
      );

      //keys
      const keyControl = `keyStock`;
      this[keyControl] = new SlotStock(this.corporationManager, $(`prs_keys`), {
        slotsIds: Object.keys(this.corporations),
      });

      for (const key_id in this.keys) {
        const key = this.keys[key_id];
        this[keyControl].addCard(key, {}, { slot: parseInt(key.type) });
      }

      //corporations
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
          {}
        );

        cards.sort((a, b) => {
          return a.location_arg - b.location_arg;
        });

        this[corpControl].addCards(cards, undefined, {
          visible: true,
        });
      }

      //actions
      const actionsInMyHandControl = "actionsInMyHandStock";
      this[actionsInMyHandControl] = new HandStock(
        this.actionManager,
        $(`prs_handOfActions$${this.player_id}`),
        { cardOverlap: "90px", sort: sortFunction("type_arg") }
      );

      this[actionsInMyHandControl].onSelectionChange = (
        selection,
        lastChange
      ) => {
        if (this.getStateName() === "day" && this.isCurrentPlayerActive()) {
          if (selection.length === 0) {
            this.selectedAction = null;
          } else {
            this.selectedAction = lastChange;
          }

          this.handleConfirmationButton();
        }
      };

      for (const card_id in this.actionsInMyHand) {
        const card = this.actionsInMyHand[card_id];
        this[actionsInMyHandControl].addCard(
          card,
          {},
          {
            visible: true,
          }
        );
      }

      //informations
      const deckOfInformationsControl = "deckOfInformationsStock";
      this[deckOfInformationsControl] = new Deck(
        this.informationManager,
        $(`prs_infoDeck`),
        {}
      );

      //informations
      for (const card_id in this.deckOfInformations) {
        const card = this.deckOfInformations[card_id];

        this[deckOfInformationsControl].addCard(card);
        this[deckOfInformationsControl].setCardVisible(card, false);
      }

      const infoInMyHandControl = "infoInMyHandStock";
      this[infoInMyHandControl] = new HandStock(
        this.informationManager,
        $(`prs_handOfInfo$${this.player_id}`),
        { cardOverlap: "90px", sort: sortFunction("type_arg", "type") }
      );

      for (const card_id in this.infoInMyHand) {
        const card = this.infoInMyHand[card_id];

        this[infoInMyHandControl].addCard(card);

        this[infoInMyHandControl].onSelectionChange = (
          selection,
          lastChange
        ) => {
          if (this.getStateName() === "day" && this.isCurrentPlayerActive()) {
            if (selection.length === 0) {
              this.selectedInfo = null;
            } else {
              this.selectedInfo = lastChange;
            }

            this.handleConfirmationButton();
          }
        };
      }

      this.setupNotifications();

      console.log("Ending game setup");
    },

    ///////////////////////////////////////////////////
    //// Game & client states

    onEnteringState: function (stateName, args) {
      console.log("Entering state: " + stateName);

      if (stateName === "playCards") {
        this["actionsInMyHandStock"].setSelectionMode("single");
        this["infoInMyHandStock"].setSelectionMode("single");
        return;
      }
    },

    onLeavingState: function (stateName) {
      console.log("Leaving state: " + stateName);

      if (stateName === "playCards") {
        this["actionsInMyHandStock"].setSelectionMode("none");
        this["infoInMyHandStock"].setSelectionMode("none");
        return;
      }
    },

    onUpdateActionButtons: function (stateName, args) {
      console.log("Update action buttons: " + stateName);

      if (stateName === "day") {
        if (!this.isCurrentPlayerActive()) {
          this.addActionButton("prs_changeMind_btn", _("Change mind"), () => {
            this.onChangeMind();
          });
        }
        return;
      }
    },

    ///////////////////////////////////////////////////
    //// Utility methods

    getStateName: function () {
      return this.gamedatas.gamestate.name;
    },

    sendAjaxCall: function (action, args = {}, allowInactive = false) {
      args.lock = true;

      const runCall = () => {
        this.ajaxcall(
          "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
          args,
          this,
          (result) => {},
          (isError) => {}
        );
      };

      if (allowInactive) {
        if (this.checkPossibleActions(action, true) && this.checkLock()) {
          runCall();
        }
        return;
      }

      if (this.checkAction(action, true)) {
        runCall();
      }
    },

    calcBackgroundPosition: function (spritePosition) {
      const xAxis = spritePosition * 100;
      return `-${xAxis}% 0`;
    },

    handleConfirmationButton: function () {
      this.removeActionButtons();

      if (this.selectedAction && this.selectedInfo) {
        this.addActionButton(
          "prs_confirmationBtn",
          _("Confirm selection"),
          () => {
            this.onPlayCards();
          }
        );
      }
    },

    ///////////////////////////////////////////////////
    //// Player's action

    onPlayCards() {
      if (!this.selectedAction || !this.selectedInfo) {
        this.showMessage(_("Please select both cards first"), "error");
        return;
      }

      this.sendAjaxCall("playCards", {
        action_card_id: this.selectedAction.id,
        info_card_id: this.selectedInfo.id,
      });
    },

    onChangeMind() {
      this.sendAjaxCall("changeMind", {}, true);
    },

    ///////////////////////////////////////////////////
    //// Reaction to cometD notifications

    setupNotifications: function () {
      console.log("notifications subscriptions setup");
      dojo.subscribe("playCards", this, "notif_playCards");
      this.notifqueue.setSynchronous("playCards", 100);
      dojo.subscribe("changeMind", this, "notif_changeMind");
      dojo.subscribe("activateActionCard", this, "notif_activateActionCard");
      dojo.subscribe("download", this, "notif_download");
      this.notifqueue.setSynchronous("playCards", 1000);
    },

    notif_playCards: function (notif) {
      const player_id = notif.args.player_id;
      const actionCard = notif.args.actionCard;
      const infoCard = notif.args.infoCard;
      const encrypt = notif.args.encrypt;

      const playedActionControl = `playedActionStock$${player_id}`;
      this[playedActionControl].addCard(actionCard);

      const playedInfoControl = `playedInfoStock$${player_id}`;
      this[playedInfoControl].addCard(infoCard);

      if (this.player_id != player_id && encrypt) {
        this[playedInfoControl].setCardVisible(infoCard, false);
      }
    },

    notif_changeMind: function (notif) {
      const actionCard = notif.args.actionCard;
      const infoCard = notif.args.infoCard;

      const actionInMyHandControl = `actionsInMyHandStock`;
      this[actionInMyHandControl].addCard(actionCard);

      const infoInMyHandControl = `infoInMyHandStock`;
      this[infoInMyHandControl].addCard(infoCard);

      this.selectedAction = null;
      this.selectedInfo = null;
    },

    notif_download: function (notif) {
      const player_id = notif.args.player_id;
      const infoCard = notif.args.infoCard;
      const encrypt = notif.args.encrypt;

      const downloadedControl = `downloadedStock$${player_id}`;
      this[downloadedControl].addCard(infoCard);

      if (encrypt) {
        this[downloadedControl].setCardVisible(infoCard);
      }
    },

    notif_activateActionCard: function (notif) {
      const actionCard = notif.args.actionCard;

      this["actionDiscardStock"].addCard(actionCard, {});
    },
  });
});
