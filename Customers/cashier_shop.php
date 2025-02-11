<?php
include("config.php");
extract($_SESSION);
$stmt_edit = $DB_con->prepare('SELECT * FROM users WHERE user_email =:user_email');
$stmt_edit->execute(array(':user_email' => $user_email));
$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
extract($edit_row);

$stmt_edit = $DB_con->prepare("select sum(order_total) as total from orderdetails where user_id=:user_id and order_status='Ordered'");
$stmt_edit->execute(array(':user_id' => $user_id));
$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
extract($edit_row);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CML Paint Trading</title>
    <link rel="shortcut icon" href="../assets/img/logo.png" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="css/local.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript" src="jquery.fancybox.js?v=2.1.5"></script>
    <link rel="stylesheet" type="text/css" href="jquery.fancybox.css?v=2.1.5" media="screen" />
    <link rel="stylesheet" type="text/css" href="jquery.fancybox-buttons.css?v=1.0.5" />

    <script type="text/javascript" src="jquery.fancybox-buttons.js?v=1.0.5"></script>
    <link rel="stylesheet" type="text/css" href="jquery.fancybox-thumbs.css?v=1.0.7" />
    <script type="text/javascript" src="jquery.fancybox-thumbs.js?v=1.0.7"></script>
    <script type="text/javascript" src="jquery.fancybox-media.js?v=1.0.6"></script>
    <link rel="stylesheet" type="text/css" href="./css/cashier_shop.css"/>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.fancybox').fancybox();
            $(".fancybox-effects-a").fancybox({
                helpers: {
                    title: {
                        type: 'outside'
                    },
                    overlay: {
                        speedOut: 0
                    }
                }
            });
            $(".fancybox-effects-b").fancybox({
                openEffect: 'none',
                closeEffect: 'none',
                helpers: {
                    title: {
                        type: 'over'
                    }
                }
            });
            $(".fancybox-effects-c").fancybox({
                wrapCSS: 'fancybox-custom',
                closeClick: true,
                openEffect: 'none',
                helpers: {
                    title: {
                        type: 'inside'
                    },
                    overlay: {
                        css: {
                            'background': 'rgba(238,238,238,0.85)'
                        }
                    }
                }
            });
            $(".fancybox-effects-d").fancybox({
                padding: 0,
                openEffect: 'elastic',
                openSpeed: 150,
                closeEffect: 'elastic',
                closeSpeed: 150,
                closeClick: true,
            });
            $('.fancybox-buttons').fancybox({
                openEffect: 'none',
                closeEffect: 'none',
                prevEffect: 'none',
                nextEffect: 'none',
                closeBtn: false,
                helpers: {
                    title: {
                        type: 'inside'
                    },
                    buttons: {}
                },
                afterLoad: function() {
                    this.title = 'Image ' + (this.index + 1) + ' of ' + this.group.length + (this.title ? ' - ' + this.title : '');
                }
            });
            $('.fancybox-thumbs').fancybox({
                prevEffect: 'none',
                nextEffect: 'none',
                closeBtn: false,
                arrows: false,
                nextClick: true,
                helpers: {
                    thumbs: {
                        width: 50,
                        height: 50
                    }
                }
            });
            $('.fancybox-media')
                .attr('rel', 'media-gallery')
                .fancybox({
                    openEffect: 'none',
                    closeEffect: 'none',
                    prevEffect: 'none',
                    nextEffect: 'none',
                    arrows: false,
                    helpers: {
                        media: {},
                        buttons: {}
                    }
                });
            $("#fancybox-manual-a").click(function() {
                $.fancybox.open('1_b.jpg');
            });
            $("#fancybox-manual-b").click(function() {
                $.fancybox.open({
                    href: 'iframe.html',
                    type: 'iframe',
                    padding: 5
                });
            });
            $("#fancybox-manual-c").click(function() {
                $.fancybox.open([{
                    href: '1_b.jpg',
                    title: 'My title'
                }, {
                    href: '2_b.jpg',
                    title: '2nd title'
                }, {
                    href: '3_b.jpg'
                }], {
                    helpers: {
                        thumbs: {
                            width: 75,
                            height: 50
                        }
                    }
                });
            });
        });
    </script>

    <style>
        #customerContact:invalid {
            border-color: red !important;
        }
    </style>
</head>

