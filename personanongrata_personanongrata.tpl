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
      class="prs_playerZone whiteblock"
      style="border-color: #{PLAYER_COLOR}"
    >
      <h3 class="prs_zoneTitle" style="color: #{PLAYER_COLOR}">
        {PLAYER_NAME}
      </h3>
      <div id="prs_hand${PLAYER_ID}" class="prs_hand"></div>
      <div id="prs_downloaded${PLAYER_ID}" class="prs_downloaded"></div>
    </div>
    <!-- END playerzone -->
  </div>
  <!-- BEGIN myzone -->
  <div
    id="prs_myZone"
    class="prs_myZone whiteblock"
    style="border-color: #{PLAYER_COLOR}"
  >
    <h3 class="prs_zoneTitle" style="color: #{PLAYER_COLOR}">{PLAYER_NAME}</h3>
    <div id="prs_hand${PLAYER_ID}" class="prs_myHand"></div>
    <div id="prs_downloaded${PLAYER_ID}" class="prs_myDownloaded"></div>
  </div>
  <!-- END myzone -->
</div>

<script type="text/javascript"></script>

{OVERALL_GAME_FOOTER}
