-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 09, 2024 at 05:09 PM
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
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `brand_id` int(11) NOT NULL,
  `brand_name` varchar(255) NOT NULL,
  `brand_logo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `brand_logo`) VALUES
(1, 'Davies', 'https://phoenixgroup.com.pg/wp-content/uploads/2021/06/davies-img.png'),
(2, 'Boysen', 'https://store.boysen.com.ph/images/boysenlogo2.png'),
(3, 'Rain or Shine', 'https://seeklogo.com/images/R/rain-or-shine-logo-B658167B07-seeklogo.com.png'),
(4, 'K92', 'https://baesacolorspaintcenter.com.ph/cdn/shop/collections/LznXc15m0F1XEBkW1FRyp8ciE6VIprKGnnYulMw3.png?v=1712806428');

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
  `quantity` int(255) NOT NULL,
  `gl` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `item_name`, `brand_name`, `item_image`, `item_date`, `expiration_date`, `item_price`, `type`, `quantity`, `gl`) VALUES
(33, 'Latex Paint', 'Rain or Shine', '353368.jpg', '2024-09-28 00:00:00.000000', '2024-09-26', '15', 'Latex', 90, 'Gallon'),
(34, 'Boysen Paint', 'Davies', '706878.jpg', '2024-09-28 00:00:00.000000', '2024-09-10', '200', 'Oil Paint', 138, 'Gallon'),
(35, 'Boysend', 'Boysen', '712709.png', '2024-10-08 00:00:00.000000', '2024-11-01', '200', 'Acrylic', 3, 'Gallon'),
(36, 'Latex Patin', 'Boysen', '750156.jpg', '2024-10-08 00:00:00.000000', '2024-10-22', '5000', 'Primer', 0, 'Liter'),
(37, 'Sample Paint', 'K92', '158008.png', '2024-10-08 00:00:00.000000', '2024-11-01', '200', 'Acrytex', 5, 'Liter');

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
  `order_pick_place` enum('Quezon City','Caloocan','Valenzuela','San Jose de Monte') DEFAULT NULL,
  `gl` enum('Gallon','Liter') DEFAULT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `orderdetails`
--

INSERT INTO `orderdetails` (`order_id`, `user_id`, `order_name`, `order_price`, `order_quantity`, `order_total`, `order_status`, `order_date`, `order_pick_up`, `order_pick_place`, `gl`, `payment_id`, `product_id`) VALUES
(69, 8, 'Latex Paint', 100, 10, 1000, 'Confirmed', '2024-09-28', '2024-10-04 02:52:00.000000', 'Quezon City', 'Gallon', 37, 33),
(70, 8, 'Latex Paint (Ice Cubes)', 100, 2, 200, 'Confirmed', '2024-09-27', '2024-09-27 02:53:00.000000', 'San Jose de Monte', 'Gallon', 36, 33),
(71, 8, 'Boysen Paint', 200, 50, 10000, 'Confirmed', '2024-09-28', '2024-09-26 02:55:00.000000', 'Quezon City', 'Gallon', 38, 34),
(72, 8, 'Boysen Paint', 200, 5, 1000, '0', '2024-09-28', '2024-09-28 03:00:00.000000', 'Valenzuela', 'Gallon', 39, 34),
(73, 8, 'Boysen Paint', 200, 1, 200, '0', '2024-09-29', '2024-10-02 02:03:00.000000', 'Valenzuela', 'Gallon', 39, 34),
(78, 8, 'Boysen Paint', 200, 2, 400, 'Pending', '2024-10-09', '2024-10-10 07:07:00.000000', 'Quezon City', 'Gallon', NULL, 34),
(79, 8, 'Latex Paint', 15, 1, 15, 'Pending', '2024-10-09', '2024-10-09 07:08:00.000000', 'Caloocan', 'Gallon', NULL, 33);

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
  `payment_status` varchar(20) DEFAULT 'verification'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paymentform`
--

INSERT INTO `paymentform` (`id`, `firstname`, `lastname`, `email`, `address`, `mobile`, `payment_method`, `payment_type`, `amount`, `payment_image_path`, `created_at`, `order_id`, `payment_status`) VALUES
(36, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Full Payment', 0.00, 'uploaded_images/SUN AND RAIN CORNSILK.PNG', '2024-09-27 18:53:35', 70, 'verification'),
(37, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Walk In', 'Down Payment', 50.00, 'uploaded_images/SUN AND RAIN ROSE WHITE.PNG', '2024-09-27 18:54:28', 69, 'verification'),
(38, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Walk In', 'Full Payment', 0.00, 'uploaded_images/SUN AND RAIN CORNSILK.PNG', '2024-09-27 18:57:00', 71, 'verification'),
(39, 'Kate', 'Ruaza', 'kate@email.com', 'myaddress', '093473455', 'Gcash', 'Full Payment', 1200.00, './uploaded_images/SUN AND RAIN MILKY WAY.PNG', '2024-09-30 05:42:49', NULL, 'verification');

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
  `status` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returnitems`
--

INSERT INTO `returnitems` (`return_id`, `user_id`, `reason`, `quantity`, `product_image`, `receipt_image`, `product_name`, `status`) VALUES
(3, 8, 'Incorrect Item', 5, 'returnItems/BAGUIO GREEN.png', 'returnItems/CLEAR GLOSS EMULSION.PNG', 'Latex Paint', 'Pending');

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
  `type` enum('Admin','Customer','Cashier') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_email`, `user_password`, `user_firstname`, `user_lastname`, `user_address`, `user_mobile`, `type`) VALUES
(6, 'admin@email.com', 'admin', 'admin', 'r', 'r', '09123456778', 'Admin'),
(8, 'kate@email.com', 'kate', 'Kate', 'Ruaza', 'myaddress', '093473455', 'Customer'),
(17, 'cashier@gmail.com', 'cash', 'cashier firstname', 'cashier lastname', 'cashier address', '091238141', 'Cashier');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

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
-- Indexes for table `returnitems`
--
ALTER TABLE `returnitems`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cartitems`
--
ALTER TABLE `cartitems`
  MODIFY `itemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `orderdetails`
--
ALTER TABLE `orderdetails`
  MODIFY `order_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `pallets`
--
ALTER TABLE `pallets`
  MODIFY `pallet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=244;

--
-- AUTO_INCREMENT for table `paymentform`
--
ALTER TABLE `paymentform`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `returnitems`
--
ALTER TABLE `returnitems`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

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
-- Constraints for table `returnitems`
--
ALTER TABLE `returnitems`
  ADD CONSTRAINT `returnitems_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
