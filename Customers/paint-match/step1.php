<div class="main-container step1-container border-2">
    <div class="progress-container">

        <?php 
            include_once("steps-navigation.php");
            include("db-connect.php");
        ?>

        <div class="choose-pallets-container">
            <!-- <a class="returnLink" href="paint-match.php?step=1">< Return</a> -->

            <h1 class="stepHeader">
                Select Color for Latex Paint: <span id="choosen-brand"><?php echo htmlspecialchars($brand) ?></span>
            </h1>
            
            <div class="choose-method">
                <a href="paint-match.php?step=2browse">
                    <div>
                        <img src="https://visualizecolor.blob.core.windows.net/ppgpaints/colorspage/browseall.jpg" alt="">
                        <p>Browse Colors</p>
                    </div>
                </a>
                <a href="paint-match.php?step=2search">
                    <div>
                        <img src="https://visualizecolor.blob.core.windows.net/ppgpaints/colorspage/search.jpg" alt="">
                        <p>Search Colors</p>
                    </div>
                </a>
            </div>
        </div>

    </div>
</div>