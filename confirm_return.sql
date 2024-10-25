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
            SET order_status = 'return'
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
                v_order_price * current_qty, 'return', v_order_date, 
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
