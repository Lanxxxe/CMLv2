-- phpMyAdmin SQL Dump
-- version 5.2.2-1.fc41
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 24, 2025 at 04:01 AM
-- Server version: 8.0.40
-- PHP Version: 8.3.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cml_paint_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE PROCEDURE `confirm_payment` (IN `p_track_id` INT)   BEGIN
    DECLARE v_payment_id INT;
    DECLARE v_amount DECIMAL(10,2);
    DECLARE v_payment_type VARCHAR(255);
    DECLARE v_total_amount DECIMAL(10,2);
    DECLARE v_current_amount DECIMAL(10,2);
    
    
    SELECT pt.payment_id, pt.amount, pf.payment_type, pf.amount, 
           (SELECT SUM(order_total) FROM orderdetails WHERE payment_id = pt.payment_id)
    INTO v_payment_id, v_amount, v_payment_type, v_current_amount, v_total_amount
    FROM payment_track pt
    JOIN paymentform pf ON pt.payment_id = pf.id
    WHERE pt.track_id = p_track_id;
    
    START TRANSACTION;
    
    
    UPDATE payment_track
    SET status = 'Confirmed'
    WHERE track_id = p_track_id;
    
    
    IF v_payment_type = 'Installment' THEN
        UPDATE paymentform
        SET months_paid = months_paid + 1,
            payment_status = IF(months_paid + 1 >= 12, 'Confirmed', payment_status)
        WHERE id = v_payment_id;
    ELSE 
        UPDATE paymentform
        SET amount = amount + v_amount,
            payment_status = IF(amount + v_amount >= v_total_amount, 'Confirmed', payment_status)
        WHERE id = v_payment_id;
    END IF;
    
    
    IF (v_payment_type = 'Installment' AND (SELECT months_paid FROM paymentform WHERE id = v_payment_id) >= 12)
        OR (v_payment_type = 'Down payment' AND (v_current_amount + v_amount >= v_total_amount)) THEN
        
        UPDATE orderdetails
        SET order_status = 'Confirmed'
        WHERE payment_id = v_payment_id;
    END IF;
    
    COMMIT;
    
    SELECT 'success' as status, 'Payment confirmed successfully' as message;
END$$

CREATE PROCEDURE `ProcessReturnItems` (IN `p_return_id` INT)   BEGIN
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
    
    
    START TRANSACTION;
    
    
    SELECT quantity INTO current_qty
    FROM returnitems
    WHERE return_id = p_return_id;
    
    
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
            
            UPDATE orderdetails 
            SET order_status = 'Returned'
            WHERE order_id = v_order_id;
            
            SET current_qty = current_qty - v_order_quantity;
        ELSE
            
            UPDATE orderdetails
            SET order_quantity = order_quantity - current_qty,
                order_total = order_price * (order_quantity - current_qty)
            WHERE order_id = v_order_id;
            
            
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
    
    
    CLOSE order_cursor;
    
    
    UPDATE returnitems 
    SET status = 'Confirmed'
    WHERE return_id = p_return_id;
    
    
    COMMIT;
    
END$$

CREATE PROCEDURE `request_payment` (IN `p_payment_id` INT, IN `p_amount` DECIMAL(10,2), IN `p_payment_image` VARCHAR(255))   BEGIN
    DECLARE last_track_status VARCHAR(20);
    DECLARE v_payment_type VARCHAR(255);
    
    
    SELECT status INTO last_track_status
    FROM payment_track
    WHERE payment_id = p_payment_id
    ORDER BY track_id DESC
    LIMIT 1;
    
    
    SELECT payment_type INTO v_payment_type
    FROM paymentform
    WHERE id = p_payment_id;
    
    
    IF (last_track_status IS NULL OR last_track_status = 'Confirmed') THEN
        
        INSERT INTO payment_track (payment_id, amount, status)
        VALUES (p_payment_id, p_amount, 'Requested');
        
        
        UPDATE paymentform
        SET payment_image_path = p_payment_image
        WHERE id = p_payment_id;
        
        SELECT 'success' as status, 'Payment request submitted successfully' as message;
    ELSE
        SELECT 'error' as status, 'Previous payment is still pending confirmation' as message;
    END IF;
END$$

CREATE PROCEDURE `restore_brand` (IN `p_delete_id` INT)   BEGIN
    DECLARE v_brand_id INT;
    DECLARE v_brand_name VARCHAR(255);
    DECLARE v_brand_img TEXT;
    DECLARE v_branch_id INT;

    
    SELECT `brand_id`, `brand_name`, `brand_img`, `branch_id`
    INTO v_brand_id, v_brand_name, v_brand_img, v_branch_id
    FROM `brands_archive`
    WHERE `delete_id` = p_delete_id;

    
    INSERT INTO `brands` (`brand_id`, `brand_name`, `brand_img`, `branch_id`)
    VALUES (v_brand_id, v_brand_name, v_brand_img, v_branch_id);

    
    DELETE FROM `brands_archive`
    WHERE `delete_id` = p_delete_id;
END$$

CREATE PROCEDURE `restore_brand_including_product_type` (IN `p_delete_id` INT)   BEGIN
    DECLARE v_brand_id INT;
    DECLARE v_brand_name VARCHAR(255);
    DECLARE v_brand_img TEXT;
    DECLARE v_branch_id INT;

    
    SELECT `brand_id`, `brand_name`, `brand_img`, `branch_id`
    INTO v_brand_id, v_brand_name, v_brand_img, v_branch_id
    FROM `brands_archive`
    WHERE `delete_id` = p_delete_id;

    
    INSERT INTO `brands` (`brand_id`, `brand_name`, `brand_img`, `branch_id`)
    VALUES (v_brand_id, v_brand_name, v_brand_img, v_branch_id);

    
    INSERT INTO `prod_type` (`type_id`, `type_name`, `brand_id`, `prod_type`)
    SELECT pa.`type_id`, pa.`type_name`, pa.`brand_id`, pa.`prod_type`
    FROM `product_type_archive` pa
    WHERE pa.`deleted_brand_id` = v_brand_id;

    
    DELETE FROM `brands_archive`
    WHERE `delete_id` = p_delete_id;

    
    DELETE FROM `product_type_archive`
    WHERE `deleted_brand_id` = v_brand_id;
END$$

CREATE PROCEDURE `restore_product_type` (IN `p_delete_id` INT)   BEGIN
    DECLARE v_deleted_brand_id INT;
    DECLARE v_type_id INT;
    DECLARE v_type_name TEXT;
    DECLARE v_brand_id INT;
    DECLARE v_prod_type TEXT;

    
    SELECT `deleted_brand_id`, `type_id`, `type_name`, `brand_id`, `prod_type`
    INTO v_deleted_brand_id, v_type_id, v_type_name, v_brand_id, v_prod_type
    FROM `product_type_archive`
    WHERE `delete_id` = p_delete_id;

    
    INSERT INTO `product_type` (`type_id`, `type_name`, `brand_id`, `prod_type`)
    VALUES (v_type_id, v_type_name, v_brand_id, v_prod_type);

    
    DELETE FROM `product_type_archive`
    WHERE `delete_id` = p_delete_id;
END$$

CREATE PROCEDURE `restore_product_with_brand` (IN `p_delete_id` INT)   BEGIN
    DECLARE v_brand_id INT;
    DECLARE v_brand_name VARCHAR(255);
    DECLARE v_brand_img TEXT;
    DECLARE v_branch_id INT;
    DECLARE v_type_id INT;
    DECLARE v_type_name TEXT;
    DECLARE v_prod_type TEXT;

    
    SELECT `type_id`, `type_name`, `prod_type`, `deleted_brand_id`
    INTO v_type_id, v_type_name, v_prod_type, v_brand_id
    FROM `product_type_archive`
    WHERE `delete_id` = p_delete_id;

    
    IF v_brand_id IS NOT NULL THEN
        
        SELECT `brand_name`, `brand_img`, `branch_id`
        INTO v_brand_name, v_brand_img, v_branch_id
        FROM `brands_archive`
        WHERE `brand_id` = v_brand_id;

        
        INSERT INTO `brands` (`brand_id`, `brand_name`, `brand_img`, `branch_id`)
        VALUES (v_brand_id, v_brand_name, v_brand_img, v_branch_id);

        
        DELETE FROM `brands_archive`
        WHERE `brand_id` = v_brand_id;
    END IF;

    
    INSERT INTO `product_type` (`type_id`, `type_name`, `brand_id`, `prod_type`)
    VALUES (v_type_id, v_type_name, v_brand_id, v_prod_type);

    
    DELETE FROM `product_type_archive`
    WHERE `delete_id` = p_delete_id;

