<?php

$preview = '<div class="receipt-preview">
    <div class="receipt-header">
        <h1>CML Paint Trading</h1>
        <!-- Uncomment these if you want to add address/contact details -->
        <!-- <p>123 Paint Street, Color City</p>
        <p>Tel: (123) 456-7890</p>
        <p>VAT Reg TIN: 123-456-789-000</p> -->
    </div>

    <div class="separator"></div>

    <div class="receipt-details">
        <p>Receipt No: <span id="receiptNo">' . str_pad($payment_id, 8, '0', STR_PAD_LEFT) . '</span></p>
        <p>Date: <span id="receiptDate">' . date('Y-m-d H:i:s') . '</span></p>
    </div>

    <div class="separator"></div>

    <table class="item-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Size</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody id="itemList">';

foreach ($items as $i => $item) {
    $preview .= '
        <tr>
            <td>' . $item['item_name'] . '</td>
            <td>' . $qtys[''] . '</td>
            <td>' . $qtys[$i] . '</td>
            <td>₱' . $item['item_price'] . '</td>
            <td>₱' . ($item['item_price'] * $qtys[$i]) . '</td>
        </tr>';
 }

$preview .= '</tbody>
    </table>

    <div class="separator"></div>

    <div class="total">
        <p>TOTAL: <span id="totalAmount">₱' . $totalPrice . '</span></p>
    </div>

    <div class="separator"></div>

    <div>
        <button type="button" id="confirmPayment" data-token="' . $token . '">Confirm</button>
        <button type="button" id="printRecieptTrigger" data-pid="' . $payment_id . '">Print</button>
        <button type="button" onclick="document.getElementById(\'checkingOut\').remove()">Cancel</button>
    </div>

</div>';
