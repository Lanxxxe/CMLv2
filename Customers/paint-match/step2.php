<div class="main-container step2-container">
    <div class="progress-container" style="flex: 1;">

        <?php 
            include_once("steps-navigation.php");
        ?>

        <div class="choose-brand-container">
            <h1 class="stepHeader">
                Select Paint Brands
            </h1>

            <div class="brand-logo-container">
                <?php 
                    include("db-connect.php");

                    $query = "SELECT * FROM brands";

                    $statement = $DB_con->prepare($query);

                    if($statement->execute()) {
                        $row = $statement->fetchAll();
                        // print_r($row[0]);

                        foreach($row as $result) {
                            ?>
                            <a href="paint-match.php?step=3&brandName=<?php echo htmlspecialchars($result['brand_name']) ?>">
                                <div class="flex flex-col">
                                    <img src="../Admin/<?php echo htmlspecialchars($result['brand_img']); ?>" alt="<?php echo htmlspecialchars($result['brand_name']) ?>">
                                </div>
                            </a>
                            <?php
                        
                        }
                    }
                ?>
            </div>
        </div>
    </div>
</div>