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
    id="prs_playerArea${PLAYER_ID}"
    class="prs_myArea prs_area"
    style="border-color: #{PLAYER_COLOR}"
  >
    <div class="prs_areaHeader">
      <h3 class="prs_areaTitle" style="color: #{PLAYER_COLOR}">{YOU}</h3>
    </div>
    <div class="prs_playerZones">
      <div
        id="prs_archive${PLAYER_ID}"
        class="prs_playerZone prs_zone whiteblock"
      >
        <h4 class="prs_zoneTitle" style="color: #{PLAYER_COLOR}">
          {ARCHIVED_CARDS}
        </h4>
        <div id="prs_actionDiscard${PLAYER_ID}" class="prs_actionDiscard"></div>
        <div id="prs_hacker${PLAYER_ID}" class="prs_hacker"></div>
        <div id="prs_archivedInfo${PLAYER_ID}"></div>
        <div id="prs_archivedCorporations${PLAYER_ID}"></div>
        <div id="prs_archivedKeys${PLAYER_ID}"></div>
      </div>
      <div
        id="prs_store${PLAYER_ID}"
        class="prs_playerZone prs_zone whiteblock"
      >
        <h4 class="prs_zoneTitle" style="color: #{PLAYER_COLOR}">
          {PLAY_AREA}
        </h4>
        <div id="prs_stored${PLAYER_ID}" class="prs_myStored prs_stored"></div>
        <div id="prs_encryptAction${PLAYER_ID}" class="prs_encryptAction"></div>
      </div>
      <div id="prs_playedCards" class="prs_playerZone prs_zone whiteblock">
        <h4 class="prs_zoneTitle" style="color: #{PLAYER_COLOR}">
          {PLAYED_TODAY}
        </h4>
        <div id="prs_playedAction${PLAYER_ID}" class="prs_playedAction"></div>
        <div id="prs_playedInfo${PLAYER_ID}" class="prs_playedInfo"></div>
      </div>
      <div class="prs_playerZone prs_zone whiteblock">
        <h4 class="prs_zoneTitle" style="color: #{PLAYER_COLOR}">
          {ACTION_HAND}
        </h4>
        <div
          id="prs_handOfActions${PLAYER_ID}"
          class="prs_handOfActions prs_hand"
        ></div>
      </div>
      <div class="prs_playerZone prs_zone whiteblock">
        <h4 class="prs_zoneTitle" style="color: #{PLAYER_COLOR}">
          {INFO_HAND}
        </h4>
        <div class="prs_handContainer">
          <div
            id="prs_leftTagContainer${PLAYER_ID}"
            class="prs_directionTagContainer"
          >
            <div class="prs_directionIcon" data-direction="clockwise"></div>
            <span id="prs_leftTag${PLAYER_ID}" class="prs_areaTitle"
              >clockwise</span
            >
          </div>
          <div
            id="prs_handOfInfo${PLAYER_ID}"
            class="prs_handOfInfo prs_hand"
          ></div>
          <div
            id="prs_rightTagContainer${PLAYER_ID}"
            class="prs_directionTagContainer"
          >
            <div class="prs_directionIcon" data-direction="clockwise"></div>
            <span id="prs_rightTag${PLAYER_ID}" class="prs_areaTitle"
              >clockwise</span
            >
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- END myzone -->
  <div id="prs_playerAreas" class="prs_playerAreas">
    <!-- BEGIN playerzone -->
    <div
      id="prs_playerArea${PLAYER_ID}"
      class="prs_area"
      style="border-color: #{PLAYER_COLOR}"
    >
      <div class="prs_areaHeader">
        <h3 class="prs_areaTitle" style="color: #{PLAYER_COLOR}">
          {PLAYER_NAME}
        </h3>
      </div>
      <div class="prs_playerZones">
        <div
          id="prs_archive${PLAYER_ID}"
          class="prs_playerZone prs_zone whiteblock"
        >
          <h4 class="prs_zoneTitle" style="color: #{PLAYER_COLOR}">
            {ARCHIVED_CARDS}
          </h4>
          <div
            id="prs_actionDiscard${PLAYER_ID}"
            class="prs_actionDiscard"
          ></div>
          <div id="prs_hacker${PLAYER_ID}" class="prs_hacker"></div>
          <div id="prs_archivedInfo${PLAYER_ID}"></div>
          <div id="prs_archivedCorporations${PLAYER_ID}"></div>
          <div id="prs_archivedKeys${PLAYER_ID}"></div>
        </div>
        <div
          id="prs_storedCards${PLAYER_ID}"
          class="prs_playerZone prs_zone whiteblock"
        >
          <h4 class="prs_zoneTitle" style="color: #{PLAYER_COLOR}">
            {PLAY_AREA}
          </h4>
          <div id="prs_stored${PLAYER_ID}" class="prs_stored"></div>
          <div
            id="prs_encryptAction${PLAYER_ID}"
            class="prs_encryptAction"
          ></div>
        </div>
        <div class="prs_playerZone prs_zone whiteblock">
          <h4 class="prs_zoneTitle" style="color: #{PLAYER_COLOR}">
            {ACTION_HAND}
          </h4>
          <div
            id="prs_handOfActions${PLAYER_ID}"
            class="prs_handOfActions prs_hand"
          ></div>
        </div>
        <div class="prs_playerZone prs_zone whiteblock">
          <h4 class="prs_zoneTitle" style="color: #{PLAYER_COLOR}">
            {INFO_HAND}
          </h4>
          <div class="prs_handContainer">
            <div
              id="prs_leftTagContainer${PLAYER_ID}"
              class="prs_directionTagContainer"
            >
              <div class="prs_directionIcon" data-direction="clockwise"></div>
              <span id="prs_leftTag${PLAYER_ID}" class="prs_areaTitle"
                >clockwise</span
              >
            </div>
            <div
              id="prs_handOfInfo${PLAYER_ID}"
              class="prs_handOfInfo prs_hand"
            ></div>
            <div
              id="prs_rightTagContainer${PLAYER_ID}"
              class="prs_directionTagContainer"
            >
              <div class="prs_directionIcon" data-direction="clockwise"></div>
              <span id="prs_rightTag${PLAYER_ID}" class="prs_areaTitle"
                >clockwise</span
              >
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- END playerzone -->
  </div>

  <div id="prs_publicArea" class="prs_publicArea prs_area whiteblock">
    <div class="prs_areaHeader">
      <h3 class="prs_areaTitle">{COMMON_AREA}</h3>
    </div>
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
