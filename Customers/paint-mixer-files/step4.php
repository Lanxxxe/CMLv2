<div class="main-container step4-container">
    <div class="progress-container" style="flex: 1;">

        <?php 
            include_once("./paint-mixer-files/steps-nav.php");
        ?>

        <div class="choose-rooms-container">
            <a class="find-more-colors" href="color-change.php?step=3">
                <span class="material-symbols-outlined">keyboard_arrow_left</span>
                <span class="text">Visualize Your Room</span>
            </a>

            <div class="outcome-container">
                <div class="right-bar-container">
                    <div class="controls">
                        <div class="cml-button try-again">
                            <span class="material-symbols-outlined icon">
                                restart_alt
                            </span>
                            <span>Try Again</span>
                        </div>
                        <div class="cml-button save-project">
                            <span class="material-symbols-outlined icon">
                                save 
                            </span>
                            <span>Save Project</span>
                        </div>
                    </div>
                    <div class="color-to-by">
                    </div>
                </div>

                <div class="visualizer-canvas-container">
                    <div class="head">
                        <div class="share">
                            <span class="label">SHARE:</span>
                            <?php
                                include_once 'images/facebookIcon.php';
                                include_once 'images/pinterestIcon.php';
                            ?>
                        </div>
                    </div>
                    <canvas id="visualizer-canvas"></canvas>
                </div>
            </div>
        </div>
    </div>


</div>