-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 05, 2024 at 05:54 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
CREATE DEFINER=`root`@`localhost` PROCEDURE `confirm_payment` (IN `p_track_id` INT)   BEGIN
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
            payment_status = IF(months_paid + 1 >= 12, 'Comfirmed', payment_status)
        WHERE id = v_payment_id;
    ELSE -- Down payment
        UPDATE paymentform
        SET amount = amount + v_amount,
            payment_status = IF(amount + v_amount >= v_total_amount, 'Comfirmed', payment_status)
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ProcessReturnItems` (IN `p_return_id` INT)   BEGIN
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
    
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `request_payment` (IN `p_payment_id` INT, IN `p_amount` DECIMAL(10,2), IN `p_payment_image` VARCHAR(255))   BEGIN
    DECLARE last_track_status VARCHAR(20);
    DECLARE v_payment_type VARCHAR(255);
    
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
    
    -- Check if we can process new payment (no track or last track was confirmed)
    IF (last_track_status IS NULL OR last_track_status = 'Confirmed') THEN
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
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(10) UNSIGNED NOT NULL,
  `admin_username` varchar(500) NOT NULL DEFAULT '',
  `admin_password` varchar(500) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_username`, `admin_password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `head_msg` varchar(255) NOT NULL,
  `ntype` enum('ordered','returned','confirmed','requested','cancelled','return request','return rejected','return deleted') NOT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `return_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `status` enum('read','unread') NOT NULL DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_notifications`
--

INSERT INTO `admin_notifications` (`id`, `user_email`, `head_msg`, `ntype`, `payment_id`, `return_id`, `order_id`, `status`, `created_at`) VALUES
(2, 'cashier@gmail.com', 'New Order Placed', 'ordered', 68, NULL, NULL, 'read', '2024-12-02 07:39:09'),
(3, 'kate@email.com', 'New Order Placed', 'ordered', 69, NULL, NULL, 'unread', '2024-12-05 15:13:19'),
(4, 'kate@email.com', 'New Order Placed', 'ordered', 70, NULL, NULL, 'unread', '2024-12-05 15:42:53'),
(5, 'kate@email.com', 'New Order Placed', 'ordered', 71, NULL, NULL, 'unread', '2024-12-05 15:43:03'),
(6, 'kate@email.com', 'New Order Placed', 'ordered', 72, NULL, NULL, 'unread', '2024-12-05 15:43:15');

-- --------------------------------------------------------

--
-- Stand-in structure for view `admin_notifications_views`
-- (See below for the actual view)
--
CREATE TABLE `admin_notifications_views` (
`id` int(11)
,`user_email` varchar(255)
,`status` enum('read','unread')
,`ntype` enum('ordered','returned','confirmed','requested','cancelled','return request','return rejected','return deleted')
,`head_msg` varchar(255)
,`created_at` timestamp
,`message` mediumtext
);

-- --------------------------------------------------------

--
-- Table structure for table `branch`
--

CREATE TABLE `branch` (
  `branch_id` int(11) NOT NULL,
  `branch_location` text DEFAULT NULL
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
  `brand_id` int(11) NOT NULL,
  `brand_name` varchar(255) NOT NULL,
  `brand_img` text DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `brand_img`, `branch_id`) VALUES
(2, 'Boysen', 'local_image/1730109970_boysen.jpg', NULL),
(9, 'Rain or Shine', 'local_image/1730110516_rain-or-shine.png', NULL),
(10, 'Ecomax', 'local_image/1730110526_ecomax.jpeg', NULL),
(12, 'Hippo', 'local_image/1730651776_hippo.png', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cartitems`
--

CREATE TABLE `cartitems` (
  `itemID` int(11) NOT NULL,
  `palletName` varchar(255) NOT NULL,
  `palletCode` varchar(255) NOT NULL,
  `palletRGB` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(10) UNSIGNED NOT NULL,
  `item_name` varchar(5000) NOT NULL DEFAULT '',
  `brand_name` varchar(255) NOT NULL,
  `item_image` varchar(5000) NOT NULL DEFAULT '',
  `item_date` datetime(6) NOT NULL,
  `expiration_date` varchar(255) DEFAULT NULL,
  `item_price` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `gl` varchar(255) NOT NULL,
  `pallet_id` int(11) DEFAULT NULL,
  `branch` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `item_name`, `brand_name`, `item_image`, `item_date`, `expiration_date`, `item_price`, `type`, `quantity`, `gl`, `pallet_id`, `branch`) VALUES
