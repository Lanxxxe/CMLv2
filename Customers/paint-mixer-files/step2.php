<div class="main-container step2-container">
    <div class="progress-container">
        <?php 
            include_once("./paint-mixer-files/steps-nav.php");
        ?>

        <div class="select-color-container">
            <a href="color-change.php?step=1">< Select a Different Color</a>
            <h1>Find Colors for Your Photo</h1>
    
            <div class="pick-color">
                <div class="all-pallet">
                    <img src="https://visualizecolor.blob.core.windows.net/ppgpaints/colorspage/browseall.jpg" alt="Color Pallets">
                    <span>Browse All Colors</span>
                </div>
                <div class="curated-pallet">
                    <img src="https://visualizecolor.blob.core.windows.net/ppgpaints/colorspage/collections.jpg" alt="Curated Photos">
                    <span>View Curated Palettes</span>
                </div>
                <div class="search-pallet">
                    <img src="https://visualizecolor.blob.core.windows.net/ppgpaints/colorspage/search.jpg" alt="Search Icon">
                    <span>Search by Color Name</span>
                </div>
            </div>  
        </div>
        
    </div>

    <?php include './paint-mixer-files/paint-order.php' ?>
</div>