<body>
    <div id="wrapper">
        <?php include_once("navigation.php") ?>

        <div id="page-wrapper">
            <div class="alert alert-default" style="color:white;background-color:#008CBA">
                <center>
                    <h3> <span class="glyphicon glyphicon-shopping-cart"></span> This is our Paint & Brush stocks, Shop now!</h3>
                </center>
            </div>

            <br />
            <label for="filter">Filter</label>
            <input class="form-control" type="text" id="search" placeholder="Search items..." oninput="filterText()" style="width: 30%; margin-bottom: 10px;">

            <div style="display: flex; width: 100%; align-items: center; gap: 10px;">
                <div class="form-group">
                    <select id="filter1" class="form-control" onchange="updateFilterOptions()">
                        <option value="All">All</option>
                        <option value="Wall">Wall</option>
                        <option value="Wood">Wood</option>
                        <option value="Metal">Metal</option>
                    </select>
                </div>
                <div class="form-group">
                    <select id="filter2" class="form-control" onchange="updateFilterOptions()">
                        <option value="Interior">Interior</option>
                        <option value="Exterior">Exterior</option>
                        <option value="Tools">Tools</option>
                    </select>
                </div>
                <div class="form-group">
                    <select id="filter3" class="form-control" onchange="filterItems()">
                        <option value="Flat/Matte">Flat/Matte</option>
                        <option value="Gloss">Gloss</option>
                        <option value="Primer">Primer</option>
                        <option value="Acrytex">Acrytex</option>
                        <option value="QDE">QDE</option>
                        <option value="Oil Paint">Oil Paint</option>
                        <option value="Enamel">Enamel</option>
                        <option value="Alkyds">Alkyds</option>
                        <option value="Acrylic">Acrylic</option>
                        <option value="Latex">Latex</option>
                        <option value="Brush">Brush</option>
                        <option value="Tape">Tape</option>
                    </select>
                </div>
            </div>


            <?php
            require '../vendor/autoload.php';

            use Dotenv\Dotenv;

            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->load();

            $dbHost = $_ENV['DB_HOST'];
            $dbName = $_ENV['DB_NAME'];
            $dbUser = $_ENV['DB_USER'];
            $dbPass = $_ENV['DB_PASS'];

            $conn=mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

            if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
            }

            $start = 0;
            $limit = 8;
            $id = 1; // Initialize $id with a default value

            if (isset($_GET['id']) && !empty($_GET['id'])) {
                $id = intval($_GET['id']);
                $start = ($id - 1) * $limit;
            }

            $branch = $_SESSION['current_branch'];
            $query = mysqli_query($conn, "
                SELECT i.* 
                FROM items i
                INNER JOIN (
                    SELECT item_name, brand_name, type, gl, pallet_id, branch, MIN(expiration_date) as min_expiration
                    FROM items
                    GROUP BY item_name, brand_name, type, gl, pallet_id, branch
                ) as subquery
                ON i.item_name = subquery.item_name 
                AND i.brand_name = subquery.brand_name 
                AND i.type = subquery.type 
                AND i.gl = subquery.gl 
                AND i.pallet_id = subquery.pallet_id 
                AND i.branch = subquery.branch 
                AND i.expiration_date = subquery.min_expiration
                WHERE i.branch = '$branch'
                LIMIT $start, $limit
            ");

            while ($query2 = mysqli_fetch_assoc($query)) {
                ?>
                <div style='min-width: 280px !important;' class='col-sm-3 panel-item' data-type='<?php echo $query2['type'] ?>' data-brand='<?php echo $query2['brand_name'] ?>'>
                    <div class='panel panel-default' style='border-color:#008CBA;'>
                        <div class='panel-heading' style='color:white;background-color: #033c73;'>
                            <center> 
                                <div style='text-align:center;background-color: white;' class='form-control'><?php echo $query2['brand_name'] ?></div>
                            </center>
                        </div>
                        <div class='panel-body'>
                            <a class='fancybox-buttons' href='../Admin/item_images/<?php echo $query2['item_image'] ?>'data-fancybox-group='button' title='Page <?php $id . "- " . $query2['item_name'] ?>'>
                                <img src='../Admin/item_images/<?php echo $query2['item_image'] ?>' class='img img-thumbnail' style='width:100%;height:260px;object-fit: contain;' />
                            </a>
                            <center><h4><?php echo $query2['item_name'] ?></h4></center>
                            <center><h4> Price: &#8369; <?php echo $query2['item_price'] ?> </h4></center>
                            <div style='display: flex;'>
                                <button class='btn btn-danger' style='flex: 1;' 
                                onclick="addToCart(<?= $query2['item_id'] ?>, '<?= htmlspecialchars($query2['item_name'], ENT_QUOTES) ?>',<?= $query2['item_price'] ?>, <?= $query2['quantity'] ?>, '<?= htmlspecialchars($query2['item_image'], ENT_QUOTES) ?>')">
                                <span class='glyphicon glyphicon-shopping-cart'></span> Add </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }

            echo "<div class='container'></div>";

            $rows = mysqli_num_rows(mysqli_query($conn, "
            SELECT i.* 
            FROM items i
            INNER JOIN (
                SELECT item_name, brand_name, type, gl, pallet_id, branch, MIN(expiration_date) as min_expiration
                FROM items
                GROUP BY item_name, brand_name, type, gl, pallet_id, branch
            ) as subquery
            ON i.item_name = subquery.item_name 
            AND i.brand_name = subquery.brand_name 
            AND i.type = subquery.type 
            AND i.gl = subquery.gl 
            AND i.pallet_id = subquery.pallet_id 
            AND i.branch = subquery.branch 
            AND i.expiration_date = subquery.min_expiration
            WHERE i.branch = '$branch'
            LIMIT $start, $limit
        "));
            $total = ceil($rows / $limit);
            echo "<br /><ul class='pager'>";
            if ($id > 1) {
                echo "<li><a style='color:white;background-color : #033c73;' href='?id=" . ($id - 1) . "'>Previous Page</a><li>";
            }
            if ($id != $total) {
                echo "<li><a style='color:white;background-color : #033c73;' href='?id=" . ($id + 1) . "' class='pager'>Next Page</a></li>";
            }
            echo "</ul>";

            echo "<center><ul class='pagination pagination-lg'>";
            for ($i = 1; $i <= $total; $i++) {
                if ($i == $id) {
                    echo "<li class='pagination active'><a style='color:white;background-color : #033c73;'>" . $i . "</a></li>";
                } else {
                    echo "<li><a href='?id=" . $i . "'>" . $i . "</a></li>";
                }
            }
            echo "</ul></center>";
            ?>

            <br />

            <div class="alert alert-default" style="background-color:#033c73;">
                <p style="color:white;text-align:center;">
                    &copy 2024 CML PAINT TRADING Shop | All Rights Reserved

                </p>

            </div>

        </div>
    </div>
    </div>
    </div>
    <!-- /#wrapper -->

    <div id="cart-sidebar" class="cart-sidebar">
        <div class="cart-header">
            <h3>Shopping Cart</h3>
            <!-- <button class="btn btn-sm btn-default" onclick="toggleCart()">×</button> -->
        </div>
        <div class="cart-items">
            <!-- Cart items will be dynamically added here -->
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <strong>Total: ₱</strong>
                <span id="cart-total-amount">0.00</span>
            </div>
            <div class="form-group">
                <label for="customerName" class="form-label">Customer name (optional):</label>
                <input id="customerName" class="form-control" placeholder="Enter customer name" type="text" name="customer_name">
            </div>
            <div class="form-group">
                <label for="customerContact" class="form-label">Customer contact no. (optional):</label>
                <input id="customerContact" class="form-control" placeholder="Enter customer contact no." type="text" name="customer_contact_no" pattern="^(0|\+63)[0-9]{10}$">
            </div>
            <button class="btn btn-success btn-block" onclick="checkout()">Checkout</button>
            <button class="btn btn-danger btn-block" onclick="clearItems()">Clear</button>
        </div>
    </div>

    <!-- Mediul Modal -->
    <div class="modal fade" id="setAccount" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
        <div class="modal-dialog modal-sm">
            <div style="color:white;background-color:#008CBA" class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h2 style="color:white" class="modal-title" id="myModalLabel">Account Settings</h2>
                </div>
                <div class="modal-body">

                    <form enctype="multipart/form-data" method="post" action="settings.php">
                        <fieldset>
                            <p>Firstname:</p>
                            <div class="form-group">

                                <input class="form-control" placeholder="Firstname" name="user_firstname" type="text" value="<?php echo $user_firstname; ?>" required>
                            </div>
                            <p>Lastname:</p>
                            <div class="form-group">
                                <input class="form-control" placeholder="Lastname" name="user_lastname" type="text" value="<?php echo $user_lastname; ?>" required>
                            </div>

                            <p>Address:</p>
                            <div class="form-group">

                                <input class="form-control" placeholder="Address" name="user_address" type="text" value="<?php echo $user_address; ?>" required>


                            </div>

                            <p>Password:</p>
                            <div class="form-group">

                                <input class="form-control" placeholder="Password" name="user_password" type="password" value="<?php echo $user_password; ?>" required>
                            </div>

                            <div class="form-group">
                                <input class="form-control hide" name="user_id" type="text" value="<?php echo $user_id; ?>" required>
                            </div>
                        </fieldset>


                </div>
                <div class="modal-footer">

                    <button class="btn btn-block btn-success btn-md" name="user_save">Save</button>

                    <button type="button" class="btn btn-block btn-danger btn-md" data-dismiss="modal">Cancel</button>


                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#priceinput').keypress(function(event) {
                return isNumber(event, this)
            });
        });

        function isNumber(evt, element) {

            var charCode = (evt.which) ? evt.which : event.keyCode

            if (
                (charCode != 45 || $(element).val().indexOf('-') != -1) &&
                (charCode != 46 || $(element).val().indexOf('.') != -1) &&
                (charCode < 48 || charCode > 57))
                return false;

            return true;
        }

        function filterText() {
            const searchValue = document.getElementById('search').value.toLowerCase();
            const panels = document.querySelectorAll('.panel-item');

            panels.forEach(panel => {
                const itemName = panel.querySelector('textarea').value.toLowerCase();
                if (itemName.includes(searchValue)) {
                    panel.style.display = '';
                } else {
                    panel.style.display = 'none';
                }
            });
        }
    </script>

    <script>
        // Function to update options in filter2 and filter3 based on filter1 and filter2 selections
        function updateFilterOptions() {
            var filter1 = document.getElementById('filter1').value.toLowerCase();
            var filter2 = document.getElementById('filter2').value.toLowerCase();
            var filter3Select = document.getElementById('filter3');

            var filter3Options = [];

            if (filter1 === 'wall') {
                if (filter2 === 'interior') {
                    filter3Options = ['Flat/Matte', 'Gloss', 'Primer'];
                } else if (filter2 === 'exterior') {
                    filter3Options = ['Acrytex', 'Primer'];
                } else if (filter2 === 'tools') {
                    filter3Options = ['Brush', 'Tape'];
                }
            } else if (filter1 === 'wood') {
                if (filter2 === 'interior') {
                    filter3Options = ['QDE', 'Oil Paint'];
                } else if (filter2 === 'exterior') {
                    filter3Options = ['Enamel'];
                } else if (filter2 === 'tools') {
                    filter3Options = ['Brush', 'Tape'];
                }
            } else if (filter1 === 'metal') {
                if (filter2 === 'interior') {
                    filter3Options = ['Alkyds'];
                } else if (filter2 === 'exterior') {
                    filter3Options = ['Acrylic', 'Latex'];
                } else if (filter2 === 'tools') {
                    filter3Options = ['Brush', 'Tape'];
                }
            }

            // Update filter3 options
            filter3Select.innerHTML = ''; // Clear current options

            for (var i = 0; i < filter3Options.length; i++) {
                var option = document.createElement('option');
                option.value = filter3Options[i].toLowerCase();
                option.textContent = filter3Options[i];
                filter3Select.appendChild(option);
            }

            filterItems();
        }

        // Function to filter items based on filter1, filter2, and filter3 selections
        function filterItems() {
            var filter1 = document.getElementById('filter1').value.toLowerCase();
            var filter2 = document.getElementById('filter2').value.toLowerCase();
            var filter3 = document.getElementById('filter3').value.toLowerCase();
            var panels = document.getElementsByClassName('panel-item');

            Array.from(panels).forEach(panel => {
                const itemType = panel.getAttribute("data-type").toLowerCase();
                if (itemType.includes(filter1) || itemType.includes(filter2) || itemType.includes(filter3)) {
                    panel.style.display = "";
                } else {
                    panel.style.display = "none";
                }
            });
        }

        // Initialize filter2 options based on filter1 on page load
        document.addEventListener('DOMContentLoaded', updateFilterOptions);
    </script>