(46, 'Boysen Paint', 'Boysen', '387590.jpeg', '2024-10-30 00:00:00.000000', '2024-11-02', '100', 'Aluminum Paint', 18.00, 'Gallon', 9, NULL),
(47, 'Boysen Gloss', 'Boysen', '777138.jpg', '2024-11-01 00:00:00.000000', '2024-11-30', '100', 'Gloss', 6.00, 'Gallon', 3, NULL),
(48, 'Boysen Paint', 'Boysen', '283333.jpeg', '2024-11-01 00:00:00.000000', '2024-12-07', '200', 'Gloss', 90.00, 'Gallon', 12, NULL),
(49, 'Boysen Paint', 'Boysen', '205967.jpeg', '2024-11-01 00:00:00.000000', '2024-12-07', '120', 'Flat Paint', 85.00, 'Gallon', 17, NULL),
(50, 'Boysen Paint', 'Boysen', '80066.jpeg', '2024-11-04 00:00:00.000000', '2026-11-03', '120', 'Flat Paint', 95.00, 'Gallon', 16, NULL),
(51, 'Latex Paint', 'Boysen', '512213.jpeg', '2024-11-07 00:00:00.000000', '2026-11-20', '100', 'Latex Paint', 81.00, 'Gallon', 1, NULL),
(53, 'Latex Paint', 'Ecomax', '95392.jpg', '2024-12-05 00:00:00.000000', '2026-12-16', '200', 'Latex Paint', 497.00, 'Gallon', 10, NULL),
(54, 'Latex Patin', 'Ecomax', '659045.jpg', '2024-12-05 00:00:00.000000', '2026-12-17', '200', 'Gloss', 230.00, 'Gallon', 16, 'Caloocan'),
(55, 'Boysen Paint', 'Boysen', '553060.jpg', '2024-12-05 00:00:00.000000', '2026-12-24', '200', 'Semi Gloss Paint', 123.00, 'Gallon', 17, 'Quezon City'),
(57, 'Paint Brush', 'Hippo', '756276.jpg', '2024-12-05 00:00:00.000000', NULL, '100', 'Paint Brush', 100.00, '', NULL, 'Quezon City');

-- --------------------------------------------------------

--
-- Table structure for table `orderdetails`
--

