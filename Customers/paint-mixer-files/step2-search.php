<div class="main-container step2-container">
    <div class="progress-container">
        <?php 
            include_once("./paint-mixer-files/steps-nav.php");
        ?>
    
        <div class="search-colors-container">
            <a href="color-change.php?step=2">< Explore Different Colors</a>
            <h1>Search by Color Name</h1>
            <span id="">Search by color name or number and save it to your project.</span>
            
            <div class="search-bar">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" oninput="search()" onfocus="focus()" placeholder="Search by color name ">
            </div>
            <h3 id="result-title"></h3>
            <div id="searchResults">
                
            </div>

        </div>
    </div>
    
    <?php include './paint-mixer-files/paint-order.php' ?>
</div>