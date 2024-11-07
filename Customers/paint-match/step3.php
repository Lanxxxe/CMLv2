<div class="main-container step3-container border-2">
    <div class="progress-container" >
        <?php 
            include_once("steps-navigation.php");
            include("db-connect.php");
        ?>

        <div class="">
            <a class="returnLink" href="paint-match.php?step=2">< Return</a>

            <h1 class="stepHeader">
                Order Summary: <?php echo htmlspecialchars($brand) . ' Latex Paint' ?>
            </h1>

            <div class="review-container">
                <?php 
                $palletColors = '';
                
                $query = "SELECT * FROM items WHERE brand_name = :brand AND type = 'Latex Paint' LIMIT 1";
                $statement = $DB_con->prepare($query);
                $statement->bindParam(':brand', $brand);

                if ($statement->execute()) {
                    $itemInformation = $statement->fetch(PDO::FETCH_ASSOC);

                    if ($itemInformation) {
                        $availableStock = $itemInformation['quantity']; // Store the available stock in a variable
                        ?>

                        <div>
                            <img src="https://down-ph.img.susercontent.com/file/f92161588cb914ccdafc5213dd758d9b" alt="Latex Paint">
                            
                            <div class="collected-pallet">
                                <?php
                                $query = "SELECT * FROM cartitems";
                                $statement = $DB_con->prepare($query);
                                $statement->execute();
                                $items = $statement->fetchAll();

                                if (!empty($items)) {
                                    $totalItems = count($items);
                                    $currentIndex = 0;
                                    
                                    foreach($items as $order) {
                                        $currentIndex++;
                                        $palletColors .= htmlspecialchars($order['palletName']);
                                    
                                        if ($currentIndex < $totalItems) {
                                            $palletColors .= ", ";
                                        }
                                    ?>
                                        <div class="palletItem">
                                            <div class="palletSquare" style="background-color: <?php echo htmlspecialchars($order['palletRGB']) ?>;"></div>
                                            <div class="palletInformation">
                                                <p>Name: <span style="font-weight: bold;"><?php echo htmlspecialchars($order['palletName']) ?></span></p>
                                                <p>Code: <span style="font-weight: bold;"><?php echo htmlspecialchars($order['palletCode']) ?></span> </p>
                                                <p>RGB: <span style="font-weight: bold;"><?php echo htmlspecialchars($order['palletRGB']) ?></span></p>
                                                <p><?php echo $palletColors ?></p>
                                            </div>
                                        </div>
                                    <?php
                                        }
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="order-Information">
                                <!-- Add onsubmit attribute to call checkQuantity function -->
                                <form class="two-column-form" action="paint-match/checkout.php" method="POST" onsubmit="return checkQuantity()">
                                    <input type="hidden" name="itemID" value="<?php echo htmlspecialchars($itemInformation['item_id']) ?>">
                                    <div class="form-group">
                                        <label for="itemName">Item: <?php echo htmlspecialchars($itemInformation['item_id']) ?> </label>
                                        <input readonly type="text" id="itemName" placeholder="<?php echo htmlspecialchars($itemInformation['item_name'] . " (" . $palletColors . ")") ?>"  value="<?php echo  htmlspecialchars($itemInformation['item_name'] . " (" . $palletColors . ")") ?>"  name="itemName">                                   
                                    </div>

                                    <div class="form-group">
                                        <label for="itemStock">Available Stock:</label>
                                        <input readonly type="text" id="itemStock" placeholder="<?php echo htmlspecialchars($availableStock) ?>" value="<?php echo htmlspecialchars($availableStock) ?>" name="itemStock">
                                    </div>

                                    <div class="form-group">
                                        <label for="price">Price:</label>
                                        <input readonly type="number" id="price" placeholder="<?php echo htmlspecialchars($itemInformation['item_price']) ?>" value="<?php echo htmlspecialchars($itemInformation['item_price']) ?>" name="price">
                                    </div>

                                    <div class="form-group">
                                        <label for="size">Gallon/Liter:</label>
                                        <input readonly type="text" id="size" placeholder="<?php echo htmlspecialchars($itemInformation['gl']) ?>" value="<?php echo htmlspecialchars($itemInformation['gl']) ?>" name="size">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="quantity">Quantity:</label>
                                        <input type="number" id="quantity" name="quantity" min="0" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="quantity">Pickup Date:</label>
                                        <input type="datetime-local" id="pickupDate" name="pickupDate" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="pickup-place">Pickup Place:</label>
                                        <select id="pickup-place" name="pickupPlace" required>
                                            <option value="" selected>Choose pickup location</option>
                                            <option value="Caloocan">Caloocan</option>
                                            <option value="Valenzuela">Valenzuela</option>
                                            <option value="Quezon City">Quezon City</option>
                                            <option value="San Jose de Monte">San Jose de Monte</option>
                                        </select>                        
                                    </div>                      

                                    <div class="form-buttons">
                                        <!-- <button type="submit" name="checkout" id="checkout">Proceed to Review</button> -->
                                        <button type="submit" name="addToCart" id="addToCart">Add to Cart</button>
                                    </div>
                                </form>
                            </div>
                        <?php
                    } else {
                        ?>
                            <h3 class="text-danger">Sorry, the paint you chose is out of stock</h3>
                            <a href="paint-match.php?step=1">< choose other brand</a>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script>
    
// JavaScript function to check quantity before form submission
function checkQuantity() {
    const quantityInput = document.getElementById('quantity').value;
    const availableStock = <?php echo json_encode($availableStock); ?>;

    if (parseInt(quantityInput) > availableStock) {
        Swal.fire({
            icon: 'warning',
            title: 'Stock Warning',
            text: 'The item is currently low in stock. Plase lower the quantity you order to proceed.',
            confirmButtonText: 'OK'
        });       
         return false; 
    }
    return true; 
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[name="quantity"]').forEach(input => {
        input.addEventListener('input', event => {
            const min = +input.getAttribute('min');
            if(+input.value < +min) {
                input.value = min;
            }
        });
    });
});
</script>