<script>
let cart = new FormData();

function addToCart(itemId, name, price, maxQuantity, itemImage) {
    const item = cart.get(itemId);
    let quantity = 1;
    if (item) {
        quantity = JSON.parse(item).quantity + 1;
    }

    if (quantity > maxQuantity) {
        showAlert('warning', 'Quantity Limit Reached', `Maximum quantity of ${maxQuantity} reached for this item.`);
        return;
    }

    cart.set(itemId, JSON.stringify({ name, price, quantity, maxQuantity, itemImage }));
    updateCartDisplay();
}

function removeItem(itemId) {
    // showConfirmation('Remove Item', 'Are you sure you want to remove this item?', () => {
        cart.delete(itemId);
        updateCartDisplay();
        // showAlert('success', 'Removed!', 'The item has been removed from your cart.');
    // });
}

function clearItems() {
    cart = new FormData();
    updateCartDisplay();
}

function updateQuantity(itemId, quantity) {
    const maxQuantity = JSON.parse(cart.get(itemId)).maxQuantity;
    const newQuantity = Math.max(1, Math.min(parseInt(quantity) || 1, maxQuantity));
    
    if (newQuantity < 1 || newQuantity > maxQuantity) {
        showAlert('warning', 'Invalid Quantity', `Quantity must be between 1 and ${maxQuantity}.`);
        return;
    }

    if (cart.has(itemId)) {
        const item = JSON.parse(cart.get(itemId));
        item.quantity = newQuantity;
        cart.set(itemId, JSON.stringify(item));
        updateCartDisplay();
    }
}

