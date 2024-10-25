<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="index.php">CML Paint Trading</a>
    </div>
    <div class="collapse navbar-collapse navbar-ex1-collapse">
        <ul class="nav navbar-nav side-nav">
            <li><a href="index.php"> &nbsp; <span class='glyphicon glyphicon-home'></span> Home</a></li>
            <li><a href="shop.php?id=1"> &nbsp; <span class='glyphicon glyphicon-shopping-cart'></span> Shop Now</a></li>
            <?php 
                if ($_SESSION['user_type'] != 'Cashier'){
                ?>
                    <li><a href="wishlist.php?id=1">&nbsp; <span class='glyphicon glyphicon-heart'></span> Wishlist </a></li>
                    <li><a href="paint-match.php"> &nbsp; <span class='glyphicon glyphicon-tint'></span> Paint Match</a></li>
                    <li><a href="color-change.php"> &nbsp; <span class='glyphicon glyphicon-glass'></span> Color Change</a></li>
                <?php
                }
            ?>
            <li><a href="cart_items.php"> &nbsp; <span class='fa fa-cart-plus'></span> Shopping Cart Lists</a></li>
            <?php 
                if ($_SESSION['user_type'] == 'Cashier'){
                    ?>
                    <li><a href="items.php"> &nbsp; <span class='fa fa-cart-plus'></span> Inventory</a></li>
                    <li><a href="salesreport.php"> &nbsp; <span class='fa fa-cart-plus'></span> Sales Report</a></li>
                <?php
                }
            ?>
            <li><a href="orders.php"> &nbsp; <span class='glyphicon glyphicon-list-alt'></span> My Ordered Items</a></li>
            <?php 
                if ($_SESSION['user_type'] != 'Cashier'){
                    ?>
                    <li><a href="invoice.php"> &nbsp; <span class='fa fa-money'></span> Invoice </a></li>
                    <li><a href="returnItemPage.php"> &nbsp; <span class='glyphicon glyphicon-remove-sign'></span> Return An Item</a></li>
                    <?php
                }   
            ?>
            <!-- <li><a href="./view_purchased.php"> &nbsp; <span class='glyphicon glyphicon-eye-open'></span> Previous Items Ordered</a></li> -->
            <li><a data-toggle="modal" data-target="#setAccount"> &nbsp; <span class='fa fa-gear'></span> Account Settings</a></li>
            <li><a href="logout.php"> &nbsp; <span class='glyphicon glyphicon-off'></span> Logout</a></li>


        </ul>
        <ul class="nav navbar-nav navbar-right navbar-user">
            <li class="dropdown messages-dropdown">
                <a href="#"><i class="fa fa-calendar"></i> <?php
                                                            $Today = date('y:m:d');
                                                            $new = date('l, F d, Y', strtotime($Today));
                                                            echo $new; ?></a>

            </li>
            <li class="dropdown user-dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class='glyphicon glyphicon-shopping-cart'></span> Total Price Ordered: &#8369; <?php echo $total; ?> </b></a>

            </li>


            <li class="dropdown user-dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> <?php echo $user_email; ?><b class="caret"></b></a>
                <ul class="dropdown-menu">
                    <li><a data-toggle="modal" data-target="#setAccount"><i class="fa fa-gear"></i> Settings</a></li>
                    <li class="divider"></li>
                    <li><a href="logout.php"><i class="fa fa-power-off"></i> Log Out</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