CREATE TABLE `orderdetails` (
  `order_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `order_name` varchar(1000) NOT NULL DEFAULT '',
  `order_price` double NOT NULL DEFAULT 0,
  `order_quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `order_total` double NOT NULL DEFAULT 0,
  `order_status` varchar(45) NOT NULL DEFAULT '',
  `order_date` date DEFAULT NULL,
  `order_pick_up` datetime(6) DEFAULT NULL,
  `order_pick_place` enum('Caloocan','San Jose Del Monte, Bulacan','Quezon City','Valenzuela City') DEFAULT NULL,
  `gl` enum('Gallon','Liter') DEFAULT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `orderdetails`
--

INSERT INTO `orderdetails` (`order_id`, `user_id`, `order_name`, `order_price`, `order_quantity`, `order_total`, `order_status`, `order_date`, `order_pick_up`, `order_pick_place`, `gl`, `payment_id`, `product_id`) VALUES
(108, 8, 'Boysen Paint', 100, 1, 100, 'Returned', '2024-11-04', '2024-11-04 19:06:00.000000', 'Caloocan', 'Gallon', 53, 46),
(109, 8, 'Boysen Gloss', 100, 1, 100, 'Rejected', '2024-11-04', '2024-11-04 19:28:00.000000', 'Caloocan', 'Gallon', 54, 47),
(110, 8, 'Boysen Paint', 120, 1, 120, 'Rejected', '2024-11-04', '2024-11-04 19:28:00.000000', 'Caloocan', 'Gallon', 54, 50),
(111, 8, 'Boysen Paint', 120, 1, 120, 'Rejected', '2024-11-04', '2024-11-04 19:28:00.000000', 'Caloocan', 'Gallon', 54, 49),
(120, 8, 'Boysen Paint', 120, 1, 120, 'Confirmed', '2024-11-08', '2024-11-09 07:32:00.000000', 'Caloocan', 'Gallon', 57, 50),
(121, 8, 'Boysen Paint', 120, 1, 120, 'Confirmed', '2024-11-08', '2024-11-09 07:33:00.000000', 'Caloocan', 'Gallon', 58, 49),
(122, 8, 'Boysen Gloss', 100, 1, 100, 'Returned', '2024-11-08', '2024-11-09 13:06:00.000000', 'Caloocan', 'Gallon', 60, 47),
(123, 8, 'Latex Paint', 100, 1, 100, 'Returned', '2024-11-08', '2024-11-09 13:06:00.000000', 'Caloocan', 'Gallon', 61, 51),
(124, 8, 'Boysen Gloss', 100, 1, 100, 'Returned', '2024-11-09', '2024-11-10 05:19:00.000000', 'Caloocan', 'Gallon', 59, 47),
(125, 8, 'Boysen Paint', 120, 13, 1560, 'Returned', '2024-11-14', '2024-11-15 01:32:00.000000', 'Caloocan', 'Gallon', 62, 49),
(126, 8, 'Latex Paint', 100, 15, 1500, 'Returned', '2024-11-14', '2024-11-15 01:33:00.000000', 'Caloocan', 'Gallon', 63, 51),
(127, 8, 'Boysen Paint', 120, 3, 360, 'Returned', '2024-11-14', '2024-11-15 01:33:00.000000', 'Caloocan', 'Gallon', 64, 50),
(134, 8, 'Boysen Gloss', 100, 1, 100, 'Verification', '2024-11-18', '2024-11-18 16:38:00.000000', 'Caloocan', 'Gallon', 66, 47),
(135, 8, 'Boysen Paint', 200, 1, 200, 'Verification', '2024-11-18', '2024-11-18 16:38:00.000000', 'Caloocan', 'Gallon', 66, 48),
(138, 17, 'Boysen Gloss', 100, 1, 100, 'Confirmed', '2024-11-17', '2024-11-17 17:55:50.000000', 'Caloocan', NULL, 67, 47),
(139, 17, 'Boysen Paint', 200, 1, 200, 'Confirmed', '2024-11-17', '2024-11-17 17:55:50.000000', 'Caloocan', NULL, 67, 48),
(141, 17, 'Boysen Paint', 200, 1, 200, 'Confirmed', '2024-12-02', '2024-12-02 08:39:09.000000', 'Caloocan', NULL, 68, 48),
(149, 8, 'Boysen Paint', 200, 1, 200, 'verification', '2024-12-05', '2024-12-06 15:41:00.000000', 'Valenzuela City', 'Gallon', 70, 48),
(150, 8, 'Latex Patin', 200, 1, 200, 'verification', '2024-12-05', '2024-12-06 15:41:00.000000', 'Quezon City', 'Gallon', 71, 54),
(151, 8, 'Latex Patin', 200, 1, 200, 'verification', '2024-12-05', '2024-12-06 15:41:00.000000', 'San Jose Del Monte, Bulacan', 'Gallon', 72, 54);

--
-- Triggers `orderdetails`
--
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
  `pallet_id` int(11) NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `rgb` varchar(255) NOT NULL
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
  `id` int(11) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `mobile` varchar(255) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `payment_type` varchar(255) NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_image_path` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_id` int(10) UNSIGNED DEFAULT NULL,
  `payment_status` varchar(20) DEFAULT 'verification',
  `months_paid` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paymentform`
--

INSERT INTO `paymentform` (`id`, `firstname`, `lastname`, `email`, `address`, `mobile`, `payment_method`, `payment_type`, `amount`, `payment_image_path`, `created_at`, `order_id`, `payment_status`, `months_paid`) VALUES
(53, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Down Payment', 50.00, './uploaded_images/person-1.jpg', '2024-11-03 19:17:09', NULL, 'Confirmed', 0),
(54, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Full Payment', 340.00, './uploaded_images/person-4.jpg', '2024-11-03 19:29:17', NULL, 'failed', 0),
(56, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Full Payment', 900.00, './uploaded_images/THALO GREEN acrylic.jpeg', '2024-11-08 07:42:56', NULL, 'failed', 0),
(57, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Down Payment', 60.00, './uploaded_images/TOULIDINE RED.jpeg', '2024-11-08 07:43:15', NULL, 'Confirmed', 0),
(58, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Full Payment', 120.00, './uploaded_images/BURNT UMBER.jpeg', '2024-11-08 07:43:34', NULL, 'Confirmed', 0),
(59, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Full Payment', 100.00, './uploaded_images/BURNT UMBER.jpeg', '2024-11-09 05:20:23', NULL, 'Returned', 0),
(60, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Full Payment', 100.00, './uploaded_images/MAHOGANY BROWN.jpeg', '2024-11-09 05:38:41', NULL, 'Returned', 0),
(61, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Full Payment', 100.00, './uploaded_images/ORIENT GOLD.jpeg', '2024-11-09 05:39:01', NULL, 'Returned', 0),
(62, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Full Payment', 1560.00, './uploaded_images/BOYSEN FLAT LATEX.jpg', '2024-11-14 01:33:54', NULL, 'Returned', 0),
(63, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Full Payment', 1500.00, './uploaded_images/Black.jpg', '2024-11-14 01:34:11', NULL, 'Returned', 0),
(64, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Full Payment', 360.00, './uploaded_images/Brands.png', '2024-11-14 01:34:24', NULL, 'Returned', 0),
(66, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Full Payment', 300.00, './uploaded_images/LAMP BLACK.jpeg', '2024-11-17 16:38:27', NULL, 'verification', 0),
(67, 'cashier firstname', 'cashier lastname', 'cashier@gmail.com', 'cashier address', '091238141', 'Walk In', 'Full Payment', 300.00, '', '2024-11-17 16:55:50', NULL, 'Confirmed', 0),
(68, 'cashier firstname', 'cashier lastname', 'cashier@gmail.com', 'cashier address', '091238141', 'Walk In', 'Full Payment', 200.00, '', '2024-12-02 07:39:09', NULL, 'Confirmed', 0),
(70, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Full Payment', 200.00, './uploaded_images/55584.jpg', '2024-12-05 15:42:53', NULL, 'verification', 0),
(71, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Full Payment', 200.00, './uploaded_images/55584.jpg', '2024-12-05 15:43:03', NULL, 'verification', 0),
(72, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Full Payment', 200.00, './uploaded_images/54808.jpeg', '2024-12-05 15:43:15', NULL, 'verification', 0);

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
            'Payment Confirmed'  -- Fixed typo
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
  `track_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'Requested',
  `amount` decimal(10,2) NOT NULL,
  `date_tracked` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `request_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_brand` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `requesting_branch` varchar(255) NOT NULL,
  `status` enum('Pending','Declined','Confirmed') DEFAULT NULL,
  `requested_at` datetime DEFAULT current_timestamp(),
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
    -- Check if the status is being changed to 'Confirmed' or 'Declined'
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
  `type_id` int(11) NOT NULL,
  `type_name` text NOT NULL,
  `brand_id` int(11) NOT NULL,
  `prod_type` text NOT NULL
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

-- --------------------------------------------------------

--
-- Table structure for table `returnitems`
--

CREATE TABLE `returnitems` (
  `return_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `receipt_image` varchar(255) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `branch` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returnitems`
--

INSERT INTO `returnitems` (`return_id`, `user_id`, `reason`, `quantity`, `product_image`, `receipt_image`, `product_name`, `status`, `branch`) VALUES
(4, 8, 'Incorrect Item', 2, 'returnItems/MAHOGANY BROWN.jpeg', 'returnItems/ORIENT GOLD.jpeg', 'Boysen Paint', 'Confirmed', NULL);

--
-- Triggers `returnitems`
--
DELIMITER $$
CREATE TRIGGER `trigger_delete_return_request` BEFORE DELETE ON `returnitems` FOR EACH ROW BEGIN
    DECLARE user_email VARCHAR(255);

    SELECT email INTO user_email
    FROM users 
    WHERE id = OLD.user_id;  -- Fixed NEW to OLD since this is a DELETE trigger
    
    INSERT INTO admin_notifications (return_id, ntype, user_email, head_msg)
    VALUES (
        OLD.return_id,
        'return deleted', 
        user_email, 
        'Return Request Deleted'
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trigger_insert_return_request` AFTER INSERT ON `returnitems` FOR EACH ROW BEGIN
    DECLARE user_email VARCHAR(255);
    
    SELECT email INTO user_email
    FROM users 
    WHERE id = NEW.user_id;
    
    INSERT INTO admin_notifications (return_id, ntype, user_email, head_msg)
    VALUES (
        NEW.return_id,
        'return request', 
        user_email, 
        'New Return Request'  -- Updated message
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trigger_update_return_request` AFTER UPDATE ON `returnitems` FOR EACH ROW BEGIN
    DECLARE user_email VARCHAR(255);

    SELECT email INTO user_email
    FROM users 
    WHERE id = NEW.user_id;
    
    IF NEW.status = 'Confirmed' AND OLD.status != 'Confirmed' THEN 
        INSERT INTO admin_notifications (return_id, ntype, user_email, head_msg)
        VALUES (
            NEW.return_id,
            'returned', 
            user_email, 
            'Return Confirmed'
        );
    ELSEIF NEW.status = 'Rejected' AND OLD.status != 'Rejected' THEN 
        INSERT INTO admin_notifications (return_id, ntype, user_email, head_msg)
        VALUES (
            NEW.return_id,
            'return rejected', 
            user_email, 
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
  `return_payment_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `return_status` text DEFAULT NULL,
  `proof_of_payment` text DEFAULT NULL,
  `amount_return` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `return_payments`
--

INSERT INTO `return_payments` (`return_payment_id`, `user_id`, `return_status`, `proof_of_payment`, `amount_return`, `quantity`, `date`) VALUES
(8, 8, 'Returned', 'refund_1731548110_673553ce11218_Black.jpg', 100, 1, '2024-11-14 09:35:10'),
(9, 8, 'Returned', 'refund_1731548152_673553f81bcba_CHOCOLATE BROWN.jpeg', 1560, 13, '2024-11-14 09:35:52'),
(10, 8, 'Returned', 'refund_1731554094_67356b2ea417a_GLOSS.jpg', 1500, 15, '2024-11-14 11:14:54'),
(11, 8, 'Returned', 'refund_1731554128_67356b501effa_ORIENT GOLD.jpeg', 360, 3, '2024-11-14 11:15:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_email` varchar(1000) NOT NULL,
  `user_password` varchar(1000) NOT NULL,
  `user_firstname` varchar(1000) NOT NULL,
  `user_lastname` varchar(1000) NOT NULL,
  `user_address` varchar(1000) NOT NULL,
  `user_mobile` varchar(255) NOT NULL,
  `type` enum('Admin','Customer','Cashier') DEFAULT NULL,
  `assigned_branch` enum('Caloocan','San Jose Del Monte, Bulacan','Quezon City','Valenzuela City') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_email`, `user_password`, `user_firstname`, `user_lastname`, `user_address`, `user_mobile`, `type`, `assigned_branch`) VALUES
(6, 'admin@email.com', 'admin', 'admin', 'administrator', 'Ilocos Norte', '09123456778', 'Admin', 'Caloocan'),
(8, 'kate@email.com', 'kate', 'Kate', 'Ruaza', 'myaddress', '093473455', 'Customer', NULL),
(17, 'cashier@gmail.com', 'cash', 'cashier firstname', 'cashier lastname', 'cashier address', '091238141', 'Cashier', 'Caloocan'),
(25, 'bulancanadmin@email.com', 'admin', 'bulacan', 'admin', 'bulacan', '90193192738', 'Admin', 'San Jose Del Monte, Bulacan'),
(26, 'qcadmin@email.com', 'admin', 'quezon city', 'admin', 'Quezon City', '09123619823', 'Admin', 'Quezon City'),
(27, 'valenzuelaadmin@email.com', 'admin', 'valenzuela', 'admin', 'Valenzuela City', '0923724971', 'Admin', 'Valenzuela City'),
(28, 'bulacancashier@email.com', 'cash', 'bulacan', 'cashier', 'Bulacan', '029187492131', 'Cashier', 'San Jose Del Monte, Bulacan'),
(29, 'qccashier@email.com', 'admin', 'quezon city', 'cashier', 'Quezon City', '092391823', 'Cashier', 'Quezon City'),
(30, 'valenzuelacashier@email.com', 'cash', 'valenzuela', 'cashier', 'Valenzuela City', '0923917941', 'Cashier', 'Valenzuela City');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wish_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`wish_id`, `user_id`, `item_id`) VALUES
(6, 0, 47);

-- --------------------------------------------------------

--
-- Structure for view `admin_notifications_views`
--
DROP TABLE IF EXISTS `admin_notifications_views`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `admin_notifications_views`  AS SELECT `a`.`id` AS `id`, `a`.`user_email` AS `user_email`, `a`.`status` AS `status`, `a`.`ntype` AS `ntype`, `a`.`head_msg` AS `head_msg`, `a`.`created_at` AS `created_at`, CASE WHEN `a`.`payment_id` is null AND `a`.`order_id` is not null THEN (select case `a`.`ntype` when 'ordered' then concat(`a`.`user_email`,' has placed a new order for ',`o`.`order_name`,'.') when 'requested' then concat(`a`.`user_email`,' has requested payment approval for ',`o`.`order_name`,'.') when 'confirmed' then concat('Payment confirmed for ',`a`.`user_email`,'\'s order of ',`o`.`order_name`,'.') when 'cancelled' then concat('Payment cancelled for ',`a`.`user_email`,'\'s order of ',`o`.`order_name`,'.') when 'returned' then concat('Item refund for ',`a`.`user_email`,'\'s order of ',`o`.`order_name`,'.') end from `orderdetails` `o` where `o`.`order_id` = `a`.`order_id`) WHEN `a`.`ntype` in ('ordered','requested','confirmed','cancelled') THEN (select case `a`.`ntype` when 'ordered' then concat(`a`.`user_email`,' has placed a new order for ',group_concat(`o`.`order_name` order by `o`.`order_name` ASC separator ', '),'.') when 'requested' then concat(`a`.`user_email`,' has requested payment approval for ',group_concat(`o`.`order_name` order by `o`.`order_name` ASC separator ', '),'.') when 'confirmed' then concat('Payment confirmed for ',`a`.`user_email`,'\'s order of ',group_concat(`o`.`order_name` order by `o`.`order_name` ASC separator ', '),'.') when 'cancelled' then concat('Payment cancelled for ',`a`.`user_email`,'\'s order of ',group_concat(`o`.`order_name` order by `o`.`order_name` ASC separator ', '),'.') when 'returned' then concat('Item refund for ',`a`.`user_email`,'\'s order of ',group_concat(`o`.`order_name` order by `o`.`order_name` ASC separator ', '),'.') end from `orderdetails` `o` where `o`.`payment_id` = `a`.`payment_id` group by `a`.`payment_id`) WHEN `a`.`ntype` in ('returned','return request','return rejected','return deleted') THEN (select case `a`.`ntype` when 'returned' then concat(`a`.`user_email`,' has returned ',`r`.`quantity`,' ',`r`.`product_name`,'. Reason: ',`r`.`reason`) when 'return request' then concat(`a`.`user_email`,' has requested to return ',`r`.`quantity`,' ',`r`.`product_name`,'. Reason: ',`r`.`reason`) when 'return rejected' then concat('Return request for ',`r`.`quantity`,' ',`r`.`product_name`,' has been rejected') when 'return deleted' then concat('Return request for ',`r`.`quantity`,' ',`r`.`product_name`,' has been deleted') end from `returnitems` `r` where `r`.`return_id` = `a`.`return_id`) END AS `message` FROM `admin_notifications` AS `a` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

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
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `branch`
--
ALTER TABLE `branch`
  MODIFY `branch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `cartitems`
--
ALTER TABLE `cartitems`
  MODIFY `itemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `orderdetails`
--
ALTER TABLE `orderdetails`
  MODIFY `order_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;

--
-- AUTO_INCREMENT for table `pallets`
--
ALTER TABLE `pallets`
  MODIFY `pallet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=245;

--
-- AUTO_INCREMENT for table `paymentform`
--
ALTER TABLE `paymentform`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `payment_track`
--
ALTER TABLE `payment_track`
  MODIFY `track_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_requests`
--
ALTER TABLE `product_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product_type`
--
ALTER TABLE `product_type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `returnitems`
--
ALTER TABLE `returnitems`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `return_payments`
--
ALTER TABLE `return_payments`
  MODIFY `return_payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wish_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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

ALTER TABLE `product_type`
  ADD CONSTRAINT `brand_id` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`brand_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;





-- Create the 'brands_archive' table to store archived brands when they are deleted
CREATE TABLE `brands_archive` (
  `delete_id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `brand_id` INT NOT NULL,
  `brand_name` VARCHAR(255) NOT NULL,
  `brand_img` TEXT DEFAULT NULL,
  `branch_id` INT DEFAULT NULL,
  `deleted_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create the 'product_type_archive' table to store archived product types when they are deleted
CREATE TABLE `product_type_archive` (
  `delete_id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `deleted_brand_id` INT,
  `type_id` INT(11) NOT NULL,
  `type_name` TEXT NOT NULL,
  `brand_id` INT(11) NOT NULL,
  `prod_type` TEXT NOT NULL,
  `deleted_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Trigger to archive brand data before deleting a brand
DELIMITER $$

CREATE TRIGGER before_delete_brand
BEFORE DELETE ON `brands`
FOR EACH ROW
BEGIN
    -- Archive the brand data
    INSERT INTO `brands_archive` (`brand_id`, `brand_name`, `brand_img`, `branch_id`)
    VALUES (OLD.brand_id, OLD.brand_name, OLD.brand_img, OLD.branch_id);
    
    -- Archive related product types
    INSERT INTO `product_type_archive` (`deleted_brand_id`, `type_id`, `type_name`, `brand_id`, `prod_type`)
    SELECT OLD.brand_id, pt.`type_id`, pt.`type_name`, pt.`brand_id`, pt.`prod_type`
    FROM `product_type` pt
    WHERE pt.`brand_id` = OLD.brand_id;
END $$

DELIMITER ;

-- Trigger to archive product data before deleting a product type
DELIMITER $$

CREATE TRIGGER before_delete_product_type
BEFORE DELETE ON `product_type`
FOR EACH ROW
BEGIN
    -- Archive the product type data
    INSERT INTO `product_type_archive` (`deleted_brand_id`, `type_id`, `type_name`, `brand_id`, `prod_type`)
    VALUES (NULL, OLD.type_id, OLD.type_name, OLD.brand_id, OLD.prod_type);
END $$

DELIMITER ;


-- Procedure to restore a deleted brand from the archive
DELIMITER $$

CREATE PROCEDURE restore_brand(IN p_delete_id INT)
BEGIN
    DECLARE v_brand_id INT;
    DECLARE v_brand_name VARCHAR(255);
    DECLARE v_brand_img TEXT;
    DECLARE v_branch_id INT;

    -- Retrieve archived brand data
    SELECT `brand_id`, `brand_name`, `brand_img`, `branch_id`
    INTO v_brand_id, v_brand_name, v_brand_img, v_branch_id
    FROM `brands_archive`
    WHERE `delete_id` = p_delete_id;

    -- Insert the brand back into the original table
    INSERT INTO `brands` (`brand_id`, `brand_name`, `brand_img`, `branch_id`)
    VALUES (v_brand_id, v_brand_name, v_brand_img, v_branch_id);

    -- Delete the brand from the archive after restoration
    DELETE FROM `brands_archive`
    WHERE `delete_id` = p_delete_id;
END $$

DELIMITER ;


-- Procedure to restore a deleted brand from the archive
DELIMITER $$

CREATE PROCEDURE restore_brand_including_product_type(IN p_delete_id INT)
BEGIN
    DECLARE v_brand_id INT;
    DECLARE v_brand_name VARCHAR(255);
    DECLARE v_brand_img TEXT;
    DECLARE v_branch_id INT;

    -- Retrieve archived brand data
    SELECT `brand_id`, `brand_name`, `brand_img`, `branch_id`
    INTO v_brand_id, v_brand_name, v_brand_img, v_branch_id
    FROM `brands_archive`
    WHERE `delete_id` = p_delete_id;

    -- Insert the brand back into the original table
    INSERT INTO `brands` (`brand_id`, `brand_name`, `brand_img`, `branch_id`)
    VALUES (v_brand_id, v_brand_name, v_brand_img, v_branch_id);

    -- Insert the associated product types back into the original table
    INSERT INTO `prod_type` (`type_id`, `type_name`, `brand_id`, `prod_type`)
    SELECT pa.`type_id`, pa.`type_name`, pa.`brand_id`, pa.`prod_type`
    FROM `product_type_archive` pa
    WHERE pa.`deleted_brand_id` = v_brand_id;

    -- Delete the brand from the archive after restoration
    DELETE FROM `brands_archive`
    WHERE `delete_id` = p_delete_id;

    -- Delete the product types from the archive after restoration
    DELETE FROM `product_type_archive`
    WHERE `deleted_brand_id` = v_brand_id;
END $$

DELIMITER ;


-- Procedure to restore a deleted product from the archive
DELIMITER $$

CREATE PROCEDURE restore_product_type(IN p_delete_id INT)
BEGIN
    DECLARE v_deleted_brand_id INT;
    DECLARE v_type_id INT;
    DECLARE v_type_name TEXT;
    DECLARE v_brand_id INT;
    DECLARE v_prod_type TEXT;

    -- Retrieve archived product data
    SELECT `deleted_brand_id`, `type_id`, `type_name`, `brand_id`, `prod_type`
    INTO v_deleted_brand_id, v_type_id, v_type_name, v_brand_id, v_prod_type
    FROM `product_type_archive`
    WHERE `delete_id` = p_delete_id;

    -- Insert the product back into the original table
    INSERT INTO `product_type` (`type_id`, `type_name`, `brand_id`, `prod_type`)
    VALUES (v_type_id, v_type_name, v_brand_id, v_prod_type);

    -- Delete the product type from the archive after restoration
    DELETE FROM `product_type_archive`
    WHERE `delete_id` = p_delete_id;
END $$

DELIMITER ;


DELIMITER $$

CREATE PROCEDURE restore_product_with_brand(IN p_delete_id INT)
BEGIN
    DECLARE v_brand_id INT;
    DECLARE v_brand_name VARCHAR(255);
    DECLARE v_brand_img TEXT;
    DECLARE v_branch_id INT;
    DECLARE v_type_id INT;
    DECLARE v_type_name TEXT;
    DECLARE v_prod_type TEXT;

    -- Retrieve archived product type data
    SELECT `type_id`, `type_name`, `prod_type`, `deleted_brand_id`
    INTO v_type_id, v_type_name, v_prod_type, v_brand_id
    FROM `product_type_archive`
    WHERE `delete_id` = p_delete_id;

    -- Check if the brand exists in the brands_archive, and if not, insert it back
    IF v_brand_id IS NOT NULL THEN
        -- Retrieve archived brand data
        SELECT `brand_name`, `brand_img`, `branch_id`
        INTO v_brand_name, v_brand_img, v_branch_id
        FROM `brands_archive`
        WHERE `brand_id` = v_brand_id;

        -- Insert the brand back into the original table if not already restored
        INSERT INTO `brands` (`brand_id`, `brand_name`, `brand_img`, `branch_id`)
        VALUES (v_brand_id, v_brand_name, v_brand_img, v_branch_id);

        -- Delete the brand from the archive after restoration
        DELETE FROM `brands_archive`
        WHERE `brand_id` = v_brand_id;
    END IF;

    -- Insert the associated product type back into the original table
    INSERT INTO `product_type` (`type_id`, `type_name`, `brand_id`, `prod_type`)
    VALUES (v_type_id, v_type_name, v_brand_id, v_prod_type);

    -- Delete the product type from the archive after restoration
    DELETE FROM `product_type_archive`
    WHERE `delete_id` = p_delete_id;

END $$

DELIMITER ;