function updateCartDisplay() {
    const cartItems = document.querySelector('.cart-items');
    cartItems.innerHTML = ''; // Clear previous items
    let total = 0;

    for (let [itemId, itemData] of cart.entries()) {
        const item = JSON.parse(itemData);
        const itemTotal = item.price * item.quantity;
        total += itemTotal;

        const itemElement = document.createElement('div');
        itemElement.className = 'cart-item';
        itemElement.innerHTML = `
            <img src="../Admin/item_images/${item.itemImage}" alt="${item.name}">
            <div class="cart-item-details">
                <div class="cart-item-header">
                    <h4>${item.name}</h4>
                    <button class="btn btn-sm btn-danger" onclick="removeItem('${itemId}')">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
                <div class="cart-item-controls">
                    <div>₱${item.price.toFixed(2)}</div>
                    <input type="number" 
                        class="form-control quantity-input" 
                        value="${item.quantity}"
                        min="1"
                        max="${item.maxQuantity}"
                        onchange="updateQuantity('${itemId}', this.value)">
                    <strong>₱${itemTotal.toFixed(2)}</strong>
                </div>
            </div>
        `;
        cartItems.appendChild(itemElement);
    }

    document.getElementById('cart-total-amount').textContent = total.toFixed(2);
    updatePaymentSection(total);
}


