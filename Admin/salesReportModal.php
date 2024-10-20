
<!-- Daily Sales -->
<div class="modal fade" id="dailySales" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
    <div class="modal-dialog modal-md">
        <div style="color:white;background-color:#008CBA" class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h2 style="color:white" class="modal-title" id="myModalLabel">Daily Sales</h2>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                        <th>Transaction ID</th>
                        <th>Product</th>
                        <th>Total</th>
                        <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch daily transactions
                        $stmt_daily = $DB_con->prepare(
                            'SELECT orderdetails.* 
                            FROM orderdetails
                                LEFT JOIN paymentform ON orderdetails.payment_id = paymentform.id
                            WHERE DATE(CURDATE()) = DATE(order_date)' . $order_type_str);
                        $stmt_daily->execute();
                        while ($row = $stmt_daily->fetch(PDO::FETCH_ASSOC)) {
                            echo '<tr>';
                            echo '<td>' . $row['order_id'] . '</td>';
                            echo '<td>' . $row['order_name'] . '</td>';
                            echo '<td>&#8369 ' . number_format($row['order_total'], 2) . '</td>';
                            echo '<td>' . date('F j, Y', strtotime($row['order_date'])) . '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-primary" href="./reports/daily.php<?php echo ($order_type)? "?order_type=$order_type": '' ?>">Save as PDF</a>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Weekly Sales -->
<div class="modal fade" id="weeklySales" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
    <div class="modal-dialog modal-md">
        <div style="color:white;background-color:#008CBA" class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h2 style="color:white" class="modal-title" id="myModalLabel">Weekly Sales</h2>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                        <th>Transaction ID</th>
                        <th>Product</th>
                        <th>Total</th>
                        <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch weekly transactions
                        $stmt_weekly = $DB_con->prepare(
                        'SELECT orderdetails.*,
                                DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) as start_date, 
                                DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 6 DAY) as end_date 
                         FROM orderdetails 
                            LEFT JOIN paymentform ON orderdetails.payment_id = paymentform.id
                         WHERE DATE(order_date) BETWEEN 
                               DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) 
                               AND DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 6 DAY)' . $order_type_str
                        );
                        $stmt_weekly->execute();
                        while ($row = $stmt_weekly->fetch(PDO::FETCH_ASSOC)) {
                            echo '<tr>';
                            echo '<td>' . $row['order_id'] . '</td>';
                            echo '<td>' . $row['order_name'] . '</td>';
                            echo '<td>&#8369 ' . number_format($row['order_total'], 2) . '</td>';
                            echo '<td>' . date('F j, Y', strtotime($row['order_date'])) . '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <a class="btn btn-primary" href="./reports/weekly.php<?php echo ($order_type)? "?order_type=$order_type": '' ?>">Save as PDF</a>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Sales -->
<div class="modal fade" id="monthlySales" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
    <div class="modal-dialog modal-md">
        <div style="color:white;background-color:#008CBA" class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h2 style="color:white" class="modal-title" id="myModalLabel">Monthly Sales</h2>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                        <th>Transaction ID</th>
                        <th>Product</th>
                        <th>Total</th>
                        <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch monthly transactions
                        $stmt_monthly = $DB_con->prepare(
                        'SELECT orderdetails.*,
                                DATE_FORMAT(CURDATE(), "%Y-%m-01") as start_date, 
                                LAST_DAY(CURDATE()) as end_date 
                         FROM orderdetails 
                            LEFT JOIN paymentform ON orderdetails.payment_id = paymentform.id
                         WHERE DATE(order_date) BETWEEN 
                               DATE_FORMAT(CURDATE(), "%Y-%m-01") 
                               AND LAST_DAY(CURDATE())' . $order_type_str
                        );
                        $stmt_monthly->execute();
                        while ($row = $stmt_monthly->fetch(PDO::FETCH_ASSOC)) {
                            echo '<tr>';
                            echo '<td>' . $row['order_id'] . '</td>';
                            echo '<td>' . $row['order_name'] . '</td>';
                            echo '<td>&#8369 ' . number_format($row['order_total'], 2) . '</td>';
                            echo '<td>' . date('F j, Y', strtotime($row['order_date'])) . '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <a class="btn btn-primary" href="./reports/monthly.php<?php echo ($order_type)? "?order_type=$order_type": '' ?>">Save as PDF</a>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
