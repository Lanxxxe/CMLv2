<div class="main-container step2-container">
    <div class="progress-container">
        <?php 
            include_once("./paint-mixer-files/steps-nav.php");
        ?>
    
        <div class="browse-colors-container">
            <a href="color-change.php?step=2">< Explore Different Colors</a>
            <h1>View Curated Color</h1>    
            
            <div class="pick-curated-color">
                <div class="retreater-container">
                    <div class="retreater-pallet">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <img src="https://visualizecolor.blob.core.windows.net/voc/collectionimages/retreater.jpg" alt="Color Pallets">
                    <span>The Retreater</span>
                </div>
                <div class="weaver-container">
                    <div class="weaver-pallet">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <img src="https://visualizecolor.blob.core.windows.net/voc/collectionimages/dream_weaver.jpg" alt="Curated Photos">
                    <span>The Dream Weaver</span>
                </div>
                <div class="commoner-container">
                    <div class="commoner-pallet">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <img src="https://visualizecolor.blob.core.windows.net/voc/collectionimages/commoner.jpg" alt="Search Icon">
                    <span>The Commoner</span>
                </div>
                <div class="brave-container">
                    <div class="brave-pallet">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <img src="https://visualizecolor.blob.core.windows.net/voc/collectionimages/brave.jpg" alt="Search Icon">
                    <span>The Brave</span>
                </div>
            </div>  
        </div>
    </div>
    
    <?php include './paint-mixer-files/paint-order.php' ?>
</div>