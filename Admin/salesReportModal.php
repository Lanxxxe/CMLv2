<!-- Daily Sales -->
<style>
.modal-content {
    width: 80vw !important;
    margin: auto;
}
.modal-dialog {
    width: 100% !important;
}
@media print {
    .modal-content {
        width: 96% !important;
        margin-block: 10px !important;
    }
}
</style>
<div class="modal fade" id="dailySales" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
    <div class="modal-dialog modal-md" id="dailySalesContent">
        <div class="print-header">
            <h1 class="h-head-text">CML Paint Trading</h1>
            <div class="reports-info">
                <h2 class="h-label">Sales Report</h2>
                <p>Date Printed: <span class="date-printed"><?php echo date('F d, Y h:i A'); ?></span></p>
                <p>Printed By: <span class="printed-by"><?php echo htmlspecialchars($_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname']); ?></span></p>
            </div>
        </div>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h2 class="modal-title" id="myModalLabel">Daily Sales</h2>
            </div>
            <div class="modal-body">
                <div class="transactions-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Transaction Date</th>
                                <th>Invoice No.</th>
                                <th>Customer</th>
                                <th>Products Ordered</th>
                                <th>Quantity</th>
                                <th>Total Amount</th>
                                <th>Payment Method</th>
                                <th>Order Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // $branch = $_SESSION['current_branch)'];
                            // Fetch daily transactions
                            $stmt_daily = $DB_con->prepare('
                            SELECT 
                                    users.user_email, 
                                    users.user_firstname, 
                                    users.user_lastname, 
                                    users.user_address, 
                                    orderdetails.*,
                                    paymentform.payment_method
                                FROM orderdetails
                                    INNER JOIN users ON users.user_id = orderdetails.user_id 
                                    LEFT JOIN paymentform ON orderdetails.payment_id = paymentform.id
                                WHERE DATE(orderdetails.order_date) = ?' . $order_type_str . "and order_pick_place = '$branch' ");
                            $stmt_daily->execute([$end_date]);
                            if ($stmt_daily->rowCount() > 0) {
                                while ($row = $stmt_daily->fetch(PDO::FETCH_ASSOC)) {
                                    $customerName = $row['user_firstname'] . ' ' . $row['user_lastname'];
                                    $orderDate = date('M d, Y', strtotime($row['order_date']));
                                    $statusClass = strtolower($row['order_status']) == 'completed' ? 'status-completed' : 
                                                 (strtolower($row['order_status']) == 'cancelled' ? 'status-cancelled' : 'status-pending');
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($orderDate); ?></td>
                                        <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                        <td><?php echo htmlspecialchars($customerName); ?></td>
                                        <td><?php echo htmlspecialchars($row['order_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['order_quantity']); ?></td>
                                        <td>₱<?php echo number_format($row['order_total'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($row['payment_method'] ?? 'N/A'); ?></td>
                                        <td><span class="order-status <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['order_status']); ?></span></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="8" style="text-align: center;">No transactions found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer hide-in-print">
                <a class="btn btn-primary" href="reports/daily.php<?php echo $pdf_args ?>">
                    <i class="fa fa-file-pdf-o"></i> Save PDF
                </a>
                <button type="button" class="btn btn-primary" onclick="printContent('dailySalesContent')">
                    <i class="fa fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Weekly Sales -->
<div class="modal fade" id="weeklySales" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
    <div class="modal-dialog modal-md" id="weeklySalesContent">
        <div class="print-header">
            <h1 class="h-head-text">CML Paint Trading</h1>
            <div class="reports-info">
                <h2 class="h-label">Sales Report</h2>
                <p>Date Printed: <span class="date-printed"><?php echo date('F d, Y h:i A'); ?></span></p>
                <p>Printed By: <span class="printed-by"><?php echo htmlspecialchars($_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname']); ?></span></p>
            </div>
        </div>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h2 class="modal-title" id="myModalLabel">Weekly Sales</h2>
            </div>
            <div class="modal-body">
                <div class="transactions-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Transaction Date</th>
                                <th>Invoice No.</th>
                                <th>Customer</th>
                                <th>Products Ordered</th>
                                <th>Quantity</th>
                                <th>Total Amount</th>
                                <th>Payment Method</th>
                                <th>Order Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch weekly transactions
                            $stmt_weekly = $DB_con->prepare(
                            'SELECT 
                                    users.user_email, 
                                    users.user_firstname, 
                                    users.user_lastname, 
                                    users.user_address, 
                                    orderdetails.*,
                                    paymentform.payment_method
                            FROM orderdetails 
                                INNER JOIN users ON users.user_id = orderdetails.user_id 
                                LEFT JOIN paymentform ON orderdetails.payment_id = paymentform.id
                            WHERE DATE(orderdetails.order_date) BETWEEN ? AND ?' . $order_type_str . "and order_pick_place = '$branch' "
                            );
                            $stmt_weekly->execute([$start_date_weekly, $end_date]);
                            if ($stmt_weekly->rowCount() > 0) {
                                while ($row = $stmt_weekly->fetch(PDO::FETCH_ASSOC)) {
                                    $customerName = $row['user_firstname'] . ' ' . $row['user_lastname'];
                                    $orderDate = date('M d, Y', strtotime($row['order_date']));
                                    $statusClass = strtolower($row['order_status']) == 'completed' ? 'status-completed' : 
                                                 (strtolower($row['order_status']) == 'cancelled' ? 'status-cancelled' : 'status-pending');
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($orderDate); ?></td>
                                        <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                        <td><?php echo htmlspecialchars($customerName); ?></td>
                                        <td><?php echo htmlspecialchars($row['order_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['order_quantity']); ?></td>
                                        <td>₱<?php echo number_format($row['order_total'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($row['payment_method'] ?? 'N/A'); ?></td>
                                        <td><span class="order-status <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['order_status']); ?></span></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="8" style="text-align: center;">No transactions found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer hide-in-print">
                <a class="btn btn-primary" href="reports/weekly.php<?php echo $pdf_args ?>">
                    <i class="fa fa-file-pdf-o"></i> Save PDF
                </a>
                <button type="button" class="btn btn-primary" onclick="printContent('weeklySalesContent')">
                    <i class="fa fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Sales -->
<div class="modal fade" id="monthlySales" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
    <div class="modal-dialog modal-md" id="monthlySalesContent">
        <div class="print-header">
            <h1 class="h-head-text">CML Paint Trading</h1>
            <div class="reports-info">
                <h2 class="h-label">Sales Report</h2>
                <p>Date Printed: <span class="date-printed"><?php echo date('F d, Y h:i A'); ?></span></p>
                <p>Printed By: <span class="printed-by"><?php echo htmlspecialchars($_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname']); ?></span></p>
            </div>
        </div>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h2 class="modal-title" id="myModalLabel">Monthly Sales</h2>
            </div>
            <div class="modal-body">
                <div class="transactions-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Transaction Date</th>
                                <th>Invoice No.</th>
                                <th>Customer</th>
                                <th>Products Ordered</th>
                                <th>Quantity</th>
                                <th>Total Amount</th>
                                <th>Payment Method</th>
                                <th>Order Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch monthly transactions
                            $stmt_monthly = $DB_con->prepare(
                            'SELECT 
                                    users.user_email, 
                                    users.user_firstname, 
                                    users.user_lastname, 
                                    users.user_address, 
                                    orderdetails.*,
                                    paymentform.payment_method
                            FROM orderdetails 
                                INNER JOIN users ON users.user_id = orderdetails.user_id 
                                LEFT JOIN paymentform ON orderdetails.payment_id = paymentform.id
                            WHERE DATE(order_date) BETWEEN 
                                ? AND ?' . $order_type_str . "and order_pick_place = '$branch' "
                            );
                            $stmt_monthly->execute([$start_date, $end_date]);
                            if ($stmt_monthly->rowCount() > 0) {
                                while ($row = $stmt_monthly->fetch(PDO::FETCH_ASSOC)) {
                                    $customerName = $row['user_firstname'] . ' ' . $row['user_lastname'];
                                    $orderDate = date('M d, Y', strtotime($row['order_date']));
                                    $statusClass = strtolower($row['order_status']) == 'completed' ? 'status-completed' : 
                                                 (strtolower($row['order_status']) == 'cancelled' ? 'status-cancelled' : 'status-pending');
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($orderDate); ?></td>
                                        <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                        <td><?php echo htmlspecialchars($customerName); ?></td>
                                        <td><?php echo htmlspecialchars($row['order_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['order_quantity']); ?></td>
                                        <td>₱<?php echo number_format($row['order_total'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($row['payment_method'] ?? 'N/A'); ?></td>
                                        <td><span class="order-status <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['order_status']); ?></span></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="8" style="text-align: center;">No transactions found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer hide-in-print">
                <a class="btn btn-primary" href="reports/monthly.php<?php echo $pdf_args ?>">
                    <i class="fa fa-file-pdf-o"></i> Save PDF
                </a>
                <button type="button" class="btn btn-primary" onclick="printContent('monthlySalesContent')">
                    <i class="fa fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