document.addEventListener('input', event => {
    const quantityInput = event.target.closest('.quantity-input');
    if (quantityInput) {
        if (+quantityInput.value < +quantityInput.min) {
            quantityInput.value = +quantityInput.min;
        }
        if (+quantityInput.value > +quantityInput.max) {
            quantityInput.value = +quantityInput.max;
        }
    }
});

function updatePaymentSection(total) {
    const paymentInput = document.getElementById('payment-amount');
    if (paymentInput) {
        const payment = parseFloat(paymentInput.value) || 0;
        const change = payment - total;
        document.getElementById('change-amount').textContent = change >= 0 ? change.toFixed(2) : '0.00';
    }
}

function processPayment() {
    const total = parseFloat(document.getElementById('cart-total-amount').textContent);
    const payment = parseFloat(document.getElementById('payment-amount').value) || 0;

    if (cart.size === 0) {
        showAlert('warning', 'Empty Cart', 'Please add items to the cart before processing payment.');
        return;
    }

    if (payment < total) {
        showAlert('error', 'Insufficient Payment', 'The payment amount must be equal to or greater than the total amount.');
        return;
    }

    Swal.fire({
        icon: 'success',
        title: 'Payment Successful',
        text: `Change: ₱${(payment - total).toFixed(2)}`,
        confirmButtonText: 'Print Receipt'
    }).then((result) => {
        if (result.isConfirmed) {
            printReceipt();
            cart = new FormData(); // Reset cart
            updateCartDisplay();
            document.getElementById('payment-amount').value = '';
        }
    });
}

