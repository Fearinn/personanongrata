{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
-- PersonaNonGrata implementation : © Matheus Gomes matheusgomesforwork@gmail.com
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------
-->

<div id="prs_gameArea" class="prs_gameArea">
  <!-- BEGIN myzone -->
  <div
    id="prs_playerZone${PLAYER_ID}"
    class="prs_myZone prs_playerZone prs_zone whiteblock"
    style="border-color: #{PLAYER_COLOR}"
  >
    <h3
      id="prs_myZoneTitle"
      class="prs_zoneTitle"
      style="color: #{PLAYER_COLOR}"
    >
      {YOU}
    </h3>
    <div id="prs_hand${PLAYER_ID}" class="prs_myHand prs_handContainer">
      <div id="prs_hacker${PLAYER_ID}" class="prs_hacker"></div>
      <div id="prs_archivedInfo${PLAYER_ID}"></div>
      <div id="prs_archivedCorporation${PLAYER_ID}"></div>
      <div id="prs_archivedKey${PLAYER_ID}"></div>
      <div id="prs_playedInfo${PLAYER_ID}" class="prs_playedInfo"></div>
      <div id="prs_playedAction${PLAYER_ID}" class="prs_playedAction"></div>
      <div
        id="prs_handOfActions${PLAYER_ID}"
        class="prs_handOfActions prs_hand"
      ></div>
      <div
        id="prs_handOfInfo${PLAYER_ID}"
        class="prs_handOfInfo prs_hand"
      ></div>
      <div id="prs_stored${PLAYER_ID}" class="prs_stored"></div>
      <div id="prs_actionDiscard${PLAYER_ID}" class="prs_actionDiscard"></div>
      <div id="prs_encryptAction${PLAYER_ID}" class="prs_encryptAction"></div>
    </div>
  </div>
  <!-- END myzone -->
  <div id="prs_playerZones" class="prs_playerZones">
    <!-- BEGIN playerzone -->
    <div
      id="prs_playerZone${PLAYER_ID}"
      class="prs_playerZone prs_zone whiteblock"
      style="border-color: #{PLAYER_COLOR}"
    >
      <h3 class="prs_zoneTitle" style="color: #{PLAYER_COLOR}">
        {PLAYER_NAME}
      </h3>
      <div id="prs_hand${PLAYER_ID}" class="prs_handContainer">
        <div id="prs_hacker${PLAYER_ID}" class="prs_hacker"></div>
        <div id="prs_archivedInfo${PLAYER_ID}"></div>
        <div id="prs_archivedCorporation${PLAYER_ID}"></div>
        <div id="prs_archivedKey${PLAYER_ID}"></div>
        <div id="prs_playedInfo${PLAYER_ID}" class="prs_playedInfo"></div>
        <div id="prs_playedAction${PLAYER_ID}" class="prs_playedAction"></div>
        <div
          id="prs_handOfActions${PLAYER_ID}"
          class="prs_handOfActions prs_hand"
        ></div>
        <div
          id="prs_handOfInfo${PLAYER_ID}"
          class="prs_handOfInfo prs_hand"
        ></div>
        <div id="prs_stored${PLAYER_ID}" class="prs_stored"></div>
        <div id="prs_actionDiscard${PLAYER_ID}" class="prs_actionDiscard"></div>
        <div id="prs_encryptAction${PLAYER_ID}" class="prs_encryptAction"></div>
      </div>
    </div>
    <!-- END playerzone -->
  </div>

  <div id="prs_publicZone" class="prs_publicZone prs_zone whiteblock">
    <h3 class="prs_zoneTitle">{CORPORATION_CARDS}</h3>
    <div id="prs_publicCards" class="prs_publicCards">
      <div id="prs_infoDeck" class="prs_infoDeck"></div>
      <div id="prs_keys" class="prs_keys"></div>
      <div id="prs_corporationDecks" class="prs_corporationDecks">
        <div id="prs_corporationDeck:1"></div>
        <div id="prs_corporationDeck:2"></div>
        <div id="prs_corporationDeck:3"></div>
        <div id="prs_corporationDeck:4"></div>
        <div id="prs_corporationDeck:5"></div>
        <div id="prs_corporationDeck:6"></div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript"></script>

{OVERALL_GAME_FOOTER}