END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` int NOT NULL,
  `user_email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `head_msg` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `ntype` enum('ordered','returned','confirmed','requested','cancelled','return request','return rejected','return deleted') COLLATE utf8mb4_general_ci NOT NULL,
  `payment_id` int DEFAULT NULL,
  `return_id` int DEFAULT NULL,
  `order_id` int DEFAULT NULL,
  `status` enum('read','unread') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_notifications`
--

INSERT INTO `admin_notifications` (`id`, `user_email`, `head_msg`, `ntype`, `payment_id`, `return_id`, `order_id`, `status`, `created_at`) VALUES
(23, 'kate@email.com', 'New Order Placed', 'ordered', 79, NULL, NULL, 'unread', '2025-03-24 00:34:19'),
(24, 'kate@email.com', 'Payment Confirmed', 'confirmed', 79, NULL, NULL, 'unread', '2025-03-24 00:34:53'),
(25, 'kate@email.com', 'Payment Approval Required', 'requested', 80, NULL, NULL, 'unread', '2025-03-24 00:35:36'),
(26, 'kate@email.com', 'Payment Confirmed', 'confirmed', 80, NULL, NULL, 'unread', '2025-03-24 00:45:37'),
(27, 'kate@email.com', 'Payment Installment Request', 'requested', 80, NULL, NULL, 'unread', '2025-03-24 00:46:15'),
(28, 'kate@email.com', 'Payment Approval Required', 'requested', 81, NULL, NULL, 'unread', '2025-03-24 01:09:26'),
(29, 'kate@email.com', 'Payment Confirmed', 'confirmed', 81, NULL, NULL, 'unread', '2025-03-24 01:09:41'),
(30, 'kate@email.com', 'Payment Installment Request', 'requested', 81, NULL, NULL, 'unread', '2025-03-24 01:47:43'),
(31, 'kate@email.com', 'Payment Installment Request', 'requested', 81, NULL, NULL, 'unread', '2025-03-24 01:48:16'),
(32, 'kate@email.com', 'New Order Placed', 'ordered', 82, NULL, NULL, 'unread', '2025-03-24 02:20:33'),
(33, 'kate@email.com', 'New Order Placed', 'ordered', 83, NULL, NULL, 'unread', '2025-03-24 02:20:47'),
(35, 'cashier@gmail.com', 'New Order Placed', 'ordered', 84, NULL, NULL, 'unread', '2025-03-24 02:22:01'),
(37, 'cashier@gmail.com', 'New Order Placed', 'ordered', 85, NULL, NULL, 'unread', '2025-03-24 02:22:34'),
(38, 'kate@email.com', 'Payment Confirmed', 'confirmed', 83, NULL, NULL, 'unread', '2025-03-24 02:22:55'),
(39, 'kate@email.com', 'Payment Confirmed', 'confirmed', 82, NULL, NULL, 'unread', '2025-03-24 02:23:00'),
(41, 'cashier@gmail.com', 'New Order Placed', 'ordered', 86, NULL, NULL, 'unread', '2025-03-24 02:41:42'),
(43, 'cashier@gmail.com', 'New Order Placed', 'ordered', 87, NULL, NULL, 'unread', '2025-03-24 02:58:12'),
(45, 'cashier@gmail.com', 'New Order Placed', 'ordered', 88, NULL, NULL, 'unread', '2025-03-24 03:14:35'),
(60, 'cashier@gmail.com', 'New Order Placed', 'ordered', 99, NULL, NULL, 'unread', '2025-03-24 03:28:27'),
(62, 'cashier@gmail.com', 'New Order Placed', 'ordered', 100, NULL, NULL, 'unread', '2025-03-24 03:34:20'),
(64, 'cashier@gmail.com', 'New Order Placed', 'ordered', 101, NULL, NULL, 'unread', '2025-03-24 03:44:11'),
(66, 'cashier@gmail.com', 'New Order Placed', 'ordered', 102, NULL, NULL, 'unread', '2025-03-24 03:46:02'),
(68, 'cashier@gmail.com', 'New Order Placed', 'ordered', 103, NULL, NULL, 'unread', '2025-03-24 03:48:03'),
(70, 'cashier@gmail.com', 'New Order Placed', 'ordered', 104, NULL, NULL, 'unread', '2025-03-24 03:50:25'),
(72, 'cashier@gmail.com', 'New Order Placed', 'ordered', 105, NULL, NULL, 'unread', '2025-03-24 03:50:32'),
(74, 'cashier@gmail.com', 'New Order Placed', 'ordered', 106, NULL, NULL, 'unread', '2025-03-24 03:50:44'),
(76, 'cashier@gmail.com', 'New Order Placed', 'ordered', 107, NULL, NULL, 'unread', '2025-03-24 03:50:50'),
(78, 'cashier@gmail.com', 'New Order Placed', 'ordered', 108, NULL, NULL, 'unread', '2025-03-24 03:51:04'),
(80, 'cashier@gmail.com', 'New Order Placed', 'ordered', 109, NULL, NULL, 'unread', '2025-03-24 03:51:21'),
(82, 'cashier@gmail.com', 'New Order Placed', 'ordered', 110, NULL, NULL, 'unread', '2025-03-24 03:51:27'),
(83, 'jay@gmail.com', 'New Order Placed', 'ordered', 111, NULL, NULL, 'unread', '2025-03-24 03:59:03'),
(84, 'jay@gmail.com', 'Payment Approval Required', 'requested', 112, NULL, NULL, 'unread', '2025-03-24 03:59:21'),
(85, 'jay@gmail.com', 'New Order Placed', 'ordered', 113, NULL, NULL, 'unread', '2025-03-24 03:59:59'),
(86, 'jay@gmail.com', 'Payment Confirmed', 'confirmed', 113, NULL, NULL, 'unread', '2025-03-24 04:00:30'),
(87, 'jay@gmail.com', 'Payment Confirmed', 'confirmed', 111, NULL, NULL, 'unread', '2025-03-24 04:00:35'),
(88, 'jay@gmail.com', 'Payment Confirmed', 'confirmed', 112, NULL, NULL, 'unread', '2025-03-24 04:00:39'),
(89, 'jay@gmail.com', 'Payment Installment Request', 'requested', 112, NULL, NULL, 'unread', '2025-03-24 04:00:54');

-- --------------------------------------------------------

--
-- Stand-in structure for view `admin_notifications_views`
-- (See below for the actual view)
--
CREATE TABLE `admin_notifications_views` (
`id` int
,`user_email` varchar(255)
,`status` enum('read','unread')
,`ntype` enum('ordered','returned','confirmed','requested','cancelled','return request','return rejected','return deleted')
,`head_msg` varchar(255)
,`created_at` timestamp
,`message` text
);

-- --------------------------------------------------------

--
-- Table structure for table `branch`
--

CREATE TABLE `branch` (
  `branch_id` int NOT NULL,
  `branch_location` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch`
--

INSERT INTO `branch` (`branch_id`, `branch_location`) VALUES
(1, 'Caloocan'),
(2, 'San Jose Del Monte, Bulacan'),
(3, 'Quezon City'),
(4, 'Valenzuela City');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `brand_id` int NOT NULL,
  `brand_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `brand_img` text COLLATE utf8mb4_general_ci,
  `branch_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `brand_img`, `branch_id`) VALUES
(2, 'Boysen', 'local_image/1730109970_boysen.jpg', NULL),
(9, 'Rain or Shine', 'local_image/1730110516_rain-or-shine.png', NULL),
(10, 'Ecomax', 'local_image/1730110526_ecomax.jpeg', NULL),
(12, 'Hippo', 'local_image/1730651776_hippo.png', NULL);

--
-- Triggers `brands`
--
DELIMITER $$
CREATE TRIGGER `before_delete_brand` BEFORE DELETE ON `brands` FOR EACH ROW BEGIN
    
    INSERT INTO `brands_archive` (`brand_id`, `brand_name`, `brand_img`, `branch_id`)
    VALUES (OLD.brand_id, OLD.brand_name, OLD.brand_img, OLD.branch_id);
    
    
    INSERT INTO `product_type_archive` (`deleted_brand_id`, `type_id`, `type_name`, `brand_id`, `prod_type`)
    SELECT OLD.brand_id, pt.`type_id`, pt.`type_name`, pt.`brand_id`, pt.`prod_type`
    FROM `product_type` pt
    WHERE pt.`brand_id` = OLD.brand_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `brands_archive`
--

