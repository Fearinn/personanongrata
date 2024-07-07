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

      this.corporationColors = {
        1: "35a7dd",
        2: "652b80",
        3: "029447",
        4: "c4c02f",
        5: "936036",
        6: "bc2026",
      };

      this.hackerManager = new CardManager(this, {
        cardHeight: 280,
        cardWidth: 180,
        selectedCardClass: "prs_selected",
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
      this.discardedActions = gamedatas.discardedActions;
      this.encryptActionUsed = gamedatas.encryptActionUsed;
      this.keysArchived = gamedatas.keysArchived;
      this.archivedCorporations = gamedatas.archivedCorporations;
      this.archivedInfo = gamedatas.archivedInfo;

      this.selectedAction = gamedatas.cardsPlayedByMe["action"];
      this.selectedInfo = gamedatas.cardsPlayedByMe["info"];

      $(`prs_playerArea$${this.player_id}`).style.order = 0;
      $(`prs_playerArea$${this.prevPlayer}`).style.order = -1;
      $(`prs_playerArea$${this.nextPlayer}`).style.order = 1;

      for (const player_id in this.players) {
        const player = this.players[player_id];

        const hackerControl = `hackerStock$${player_id}`;
        this[hackerControl] = new LineStock(
          this.hackerManager,
          $(`prs_hacker$${player_id}`),
          { direction: "column" }
        );

        this[hackerControl].onSelectionChange = (selection, lastChange) => {
          if (
            this.getStateName() === "breakTie" &&
            this.isCurrentPlayerActive()
          ) {
            if (selection.length === 0) {
              this.selectedTieWinner = null;
            } else {
              console.log(lastChange);
              this.selectedTieWinner = lastChange.location_arg;
            }

            this.handleConfirmationButton();
          }
        };

        const hackerCard = this.hackers[player_id];

        this[hackerControl].addCard(hackerCard);

        if (!this.clockwise) {
          this[hackerControl].flipCard(hackerCard);
        }

        if (this.player_id != player_id) {
          //actions
          const actionsInHandControl = `actionsInHandStock$${player_id}`;

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
            this.updateHandWidth(this[actionsInHandControl]);
          }

          //informations
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

          //end of current player excluding
        }

        //discard
        const discardedActionsControl = `discardedActionsStock$${player_id}`;
        this[discardedActionsControl] = new AllVisibleDeck(
          this.actionManager,
          $(`prs_actionDiscard$${player_id}`),
          { horizontalShift: "32px", verticalShift: "0px" }
        );

        const discardedCards = this.discardedActions[player_id];

        for (const card_id in discardedCards) {
          const card = discardedCards[card_id];
          this[discardedActionsControl].addCard(card);
        }

        //archived
        const archivedCorporationsControl = `archivedCorporationStock$${player_id}`;
        this[archivedCorporationsControl] = new AllVisibleDeck(
          this.corporationManager,
          $(`prs_archivedCorporation$${player_id}`),
          {
            horizontalShift: "32px",
            verticalShift: "0px",
            sort: sortFunction("type, type_arg"),
          }
        );

        const archivedCorporations = this.archivedCorporations[player_id];

        for (const card_id in archivedCorporations) {
          const card = archivedCorporations[card_id];

          this[archivedCorporationsControl].addCard(card);
        }

        const archivedKeyControl = `archivedKeysStock$${player_id}`;
        this[archivedKeyControl] = new AllVisibleDeck(
          this.keyManager,
          $(`prs_archivedKey$${player_id}`),
          { horizontalShift: "32px", verticalShift: "0px" }
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
          {
            horizontalShift: "32px",
            verticalShift: "0px",
            sort: sortFunction("type", "type_arg"),
          }
        );

        const archivedInfo = this.archivedInfo[player_id];
        const isCurrentPlayer = player_id == this.player_id;
        const hackerElement = isCurrentPlayer
          ? undefined
          : $(`prs_hacker$${player_id}`);

        for (const card_id in archivedInfo) {
          const card = archivedInfo[card_id];
          this[archivedInfoControl].addCard(
            card,
            {},
            { forceToElement: hackerElement }
          );
          this[archivedInfoControl].setCardVisible(card, isCurrentPlayer);
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
      const actionsInHandControl = `actionsInHandStock$${this.player_id}`;
      this[actionsInHandControl] = new HandStock(
        this.actionManager,
        $(`prs_handOfActions$${this.player_id}`),
        { cardOverlap: "90px", sort: sortFunction("type_arg") }
      );

      this[actionsInHandControl].onSelectionChange = (
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
        this[actionsInHandControl].addCard(card);
        this.updateHandWidth(this[actionsInHandControl]);
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
        { cardOverlap: "90px", sort: sortFunction("type", "type_arg") }
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

      if (stateName === "breakTie") {
        for (const player_id in this.players) {
          const hackerControl = `hackerStock$${player_id}`;
          this[hackerControl].setSelectionMode("none");
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

          this[`actionsInHandStock$${this.player_id}`].setSelectionMode("none");
          this[`infoInHandStock$${this.player_id}`].setSelectionMode("none");
        }
        return;
      }

      if (stateName === "playCards") {
        if (this.isCurrentPlayerActive()) {
          this[`actionsInHandStock$${this.player_id}`].setSelectionMode(
            "single"
          );
          this[`infoInHandStock$${this.player_id}`].setSelectionMode("single");
        }
        return;
      }

      if (stateName === "stealCard") {
        const corporationId = args.corporationId;
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

      if (stateName === "breakTie") {
        const tiedPlayers = args.tiedPlayers;
        if (this.isCurrentPlayerActive()) {
          for (const player_id in this.players) {
            const hackerControl = `hackerStock$${player_id}`;
            this[hackerControl].setSelectionMode("single");

            if (!tiedPlayers[player_id]) {
              this[hackerControl].setSelectableCards([]);
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

      if (!cardNumber) {
        stock.element.style.width = "0px";
      }
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

      if (this.getStateName() === "breakTie") {
        if (this.selectedTieWinner) {
          this.addActionButton(
            "prs_confirmationBtn",
            _("Confirm selection"),
            () => {
              this.onBreakTie();
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

    onBreakTie() {
      this.sendAjaxCall("breakTie", {
        tie_winner: this.selectedTieWinner,
      });
    },

    ///////////////////////////////////////////////////
    //// Reaction to cometD notifications

    setupNotifications: function () {
      console.log("notifications subscriptions setup");
      dojo.subscribe("playCards", this, "notif_playCards");
      dojo.subscribe("revealPlayed", this, "notif_revealPlayed");
      dojo.subscribe("changeMind", this, "notif_changeMind");
      dojo.subscribe("store", this, "notif_store");
      dojo.subscribe("storePrivate", this, "notif_storePrivate");
      dojo.subscribe("activateActionCard", this, "notif_activateActionCard");
      dojo.subscribe("revealEncrypted", this, "notif_revealEncrypted");
      dojo.subscribe("obtainCorporation", this, "notif_obtainCorporation");
      dojo.subscribe("obtainKey", this, "notif_obtainKey");
      dojo.subscribe("tie", this, "notif_tie");
      dojo.subscribe("flipHackers", this, "notif_flipHackers");
      dojo.subscribe("stealCard", this, "notif_stealCard");
      dojo.subscribe("resetActions", this, "notif_resetActions");
      dojo.subscribe("discardLastInfo", this, "notif_discardLastInfo");
      dojo.subscribe("drawNewInfo", this, "notif_drawNewInfo");
      dojo.subscribe("drawNewInfoPrivate", this, "notif_drawNewInfoPrivate");
      dojo.subscribe("passHands", this, "notif_passHands");
      dojo.subscribe("receiveNewInfo", this, "notif_receiveNewInfo");
      dojo.subscribe(
        "computeArchivedPoints",
        this,
        "notif_computeArchivedPoints"
      );

      this.notifqueue.setSynchronous("revealPlayed", 1000);
      this.notifqueue.setSynchronous("store", 1000);
      this.notifqueue.setSynchronous("storePrivate", 1000);
      this.notifqueue.setSynchronous("activateActionCard", 1000);
      this.notifqueue.setSynchronous("revealEncrypted", 1000);
      this.notifqueue.setSynchronous("obtainCorporation", 1000);
      this.notifqueue.setSynchronous("obtainKey", 1000);
      this.notifqueue.setSynchronous("stealCard", 1000);
      this.notifqueue.setSynchronous("resetActions", 1000);
      this.notifqueue.setSynchronous("discardLastInfo", 1000);
      this.notifqueue.setSynchronous("drawNewInfoPrivate", 1000);
      this.notifqueue.setSynchronous("passHands", 1000);
      this.notifqueue.setSynchronous("computeArchivedPoints", 1000);
    },

    notif_playCards: function (notif) {
      const player_id = notif.args.player_id;
      const actionCard = notif.args.actionCard;
      const infoCard = notif.args.infoCard;

      const playedActionControl = `playedActionStock$${player_id}`;
      const playedInfoControl = `playedInfoStock$${player_id}`;

      this[playedActionControl].addCard(actionCard);
      this[playedInfoControl].addCard(infoCard);

      const actionsInHandControl = `actionsInHandStock$${player_id}`;
      const infoInHandControl = `infoInHandStock$${player_id}`;

      this.updateHandWidth(this[actionsInHandControl]);
      this.updateHandWidth(this[infoInHandControl]);
    },

    notif_revealPlayed: function (notif) {
      const player_id = notif.args.player_id;
      const actionCard = notif.args.actionCard;
      const infoCard = notif.args.infoCard;

      if (player_id == this.player_id) {
        return;
      }

      const playedActionControl = `playedActionStock$${player_id}`;
      this[playedActionControl].addCard(actionCard);

      const actionsInHandControl = `actionsInHandStock$${player_id}`;
      const infoInHandControl = `infoInHandStock$${player_id}`;

      const hand = this[infoInHandControl].getCards();
      const randomIndex = Math.floor(Math.random() * hand.length);
      const randomCard = hand[randomIndex];

      this[infoInHandControl].removeCard(randomCard);

      const playedInfoControl = `playedInfoStock$${player_id}`;
      this[playedInfoControl].removeAll();

      this[playedInfoControl].addCard(infoCard, {
        fromElement: $(`prs_infoInHand$${player_id}`),
      });

      this.updateHandWidth(this[actionsInHandControl]);
      this.updateHandWidth(this[infoInHandControl]);
    },

    notif_changeMind: function (notif) {
      const player_id = notif.args.player_id;
      const actionCard = notif.args.actionCard;
      const infoCard = notif.args.infoCard;

      const actionsInHandControl = `actionsInHandStock$${player_id}`;
      this[actionsInHandControl].addCard(actionCard);
      this.updateHandWidth(this[actionsInHandControl]);

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

      if (encrypt && player_id == this.player_id) {
        return;
      }

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

    notif_storePrivate: function (notif) {
      const player_id = notif.args.player_id;
      const card = notif.args.infoCard;

      const storedControl = `storedStock$${player_id}`;

      this[storedControl].addCard(card);
      this[storedControl].setCardVisible(card, false);

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

      this[`discardedActionsStock$${player_id}`].addCard(actionCard);
    },

    notif_revealEncrypted: function (notif) {
      const player_id = notif.args.player_id;
      const infoCard = notif.args.infoCard;

      const storedControl = `storedStock$${player_id}`;

      this[storedControl].removeCard({ id: `-1:${player_id}` });

      this[storedControl].addCard(infoCard);
      this.setSlotOffset(this[storedControl].getCardElement(infoCard));

      const actionCard = this[`encryptActionStock$${player_id}`].getCards()[0];

      const discardedActionsControl = `discardedActionsStock$${player_id}`;
      this[discardedActionsControl].addCard(actionCard);

      this[discardedActionsControl].getCardElement(
        actionCard
      ).style.marginTop = 0;
    },

    notif_obtainCorporation: function (notif) {
      const player_id = notif.args.player_id;
      const corporationCard = notif.args.corporationCard;

      const archivedCorporationsControl = `archivedCorporationStock$${player_id}`;
      this[archivedCorporationsControl].addCard(corporationCard);
    },

    notif_obtainKey: function (notif) {
      const player_id = notif.args.player_id;
      const keyCard = notif.args.keyCard;

      const archivedKeyControl = `archivedKeysStock$${player_id}`;
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

    notif_resetActions: function (notif) {
      for (const player_id in this.players) {
        const actionsInHandControl = `actionsInHandStock$${player_id}`;
        const discardedActionsControl = `discardedActionsStock$${player_id}`;

        this[discardedActionsControl].getCards().forEach((card) => {
          this[actionsInHandControl].addCard(card);
          this[actionsInHandControl].setCardVisible(
            card,
            player_id == this.player_id
          );
        });
        this.updateHandWidth(this[actionsInHandControl]);
      }
    },

    notif_discardLastInfo: function (notif) {
      for (const player_id in this.players) {
        const infoInHandControl = `infoInHandStock$${player_id}`;

        this[infoInHandControl].removeAll();
        this.updateHandWidth(this[infoInHandControl]);
      }
    },

    notif_drawNewInfo: function (notif) {
      const newInfo = notif.args.newInfo;
      const removedFromDeck = notif.args.removedFromDeck;

      for (const player_id in newInfo) {
        if (player_id == this.player_id) {
          continue;
        }

        const infoInHandControl = `infoInHandStock$${player_id}`;
        const infoCards = newInfo[player_id];
        const cardsRemoved = removedFromDeck[player_id];

        for (const card_id in infoCards) {
          const card = infoCards[card_id];
          this[infoInHandControl].addCard(card, {
            fromElement: $("prs_infoDeck"),
          });
        }

        for (const card_id in cardsRemoved) {
          const card = cardsRemoved[card_id];
          this["deckOfInformationsStock"].removeCard(card);
        }

        this.updateHandWidth(this[infoInHandControl]);
      }
    },

    notif_drawNewInfoPrivate: function (notif) {
      const player_id = notif.args.player_id;
      const infoCards = notif.args.infoCards;

      const infoInHandControl = `infoInHandStock$${player_id}`;

      for (const card_id in infoCards) {
        const card = infoCards[card_id];
        this[infoInHandControl].addCard(card);
      }

      this.updateHandWidth(this[infoInHandControl]);
    },

    notif_receiveNewInfo: function (notif) {
      const player_id = notif.args.player_id;
      const sender_id = notif.args.player_id2;
      const infoCards = notif.args.infoCards;
      const removeFromSender = notif.args.removeFromSender;

      const infoInHandControl = `infoInHandStock$${player_id}`;

      this[infoInHandControl].removeAll();

      for (const card_id in infoCards) {
        const card = infoCards[card_id];
        this[infoInHandControl].addCard(card, {
          fromElement: $(`prs_handOfInfo$${sender_id}`),
        });
      }

      if (removeFromSender) {
        this[`infoInHandStock$${sender_id}`].removeAll();
      }

      this.updateHandWidth(this[infoInHandControl]);
    },

    notif_passHands: function (notif) {
      const newInfo = notif.args.newInfo;
      const senders = notif.args.senders;

      for (const player_id in this.players) {
        if (player_id == this.player_id) {
          continue;
        }

        const sender_id = senders[player_id];

        const infoInHandControl = `infoInHandStock$${player_id}`;
        const infoCards = newInfo[player_id];

        for (const card_id in infoCards) {
          const card = infoCards[card_id];
          this[infoInHandControl].addCard(card, {
            fromElement: $(`prs_handOfInfo$${sender_id}`),
          });
        }

        this.updateHandWidth(this[infoInHandControl]);

        if (sender_id != this.player_id) {
          this[`infoInHandStock$${sender_id}`].removeAll();
        }
      }
    },

    notif_computeArchivedPoints: function (notif) {
      const player_id = notif.args.player_id;
      const player_color = notif.args.player_color;
      const points = notif.args.points;
      const corporationCards = notif.args.corporationCards;
      const infoCards = notif.args.infoCards;
      const keyCards = notif.args.keyCards;

      const archivedCorporationsControl = `archivedCorporationStock$${player_id}`;
      this[archivedCorporationsControl].removeAll();

      for (const card_id in corporationCards) {
        const card = corporationCards[card_id];
        this[archivedCorporationsControl].addCard(card);
        this[archivedCorporationsControl].setCardVisible(card, true);

        const cardElement =
          this[archivedCorporationsControl].getCardElement(card);
        this.displayScoring(cardElement, player_color, card.type_arg);
      }

      const archivedInfoControl = `archivedInfoStock$${player_id}`;
      this[archivedInfoControl].removeAll();

      for (const card_id in infoCards) {
        const card = infoCards[card_id];
        this[archivedInfoControl].addCard(card);
        this[archivedInfoControl].setCardVisible(card, true);

        const cardElement = this[archivedInfoControl].getCardElement(card);
        this.displayScoring(cardElement, player_color, card.type_arg);
      }

      const archivedKeysControl = `archivedKeysStock$${player_id}`;
      this[archivedKeysControl].removeAll();

      for (const card_id in keyCards) {
        const card = keyCards[card_id];
        this[archivedKeysControl].addCard(card);
        this[archivedKeysControl].setCardVisible(card, true);

        const cardElement = this[archivedKeysControl].getCardElement(card);
        this.displayScoring(cardElement, player_color, card.type_arg);
      }

      this.scoreCtrl[player_id].toValue(points);
    },

    //Style logs
    // @Override
    format_string_recursive: function (log, args) {
      try {
        if (log && args && !args.processed) {
          args.processed = true;

          console.log(args, args.corporationId, args.corporation_label, "args");

          if (args.corporationId) {
            const corporationId = args.corporationId;
            const corporationColor = this.corporationColors[corporationId];

            console.log(corporationColor);

            if (args.corporation_label) {
              args.corporation_label = `<span style="color: #${corporationColor}">${args.corporation_label}</span>`;
            }

            if (args.info_label) {
              args.info_label = `<span style="color: #${corporationColor}">${args.info_label}</span>`;
            }
          }
        }
      } catch (e) {
        console.error(log, args, "Exception thrown", e.stack);
      }

      return this.inherited(arguments);
    },
  });
});
