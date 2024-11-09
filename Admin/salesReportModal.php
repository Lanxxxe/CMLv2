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
                                WHERE DATE(CURDATE()) = DATE(orderdetails.order_date)' . $order_type_str);
                            $stmt_daily->execute();
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
                                    paymentform.payment_method,
                                    DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) as start_date, 
                                    DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 6 DAY) as end_date 
                            FROM orderdetails 
                                INNER JOIN users ON users.user_id = orderdetails.user_id 
                                LEFT JOIN paymentform ON orderdetails.payment_id = paymentform.id
                            WHERE DATE(orderdetails.order_date) BETWEEN 
                                DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) 
                                AND DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 6 DAY)' . $order_type_str
                            );
                            $stmt_weekly->execute();
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
                                    paymentform.payment_method,
                                    DATE_FORMAT(CURDATE(), "%Y-%m-01") as start_date, 
                                    LAST_DAY(CURDATE()) as end_date 
                            FROM orderdetails 
                                INNER JOIN users ON users.user_id = orderdetails.user_id 
                                LEFT JOIN paymentform ON orderdetails.payment_id = paymentform.id
                            WHERE DATE(order_date) BETWEEN 
                                DATE_FORMAT(CURDATE(), "%Y-%m-01") 
                                AND LAST_DAY(CURDATE())' . $order_type_str
                            );
                            $stmt_monthly->execute();
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
                <button type="button" class="btn btn-primary" onclick="printContent('monthlySalesContent')">
                    <i class="fa fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
