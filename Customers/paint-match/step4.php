<!-- DO NOT DELETE THIS FILE FOR FUTURE REFERENCES -->

<div class="main-container step4-container border-2">
    <div class="progress-container">

        <?php 
            include_once("steps-navigation.php");
            include_once('db-connect.php');
        ?>

        <div class="choose-rooms-container">
            <h1 class="text-center mt-4">
                Purchase Receipt
            </h1>
            
            <div class="receipt-container">
    
                <h5 class="text-center">
                    ** Please save a screenshot of the receipt **
                </h5>
                
                <div class="two-column-receipt">
                    <?php 
                    
                    $query = "SELECT * FROM paymentform WHERE id = :orderID LIMIT 1";

                    $statement = $DB_con->prepare($query);

                    $statement->bindParam(':orderID', $receiptID);
                    
                    $statement->execute();

                    $info = $statement->fetch(PDO::FETCH_ASSOC);
                    
                    if ($info) {

                        if ($info) {
                            ?>
                            <div class="items-description">
                                <label for="item-type">Name: </label>
                                <input readonly type="text" placeholder="<?php echo htmlspecialchars($info['firstname']) . ' ' . htmlspecialchars($info['lastname']) ?>">
                            </div>

                            <div class="items-description">
                                <label for="itemStock">Mobile:</label>
                                <input readonly type="text" placeholder="<?php echo htmlspecialchars($info['mobile'])?>">
                            </div>

                            <div class="items-description">
                                <label for="quantity">Item:</label>
                                <input readonly type="number" placeholder="Latex Paint">
                            </div>

                            <?php
                        } else {
                            ?>
                                <div class="items-description">
                                    <h1>The order ID number <span><?php echo htmlspecialchars($receiptID) ?></span> has no record on our system.</h1>
                                </div>
                            <?php 
                            exit();
                        }
                    }

                ?>   
                    
                    <?php 
                        $query = "SELECT palletName FROM cartitems";

                        $statement = $DB_con->prepare($query);

                        if ($statement->execute()) {
                            $codes = $statement->fetchAll(PDO::FETCH_ASSOC);
                            $colorCodes = "";
                            foreach($codes as $code){
                                $colorCodes .= htmlspecialchars($code['palletName']) . " ";
                            }
                            ?>
                            <div class="items-description">
                                <label for="quantity">Colors Codes:</label>
                                <input readonly type="text" value="<?php echo trim($colorCodes); ?>">
                            </div>
                            <?php
                        }
                        ?>
                    
                    <?php 
                    
                    if ($info) {
                        ?>
                                <div class="items-description">
                                <label for="size">Total Bill:</label>
                                <input readonly type="number"  placeholder="<?php echo htmlspecialchars($info['amount']) ?>">
                            </div>
                            
                            <div class="items-description">
                                <label for="price">Payment Method:</label>
                                <input readonly type="number"  placeholder="<?php echo htmlspecialchars($info['payment_method']) ?>">
                            </div>

                            <form action="paint-match/confirmCheckout.php" method="POST">
                                <button class="returnButton" type="submit" name="confirmCheckout">Return</button>
                            </form>
                        <?php
                    }
                    ?>
                    

                </div>
            </div>
        </div>
    </div>

</div>