function printReceipt() {
    const receiptWindow = window.open('', '_blank');
    const total = parseFloat(document.getElementById('cart-total-amount').textContent);
    const payment = parseFloat(document.getElementById('payment-amount').value) || 0;
    const change = payment - total;
    
    let receiptContent = `
        <html>
        <head>
            <title>Receipt - CML Paint Trading</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .receipt-header { text-align: center; margin-bottom: 20px; }
                .receipt-item { margin: 10px 0; }
                .receipt-total { margin-top: 20px; border-top: 1px solid #000; padding-top: 10px; }
                .receipt-footer { margin-top: 30px; text-align: center; font-size: 0.9em; }
            </style>
        </head>
        <body>
            <div class="receipt-header">
                <h2>CML Paint Trading</h2>
                <p>Official Receipt</p>
                <p>${new Date().toLocaleString()}</p>
            </div>
            <div class="receipt-items">
    `;

    for (let [itemId, itemData] of cart.entries()) {
        const item = JSON.parse(itemData);
        const itemTotal = item.price * item.quantity;
        receiptContent += `
            <div class="receipt-item">
                <p>${item.name} x ${item.quantity}</p>
                <p>Unit Price: ₱${item.price.toFixed(2)}</p>
                <p>Subtotal: ₱${itemTotal.toFixed(2)}</p>
            </div>
        `;
    }

    receiptContent += `
            </div>
            <div class="receipt-total">
                <p><strong>Total Amount: ₱${total.toFixed(2)}</strong></p>
                <p>Payment: ₱${payment.toFixed(2)}</p>
                <p>Change: ₱${change.toFixed(2)}</p>
            </div>
            <div class="receipt-footer">
                <p>Thank you for shopping at CML Paint Trading!</p>
                <p>Please come again</p>
            </div>
        </body>
        </html>
    `;

    receiptWindow.document.write(receiptContent);
    receiptWindow.document.close();
    
    setTimeout(() => {
        receiptWindow.print();
        receiptWindow.close();
    }, 500);
}

// Utility functions for alerts and confirmations
function showAlert(icon, title, text) {
    Swal.fire({ icon, title, text });
}

function showConfirmation(title, text, onConfirm) {
    Swal.fire({
        title,
        text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, remove it!'
    }).then((result) => {
        if (result.isConfirmed) onConfirm();
    });
}

// Initialize cart display on page load
document.addEventListener('DOMContentLoaded', () => {
    updateCartDisplay();
});

function checkout() {
    const items = new FormData();
    const customer_name = document.getElementById('customerName').value;
    const customer_contact_no = document.getElementById('customerContact').value;

    for (const [key, val] of cart.entries()) {
        const qty = JSON.parse(val).quantity;
        items.append('qtys[]', qty);
        items.append('item_ids[]', key);
    }
    items.append('customer_name', customer_name);
    items.append('customer_contact_no', customer_contact_no);

    fetch('./cashier_checkout.php', {
        method: 'POST',
        body: items,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = document.createElement('div');
            modal.id = 'checkingOut';
            modal.innerHTML = data.message;
            document.body.appendChild(modal);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops!',
                text: data.message,
                confirmButtonText: 'Try Again'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An unexpected error occurred. Please try again.',
            confirmButtonText: 'Close'
        });
    });
}

function printReceipt() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = './cashier_reciept.php'; // Your receipt generation script
    form.target = '_blank'; // Open in a new tab

    for (const [key, val] of cart.entries()) {
        const qty = JSON.parse(val).quantity;

        // Create hidden input for item ID
        const itemIdInput = document.createElement('input');
        itemIdInput.type = 'hidden';
        itemIdInput.name = 'item_ids[]';
        itemIdInput.value = key;
        form.appendChild(itemIdInput);

        // Create hidden input for quantity
        const qtyInput = document.createElement('input');
        qtyInput.type = 'hidden';
        qtyInput.name = 'qtys[]';
        qtyInput.value = qty;
        form.appendChild(qtyInput);
    }

    // Append the form to the body
    document.body.appendChild(form);

    // Submit the form
    form.submit();

    // Remove the form after submission
    form.remove();
}

document.addEventListener('click', (event) => {
    const printRecieptTrigger = event.target.closest('#printRecieptTrigger');
    if (printRecieptTrigger) {
        printReceipt();
    }

    const confirmPayment = event.target.closest('#confirmPayment');
    if (confirmPayment) {
        const token = confirmPayment.getAttribute('data-token');
        const formData = new FormData();
        formData.append('_token', token);
        fetch('./cashier_checkout.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('checkingOut').remove();
                Swal.fire({
                    icon: 'success',
                    title: 'Payment Confirmed!',
                    text: data.message,
                    confirmButtonText: 'Ok'
                    }).then(arg => {
                        location.reload();
                    });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: data.message,
                    confirmButtonText: 'Try Again'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An unexpected error occurred. Please try again.',
                confirmButtonText: 'Close'
            });
        });
    }
});
</script>
</body>

</html>

