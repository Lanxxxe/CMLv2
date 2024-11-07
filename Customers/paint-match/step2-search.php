<div class="main-container step2-container border-2">
    <div class="progress-container">

        <?php 
            include_once("steps-navigation.php");
            include("db-connect.php");
        ?>

        <div class="choose-pallets-container">

            <a class="returnLink" href="paint-match.php?step=2">< Return</a>
    
            <h1>
                Search Color for Latex Paint: <span id="choosen-brand"><?php echo htmlspecialchars($brand) ?></span>
            </h1>


            <div class="color-items">

                <div class="search-result-container">
                    <form class="searchForm" action="paint-match.php?step=2search&brandName=<?php echo htmlspecialchars($brand) ?>" method="POST">
                        <input type="text" name="searchValue" placeholder="Enter the name of a color">
                        
                        <button type="submit" name="searchButton">Search</button>
                    </form>

                    <div class="query-result">
                        <?php 
                        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['searchButton'])) {
                            $searchValues = $_POST['searchValue'];

                            $query = "SELECT * FROM pallets WHERE name LIKE :searchValue OR code LIKE :searchValue";
                            $statement = $DB_con->prepare($query);
                            
                            $statement->bindValue(':searchValue', "%$searchValues%");
                            
                            
                            if($statement->execute()) {
                                $result = $statement->fetchAll(PDO::FETCH_ASSOC);

                                if ($result) {
                                    foreach($result as $pallet) {
                                        ?>
                                            <form action="paint-match/addToCart.php" method="POST">
                                                <input type="hidden" name="curStep" value="2search">
                                                <input type="hidden" name="brand" value="<?php echo htmlspecialchars($brand) ?>">
                                                <div class="pallets">
                                                    <div class="color" id="<?php echo htmlspecialchars($pallet['code']) ?>" style="background-color: <?php echo htmlspecialchars($pallet['rgb']) ?>;">
                                                        <input type="hidden" name="palletName" value="<?php echo htmlspecialchars($pallet['name']) ?>">
                                                        <input type="hidden" name="palletCode" value="<?php echo htmlspecialchars($pallet['code']) ?>">
                                                        <input type="hidden" name="palletRGB" value="<?php echo htmlspecialchars($pallet['rgb']) ?>">
                                                        <button class="submitButton" type="submit" name="addPallet">Add</button>
                                                    </div>
                                                    <span class="code"><?php echo htmlspecialchars($pallet['name']) ?></span>
                                                </div>
                                            </form>

                                        <?php
                                    }
                                } else {
                                    ?>
                                        <h3>The color you searched is not available</h3>
                                    <?php
                                }
                            }             
                        }
                        
                        ?>
                    </div>
                </div>

                <div id="orderItems">
                        <h3>Collected Pallet</h3>
    
                        <?php 
                        $query = "SELECT * FROM cartitems";

                        $statement = $DB_con->prepare($query);

                        $statement->execute();

                        $items = $statement->fetchAll();

                        if (!empty($items)) {
                            foreach($items as $order) {
                            ?>
                                <div class="palletItem">
                                    <form action="paint-match/removeFromCart.php" method="POST">
                                        <input type="hidden" name="curStep" value="2search">
                                        <input type="hidden" name="brand" value="<?php echo htmlspecialchars($brand) ?>">
                                        
                                        <div class="palletSquare" style="background-color: <?php echo htmlspecialchars($order['palletRGB']) ?>;">
                                            <input type="hidden" name="palletID" value="<?php echo htmlspecialchars($order['itemID']) ?>">
                                            <button class="removeButton" type="submit" name="removeButton">Remove</button>
                                        </div>
                                    </form>
                                    <div class="palletInformation">
                                        <p>Name: <span style="font-weight: bold;"><?php echo htmlspecialchars($order['palletName']) ?></span></p>
                                        <p>Code: <span style="font-weight: bold;"><?php echo htmlspecialchars($order['palletCode']) ?></span> </p>
                                        <p>RGB: <span style="font-weight: bold;"><?php echo htmlspecialchars($order['palletRGB']) ?></span></p>
                                    </div>
                                </div>

                            <?php
                            }
                        } else {
                            ?>
                            <div class="palletItem">
                                <p class=" text-danger">Empty Pallet List</p>
                            </div>
                            <?php
                        }
                        
                        ?>


                    <a href="paint-match.php?step=2" class="checkoutButton" role="button">Proceed to checkout</a>
                </div>
                
            </div>
        </div>

    </div>
</div>