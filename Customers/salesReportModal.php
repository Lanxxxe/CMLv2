
<!-- Daily Sales -->
<div class="modal fade" id="dailySales" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
    <div class="modal-dialog modal-md" id="dailySalesContent">
        <div style="color:white;background-color:#008CBA" class="modal-content">
            <div class="modal-header">
                <button type="button" class="close hide-in-print" data-dismiss="modal" aria-label="Close"><span
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
                        $current_user_id = $_SESSION['user_id'];
                        // Fetch daily transactions
                        $stmt_daily = $DB_con->prepare('SELECT * FROM orderdetails WHERE DATE(order_date) = CURDATE() 
                        AND user_id = ?');
                        $stmt_daily->bindParam(1, $current_user_id, PDO::PARAM_INT);
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
                <div class="modal-footer hide-in-print">
                    <a class="btn btn-primary" href="../Admin/reports/daily.php">Save as PDF</a>
                    <button type="button" class="btn btn-primary" onclick="printContent('dailySalesContent')">Print</button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Weekly Sales -->
<div class="modal fade" id="weeklySales" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
    <div class="modal-dialog modal-md" id="weeklySalesContent">
        <div style="color:white;background-color:#008CBA" class="modal-content">
            <div class="modal-header">
                <button type="button" class="close hide-in-print" data-dismiss="modal" aria-label="Close"><span
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
                            'SELECT * FROM orderdetails WHERE DATE(order_date) BETWEEN DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) AND CURDATE()
                        AND user_id = ?');
                        $stmt_weekly->bindParam(1, $current_user_id, PDO::PARAM_INT);
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
            <div class="modal-footer hide-in-print">
                <a class="btn btn-primary" href="../Admin/reports/weekly.php">Save as PDF</a>
                <button type="button" class="btn btn-primary" onclick="printContent('weeklySalesContent')">Print</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Sales -->
<div class="modal fade" id="monthlySales" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
    <div class="modal-dialog modal-md" id="monthlySalesContent">
        <div style="color:white;background-color:#008CBA" class="modal-content">
            <div class="modal-header">
                <button type="button" class="close hide-in-print" data-dismiss="modal" aria-label="Close"><span
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
                            'SELECT * FROM orderdetails 
                            WHERE DATE(order_date) BETWEEN DATE_FORMAT(CURDATE(), 
                            "%Y-%m-01") 
                            AND LAST_DAY(CURDATE()) 
                            AND user_id = ?');
                        $stmt_monthly->bindParam(1, $current_user_id, PDO::PARAM_INT);
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
            <div class="modal-footer hide-in-print">
                <a class="btn btn-primary" href="../Admin/reports/monthly.php">Save as PDF</a>
                <button type="button" class="btn btn-primary" onclick="printContent('monthlySalesContent')">Print</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
