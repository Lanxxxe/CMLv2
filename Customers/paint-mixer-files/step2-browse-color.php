<div class="main-container step2-container">
    <div class="progress-container">
        <?php 
            include_once("./paint-mixer-files/steps-nav.php");
        ?>
    
        <div class="browse-colors-container">
            <a href="color-change.php?step=2">< Explore Different Colors</a>
            <h1>Browse Paint Colors</h1>
            <span id="color-name">Choose a Color</span>
    
            <div class="color-container">
                <div id="circle-colors-container"></div>
                <div id="square-colors-container"></div>
            </div>
        </div>
    </div>
    
    <?php include './paint-mixer-files/paint-order.php' ?>
</div>