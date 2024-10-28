USE cml_paint_db;



-- Procedure to request a payment
DELIMITER //

CREATE PROCEDURE request_payment(
    IN p_payment_id INT,
    IN p_amount DECIMAL(10,2),
    IN p_payment_image VARCHAR(255)
)
BEGIN
    DECLARE last_track_status VARCHAR(20);
    DECLARE v_payment_type VARCHAR(255);
    DECLARE v_payment_status VARCHAR(20);
    
    -- Get the status of the latest track record
    SELECT status INTO last_track_status
    FROM payment_track
    WHERE payment_id = p_payment_id
    ORDER BY track_id DESC
    LIMIT 1;
    
    -- Get payment type
    SELECT payment_type INTO v_payment_type
    FROM paymentform
    WHERE id = p_payment_id;
    
    SELECT payment_status INTO v_payment_status
    FROM paymentform
    WHERE id = p_payment_id;
    -- Check if we can process new payment (no track or last track was confirmed)
    IF (v_payment_status = 'Confirmed' AND (last_track_status IS NULL OR last_track_status = 'Confirmed')) THEN
        -- Insert new payment track record
        INSERT INTO payment_track (payment_id, amount, status)
        VALUES (p_payment_id, p_amount, 'Requested');
        
        -- Update payment image in paymentform
        UPDATE paymentform
        SET payment_image_path = p_payment_image
        WHERE id = p_payment_id;
        
        SELECT 'success' as status, 'Payment request submitted successfully' as message;
    ELSE
        SELECT 'error' as status, 'Previous payment is still pending confirmation' as message;
    END IF;
END //

DELIMITER ;


DELIMITER //
-- Procedure to confirm payment
CREATE PROCEDURE confirm_payment(
    IN p_track_id INT
)
BEGIN
    DECLARE v_payment_id INT;
    DECLARE v_amount DECIMAL(10,2);
    DECLARE v_payment_type VARCHAR(255);
    DECLARE v_total_amount DECIMAL(10,2);
    DECLARE v_current_amount DECIMAL(10,2);
    
    -- Get payment details
    SELECT pt.payment_id, pt.amount, pf.payment_type, pf.amount, 
           (SELECT SUM(order_total) FROM orderdetails WHERE payment_id = pt.payment_id)
    INTO v_payment_id, v_amount, v_payment_type, v_current_amount, v_total_amount
    FROM payment_track pt
    JOIN paymentform pf ON pt.payment_id = pf.id
    WHERE pt.track_id = p_track_id;
    
    START TRANSACTION;
    
    -- Update payment track status
    UPDATE payment_track
    SET status = 'Confirmed'
    WHERE track_id = p_track_id;
    
    -- Update paymentform based on payment type
    IF v_payment_type = 'Installment' THEN
        UPDATE paymentform
        SET months_paid = months_paid + 1,
            payment_status = IF(months_paid + 1 >= 12, 'Confirmed', payment_status)
        WHERE id = v_payment_id;
    ELSE -- Down payment
        UPDATE paymentform
        SET amount = amount + v_amount,
            payment_status = IF(amount + v_amount >= v_total_amount, 'Confirmed', payment_status)
        WHERE id = v_payment_id;
    END IF;
    
    -- Update orderdetails status if payment is complete
    IF (v_payment_type = 'Installment' AND (SELECT months_paid FROM paymentform WHERE id = v_payment_id) >= 12)
        OR (v_payment_type = 'Down payment' AND (v_current_amount + v_amount >= v_total_amount)) THEN
        
        UPDATE orderdetails
        SET order_status = 'Confirmed'
        WHERE payment_id = v_payment_id;
    END IF;
    
    COMMIT;
    
    SELECT 'success' as status, 'Payment confirmed successfully' as message;
END //

DELIMITER ;


DELIMITER //

CREATE PROCEDURE ProcessReturnItems(IN p_return_id INT)
BEGIN
    DECLARE current_qty INT;
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_order_id INT;
    DECLARE v_order_quantity INT;
    DECLARE v_user_id INT;
    DECLARE v_order_name VARCHAR(1000);
    DECLARE v_order_price DOUBLE;
    DECLARE v_order_total DOUBLE;
    DECLARE v_order_status VARCHAR(45);
    DECLARE v_order_date DATE;
    DECLARE v_order_pick_up DATETIME(6);
    DECLARE v_order_pick_place VARCHAR(45);
    DECLARE v_gl VARCHAR(45);
    DECLARE v_payment_id INT;
    DECLARE v_product_id INT;
    
    -- Cursor for orders sorted by date
    DECLARE order_cursor CURSOR FOR 
        SELECT order_id, order_quantity,
               user_id, order_name, order_price, order_total,
               order_status, order_date, order_pick_up, order_pick_place,
               gl, payment_id, product_id
        FROM orderdetails 
        WHERE user_id = (SELECT user_id FROM returnitems WHERE return_id = p_return_id)
        AND product_id = (SELECT product_id FROM orderdetails WHERE order_name = 
                        (SELECT product_name FROM returnitems WHERE return_id = p_return_id) LIMIT 1)
        ORDER BY order_date DESC;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Start transaction
    START TRANSACTION;
    
    -- Get initial return quantity
    SELECT quantity INTO current_qty
    FROM returnitems
    WHERE return_id = p_return_id;
    
    -- Open cursor
    OPEN order_cursor;
    
    read_loop: LOOP
        FETCH order_cursor INTO v_order_id, v_order_quantity,
                               v_user_id, v_order_name, v_order_price, v_order_total,
                               v_order_status, v_order_date, v_order_pick_up, v_order_pick_place,
                               v_gl, v_payment_id, v_product_id;
                               
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        IF v_order_quantity <= current_qty THEN
            -- Update entire order to 'return' status
            UPDATE orderdetails 
            SET order_status = 'Returned'
            WHERE order_id = v_order_id;
            
            SET current_qty = current_qty - v_order_quantity;
        ELSE
            -- Split the order
            UPDATE orderdetails
            SET order_quantity = order_quantity - current_qty,
                order_total = order_price * (order_quantity - current_qty)
            WHERE order_id = v_order_id;
            
            -- Insert new order for the returned portion
            INSERT INTO orderdetails (
                user_id, order_name, order_price, order_quantity, 
                order_total, order_status, order_date, order_pick_up,
                order_pick_place, gl, payment_id, product_id
            )
            VALUES (
                v_user_id, v_order_name, v_order_price, current_qty,
                v_order_price * current_qty, 'Returned', v_order_date, 
                v_order_pick_up, v_order_pick_place, v_gl,
                v_payment_id, v_product_id
            );
            
            SET current_qty = 0;
            LEAVE read_loop;
        END IF;
        
        IF current_qty = 0 THEN
            LEAVE read_loop;
        END IF;
    END LOOP;
    
    -- Close cursor
    CLOSE order_cursor;
    
    -- Update return item status
    UPDATE returnitems 
    SET status = 'Confirmed'
    WHERE return_id = p_return_id;
    
    -- Commit transaction
    COMMIT;
    
END //

DELIMITER ;



