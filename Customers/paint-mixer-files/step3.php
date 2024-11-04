<div class="main-container step3-container">
  <div class="progress-container">

    <?php include_once("./paint-mixer-files/steps-nav.php") ?>

    <div class="room-editor-container">
      <a class="find-more-colors" href="color-change.php?step=2">
        <span class="material-symbols-outlined">keyboard_arrow_left</span>
        <span class="text">Find More Colors</span>
      </a>

      <h1>Visualize Your Room</h1>
      <h2>Click a wall to paint the selected color.</h2>

      <div class="room-visualizer">
        <div class="canvas-control">
          <button class="reset-button">
            <span class="material-symbols-outlined">
            replay
            </span>
            <span class="text">Reset</span>
          </button>
          <button class="undo-button">
            <span class="material-symbols-outlined">
            undo
            </span>
            <span class="text">Undo</span>
          </button>
          <button class="redo-button">
            <span class="material-symbols-outlined">
              redo 
            </span>
            <span class="text">Redo</span>
          </button>
          <a class="change-photo" href="color-change.php?step=1">Change Your Photo</a>
        </div>
        <div class="visualizer-canvas-container">
          <canvas id="visualizer-canvas"></canvas>
          <canvas id="zoomCanvas" class="hide">
          </canvas>
        </div>
      </div>

      <div class="share">
        <span class="label">SHARE:</span>
        <?php
          include_once "images/facebookIcon.php";
          include_once "images/pinterestIcon.php";
        ?>
      </div>
    </div>

  </div>

  <div class="choosen-color-container">
      <div class="color-picked-container">
          <div class="pallet-icon">
              <i class="bi bi-palette-fill"></i>
              <span id="order-count">0</span>
          </div>
          <span>Saved Colors</span>
      </div>
      
      <div class="right-side-bar">
        <div class="rsb-container-1">
          <!-- <div class="cml-button order-paint">
            <span>Order Paint</span>
            <span class="material-symbols-outlined">
              keyboard_arrow_right
            </span>
          </div> -->
          <div class="cml-button save-project">
            <span class="material-symbols-outlined icon">
            save 
            </span>
            <span>Save</span>
          </div>
        </div>

        <div class="">
          <div class="rsb-container-2">
            <div class="add-color-container">
              <span class="material-symbols-outlined icon">
                add
              </span>
              <span class="text">SAVE MORE COLORS</span>
            </div>
          </div>
          <div class="colors picked-container"></div>
        </div>
      </div>
  </div>

</div>