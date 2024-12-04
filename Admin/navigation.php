<style>
#notificationBadge {
    background-color: #ff0000 !important; /* Bright red color */
    color: white !important;
    border-radius: 50% !important;
    font-size: 8px !important; /* Smaller font size */
    font-weight: bold !important;
    position: absolute !important; /* Position it relative to the parent */
    top: 14px !important; /* Align to the top */
    left: 8px !important; /* Align to the left */
    height: 12px !important;
    width: 12px !important;
    text-align: center !important;
    line-height: 14px !important;
}
#notificationButton {
    position: relative !important; /* Make the parent element positioned */
}
</style>
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <img class="logo-custom" src="../assets/img/logo.png" alt="" style="height: 40px; margin-left: 15px;" />
    </div>
    <div class="collapse navbar-collapse navbar-ex1-collapse">
        <ul class="nav navbar-nav side-nav">
            <li id="nav_home"><a href="index.php"> &nbsp; &nbsp; &nbsp; Home (<?php echo $_SESSION['current_branch'] ? $_SESSION['current_branch'] : '' ?>) </a></li>
            <li id="nav_dashboard"><a href="orderdetails.php"> &nbsp; &nbsp; &nbsp; Dashboard</a></li>
            <li id="nav_add_stock"><a data-toggle="modal" data-target="#uploadModal"> &nbsp; &nbsp; &nbsp; Add Paint Products</a></li>
            <li id="nav_add_tools"><a data-toggle="modal" data-target="#uploadItems"> &nbsp; &nbsp; &nbsp; Add Paint Tools</a></li>
            <li id="nav_item_management"><a href="items.php"> &nbsp; &nbsp; &nbsp; Inventory</a></li>
            <li id="nav_order_request"><a href="customers.php"> &nbsp; &nbsp; &nbsp; Order Request</a></li>
            <li id="nav_return_request"><a href="manage_return.php"> &nbsp; &nbsp; &nbsp; Return Request</a></li>
            <li id="nav_sales_report"><a href="salesreport.php"> &nbsp; &nbsp; &nbsp; Sales Report</a></li>
            <li id="nav_invoice_report"><a href="invoice_report.php"> &nbsp; &nbsp; &nbsp; Invoice Report</a></li>
            <?php 
                if ($_SESSION['current_branch'] == 'Caloocan'){
            ?>
                <li id="nav_maintenance"><a href="maintenance.php"> &nbsp; &nbsp; &nbsp; Maintenance</a></li>
                <li id="nav_user_management"><a href="userManagement.php"> &nbsp; &nbsp; &nbsp; User Management</a></li>
                <li id="nav_request_stock"><a href="stockRequests.php"> &nbsp; &nbsp; &nbsp; Stock Requests</a></li>
            <?php
                } else {
            ?>
                <li id="nav_request_stock"><a href="stockRequests.php"> &nbsp; &nbsp; &nbsp; Request Stock</a></li>
            <?php
                }
            ?>
            <li id="nav_logout"><a href="javascript:confirmLogout()"> &nbsp; &nbsp; &nbsp; Logout</a></li>
        </ul>
        <ul class="nav navbar-nav navbar-right navbar-user">
            <?php

                $stmt = $DB_con->prepare('SELECT COUNT(id) as notifications_count FROM admin_notifications_views WHERE status = "unread"');
                $stmt->execute();
                $unreadCount = $stmt->fetch(PDO::FETCH_NUM)[0];
            ?>
                <li class="dropdown messages-dropdown" id="notificationButton">
                    <a href="./notifications.php">
                        <i class="fa fa-bell"></i>
                        Notifications
                        <span id="notificationBadge" style="display: <?php echo ($unreadCount > 0) ? 'inline-block' : 'none'; ?>;">
                            <?php echo $unreadCount; ?>
                        </span>
                    </a>
                </li>

            <li class="dropdown messages-dropdown">
                <a href="#"><i class="fa fa-calendar"></i>  <?php
                $Today=date('y:m:d');
                $new=date('l, F d, Y',strtotime($Today));
                echo $new; ?></a>
                
            </li>
            <li class="dropdown user-dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> <?php   extract($_SESSION); echo $admin_username; ?><b class="caret"></b></a>
                <ul class="dropdown-menu">
                    <li><a href="javascript:confirmLogout()"><i class="fa fa-power-off"></i> Log Out</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<script>
    function confirmLogout() {
      Swal.fire({
        title: 'Are you sure?',
        text: 'Do you really want to log out?',
        icon: 'warning',
        showCancelButton: true,  // Displays the "Cancel" button
        confirmButtonText: 'Yes, log out',
        cancelButtonText: 'No, stay logged in',
        confirmButtonColor: '#d33',  // Custom color for the "Yes" button
        cancelButtonColor: '#3085d6', // Custom color for the "No" button
      }).then((result) => {
        if (result.isConfirmed) {
          // Proceed with the logout action
          window.location.href = 'logout.php';  // Redirect to the logout route
        }
      });
    }
</script>