CREATE TABLE `brands_archive` (
  `delete_id` int NOT NULL,
  `brand_id` int NOT NULL,
  `brand_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `brand_img` text COLLATE utf8mb4_general_ci,
  `branch_id` int DEFAULT NULL,
  `deleted_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cartitems`
--

CREATE TABLE `cartitems` (
  `itemID` int NOT NULL,
  `palletName` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `palletCode` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `palletRGB` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int UNSIGNED NOT NULL,
  `item_name` varchar(5000) NOT NULL DEFAULT '',
  `brand_name` varchar(255) NOT NULL,
  `item_image` varchar(5000) NOT NULL DEFAULT '',
  `item_date` datetime(6) NOT NULL,
  `expiration_date` varchar(255) DEFAULT NULL,
  `item_price` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `gl` varchar(255) NOT NULL,
  `pallet_id` int DEFAULT NULL,
  `branch` varchar(50) DEFAULT NULL,
  `hex` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `item_name`, `brand_name`, `item_image`, `item_date`, `expiration_date`, `item_price`, `type`, `quantity`, `gl`, `pallet_id`, `branch`, `hex`) VALUES
(87, 'Latex Paint', 'Boysen', '764116.jpg', '2025-02-13 00:00:00.000000', '2027-02-13', '200', 'Latex Paint', 91.00, 'Gallon', 15, 'Caloocan', ''),
(88, 'Latex Paint', 'Boysen', '278330.jpg', '2025-02-13 00:00:00.000000', '2027-02-12', '250', 'Oil Paint', 141.00, 'Gallon', 3, 'Caloocan', ''),
(89, 'Latex Paint', 'Boysen', '75472.jpg', '2025-02-13 00:00:00.000000', '2027-02-13', '100', 'Gloss', 192.00, 'Gallon', 12, 'Valenzuela City', ''),
(90, 'Boysen Paint', 'Boysen', '426978.png', '2025-02-13 00:00:00.000000', '2027-02-13', '200', 'Latex Paint', 191.00, 'Gallon', 6, 'Valenzuela City', '');

-- --------------------------------------------------------

--
-- Table structure for table `orderdetails`
--

CREATE TABLE `orderdetails` (
  `order_id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL DEFAULT '0',
  `order_name` varchar(1000) NOT NULL DEFAULT '',
  `order_price` double NOT NULL DEFAULT '0',
  `order_quantity` int UNSIGNED NOT NULL DEFAULT '0',
  `order_total` double NOT NULL DEFAULT '0',
  `order_status` varchar(45) NOT NULL DEFAULT '',
  `order_date` date DEFAULT NULL,
  `order_pick_up` datetime(6) DEFAULT NULL,
  `order_pick_place` enum('Caloocan','San Jose Del Monte, Bulacan','Quezon City','Valenzuela City') DEFAULT NULL,
  `gl` enum('Gallon','Liter') DEFAULT NULL,
  `payment_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `orderdetails`
--

INSERT INTO `orderdetails` (order_id, user_id, order_name, order_price, order_quantity, order_total, order_status, order_date, order_pick_up, order_pick_place, gl, payment_id, product_id) VALUES
(170, 8, 'Latex Paint', 200, 2, 400, 'Confirmed', '2025-03-15', '2025-03-16 14:30:00.000000', 'Caloocan', 'Gallon', 81, 87),
(171, 8, 'Latex Paint', 250, 1, 250, 'Confirmed', '2025-03-10', '2025-03-11 09:45:00.000000', 'Caloocan', 'Gallon', 79, 88),
(172, 8, 'Latex Paint', 100, 1, 100, 'Confirmed', '2025-02-28', '2025-03-01 18:00:00.000000', 'Valenzuela City', 'Gallon', 79, 89),
(173, 8, 'Boysen Paint', 200, 3, 600, 'Confirmed', '2025-03-05', '2025-03-06 12:15:00.000000', 'Valenzuela City', 'Gallon', 80, 90),
(174, 8, 'Latex Paint', 200, 1, 200, 'Confirmed', '2025-03-20', '2025-03-21 16:19:00.000000', 'Caloocan', 'Gallon', 82, 87),
(175, 8, 'Latex Paint', 250, 1, 250, 'Confirmed', '2025-03-18', '2025-03-19 10:20:00.000000', 'Caloocan', 'Gallon', 83, 88),
(178, 17, 'Latex Paint', 250, 1, 250, 'Confirmed', '2025-03-12', '2025-03-13 08:22:01.000000', 'Caloocan', 'Gallon', 84, 88),
(179, 17, 'Boysen Paint', 200, 1, 200, 'Confirmed', '2025-03-12', '2025-03-13 08:22:01.000000', 'Caloocan', 'Gallon', 84, 90),
(182, 17, 'Latex Paint', 250, 1, 250, 'Confirmed', '2025-03-14', '2025-03-15 11:22:34.000000', 'Caloocan', 'Gallon', 85, 88),
(183, 17, 'Latex Paint', 100, 1, 100, 'Confirmed', '2025-03-14', '2025-03-15 11:22:34.000000', 'Caloocan', 'Gallon', 85, 89),
(186, 17, 'Latex Paint', 100, 1, 100, 'Confirmed', '2025-03-16', '2025-03-17 13:41:42.000000', 'Caloocan', 'Gallon', 86, 89),
(187, 17, 'Boysen Paint', 200, 1, 200, 'Confirmed', '2025-03-16', '2025-03-17 13:41:42.000000', 'Caloocan', 'Gallon', 86, 90),
(189, 17, 'Latex Paint', 250, 1, 250, 'Confirmed', '2025-03-18', '2025-03-19 15:58:12.000000', 'Caloocan', 'Gallon', 87, 88),
(191, 17, 'Latex Paint', 100, 1, 100, 'Confirmed', '2025-03-19', '2025-03-20 10:14:35.000000', 'Caloocan', 'Gallon', 88, 89),
(206, 17, 'Latex Paint', 100, 1, 100, 'Confirmed', '2025-03-21', '2025-03-22 12:28:27.000000', 'Caloocan', 'Gallon', 99, 89),
(208, 17, 'Boysen Paint', 200, 1, 200, 'Confirmed', '2025-03-22', '2025-03-23 14:34:20.000000', 'Caloocan', 'Gallon', 100, 90),
(210, 17, 'Latex Paint', 250, 1, 250, 'Confirmed', '2025-03-23', '2025-03-24 16:44:11.000000', 'Caloocan', 'Gallon', 101, 88),
(212, 17, 'Latex Paint', 200, 1, 200, 'Confirmed', '2025-03-24', '2025-03-25 18:46:02.000000', 'Caloocan', 'Gallon', 102, 87),
(214, 17, 'Boysen Paint', 200, 1, 200, 'Confirmed', '2025-03-24', '2025-03-25 18:48:03.000000', 'Caloocan', 'Gallon', 103, 90),
(216, 17, 'Latex Paint', 200, 1, 200, 'Confirmed', '2025-03-24', '2025-03-25 18:50:25.000000', 'Caloocan', 'Gallon', 104, 87),
(219, 17, 'Latex Paint', 250, 1, 250, 'Confirmed', '2025-03-24', '2025-03-25 18:50:32.000000', 'Caloocan', 'Gallon', 105, 88),
(220, 17, 'Latex Paint', 100, 1, 100, 'Confirmed', '2025-03-24', '2025-03-25 18:50:32.000000', 'Caloocan', 'Gallon', 105, 89),
(222, 17, 'Boysen Paint', 200, 1, 200, 'Confirmed', '2025-03-24', '2025-03-25 18:50:44.000000', 'Caloocan', 'Gallon', 106, 90),
(224, 17, 'Latex Paint', 250, 1, 250, 'Confirmed', '2025-03-24', '2025-03-25 18:50:50.000000', 'Caloocan', 'Gallon', 107, 88),
(226, 17, 'Latex Paint', 100, 1, 100, 'Confirmed', '2025-03-24', '2025-03-25 18:51:04.000000', 'Caloocan', 'Gallon', 108, 89),
(228, 17, 'Latex Paint', 200, 2, 400, 'Confirmed', '2025-03-24', '2025-03-25 18:51:21.000000', 'Caloocan', 'Gallon', 109, 87),
(230, 17, 'Boysen Paint', 200, 2, 400, 'Confirmed', '2025-03-24', '2025-03-25 18:51:27.000000', 'Caloocan', 'Gallon', 110, 90),
(231, 31, 'Latex Paint', 200, 1, 200, 'Confirmed', '2025-03-24', '2025-03-25 18:58:00.000000', 'Caloocan', 'Gallon', 112, 87),
(232, 31, 'Latex Paint', 250, 1, 250, 'Confirmed', '2025-03-24', '2025-03-25 18:58:00.000000', 'Caloocan', 'Gallon', 112, 88),
(233, 31, 'Latex Paint', 100, 1, 100, 'Pending', '2025-03-24', '2025-03-25 18:58:00.000000', 'Valenzuela City', 'Gallon', NULL, 89),
(234, 31, 'Latex Paint', 200, 1, 200, 'Confirmed', '2025-03-24', '2025-03-25 18:58:00.000000', 'Caloocan', 'Gallon', 111, 87),
(235, 31, 'Latex Paint', 200, 1, 200, 'Confirmed', '2025-03-24', '2025-03-25 18:59:00.000000', 'Caloocan', 'Gallon', 113, 87),
(236, 32, 'Boysen Paint', 200, 2, 400, 'Confirmed', '2025-03-10', '2025-03-11 10:00:00.000000', 'Caloocan', 'Gallon', 114, 90),
(237, 33, 'Latex Paint', 250, 1, 250, 'Confirmed', '2025-03-12', '2025-03-13 11:30:00.000000', 'Caloocan', 'Gallon', 115, 88),
(238, 34, 'Latex Paint', 100, 3, 300, 'Confirmed', '2025-03-14', '2025-03-15 12:45:00.000000', 'Valenzuela City', 'Gallon', 116, 89),
(239, 35, 'Boysen Paint', 200, 1, 200, 'Confirmed', '2025-03-16', '2025-03-17 14:00:00.000000', 'Caloocan', 'Gallon', 117, 90),
(240, 36, 'Latex Paint', 200, 2, 400, 'Confirmed', '2025-03-18', '2025-03-19 15:15:00.000000', 'Caloocan', 'Gallon', 118, 87),
(241, 37, 'Latex Paint', 250, 1, 250, 'Confirmed', '2025-03-20', '2025-03-21 16:30:00.000000', 'Caloocan', 'Gallon', 119, 88),
(242, 38, 'Latex Paint', 100, 1, 100, 'Confirmed', '2025-03-22', '2025-03-23 17:45:00.000000', 'Caloocan', 'Gallon', 120, 89),
(243, 39, 'Boysen Paint', 200, 3, 600, 'Confirmed', '2025-03-24', '2025-03-25 18:00:00.000000', 'Caloocan', 'Gallon', 121, 90);

--
-- Triggers `orderdetails`
--
DELIMITER $$
CREATE TRIGGER `delete_on_cart` BEFORE DELETE ON `orderdetails` FOR EACH ROW BEGIN
    UPDATE items SET quantity = quantity + OLD.order_quantity WHERE item_id = OLD.product_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trigger_change_order_status_notif` AFTER UPDATE ON `orderdetails` FOR EACH ROW BEGIN
    DECLARE user_email VARCHAR(255);

    SELECT email INTO user_email
    FROM paymentform 
    WHERE id = NEW.payment_id
    LIMIT 1;

    IF NEW.order_status = 'Confirmed' AND OLD.order_status != 'Confirmed' 
       AND OLD.order_status != 'verification' THEN 
        INSERT INTO admin_notifications (order_id, ntype, user_email, head_msg)
        VALUES (
            NEW.order_id,
            'confirmed', 
            user_email, 
            'Order Confirmed'
        );
    ELSEIF NEW.order_status = 'Rejected' AND OLD.order_status != 'Rejected' 
           AND OLD.order_status != 'verification' THEN
        INSERT INTO admin_notifications (order_id, ntype, user_email, head_msg)
        VALUES (
            NEW.order_id,
            'cancelled', 
            user_email, 
            'Order Cancelled'
        );
    ELSEIF NEW.order_status = 'Returned' AND OLD.order_status != 'Returned' THEN
        INSERT INTO admin_notifications (order_id, ntype, user_email, head_msg)
        VALUES (
            NEW.order_id,
            'returned', 
            user_email, 
            'Order Returned'
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `pallets`
--

CREATE TABLE `pallets` (
  `pallet_id` int NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `rgb` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pallets`
--

INSERT INTO `pallets` (`pallet_id`, `code`, `name`, `rgb`) VALUES
(1, 'BM-0001', 'Fur White', 'rgb(241,233,225)'),
(2, 'BM-0002', 'Raspberry Run', 'rgb(188,112,134)'),
(3, 'BM-0003', 'Amazing Grace', 'rgb(242,227,217)'),
(4, 'BM-0004', 'Ice Cubes', 'rgb(218,220,224)'),
(5, 'BM-0005', 'Princess Pink', 'rgb(246,223,217)'),
(6, 'BM-0006', 'Enchanted White', 'rgb(243,232,214)'),
(7, 'BM-0008', 'Fossil', 'rgb(137,147,150)'),
(8, 'BM-0009', 'Red Desire', 'rgb(164,88,91)'),
(9, 'BM-0010', 'White Lace', 'rgb(239,232,217)'),
(10, 'BM-0011', 'Seashell White', 'rgb(241,225,212)'),
(11, 'BM-0012', 'Berrylicious', 'rgb(164,85,73)'),
(12, 'BM-0013', 'Barefoot', 'rgb(246,223,204)'),
(13, 'BM-0014', 'Creekside', 'rgb(180,162,141)'),
(14, 'BM-0015', 'Ballerina', 'rgb(247,220,203)'),
(15, 'BM-0016', 'Romance', 'rgb(165,137,142)'),
(16, 'BM-0017', 'Angel Face', 'rgb(252,225,214)'),
(17, 'BM-0018', 'Brunette', 'rgb(173,130,120)'),
(18, 'BM-0019', 'Muted Pink', 'rgb(239,196,185)'),
(19, 'BM-0020', 'Burnt Brick', 'rgb(183,128,116)'),
(20, 'BM-0021', 'Rosella', 'rgb(216,172,163)'),
(21, 'BM-0022', 'Fair Play', 'rgb(232,216,201)'),
(22, 'BM-0023', 'Earthenware', 'rgb(167,135,124)'),
(23, 'BM-0024', 'Weaved Basket', 'rgb(220,192,176)'),
(24, 'BM-0025', 'Simply Brown', 'rgb(122,103,98)'),
(25, 'BM-0026', 'White Senses', 'rgb(232,226,224)'),
(26, 'BM-0027', 'Cream Satin', 'rgb(243,236,221)'),
(27, 'BM-0028', 'Gray Aura', 'rgb(215,206,197)'),
(28, 'BM-0029', 'Cottage White', 'rgb(243,234,221)'),
(29, 'BM-0030', 'Dusty Trail', 'rgb(179,156,147)'),
(30, 'BM-0031', 'White Delight', 'rgb(243,234,217)'),
(31, 'BM-0033', 'Vanilla Ice', 'rgb(251,238,221)'),
(32, 'BM-0034', 'Smooth Cream', 'rgb(246,221,197)'),
(33, 'BM-0035', 'Wool Beige', 'rgb(247,230,211)'),
(34, 'BM-0036', 'Champagne', 'rgb(227,204,178)'),
(35, 'BM-0037', 'Coffee Creamer', 'rgb(245,226,206)'),
(36, 'BM-0038', 'Railroad', 'rgb(185,172,160)'),
(37, 'BM-0039', 'Manor White', 'rgb(246,225,203)'),
(38, 'BM-0040', 'Harbour Bay', 'rgb(137,160,170)'),
(39, 'BM-0041', 'Victorian White', 'rgb(246,225,203)'),
(40, 'BM-0042', 'White Wings', 'rgb(240,236,220)'),
(41, 'BM-0043', 'Pearl Necklace', 'rgb(241,217,190)'),
(42, 'BM-0044', 'Red Brocade', 'rgb(150,90,92)'),
(43, 'BM-0045', 'Sweet Yellow', 'rgb(250,221,183)'),
(44, 'BM-0046', 'Pink Ribbon', 'rgb(239,191,185)'),
(45, 'BM-0047', 'Full Moon', 'rgb(242,210,168)'),
(46, 'BM-0048', 'Clay Play', 'rgb(196,156,128)'),
(47, 'BM-0049', 'Being Happy', 'rgb(255,215,166)'),
(48, 'BM-0050', 'Urban Black', 'rgb(99,103,107)'),
(49, 'BM-0051', 'Honey Pie', 'rgb(253,209,158)'),
(50, 'BM-0052', 'Vanity Fair', 'rgb(243,223,205)'),
(51, 'BM-0053', 'Peach Tart', 'rgb(246,198,158)'),
(52, 'BM-0054', 'Brown Ben', 'rgb(150,105,82)'),
(53, 'BM-0056', 'Mystic Gray', 'rgb(179,182,179)'),
(54, 'BM-0057', 'Whipped Cream', 'rgb(246,235,209)'),
(55, 'BM-0058', 'Buttercup', 'rgb(255,223,172)'),
(56, 'BM-0059', 'Silky Cream', 'rgb(241,225,200)'),
(57, 'BM-0060', 'Aqua Cool', 'rgb(132,199,208)'),
(58, 'BM-0061', 'Sunny Smile', 'rgb(247,231,195)'),
(59, 'BM-0062', 'Boyish Green', 'rgb(214,224,202)'),
(60, 'BM-0063', 'Spring Shoot', 'rgb(245,229,193)'),
(61, 'BM-0064', 'Cool Aqua', 'rgb(201,225,211)'),
(62, 'BM-0065', 'Sunlit', 'rgb(247,227,188)'),
(63, 'BM-0066', 'Clay Art', 'rgb(174,137,129)'),
(64, 'BM-0067', 'Creamy Candy', 'rgb(248,221,180)'),
(65, 'BM-0068', 'Gold Truffles', 'rgb(214,167,103)'),
(66, 'BM-0069', 'Sunshiny', 'rgb(245,223,182)'),
(67, 'BM-0070', 'Lime Bubble', 'rgb(232,225,181)'),
(68, 'BM-0071', 'Fun in the Sun', 'rgb(253,231,187)'),
(69, 'BM-0072', 'Gold Glimmer', 'rgb(218,188,134)'),
(70, 'BM-0073', 'Summer\'s Day', 'rgb(250,223,171)'),
(71, 'BM-0074', 'Lightly Green', 'rgb(235,235,211)'),
(72, 'BM-0075', 'Sun Blessed', 'rgb(255,219,162)'),
(73, 'BM-0076', 'Frozen Lake', 'rgb(187,190,191)'),
(74, 'BM-0077', 'Golden Chimes', 'rgb(247,206,121)'),
(75, 'BM-0078', 'Hint of Mint', 'rgb(233,234,196)'),
(76, 'BM-0080', 'Safari Day', 'rgb(139,128,110)'),
(77, 'BM-0081', 'Cozy Yellow', 'rgb(233,181,120)'),
(78, 'BM-0082', 'Young Leaf', 'rgb(220,203,173)'),
(79, 'BM-0083', 'Almond Cream', 'rgb(243,233,212)'),
(80, 'BM-0084', 'Honey Jar', 'rgb(240,207,168)'),
(81, 'BM-0085', 'Biege Sonnet', 'rgb(237,221,193)'),
(82, 'BM-0086', 'Escapade', 'rgb(114,130,124)'),
(83, 'BM-0087', 'Angel\'s Halo', 'rgb(241,224,194)'),
(84, 'BM-0089', 'Soft Linen', 'rgb(242,223,193)'),
(85, 'BM-0090', 'Caramel Latte', 'rgb(172,139,105)'),
(86, 'BM-0091', 'Honey Star', 'rgb(238,216,182)'),
(87, 'BM-0092', 'Gingerbread', 'rgb(174,143,118)'),
(88, 'BM-0093', 'Golden Beige', 'rgb(230,207,178)'),
(89, 'BM-0094', 'Pebbles', 'rgb(178,167,152)'),
(90, 'BM-0095', 'Cashmere', 'rgb(224,199,169)'),
(91, 'BM-0096', 'Archipelago', 'rgb(143,123,107)'),
(92, 'BM-0097', 'Straw Hat', 'rgb(227,196,158)'),
(93, 'BM-0098', 'Matted Rug', 'rgb(187,165,135)'),
(94, 'BM-0099', 'Biscotti', 'rgb(224,192,154)'),
(95, 'BM-0100', 'Green Thumb', 'rgb(70,96,76)'),
(96, 'BM-0101', 'Nolstagic', 'rgb(218,201,184)'),
(97, 'BM-0102', 'Brocade White', 'rgb(221,216,209)'),
(98, 'BM-0103', 'Fine Craft', 'rgb(185,159,145)'),
(99, 'BM-0104', 'Nude Pink', 'rgb(225,209,202)'),
(100, 'BM-0105', 'White Spark', 'rgb(248,242,219)'),
(101, 'BM-0106', 'Gray Gates', 'rgb(145,153,151)'),
(102, 'BM-0107', 'Milky Bar', 'rgb(250,239,217)'),
(103, 'BM-0108', 'Burnished Copper', 'rgb(160,121,87)'),
(104, 'BM-0109', 'Tranquility', 'rgb(241,232,206)'),
(105, 'BM-0110', 'Terrace Hills', 'rgb(108,114,101)'),
(106, 'BM-0111', 'Sunny Day', 'rgb(244,234,200)'),
(107, 'BM-0112', 'Green Gables', 'rgb(105,135,107)'),
(108, 'BM-0113', 'Refreshing', 'rgb(241,232,200)'),
(109, 'BM-0114', 'Treasure Trove', 'rgb(143,125,112)'),
(110, 'BM-0115', 'Kiwi Kiss', 'rgb(233,224,187)'),
(111, 'BM-0116', 'Blue Powder', 'rgb(172,196,206)'),
(112, 'BM-0117', 'Lichen', 'rgb(220,210,173)'),
(113, 'BM-0119', 'Snappy Green', 'rgb(218,209,143)'),
(114, 'BM-0120', 'Chic Gray', 'rgb(198,194,190)'),
(115, 'BM-0121', 'Ivory Inn', 'rgb(229,216,195)'),
(116, 'BM-0122', 'Little Moss', 'rgb(203,187,159)'),
(117, 'BM-0123', 'Cornerstone', 'rgb(219,203,179)'),
(118, 'BM-0124', 'Fragrant Wood', 'rgb(190,171,142)'),
(119, 'BM-0125', 'Kindred Spirit', 'rgb(234,217,186)'),
(120, 'BM-0126', 'Raspberry Bliss', 'rgb(154,118,120)'),
(121, 'BM-0127', 'Underwood', 'rgb(207,192,164)'),
(122, 'BM-0128', 'Youthful Green', 'rgb(230,228,194)'),
(123, 'BM-0129', 'Oliverio', 'rgb(208,183,138)'),
(124, 'BM-0131', 'Oh Olive', 'rgb(158,143,111)'),
(125, 'BM-0132', 'Pink Hearts', 'rgb(239,221,212)'),
(126, 'BM-0133', 'Wooden Crate', 'rgb(126,111,95)'),
(127, 'BM-0134', 'Cotton Roll', 'rgb(218,201,184)'),
(128, 'BM-0135', 'Morning Dew', 'rgb(239,231,209)'),
(129, 'BM-0136', 'Sugar Cane', 'rgb(211,201,164)'),
(130, 'BM-0137', 'White Bud', 'rgb(241,236,222)'),
(131, 'BM-0138', 'Little Gardenia', 'rgb(231,227,183)'),
(132, 'BM-0139', 'Sagely White', 'rgb(235,229,213)'),
(133, 'BM-0140', 'Robin\'s Hood', 'rgb(200,190,162)'),
(134, 'BM-0141', 'Minty Fresh', 'rgb(235,233,218)'),
(135, 'BM-0142', 'Misty Moss', 'rgb(227,226,204)'),
(136, 'BM-0143', 'My Sanctuary', 'rgb(194,193,179)'),
(137, 'BM-0144', 'Deep Terraine', 'rgb(102,99,92)'),
(138, 'BM-0145', 'Blissful', 'rgb(240,236,214)'),
(139, 'BM-0146', 'Surfer Blue', 'rgb(94,188,203)'),
(140, 'BM-0147', 'Soft Spring', 'rgb(238,233,212)'),
(141, 'BM-0148', 'Light Pear', 'rgb(233,228,192)'),
(142, 'BM-0149', 'Heavenly', 'rgb(222,223,194)'),
(143, 'BM-0150', 'Esplanade', 'rgb(151,143,109)'),
(144, 'BM-0151', 'Little Hideout', 'rgb(186,203,182)'),
(145, 'BM-0152', 'Garden Swing', 'rgb(223,229,200)'),
(146, 'BM-0153', 'Restful Ride', 'rgb(125,147,144)'),
(147, 'BM-0154', 'Graceful Swan', 'rgb(248,236,210)'),
(148, 'BM-0155', 'Foamy White', 'rgb(110,126,129)'),
(149, 'BM-0156', 'Ice Queen', 'rgb(216,217,212)'),
(150, 'BM-0157', 'Green Infusion', 'rgb(226,238,224)'),
(151, 'BM-0158', 'Clear Jade', 'rgb(98,204,187)'),
(152, 'BM-0159', 'Aegean Seas', 'rgb(164,222,215)'),
(153, 'BM-0160', 'Glorious Green', 'rgb(61,193,180)'),
(154, 'BM-0161', 'Seaside Walk', 'rgb(126,209,208)'),
(155, 'BM-0162', 'Breezy Day', 'rgb(223,235,231)'),
(156, 'BM-0163', 'Cebu Seas', 'rgb(63,178,194)'),
(157, 'BM-0164', 'White Sands', 'rgb(246,230,206)'),
(158, 'BM-0165', 'Little Grove', 'rgb(226,225,215)'),
(159, 'BM-0166', 'Pine Cove', 'rgb(159,185,162)'),
(160, 'BM-0167', 'Crown Gray', 'rgb(187,195,192)'),
(161, 'BM-0168', 'Royal Robe', 'rgb(100,124,158)'),
(162, 'BM-0169', 'Moody Gray', 'rgb(135,147,152)'),
(163, 'BM-0170', 'Innocence', 'rgb(249,219,205)'),
(164, 'BM-0171', 'Under Cover', 'rgb(85,90,90)'),
(165, 'BM-0172', 'Blue Clouds', 'rgb(201,220,216)'),
(166, 'BM-0173', 'White Sails', 'rgb(240,233,221)'),
(167, 'BM-0174', 'Blue Wisp', 'rgb(208,218,221)'),
(168, 'BM-0175', 'Blue Icicle', 'rgb(226,233,228)'),
(169, 'BM-0176', 'Monsoon Rains', 'rgb(129,160,168)'),
(170, 'BM-0177', 'May Day', 'rgb(214,230,225)'),
(171, 'BM-0178', 'Aqua Song', 'rgb(144,201,201)'),
(172, 'BM-0179', 'Dewdrops', 'rgb(210,229,226)'),
(173, 'BM-0180', 'Bubbly Blue', 'rgb(221,231,228)'),
(174, 'BM-0181', 'Under the Skies', 'rgb(203,228,228)'),
(175, 'BM-0182', 'Grounded', 'rgb(197,191,188)'),
(176, 'BM-0183', 'Dreamtime', 'rgb(190,221,226)'),
(177, 'BM-0184', 'Turquoise Treat', 'rgb(0,146,163)'),
(178, 'BM-0185', 'Blue Splash', 'rgb(197,222,228)'),
(179, 'BM-0186', 'Tad of Teal', 'rgb(70,164,187)'),
(180, 'BM-0187', 'Boracay Waters', 'rgb(177,202,213)'),
(181, 'BM-0188', 'Ports Bay', 'rgb(130,157,173)'),
(182, 'BM-0189', 'Island Blue', 'rgb(159,185,197)'),
(183, 'BM-0190', 'Puka Beach', 'rgb(238,228,216)'),
(184, 'BM-0191', 'Blue Paradise', 'rgb(128,191,210)'),
(185, 'BM-0192', 'Dandelion White', 'rgb(250,241,228)'),
(186, 'BM-0193', 'Gray Tinge', 'rgb(228,229,229)'),
(187, 'BM-0194', 'Makati Haze', 'rgb(181,194,198)'),
(188, 'BM-0195', 'Walking on Air', 'rgb(230,229,229)'),
(189, 'BM-0196', 'Blue Tie', 'rgb(177,201,209)'),
(190, 'BM-0197', 'Gray Drizzle', 'rgb(223,223,223)'),
(191, 'BM-0198', 'Pearl Gray', 'rgb(194,192,194)'),
(192, 'BM-0199', 'Lilac Luxury', 'rgb(180,186,198)'),
(193, 'BM-0200', 'Elegant Brown', 'rgb(120,109,111)'),
(194, 'BM-0201', 'Gray Cape', 'rgb(149,147,152)'),
(195, 'BM-0202', 'Whipering Pink', 'rgb(239,224,222)'),
(196, 'BM-0203', 'Lavender Touch', 'rgb(231,225,227)'),
(197, 'BM-0204', 'Pigeon Hole', 'rgb(184,180,181)'),
(198, 'BM-0205', 'Light Heather', 'rgb(235,229,227)'),
(199, 'BM-0206', 'Plum Touch', 'rgb(221,208,206)'),
(200, 'BM-0207', 'Charm', 'rgb(212,204,201)'),
(201, 'BM-0208', 'Rose Musing', 'rgb(197,173,167)'),
(202, 'BM-0209', 'Rising Mauve', 'rgb(196,184,184)'),
(203, 'BM-0210', 'Rugged Path', 'rgb(150,164,172)'),
(204, 'BM-0211', 'Fine Sand', 'rgb(244,230,218)'),
(205, 'BM-0212', 'Stepping Stone', 'rgb(177,163,154)'),
(206, 'BM-0213', 'Natural Stone', 'rgb(237,220,205)'),
(207, 'BM-0214', 'Wet Sand', 'rgb(190,173,154)'),
(208, 'BM-0215', 'Old Charm', 'rgb(211,196,191)'),
(209, 'BM-0216', 'Suede Shoes', 'rgb(178,154,147)'),
(210, 'BM-0217', 'Tuscan Tate', 'rgb(198,181,176)'),
(211, 'BM-0218', 'Choco Cherry', 'rgb(124,94,96)'),
(212, 'BM-0219', 'In the Shade', 'rgb(193,179,174)'),
(213, 'BM-0220', 'Deep Taupe', 'rgb(151,135,131)'),
(214, 'BM-0221', 'Bohemian Scarf', 'rgb(179,166,162)'),
(215, 'BM-0222', 'Knitted Shawl', 'rgb(145,124,114)'),
(216, 'BM-0223', 'White Waltz', 'rgb(232,229,222)'),
(217, 'BM-0224', 'Castle Rock', 'rgb(140,136,139)'),
(218, 'BM-0225', 'Gray Frost', 'rgb(230,232,227)'),
(219, 'BM-0226', 'Glazed Gray', 'rgb(187,191,192)'),
(220, 'BM-0227', 'Silver Gray', 'rgb(213,215,216)'),
(221, 'BM-0228', 'Gothic Gray', 'rgb(141,147,153)'),
(222, 'BM-0229', 'Gavin Gray', 'rgb(178,189,195)'),
(223, 'BM-0230', 'Stormy Night', 'rgb(130,141,151)'),
(224, 'BM-0231', 'Thunderstorm', 'rgb(141,148,151)'),
(225, 'BM-0232', 'Tattoo', 'rgb(95,98,104)'),
(226, 'BM-0233', 'Graceful White', 'rgb(234,233,224)'),
(227, 'BM-0234', 'Gray Pewter', 'rgb(151,165,171)'),
(228, 'BM-0235', 'Fog', 'rgb(203,203,198)'),
(229, 'BM-0236', 'Gray Shadows', 'rgb(140,148,148)'),
(230, 'BM-0237', 'Aristocrat', 'rgb(191,196,194)'),
(231, 'BM-0238', 'So in Love', 'rgb(175,95,104)'),
(232, 'BM-0239', 'Gray Gown', 'rgb(192,194,193)'),
(233, 'BM-0240', 'Sweet Illusion', 'rgb(225,206,208)'),
(234, 'BM-0241', 'Cast in Stone', 'rgb(175,174,172)'),
(235, 'BM-0242', 'Incognito', 'rgb(210,208,205)'),
(236, 'BM-0243', 'Steel Shots', 'rgb(178,179,172)'),
(237, 'BM-0244', 'Sandy Beaches', 'rgb(254,208,158)'),
(238, 'BM-0245', 'Stone Statue', 'rgb(200,193,184)'),
(239, 'BM-0246', 'Bably Blue', 'rgb(155,208,217)'),
(240, 'BM-0247', 'Pottery Loft', 'rgb(201,192,184)'),
(241, 'BM-0248', 'Deep Forest', 'rgb(82,98,94)'),
(242, 'BM-0249', 'Stone Age', 'rgb(142,138,132)'),
(243, 'BM-0250', 'Arrow White', 'rgb(232,220,204)');

-- --------------------------------------------------------

--
-- Table structure for table `paymentform`
--

CREATE TABLE `paymentform` (
  `id` int NOT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `mobile` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `payment_type` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_image_path` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `order_id` int UNSIGNED DEFAULT NULL,
  `payment_status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'verification',
  `months_paid` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paymentform`
--


INSERT INTO `paymentform` (id, firstname, lastname, email, address, mobile, payment_method, payment_type, amount, payment_image_path, created_at, order_id, payment_status, months_paid) VALUES
(79, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Full Payment', 350.00, './uploaded_images/e79dd102b35213f815291e0fb4bd12df.jpg', '2025-03-15 10:34:19', NULL, 'Confirmed', 0),
(80, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Down Payment', 300.00, './uploaded_images/67e0ab578ee3b.jpg', '2025-03-10 11:35:36', NULL, 'Confirmed', 0),
(81, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Down Payment', 400.00, './uploaded_images/67e0b9e0c7c08.jpg', '2025-03-05 12:09:26', NULL, 'Confirmed', 0),
(82, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Full Payment', 200.00, './uploaded_images/e79dd102b35213f815291e0fb4bd12df.jpg', '2025-03-20 13:20:33', NULL, 'Confirmed', 0),
(83, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Full Payment', 250.00, './uploaded_images/e79dd102b35213f815291e0fb4bd12df.jpg', '2025-03-18 14:20:47', NULL, 'Confirmed', 0),
(84, 'Maria', 'Santos', 'maria@gmail.com', 'Manila', '091238141', 'Walk In', 'Full Payment', 450.00, '', '2025-03-12 15:22:01', NULL, 'Confirmed', 0),
(85, 'Juan', 'Dela Cruz', 'juan@gmail.com', 'Quezon City', '091238142', 'Walk In', 'Full Payment', 350.00, '', '2025-03-14 16:22:34', NULL, 'Confirmed', 0),
(86, 'Ana', 'Reyes', 'ana@gmail.com', 'Makati', '091238143', 'Walk In', 'Full Payment', 300.00, '', '2025-03-16 17:41:42', NULL, 'Confirmed', 0),
(87, 'Pedro', 'Gonzales', 'pedro@gmail.com', 'Pasig', '091238144', 'Walk In', 'Full Payment', 250.00, '', '2025-03-18 18:58:12', NULL, 'Confirmed', 0),
(88, 'Luis', 'Torres', 'luis@gmail.com', 'Taguig', '091238145', 'Walk In', 'Full Payment', 100.00, '', '2025-03-19 19:14:35', NULL, 'Confirmed', 0),
(99, 'Joshua', 'Smith', 'joshua@gmail.com', 'Caloocan', '09593536253', 'Walk In', 'Full Payment', 100.00, '', '2025-03-21 20:28:27', NULL, 'Confirmed', 0),
(100, 'Johnson', 'Lee', 'johnson@gmail.com', 'Caloocan', '09535532111', 'Walk In', 'Full Payment', 200.00, '', '2025-03-22 21:34:20', NULL, 'Confirmed', 0),
(101, 'Anna', 'Martinez', 'anna@gmail.com', 'Caloocan', '09123456789', 'Walk In', 'Full Payment', 250.00, '', '2025-03-23 22:44:11', NULL, 'Confirmed', 0),
(102, 'John', 'Doe', 'john@gmail.com', 'Caloocan', '09123456788', 'Walk In', 'Full Payment', 200.00, '', '2025-03-24 23:46:02', NULL, 'Confirmed', 0),
(103, 'Michael', 'Tan', 'michael@gmail.com', 'Caloocan', '09123456787', 'Walk In', 'Full Payment', 200.00, '', '2025-03-24 23:48:03', NULL, 'Confirmed', 0),
(104, 'Sarah', 'Lim', 'sarah@gmail.com', 'Caloocan', '09123456786', 'Walk In', 'Full Payment', 200.00, '', '2025-03-24 23:50:25', NULL, 'Confirmed', 0),
(105, 'David', 'Garcia', 'david@gmail.com', 'Caloocan', '09123456785', 'Walk In', 'Full Payment', 350.00, '', '2025-03-24 23:50:32', NULL, 'Confirmed', 0),
(106, 'Elena', 'Chua', 'elena@gmail.com', 'Caloocan', '09123456784', 'Walk In', 'Full Payment', 200.00, '', '2025-03-24 23:50:44', NULL, 'Confirmed', 0),
(107, 'Carlos', 'Ng', 'carlos@gmail.com', 'Caloocan', '09123456783', 'Walk In', 'Full Payment', 250.00, '', '2025-03-24 23:50:50', NULL, 'Confirmed', 0),
(108, 'Sofia', 'Yu', 'sofia@gmail.com', 'Caloocan', '09123456782', 'Walk In', 'Full Payment', 100.00, '', '2025-03-24 23:51:04', NULL, 'Confirmed', 0),
(109, 'Daniel', 'Wong', 'daniel@gmail.com', 'Caloocan', '09123456781', 'Walk In', 'Full Payment', 400.00, '', '2025-03-24 23:51:21', NULL, 'Confirmed', 0),
(110, 'Grace', 'Chen', 'grace@gmail.com', 'Caloocan', '09123456780', 'Walk In', 'Full Payment', 400.00, '', '2025-03-24 23:51:27', NULL, 'Confirmed', 0),
(111, 'Jay', 'Casio', 'jay@gmail.com', 'City', '09123456789', 'Gcash', 'Full Payment', 200.00, './uploaded_images/e79dd102b35213f815291e0fb4bd12df.jpg', '2025-03-24 23:59:03', NULL, 'Confirmed', 0),
(112, 'Jay', 'Casio', 'jay@gmail.com', 'City', '09123456789', 'Gcash', 'Down Payment', 450.00, './uploaded_images/67e0d8f658d09.jpg', '2025-03-24 23:59:21', NULL, 'Confirmed', 0),
(113, 'Jay', 'Casio', 'jay@gmail.com', 'City', '09123456789', 'Gcash', 'Full Payment', 200.00, './uploaded_images/e79dd102b35213f815291e0fb4bd12df.jpg', '2025-03-24 23:59:59', NULL, 'Confirmed', 0),
(114, 'Michael', 'Tan', 'michael@gmail.com', 'Caloocan', '09123456787', 'Gcash', 'Full Payment', 400.00, './uploaded_images/e79dd102b35213f815291e0fb4bd12df.jpg', '2025-03-10 09:00:00', NULL, 'Confirmed', 0),
(115, 'Sarah', 'Lim', 'sarah@gmail.com', 'Caloocan', '09123456786', 'Gcash', 'Full Payment', 250.00, './uploaded_images/e79dd102b35213f815291e0fb4bd12df.jpg', '2025-03-12 10:30:00', NULL, 'Confirmed', 0),
(116, 'David', 'Garcia', 'david@gmail.com', 'Caloocan', '09123456785', 'Gcash', 'Down Payment', 150.00, './uploaded_images/67e0b9e0c7c08.jpg', '2025-03-14 11:45:00', NULL, 'Confirmed', 0),
(117, 'Elena', 'Chua', 'elena@gmail.com', 'Caloocan', '09123456784', 'Walk In', 'Full Payment', 200.00, '', '2025-03-16 13:00:00', NULL, 'Confirmed', 0),
(118, 'Carlos', 'Ng', 'carlos@gmail.com', 'Caloocan', '09123456783', 'Gcash', 'Full Payment', 400.00, './uploaded_images/e79dd102b35213f815291e0fb4bd12df.jpg', '2025-03-18 14:15:00', NULL, 'Confirmed', 0),
(119, 'Sofia', 'Yu', 'sofia@gmail.com', 'Caloocan', '09123456782', 'Gcash', 'Full Payment', 250.00, './uploaded_images/e79dd102b35213f815291e0fb4bd12df.jpg', '2025-03-20 15:30:00', NULL, 'Confirmed', 0),
(120, 'Daniel', 'Wong', 'daniel@gmail.com', 'Caloocan', '09123456781', 'Walk In', 'Full Payment', 100.00, '', '2025-03-22 16:45:00', NULL, 'Confirmed', 0),
(121, 'Grace', 'Chen', 'grace@gmail.com', 'Caloocan', '09123456780', 'Gcash', 'Full Payment', 600.00, './uploaded_images/e79dd102b35213f815291e0fb4bd12df.jpg', '2025-03-24 17:00:00', NULL, 'Confirmed', 0);

--
-- Triggers `paymentform`
--
DELIMITER $$
CREATE TRIGGER `trigger_order_notif` AFTER INSERT ON `paymentform` FOR EACH ROW BEGIN
    IF NEW.payment_type = 'Full Payment' THEN 
        INSERT INTO admin_notifications (payment_id, ntype, user_email, head_msg)
        VALUES (
            NEW.id,
            'ordered', 
            NEW.email, 
            'New Order Placed'
        );
    ELSEIF NEW.payment_type IN ('Installment', 'Down Payment') THEN
        INSERT INTO admin_notifications (payment_id, ntype, user_email, head_msg)
        VALUES (
            NEW.id,
            'requested', 
            NEW.email, 
            'Payment Approval Required'
        );
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trigger_update_order_notif` AFTER UPDATE ON `paymentform` FOR EACH ROW BEGIN
    IF NEW.payment_status = 'Confirmed' AND OLD.payment_status != 'Confirmed' THEN 
        INSERT INTO admin_notifications (payment_id, ntype, user_email, head_msg)
        VALUES (
            NEW.id,
            'confirmed', 
            NEW.email, 
            'Payment Confirmed'  
        );
    ELSEIF NEW.payment_status = 'Failed' AND OLD.payment_status != 'Failed' THEN
        INSERT INTO admin_notifications (payment_id, ntype, user_email, head_msg)
        VALUES (
            NEW.id,
            'cancelled', 
            NEW.email, 
            'Payment Cancelled'
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `payment_track`
--

CREATE TABLE `payment_track` (
  `track_id` int NOT NULL,
  `payment_id` int NOT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'Requested',
  `amount` decimal(10,2) NOT NULL,
  `date_tracked` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `payment_track`
--

INSERT INTO `payment_track` (track_id, payment_id, status, amount, date_tracked) VALUES
(1, 80, 'Requested', 130.00, '2025-03-10 08:46:15.588063'),
(2, 81, 'Confirmed', 100.00, '2025-03-05 09:47:43.267358'),
(3, 81, 'Confirmed', 100.00, '2025-03-05 09:48:16.819990'),
(4, 112, 'Confirmed', 225.00, '2025-03-24 12:00:54.364818'),
(5, 114, 'Confirmed', 400.00, '2025-03-10 09:15:00.000000'),
(6, 115, 'Confirmed', 250.00, '2025-03-12 10:45:00.000000'),
(7, 116, 'Requested', 150.00, '2025-03-14 12:00:00.000000'),
(8, 116, 'Confirmed', 150.00, '2025-03-14 12:30:00.000000'),
(9, 117, 'Confirmed', 200.00, '2025-03-16 13:15:00.000000'),
(10, 118, 'Confirmed', 400.00, '2025-03-18 14:30:00.000000'),
(11, 119, 'Confirmed', 250.00, '2025-03-20 15:45:00.000000'),
(12, 120, 'Confirmed', 100.00, '2025-03-22 17:00:00.000000'),
(13, 121, 'Confirmed', 600.00, '2025-03-24 18:15:00.000000');

--
-- Triggers `payment_track`
--
DELIMITER $$
CREATE TRIGGER `trigger_partial_payment_notif` AFTER INSERT ON `payment_track` FOR EACH ROW BEGIN
    DECLARE user_email VARCHAR(255);
    
    IF NEW.status = 'Requested' THEN
        SELECT email INTO user_email
        FROM paymentform 
        WHERE id = NEW.payment_id
        LIMIT 1;
        
        INSERT INTO admin_notifications (payment_id, ntype, user_email, head_msg)
        VALUES (
            NEW.payment_id,
            'requested', 
            user_email, 
            'Payment Installment Request'
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `product_requests`
--

CREATE TABLE `product_requests` (
  `request_id` int NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `product_brand` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quantity` int NOT NULL,
  `requesting_branch` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Pending','Declined','Confirmed') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `requested_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `approved_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_requests`
--

INSERT INTO `product_requests` (`request_id`, `product_name`, `product_brand`, `quantity`, `requesting_branch`, `status`, `requested_at`, `approved_date`) VALUES
(1, 'Paint', 'Boysen', 10, 'Quezon City', 'Confirmed', '2024-12-04 07:54:17', '2024-12-04 07:54:39'),
(2, 'Paint Brush', 'Ecomax', 5, 'Quezon City', 'Declined', '2024-12-04 07:54:24', '2024-12-04 07:54:50'),
(3, 'Gloss Paint', 'Boysen', 20, 'Quezon City', 'Pending', '2024-12-04 08:07:30', NULL);

--
-- Triggers `product_requests`
--
DELIMITER $$
CREATE TRIGGER `update_approved_date` BEFORE UPDATE ON `product_requests` FOR EACH ROW BEGIN
    
    IF NEW.status IN ('Confirmed', 'Declined') AND OLD.status != NEW.status THEN
        SET NEW.approved_date = NOW();
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `product_type`
--

CREATE TABLE `product_type` (
  `type_id` int NOT NULL,
  `type_name` text COLLATE utf8mb4_general_ci NOT NULL,
  `brand_id` int NOT NULL,
  `prod_type` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_type`
--

INSERT INTO `product_type` (`type_id`, `type_name`, `brand_id`, `prod_type`) VALUES
(8, 'Latex Paint', 2, 'Paint'),
(9, 'Gloss', 2, 'Paint'),
(10, 'Oil Paint', 2, 'Paint'),
(11, 'Aluminum Paint', 2, 'Paint'),
(12, 'Semi Gloss Paint', 2, 'Paint'),
(13, 'Enamel', 2, 'Paint'),
(14, 'Exterior Paint', 2, 'Paint'),
(15, 'Interior Paint', 2, 'Paint'),
(16, 'Emulsion', 2, 'Paint'),
(17, 'Primer', 2, 'Paint'),
(18, 'Acrylic', 2, 'Paint'),
(19, 'Flat Paint', 2, 'Paint'),
(20, 'Matte Finish', 2, 'Paint'),
(26, 'Paint Brush', 12, 'Tool'),
(27, 'Spray Gun', 12, 'Tool'),
(28, 'Gloss', 12, 'Paint'),
(29, 'Gloss', 10, 'Paint'),
(30, 'Latex Paint', 10, 'Paint');

--
-- Triggers `product_type`
--
DELIMITER $$
CREATE TRIGGER `before_delete_product_type` BEFORE DELETE ON `product_type` FOR EACH ROW BEGIN
    
    INSERT INTO `product_type_archive` (`deleted_brand_id`, `type_id`, `type_name`, `brand_id`, `prod_type`)
    VALUES (NULL, OLD.type_id, OLD.type_name, OLD.brand_id, OLD.prod_type);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `product_type_archive`
--

CREATE TABLE `product_type_archive` (
  `delete_id` int NOT NULL,
  `deleted_brand_id` int DEFAULT NULL,
  `type_id` int NOT NULL,
  `type_name` text COLLATE utf8mb4_general_ci NOT NULL,
  `brand_id` int NOT NULL,
  `prod_type` text COLLATE utf8mb4_general_ci NOT NULL,
  `deleted_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `returnitems`
--

CREATE TABLE `returnitems` (
  `return_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `reason` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `product_image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `receipt_image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `branch` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `returnitems`
--
DELIMITER $$
CREATE TRIGGER `trigger_delete_return_request` BEFORE DELETE ON `returnitems` FOR EACH ROW BEGIN
    DECLARE v_user_email VARCHAR(255);

    SELECT user_email INTO v_user_email
    FROM users 
    WHERE user_id = OLD.user_id;  
    
    INSERT INTO admin_notifications (return_id, ntype, user_email, head_msg)
    VALUES (
        OLD.return_id,
        'return deleted', 
        v_user_email, 
        'Return Request Deleted'
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trigger_insert_return_request` AFTER INSERT ON `returnitems` FOR EACH ROW BEGIN
    DECLARE v_user_email VARCHAR(255);
    
    SELECT user_email INTO v_user_email
    FROM users 
    WHERE user_id = NEW.user_id;
    
    INSERT INTO admin_notifications (return_id, ntype, user_email, head_msg)
    VALUES (
        NEW.return_id,
        'return request', 
        v_user_email, 
        'New Return Request'  
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trigger_update_return_request` AFTER UPDATE ON `returnitems` FOR EACH ROW BEGIN
    DECLARE v_user_email VARCHAR(255);

    SELECT user_email INTO v_user_email
    FROM users 
    WHERE user_id = NEW.user_id;
    
    IF NEW.status = 'Confirmed' AND OLD.status != 'Confirmed' THEN 
        INSERT INTO admin_notifications (return_id, ntype, user_email, head_msg)
        VALUES (
            NEW.return_id,
            'returned', 
            v_user_email, 
            'Return Confirmed'
        );
    ELSEIF NEW.status = 'Rejected' AND OLD.status != 'Rejected' THEN 
        INSERT INTO admin_notifications (return_id, ntype, user_email, head_msg)
        VALUES (
            NEW.return_id,
            'return rejected', 
            v_user_email, 
            'Return Request Rejected'
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `return_payments`
--

CREATE TABLE `return_payments` (
  `return_payment_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `return_status` text COLLATE utf8mb4_general_ci,
  `proof_of_payment` text COLLATE utf8mb4_general_ci,
  `amount_return` int DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  `branch` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `user_email` varchar(1000) NOT NULL,
  `user_password` varchar(1000) NOT NULL,
  `user_firstname` varchar(1000) NOT NULL,
  `user_lastname` varchar(1000) NOT NULL,
  `user_address` varchar(1000) NOT NULL,
  `user_mobile` varchar(255) NOT NULL,
  `type` enum('Admin','Customer','Cashier') DEFAULT NULL,
  `assigned_branch` enum('Caloocan','San Jose Del Monte, Bulacan','Quezon City','Valenzuela City') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_email`, `user_password`, `user_firstname`, `user_lastname`, `user_address`, `user_mobile`, `type`, `assigned_branch`) VALUES
(6, 'admin@email.com', 'admin', 'admin', 'administrator', 'Ilocos Norte', '09123456778', 'Admin', 'Caloocan'),
(8, 'kate@email.com', 'kate', 'Kate', 'Ruaza', 'myaddress', '093473455', 'Customer', NULL),
(17, 'cashier@gmail.com', 'cash', 'John', 'Lee', 'Caloocan', '091238141', 'Cashier', 'Caloocan'),
(25, 'bulancanadmin@email.com', 'admin', 'bulacan', 'admin', 'bulacan', '90193192738', 'Admin', 'San Jose Del Monte, Bulacan'),
(26, 'qcadmin@email.com', 'admin', 'quezon city', 'admin', 'Quezon City', '09123619823', 'Admin', 'Quezon City'),
(27, 'valenzuelaadmin@email.com', 'admin', 'valenzuela', 'admin', 'Valenzuela City', '0923724971', 'Admin', 'Valenzuela City'),
(28, 'bulacancashier@email.com', 'cash', 'bulacan', 'cashier', 'Bulacan', '029187492131', 'Cashier', 'San Jose Del Monte, Bulacan'),
(29, 'qccashier@email.com', 'admin', 'quezon city', 'cashier', 'Quezon City', '092391823', 'Cashier', 'Quezon City'),
(30, 'valenzuelacashier@email.com', 'cash', 'valenzuela', 'cashier', 'Valenzuela City', '0923917941', 'Cashier', 'Valenzuela City'),
(31, 'jay@gmail.com', 'jay', 'Jay', 'Casio', 'City', '09123456789', 'Customer', 'Caloocan'),
(32, 'michael@gmail.com', 'michael', 'Michael', 'Tan', 'Caloocan', '09123456787', 'Customer', 'Caloocan'),
(33, 'sarah@gmail.com', 'sarah', 'Sarah', 'Lim', 'Caloocan', '09123456786', 'Customer', 'Caloocan'),
(34, 'david@gmail.com', 'david', 'David', 'Garcia', 'Caloocan', '09123456785', 'Customer', 'Caloocan'),
(35, 'elena@gmail.com', 'elena', 'Elena', 'Chua', 'Caloocan', '09123456784', 'Customer', 'Caloocan'),
(36, 'carlos@gmail.com', 'carlos', 'Carlos', 'Ng', 'Caloocan', '09123456783', 'Customer', 'Caloocan'),
(37, 'sofia@gmail.com', 'sofia', 'Sofia', 'Yu', 'Caloocan', '09123456782', 'Customer', 'Caloocan'),
(38, 'daniel@gmail.com', 'daniel', 'Daniel', 'Wong', 'Caloocan', '09123456781', 'Customer', 'Caloocan'),
(39, 'grace@gmail.com', 'grace', 'Grace', 'Chen', 'Caloocan', '09123456780', 'Customer', 'Caloocan');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wish_id` int NOT NULL,
  `user_id` int NOT NULL,
  `item_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branch`
--
ALTER TABLE `branch`
  ADD PRIMARY KEY (`branch_id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`),
  ADD UNIQUE KEY `brand_id` (`brand_id`);

--
-- Indexes for table `brands_archive`
--
ALTER TABLE `brands_archive`
  ADD PRIMARY KEY (`delete_id`);

--
-- Indexes for table `cartitems`
--
ALTER TABLE `cartitems`
  ADD PRIMARY KEY (`itemID`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `FK_orderdetails_1` (`user_id`),
  ADD KEY `FK_orderdetails_paymentform` (`payment_id`);

--
-- Indexes for table `pallets`
--
ALTER TABLE `pallets`
  ADD PRIMARY KEY (`pallet_id`),
  ADD UNIQUE KEY `pallet_id` (`pallet_id`);

--
-- Indexes for table `paymentform`
--
ALTER TABLE `paymentform`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_paymentform_orderdetails` (`order_id`);

--
-- Indexes for table `payment_track`
--
ALTER TABLE `payment_track`
  ADD PRIMARY KEY (`track_id`),
  ADD KEY `paymentform_track_fk` (`payment_id`);

--
-- Indexes for table `product_requests`
--
ALTER TABLE `product_requests`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `product_type`
--
ALTER TABLE `product_type`
  ADD PRIMARY KEY (`type_id`),
  ADD KEY `fk_brand` (`brand_id`);

--
-- Indexes for table `product_type_archive`
--
ALTER TABLE `product_type_archive`
  ADD PRIMARY KEY (`delete_id`);

--
-- Indexes for table `returnitems`
--
ALTER TABLE `returnitems`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `return_payments`
--
ALTER TABLE `return_payments`
  ADD PRIMARY KEY (`return_payment_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wish_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `branch`
--
ALTER TABLE `branch`
  MODIFY `branch_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `brands_archive`
--
ALTER TABLE `brands_archive`
  MODIFY `delete_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cartitems`
--
ALTER TABLE `cartitems`
  MODIFY `itemID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `orderdetails`
--
ALTER TABLE `orderdetails`
  MODIFY `order_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=244;

--
-- AUTO_INCREMENT for table `pallets`
--
ALTER TABLE `pallets`
  MODIFY `pallet_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=245;

--
-- AUTO_INCREMENT for table `paymentform`
--
ALTER TABLE `paymentform`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `payment_track`
--
ALTER TABLE `payment_track`
  MODIFY `track_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `product_requests`
--
ALTER TABLE `product_requests`
  MODIFY `request_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product_type`
--
ALTER TABLE `product_type`
  MODIFY `type_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `product_type_archive`
--
ALTER TABLE `product_type_archive`
  MODIFY `delete_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `returnitems`
--
ALTER TABLE `returnitems`
  MODIFY `return_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `return_payments`
--
ALTER TABLE `return_payments`
  MODIFY `return_payment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wish_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

-- --------------------------------------------------------

--
-- Structure for view `admin_notifications_views`
--
DROP TABLE IF EXISTS `admin_notifications_views`;

CREATE VIEW `admin_notifications_views`  AS SELECT `a`.`id` AS `id`, `a`.`user_email` AS `user_email`, `a`.`status` AS `status`, `a`.`ntype` AS `ntype`, `a`.`head_msg` AS `head_msg`, `a`.`created_at` AS `created_at`, (case when ((`a`.`payment_id` is null) and (`a`.`order_id` is not null)) then (select (case `a`.`ntype` when 'ordered' then concat(`a`.`user_email`,' has placed a new order for ',convert(`o`.`order_name` using utf8mb4),'.') when 'requested' then concat(`a`.`user_email`,' has requested payment approval for ',convert(`o`.`order_name` using utf8mb4),'.') when 'confirmed' then concat('Payment confirmed for ',`a`.`user_email`,'\'s order of ',convert(`o`.`order_name` using utf8mb4),'.') when 'cancelled' then concat('Payment cancelled for ',`a`.`user_email`,'\'s order of ',convert(`o`.`order_name` using utf8mb4),'.') when 'returned' then concat('Item refund for ',`a`.`user_email`,'\'s order of ',convert(`o`.`order_name` using utf8mb4),'.') end) from `orderdetails` `o` where (`o`.`order_id` = `a`.`order_id`)) when (`a`.`ntype` in ('ordered','requested','confirmed','cancelled')) then (select (case `a`.`ntype` when 'ordered' then concat(`a`.`user_email`,' has placed a new order for ',convert(group_concat(`o`.`order_name` order by `o`.`order_name` ASC separator ', ') using utf8mb4),'.') when 'requested' then concat(`a`.`user_email`,' has requested payment approval for ',convert(group_concat(`o`.`order_name` order by `o`.`order_name` ASC separator ', ') using utf8mb4),'.') when 'confirmed' then concat('Payment confirmed for ',`a`.`user_email`,'\'s order of ',convert(group_concat(`o`.`order_name` order by `o`.`order_name` ASC separator ', ') using utf8mb4),'.') when 'cancelled' then concat('Payment cancelled for ',`a`.`user_email`,'\'s order of ',convert(group_concat(`o`.`order_name` order by `o`.`order_name` ASC separator ', ') using utf8mb4),'.') when 'returned' then concat('Item refund for ',`a`.`user_email`,'\'s order of ',convert(group_concat(`o`.`order_name` order by `o`.`order_name` ASC separator ', ') using utf8mb4),'.') end) from `orderdetails` `o` where (`o`.`payment_id` = `a`.`payment_id`) group by `a`.`payment_id`) when (`a`.`ntype` in ('returned','return request','return rejected','return deleted')) then (select (case `a`.`ntype` when 'returned' then concat(`a`.`user_email`,' has returned ',`r`.`quantity`,' ',`r`.`product_name`,'. Reason: ',`r`.`reason`) when 'return request' then concat(`a`.`user_email`,' has requested to return ',`r`.`quantity`,' ',`r`.`product_name`,'. Reason: ',`r`.`reason`) when 'return rejected' then concat('Return request for ',`r`.`quantity`,' ',`r`.`product_name`,' has been rejected') when 'return deleted' then concat('Return request for ',`r`.`quantity`,' ',`r`.`product_name`,' has been deleted') end) from `returnitems` `r` where (`r`.`return_id` = `a`.`return_id`)) end) AS `message` FROM `admin_notifications` AS `a` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD CONSTRAINT `FK_orderdetails_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_orderdetails_paymentform` FOREIGN KEY (`payment_id`) REFERENCES `paymentform` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `paymentform`
--
ALTER TABLE `paymentform`
  ADD CONSTRAINT `FK_paymentform_orderdetails` FOREIGN KEY (`order_id`) REFERENCES `orderdetails` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payment_track`
--
ALTER TABLE `payment_track`
  ADD CONSTRAINT `paymentform_track_fk` FOREIGN KEY (`payment_id`) REFERENCES `paymentform` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_type`
--
ALTER TABLE `product_type`
  ADD CONSTRAINT `brand_id` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`brand_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
