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
          div.style.zIndex = 1;
        },
        setupFrontDiv: (card, div) => {
          div.style.background = `url(${g_gamethemeurl}img/hackers.png)`;
          div.style.backgroundPosition = this.calcBackgroundPosition(
            card.type_arg + 3
          );

          div.classList.add("prs_cardFace");
          div.dataset.direction = "backwards";
        },
        setupBackDiv: (card, div) => {
          div.dataset.direction = "forward";
          div.style.backgroundImage = `url(${g_gamethemeurl}img/hackers.png)`;
          div.style.backgroundPosition = this.calcBackgroundPosition(
            card.type_arg - 1
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

          const type = Number(card.type);
          const type_arg = Number(card.type_arg);

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

      this.keyManager = new CardManager(this, {
        cardHeight: 280,
        cardWidth: 180,
        getId: (card) => `key-${card.id}`,
        setupDiv: (card, div) => {
          div.classList.add("prs_card");
          div.style.width = "180px";
          div.style.height = "280px";
          div.style.position = "relative";
        },
        setupFrontDiv: (card, div) => {
          div.style.background = `url(${g_gamethemeurl}img/corporations.png)`;

          const type = Number(card.type);
          const position = type * 4 - 3;

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
        cardHeight: 280,
        cardWidth: 180,
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

          const type = Number(card.type);
          const type_arg = Number(card.type_arg);

          const position = (type - 1) * 5 + type_arg;

          div.style.backgroundPosition = this.calcBackgroundPosition(position);
          div.classList.add("prs_cardFace");
        },
        setupBackDiv: (card, div) => {
          div.style.backgroundImage = `url(${g_gamethemeurl}img/actions.png)`;

          const type = Number(card.type);

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

          const type = Number(card.type);
          const type_arg = Number(card.type_arg);
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
      this.clockwise = gamedatas.clockwise;
      this.nextPlayer = gamedatas.nextPlayer;
      this.prevPlayer = gamedatas.prevPlayer;

      this.corporations = gamedatas.corporations;
      this.hackers = gamedatas.hackers;
      this.keysOnTable = gamedatas.keysOnTable;
      this.corporationDecks = gamedatas.corporationDecks;
      this.actionsInMyHand = gamedatas.actionsInMyHand;
      this.actionsInOtherHands = gamedatas.actionsInOtherHands;
      this.deckOfInformations = gamedatas.deckOfInformations;
      this.infoInMyHand = gamedatas.infoInMyHand;
      this.infoInOtherHands = gamedatas.infoInOtherHands;
      this.infoStoredByMe = gamedatas.infoStoredByMe;
      this.infoStoredByOthers = gamedatas.infoStoredByOthers;
      this.actionsDiscarded = gamedatas.actionsDiscarded;
      this.encryptActionUsed = gamedatas.encryptActionUsed;
      this.keysArchived = gamedatas.keysArchived;
      this.corporationsArchived = gamedatas.corporationsArchived;
      this.archivedInfo = gamedatas.archivedInfo;

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
          $(`prs_hacker$${player_id}`),
          { direction: "column" }
        );

        const hackerCard = this.hackers[player_id];

        this[hackerControl].addCard(hackerCard);

        if (!this.clockwise) {
          this[hackerControl].flipCard(hackerCard);
        }

        if (this.player_id != player_id) {
          //actions
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
            this.updateHandWidth(this[actionsInHandControl]);
          }

          //informations
          const infoInHandControl = `infoInHandStock$${player_id}`;
          this[infoInHandControl] = new HandStock(
            this.informationManager,
            $(`prs_handOfInfo$${player_id}`),
            { cardOverlap: "160px", sort: sortFunction("type_arg", "type") }
          );

          const infoCards = this.infoInOtherHands[player_id];

          for (const card_id in infoCards) {
            const card = infoCards[card_id];
            this[infoInHandControl].addCard(card);
            this[infoInHandControl].setCardVisible(card, false);
            this.updateHandWidth(this[infoInHandControl]);
          }

          //played
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

          //stored
          const storedControl = `storedStock$${player_id}`;
          this[storedControl] = new SlotStock(
            this.informationManager,
            $(`prs_stored$${player_id}`),
            {
              slotsIds: [-1].concat(Object.keys(this.corporations)),
              mapCardToSlot: (card) => {
                if (card.location === "encrypted" || !card.type) {
                  return -1;
                }

                return card.type;
              },
            }
          );

          this[storedControl].onSelectionChange = (selection, lastChange) => {
            if (
              this.getStateName() === "stealCard" &&
              this.isCurrentPlayerActive()
            ) {
              if (selection.length === 0) {
                this.selectedInfo = null;
              } else {
                this.selectedInfo = lastChange;
              }

              this.handleConfirmationButton();
            }
          };

          const storedCards = this.infoStoredByOthers[player_id];
          const visibleStored = storedCards["visible"];

          for (const card_id in visibleStored) {
            const card = visibleStored[card_id];
            this[storedControl].addCard(card);
            this.setSlotOffset(this[storedControl].getCardElement(card));
          }

          const encryptedCard = storedCards["encrypted"];
          if (encryptedCard) {
            this[storedControl].addCard(encryptedCard);
          }

          const encryptActionControl = `encryptActionStock$${player_id}`;
          this[encryptActionControl] = new LineStock(
            this.actionManager,
            $(`prs_encryptAction$${player_id}`),
            {}
          );

          const encryptActionUsed = this.encryptActionUsed[player_id];

          if (encryptActionUsed) {
            this[encryptActionControl].addCard(
              encryptActionUsed,
              {},
              { forceToElement: $(`information--1:${player_id}`).parentElement }
            );
            this.setSlotOffset(
              this[encryptActionControl].getCardElement(encryptActionUsed),
              8
            );
          }

          //discard
          const actionsDiscardedControl = `actionsDiscardedStock$${player_id}`;
          this[actionsDiscardedControl] = new ManualPositionStock(
            this.actionManager,
            $(`prs_actionDiscard$${player_id}`),
            {},
            (element, cards, lastCard, stock) => {
              element.style.width = "180px";
              element.style.height = 280 + 32 * cards.length + "px";

              const index = cards.length - 1;

              lastCardElement = stock.getCardElement(lastCard);
              lastCardElement.style.position = "absolute";
              lastCardElement.style.top = `${index * 32}px`;
            }
          );

          const discardedCards = this.actionsDiscarded[player_id];

          for (const card_id in discardedCards) {
            const card = discardedCards[card_id];
            this[actionsDiscardedControl].addCard(card);
          }

          //end of current player excluding
        }

        //archived
        const archivedCorporationControl = `archivedCorporationStock$${player_id}`;
        this[archivedCorporationControl] = new SlotStock(
          this.corporationManager,
          $(`prs_archivedCorporation$${player_id}`),
          {
            slotsIds: Object.keys(this.corporations),
            mapCardToSlot: (card) => {
              return card.type;
            },
          }
        );

        const archivedCorporations = this.corporationsArchived[player_id];

        for (const card_id in archivedCorporations) {
          const card = archivedCorporations[card_id];

          this[archivedCorporationControl].addCard(card);
          this.setSlotOffset(
            this[archivedCorporationControl].getCardElement(card)
          );
        }

        const archivedKeyControl = `archivedKeyStock$${player_id}`;
        this[archivedKeyControl] = new SlotStock(
          this.keyManager,
          $(`prs_archivedKey$${player_id}`),
          {
            slotsIds: Object.keys(this.corporations),
            mapCardToSlot: (card) => {
              return card.type;
            },
          }
        );

        const archivedKeys = this.keysArchived[player_id];
        for (const card_id in archivedKeys) {
          const card = archivedKeys[card_id];

          this[archivedKeyControl].addCard(card);
        }

        const archivedInfoControl = `archivedInfoStock$${player_id}`;
        this[archivedInfoControl] = new AllVisibleDeck(
          this.informationManager,
          $(`prs_archivedInfo$${player_id}`),
          {}
        );

        const archivedInfo = this.archivedInfo[player_id];
        for (const card_id in archivedInfo) {
          const card = archivedInfo[card_id];
          this[archivedInfoControl].addCard(card);
          this[archivedInfoControl].setCardVisible(
            card,
            player_id == this.player_id
          );
        }

        //end of players loop
      }

      //played
      const playedActionControl = `playedActionStock$${this.player_id}`;
      this[playedActionControl] = new LineStock(
        this.actionManager,
        $(`prs_playedAction$${this.player_id}`),
        {}
      );
      if (this.selectedAction) {
        this[playedActionControl].addCard(this.selectedAction);
      }

      const playedInfoControl = `playedInfoStock$${this.player_id}`;
      this[playedInfoControl] = new LineStock(
        this.informationManager,
        $(`prs_playedInfo$${this.player_id}`),
        {}
      );

      if (this.selectedInfo) {
        this[playedInfoControl].addCard(this.selectedInfo);
      }

      //stored
      const storedControl = `storedStock$${this.player_id}`;
      this[storedControl] = new SlotStock(
        this.informationManager,
        $(`prs_stored$${this.player_id}`),
        {
          slotsIds: [-1].concat(Object.keys(this.corporations)),
          mapCardToSlot: (card) => {
            if (card.location === "encrypted" || !card.type) {
              return -1;
            }

            return card.type;
          },
        }
      );

      for (const card_id in this.infoStoredByMe) {
        const card = this.infoStoredByMe[card_id];

        this[storedControl].addCard(card);
        this.setSlotOffset(this[storedControl].getCardElement(card));
      }

      const encryptActionControl = `encryptActionStock$${this.player_id}`;
      this[encryptActionControl] = new LineStock(
        this.actionManager,
        $(`prs_encryptAction$${this.player_id}`),
        {}
      );

      const encryptActionUsed = this.encryptActionUsed[this.player_id];
      const encryptedInfo = this.getEncrypted(this.player_id);

      if (encryptActionUsed) {
        this[encryptActionControl].addCard(
          encryptActionUsed,
          {},
          {
            forceToElement: $(`information-${encryptedInfo.id}`).parentElement,
          }
        );
        this.setSlotOffset(
          this[encryptActionControl].getCardElement(encryptActionUsed),
          8
        );
      }

      //discard
      const actionsDiscardedControl = `actionsDiscardedStock$${this.player_id}`;
      this[actionsDiscardedControl] = new ManualPositionStock(
        this.actionManager,
        $(`prs_actionDiscard$${this.player_id}`),
        {},
        (element, cards, lastCard, stock) => {
          element.style.width = "180px";
          element.style.height = 280 + 32 * cards.length + "px";

          const index = cards.length - 1;

          lastCardElement = stock.getCardElement(lastCard);
          lastCardElement.style.position = "absolute";
          lastCardElement.style.top = `${index * 32}px`;
        }
      );

      const discardedCards = this.actionsDiscarded[this.player_id];

      for (const card_id in discardedCards) {
        const card = discardedCards[card_id];

        this[actionsDiscardedControl].addCard(card);
      }

      //keys
      const keysControl = `keysStock`;
      this[keysControl] = new SlotStock(this.keyManager, $(`prs_keys`), {
        slotsIds: Object.keys(this.corporations),
        slotClasses: ["prs_keySlot"],
        mapCardToSlot: (card) => {
          return card.type;
        },
      });

      for (const key_id in this.keysOnTable) {
        const keyCard = this.keysOnTable[key_id];

        this[keysControl].addCard(keyCard);
      }

      //corporations
      for (const corporation_id in this.corporations) {
        const corporationDeck = this.corporationDecks[corporation_id];
        const cards = [];

        for (const card_id in corporationDeck) {
          const card = corporationDeck[card_id];
          cards.push(card);
        }

        const corporationDeckControl = `corporationDeck:${corporation_id}`;
        this[corporationDeckControl] = new AllVisibleDeck(
          this.corporationManager,
          $(`prs_corporationDeck:${corporation_id}`),
          { horizontalShift: "0px" }
        );

        cards.sort((a, b) => {
          return a.location_arg - b.location_arg;
        });

        cards.forEach((card) => {
          this[corporationDeckControl].addCard(card);
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
        this[actionsInMyHandControl].addCard(card);
        this.updateHandWidth(this[actionsInMyHandControl]);
      }

      //informations
      const deckOfInformationsControl = "deckOfInformationsStock";
      this[deckOfInformationsControl] = new Deck(
        this.informationManager,
        $(`prs_infoDeck`),
        {}
      );

      for (const card_id in this.deckOfInformations) {
        const card = this.deckOfInformations[card_id];

        this[deckOfInformationsControl].addCard(card);
        this[deckOfInformationsControl].setCardVisible(card, false);
      }

      const infoInHandControl = `infoInHandStock$${this.player_id}`;
      this[infoInHandControl] = new HandStock(
        this.informationManager,
        $(`prs_handOfInfo$${this.player_id}`),
        { cardOverlap: "90px", sort: sortFunction("type_arg", "type") }
      );

      for (const card_id in this.infoInMyHand) {
        const card = this.infoInMyHand[card_id];
        this[infoInHandControl].addCard(card);
        this.updateHandWidth(this[infoInHandControl]);

        this[infoInHandControl].onSelectionChange = (selection, lastChange) => {
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
    },

    onLeavingState: function (stateName) {
      console.log("Leaving state: " + stateName);

      if (stateName === "stealCard") {
        for (const player_id in this.players) {
          const storedControl = `storedStock$${player_id}`;
          this[storedControl].setSelectionMode("none");
        }
      }
    },

    onUpdateActionButtons: function (stateName, args) {
      console.log("Update action buttons: " + stateName);

      if (stateName === "day") {
        if (!this.isCurrentPlayerActive()) {
          this.addActionButton("prs_changeMind_btn", _("Change mind"), () => {
            this.onChangeMind();
          });

          this["actionsInMyHandStock"].setSelectionMode("none");
          this[`infoInHandStock$${this.player_id}`].setSelectionMode("none");
        }
        return;
      }

      if (stateName === "playCards") {
        if (this.isCurrentPlayerActive()) {
          this["actionsInMyHandStock"].setSelectionMode("single");
          this[`infoInHandStock$${this.player_id}`].setSelectionMode("single");
        }
        return;
      }

      if (stateName === "stealCard") {
        const corporationId = args.args.corporationId;
        if (this.isCurrentPlayerActive()) {
          for (const player_id in this.players) {
            if (player_id != this.player_id) {
              const storedControl = `storedStock$${player_id}`;
              this[storedControl].setSelectionMode("single");

              const selectableCards = this[storedControl]
                .getCards()
                .filter((card) => {
                  return card.type == corporationId;
                });

              this[storedControl].setSelectableCards(selectableCards);
            }
          }
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

    getEncrypted: function (player_id) {
      const encryptedInfo = this[`storedStock$${player_id}`]
        .getCards()
        .find((card) => {
          return card.location === "encrypted";
        });

      return encryptedInfo;
    },

    updateHandWidth: function (stock) {
      cardNumber = stock.getCards().length;

      shift = Number(
        stock.element.style.getPropertyValue("--card-shift").split("px")[0]
      );

      overlap = Number(
        stock.element.style.getPropertyValue("--card-overlap").split("px")[0]
      );

      const width = 180 + (180 + shift - overlap) * cardNumber;

      stock.element.style.width = width + "px";
    },

    setSlotOffset: function (cardElement, offset = 48) {
      const slotElement = cardElement.parentNode;
      const index = slotElement.childNodes.length - 1;

      slotElement.style.height = 280 + offset * index + "px";

      if (!index) {
        cardElement.style.marginTop = 0;
        return;
      }

      cardElement.style.marginTop = -280 + offset + "px";
    },

    handleConfirmationButton: function () {
      this.removeActionButtons();

      if (this.getStateName() === "day") {
        if (this.selectedAction && this.selectedInfo) {
          this.addActionButton(
            "prs_confirmationBtn",
            _("Confirm selection"),
            () => {
              this.onPlayCards();
            }
          );
        }
        return;
      }

      if (this.getStateName() === "stealCard") {
        if (this.selectedInfo) {
          this.addActionButton(
            "prs_confirmationBtn",
            _("Confirm selection"),
            () => {
              this.onStealCard();
            }
          );
        }
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

    onStealCard() {
      this.sendAjaxCall("stealCard", {
        card_id: this.selectedInfo.id,
      });
    },

    ///////////////////////////////////////////////////
    //// Reaction to cometD notifications

    setupNotifications: function () {
      console.log("notifications subscriptions setup");
      dojo.subscribe("playCards", this, "notif_playCards");
      this.notifqueue.setSynchronous("playCards", 1000);
      dojo.subscribe("changeMind", this, "notif_changeMind");
      dojo.subscribe("store", this, "notif_store");
      this.notifqueue.setSynchronous("store", 1000);
      dojo.subscribe("activateActionCard", this, "notif_activateActionCard");
      this.notifqueue.setSynchronous("activateActionCard", 1000);
      dojo.subscribe("revealEncrypted", this, "notif_revealEncrypted");
      this.notifqueue.setSynchronous("revealEncrypted", 1000);
      dojo.subscribe("obtainCorporation", this, "notif_obtainCorporation");
      this.notifqueue.setSynchronous("obtainCorporation", 1000);
      dojo.subscribe("obtainKey", this, "notif_obtainKey");
      this.notifqueue.setSynchronous("obtainKey", 1000);
      dojo.subscribe("tie", this, "notif_tie");
      this.notifqueue.setSynchronous("tie", 1000);
      dojo.subscribe("flipHackers", this, "notif_flipHackers");
      dojo.subscribe("stealCard", this, "notif_stealCard");
    },

    notif_playCards: function (notif) {
      const player_id = notif.args.player_id;
      const actionCard = notif.args.actionCard;
      const infoCard = notif.args.infoCard;
      const isCurrentPlayer = this.player_id == player_id;

      const playedActionControl = `playedActionStock$${player_id}`;
      this[playedActionControl].addCard(actionCard);

      const actionsInHandControl = `actionsInMyHandStock`;
      const infoInHandControl = `infoInHandStock$${player_id}`;

      if (!isCurrentPlayer) {
        const hand = this[infoInHandControl].getCards();
        const randomIndex = Math.floor(Math.random() * hand.length);
        const randomCard = hand[randomIndex];

        this[infoInHandControl].removeCard(randomCard);
      }

      const playedInfoControl = `playedInfoStock$${player_id}`;
      this[playedInfoControl].removeAll();

      this[playedInfoControl].addCard(infoCard, {
        fromElement: $(`prs_playedInfo$${player_id}`),
      });

      this.updateHandWidth(this[actionsInHandControl]);
      this.updateHandWidth(this[infoInHandControl]);
    },

    notif_changeMind: function (notif) {
      const player_id = notif.args.player_id;
      const actionCard = notif.args.actionCard;
      const infoCard = notif.args.infoCard;

      const actionsInMyHandControl = `actionsInMyHandStock`;
      this[actionsInMyHandControl].addCard(actionCard);
      this.updateHandWidth(this[actionsInMyHandControl]);

      const infoInHandControl = `infoInHandStock$${player_id}`;
      this[infoInHandControl].addCard(infoCard);
      this.updateHandWidth(this[infoInHandControl]);

      this.selectedAction = null;
      this.selectedInfo = null;
    },

    notif_store: function (notif) {
      const player_id = notif.args.player_id;
      const card = notif.args.infoCard;
      const encrypt = notif.args.encrypt;

      if (encrypt) {
        const playedInfoControl = `playedInfoStock$${player_id}`;
        this[playedInfoControl].removeAll();
      }

      const storedControl = `storedStock$${player_id}`;
      this[storedControl].addCard(card, {
        fromElement: encrypt ? $(`prs_playedInfo$${player_id}`) : undefined,
      });
      this.setSlotOffset(this[storedControl].getCardElement(card));
    },

    notif_activateActionCard: function (notif) {
      const player_id = notif.args.player_id;
      const actionCard = notif.args.actionCard;
      const encrypt = notif.args.encrypt;

      if (encrypt) {
        const encryptedInfo = this.getEncrypted(player_id);
        const encryptActionControl = `encryptActionStock$${player_id}`;
        this[encryptActionControl].addCard(
          actionCard,
          {},
          { forceToElement: $(`information-${encryptedInfo.id}`).parentElement }
        );

        const actionElement =
          this[encryptActionControl].getCardElement(actionCard);
        this.setSlotOffset(actionElement, 8);

        return;
      }

      this[`actionsDiscardedStock$${player_id}`].addCard(actionCard);
    },

    notif_revealEncrypted: function (notif) {
      const player_id = notif.args.player_id;
      const infoCard = notif.args.infoCard;

      const storedControl = `storedStock$${player_id}`;

      this[storedControl].removeCard({ id: `-1:${player_id}` });

      this[storedControl].addCard(infoCard);
      this.setSlotOffset(this[storedControl].getCardElement(infoCard));

      const actionCard = this[`encryptActionStock$${player_id}`].getCards()[0];

      const actionsDiscardedControl = `actionsDiscardedStock$${player_id}`;
      this[actionsDiscardedControl].addCard(actionCard);

      this[actionsDiscardedControl].getCardElement(
        actionCard
      ).style.marginTop = 0;
    },

    notif_obtainCorporation: function (notif) {
      const player_id = notif.args.player_id;
      const corporationCard = notif.args.corporationCard;

      const archivedCorporationControl = `archivedCorporationStock$${player_id}`;
      this[archivedCorporationControl].addCard(corporationCard);
      this.setSlotOffset(
        this[archivedCorporationControl].getCardElement(corporationCard)
      );
    },

    notif_obtainKey: function (notif) {
      const player_id = notif.args.player_id;
      const keyCard = notif.args.keyCard;

      const archivedKeyControl = `archivedKeyStock$${player_id}`;
      this[archivedKeyControl].addCard(keyCard);
    },

    notif_tie: function (notif) {},

    notif_flipHackers: function (notif) {
      for (const player_id in this.players) {
        const hackerControl = `hackerStock$${player_id}`;

        const hackerCard = this[hackerControl].getCards()[0];
        this[hackerControl].flipCard(hackerCard);
      }
    },

    notif_stealCard: function (notif) {
      const player_id = notif.args.player_id;
      const infoCard = notif.args.infoCard;
      const isCurrentPlayer = player_id == this.player_id;

      const hackerElement = isCurrentPlayer
        ? undefined
        : $(`prs_hacker$${player_id}`);

      const archivedInfoControl = `archivedInfoStock$${player_id}`;
      this[archivedInfoControl].addCard(
        infoCard,
        {},
        { forceToElement: hackerElement }
      );
      this[archivedInfoControl].setCardVisible(infoCard, isCurrentPlayer);
    },
  });
});
