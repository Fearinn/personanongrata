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
  <div id="prs_playerZones" class="prs_playerZones">
    <!-- BEGIN playerzone -->
    <div
      id="prs_playerZone"
      class="prs_playerZone prs_zone whiteblock"
      style="border-color: #{PLAYER_COLOR}"
    >
      <h3 class="prs_zoneTitle" style="color: #{PLAYER_COLOR}">
        {PLAYER_NAME}
      </h3>
      <div id="prs_hacker${PLAYER_ID}" class="prs_hacker"></div>
      <div id="prs_hand${PLAYER_ID}" class="prs_hand">
        <div id="prs_handOfActions${PLAYER_ID}" class="prs_handOfActions"></div>
      </div>
      <div id="prs_downloaded${PLAYER_ID}" class="prs_downloaded"></div>
    </div>
    <!-- END playerzone -->
  </div>
  <div id="prs_publicZone" class="prs_publicZone prs_zone whiteblock">
    <h3 class="prs_zoneTitle">{CORPORATION_CARDS}</h3>
    <div id="prs_keys" class="prs_keys"></div>
    <div id="prs_corporationDecks" class="prs_corporationDecks">
      <div id="prs_corpDeck:1"></div>
      <div id="prs_corpDeck:2"></div>
      <div id="prs_corpDeck:3"></div>
      <div id="prs_corpDeck:4"></div>
      <div id="prs_corpDeck:5"></div>
      <div id="prs_corpDeck:6"></div>
    </div>
  </div>
  <!-- BEGIN myzone -->
  <div
    id="prs_myZone"
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
    <div id="prs_hand${PLAYER_ID}" class="prs_myHand prs_hand">
      <div id="prs_hacker${PLAYER_ID}" class="prs_hacker"></div>
      <div id="prs_handOfActions${PLAYER_ID}" class="prs_handOfActions"></div>
    </div>
    <div id="prs_downloaded${PLAYER_ID}" class="prs_myDownloaded"></div>
  </div>
  <!-- END myzone -->
</div>

<script type="text/javascript"></script>

{OVERALL_GAME_FOOTER}
