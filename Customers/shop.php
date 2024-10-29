<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 0);
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $date = date('Y-m-d H:i:s');
    $message = "($date) Error: [$errno] $errstr - $errfile:$errline" . PHP_EOL;
    error_log($message, 3, '../error.log');
}

set_error_handler("customErrorHandler");

if (!$_SESSION['user_email']) {
    echo '<script type="text/javascript">
            Swal.fire({
                icon: "warning",
                title: "Unauthorized Access",
                text: "You need to log in to access this page.",
                confirmButtonText: "OK"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "../index.php";
                }
            });
          </script>';
    exit();
}

include("config.php");
extract($_SESSION);
$stmt_edit = $DB_con->prepare('SELECT * FROM users WHERE user_email =:user_email');
$stmt_edit->execute(array(':user_email' => $user_email));
$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
extract($edit_row);

$stmt_edit = $DB_con->prepare("select sum(order_total) as total from orderdetails where user_id=:user_id and order_status='Ordered'");
$stmt_edit->execute(array(':user_id' => $user_id));
$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
extract($edit_row);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CML Paint Trading</title>
    <link rel="shortcut icon" href="../assets/img/logo.png" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="css/local.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript" src="jquery.fancybox.js?v=2.1.5"></script>
    <link rel="stylesheet" type="text/css" href="jquery.fancybox.css?v=2.1.5" media="screen" />
    <link rel="stylesheet" type="text/css" href="jquery.fancybox-buttons.css?v=1.0.5" />
    <script type="text/javascript" src="jquery.fancybox-buttons.js?v=1.0.5"></script>
    <link rel="stylesheet" type="text/css" href="jquery.fancybox-thumbs.css?v=1.0.7" />
    <script type="text/javascript" src="jquery.fancybox-thumbs.js?v=1.0.7"></script>
    <script type="text/javascript" src="jquery.fancybox-media.js?v=1.0.6"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.fancybox').fancybox();
            $(".fancybox-effects-a").fancybox({
                helpers: {
                    title: {
                        type: 'outside'
                    },
                    overlay: {
                        speedOut: 0
                    }
                }
            });
            $(".fancybox-effects-b").fancybox({
                openEffect: 'none',
                closeEffect: 'none',
                helpers: {
                    title: {
                        type: 'over'
                    }
                }
            });
            $(".fancybox-effects-c").fancybox({
                wrapCSS: 'fancybox-custom',
                closeClick: true,
                openEffect: 'none',
                helpers: {
                    title: {
                        type: 'inside'
                    },
                    overlay: {
                        css: {
                            'background': 'rgba(238,238,238,0.85)'
                        }
                    }
                }
            });
            $(".fancybox-effects-d").fancybox({
                padding: 0,
                openEffect: 'elastic',
                openSpeed: 150,
                closeEffect: 'elastic',
                closeSpeed: 150,
                closeClick: true,
            });
            $('.fancybox-buttons').fancybox({
                openEffect: 'none',
                closeEffect: 'none',
                prevEffect: 'none',
                nextEffect: 'none',
                closeBtn: false,
                helpers: {
                    title: {
                        type: 'inside'
                    },
                    buttons: {}
                },
                afterLoad: function() {
                    this.title = 'Image ' + (this.index + 1) + ' of ' + this.group.length + (this.title ? ' - ' + this.title : '');
                }
            });
            $('.fancybox-thumbs').fancybox({
                prevEffect: 'none',
                nextEffect: 'none',
                closeBtn: false,
                arrows: false,
                nextClick: true,
                helpers: {
                    thumbs: {
                        width: 50,
                        height: 50
                    }
                }
            });
            $('.fancybox-media')
                .attr('rel', 'media-gallery')
                .fancybox({
                    openEffect: 'none',
                    closeEffect: 'none',
                    prevEffect: 'none',
                    nextEffect: 'none',
                    arrows: false,
                    helpers: {
                        media: {},
                        buttons: {}
                    }
                });
            $("#fancybox-manual-a").click(function() {
                $.fancybox.open('1_b.jpg');
            });
            $("#fancybox-manual-b").click(function() {
                $.fancybox.open({
                    href: 'iframe.html',
                    type: 'iframe',
                    padding: 5
                });
            });
            $("#fancybox-manual-c").click(function() {
                $.fancybox.open([{
                    href: '1_b.jpg',
                    title: 'My title'
                }, {
                    href: '2_b.jpg',
                    title: '2nd title'
                }, {
                    href: '3_b.jpg'
                }], {
                    helpers: {
                        thumbs: {
                            width: 75,
                            height: 50
                        }
                    }
                });
            });
        });
    </script>
</head>

<body>
    <div id="wrapper">
        <?php include_once("navigation.php") ?>

        <div id="page-wrapper">
            <div class="alert alert-default" style="color:white;background-color:#008CBA">
                <center>
                    <h3> <span class="glyphicon glyphicon-shopping-cart"></span> This is our Paint & Brush stocks, Shop now!</h3>
                </center>
            </div>

            <br />
            <label for="filter">Filter</label>
            <input class="form-control" type="text" id="search" placeholder="Search items..." oninput="filterText()" style="width: 30%; margin-bottom: 10px;">

            <div style="display: flex; width: 100%; align-items: center; gap: 10px;">
                <div class="form-group">
                    <select id="filter1" class="form-control" onchange="updateFilterOptions()">
                        <option value="All">All</option>
                        <option value="Wall">Wall</option>
                        <option value="Wood">Wood</option>
                        <option value="Metal">Metal</option>
                    </select>
                </div>
                <div class="form-group">
                    <select id="filter2" class="form-control" onchange="updateFilterOptions()">
                        <option value="Interior">Interior</option>
                        <option value="Exterior">Exterior</option>
                        <option value="Tools">Tools</option>
                    </select>
                </div>
                <div class="form-group">
                    <select id="filter3" class="form-control" onchange="filterItems()">
                        <option value="Flat/Matte">Flat/Matte</option>
                        <option value="Gloss">Gloss</option>
                        <option value="Primer">Primer</option>
                        <option value="Acrytex">Acrytex</option>
                        <option value="QDE">QDE</option>
                        <option value="Oil Paint">Oil Paint</option>
                        <option value="Enamel">Enamel</option>
                        <option value="Alkyds">Alkyds</option>
                        <option value="Acrylic">Acrylic</option>
                        <option value="Latex">Latex</option>
                        <option value="Brush">Brush</option>
                        <option value="Tape">Tape</option>
                    </select>
                </div>
            </div>


            <?php
            require '../vendor/autoload.php';

            use Dotenv\Dotenv;

            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->load();

            $dbHost = $_ENV['DB_HOST'];
            $dbName = $_ENV['DB_NAME'];
            $dbUser = $_ENV['DB_USER'];
            $dbPass = $_ENV['DB_PASS'];

            $conn=mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

            if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
            }

            $start = 0;
            $limit = 8;
            $id = 1; // Initialize $id with a default value

            if (isset($_GET['id']) && !empty($_GET['id'])) {
                $id = intval($_GET['id']);
                $start = ($id - 1) * $limit;
            }

            $query = mysqli_query($conn, "SELECT item_id FROM wishlist WHERE user_id = {$_SESSION['user_id']}");
            $wishlist = mysqli_fetch_all($query, MYSQLI_NUM);

            function inWishlist($item) {
                global $wishlist;
                foreach ($wishlist as $wish) {
                    if ($wish[0] === $item) {
                        return true;
                    }
                }
                return false;
            }


            $query = mysqli_query($conn, "SELECT DISTINCT item_id, item_name, brand_name, item_image, item_price FROM items LIMIT $start, $limit");

            while ($query2 = mysqli_fetch_assoc($query)) {
                $exist = inWishlist($query2['item_id']);
                ?>
                <div style='min-width: 280px !important;' class='col-sm-3 panel-item' data-type='<?php echo $query2['type'] ?>' data-brand='<?php echo $query2['brand_name'] ?>'>
                    <div class='panel panel-default' style='border-color:#008CBA;'>
                        <div class='panel-heading' style='color:white;background-color: #033c73;'>
                            <center> 
                                <div style='text-align:center;background-color: white;' class='form-control'><?php echo $query2['brand_name'] ?></div>
                            </center>
                        </div>
                        <div class='panel-body'>
                            <a class='fancybox-buttons' href='../Admin/item_images/<?php echo $query2['item_image'] ?>'data-fancybox-group='button' title='Page <?php $id . "- " . $query2['item_name'] ?>'>
                                <img src='../Admin/item_images/<?php echo $query2['item_image'] ?>' class='img img-thumbnail' style='width:100%;height:260px;object-fit: contain;' />
                            </a>
                            <center><h4><?php echo $query2['item_name'] ?></h4></center>
                            <center><h4> Price: &#8369; <?php echo $query2['item_price'] ?> </h4></center>
                            <div style='display: flex;'>
                                <a class='btn btn-danger' style='flex: 1;' href='add_to_cart.php?cart=<?php echo $query2['item_id']?>'><span class='glyphicon glyphicon-shopping-cart'></span> Add to cart</a>
                            <?php
                                if ($exist) {
                                echo "<button class='btn btn-light' style='margin-left: 7px;' disabled><span class='glyphicon glyphicon-heart'></span></button>";
                            } else {
                                echo "<a class='btn btn-primary' style='margin-left: 7px;' href='./add_to_wishlist.php?item={$query2['item_id']}&user={$_SESSION['user_id']}'><span class='glyphicon glyphicon-heart'></span></a>";
                            }
                            ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }

            echo "<div class='container'></div>";

            $rows = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM items"));
            $total = ceil($rows / $limit);
            echo "<br /><ul class='pager'>";
            if ($id > 1) {
                echo "<li><a style='color:white;background-color : #033c73;' href='?id=" . ($id - 1) . "'>Previous Page</a><li>";
            }
            if ($id != $total) {
                echo "<li><a style='color:white;background-color : #033c73;' href='?id=" . ($id + 1) . "' class='pager'>Next Page</a></li>";
            }
            echo "</ul>";

            echo "<center><ul class='pagination pagination-lg'>";
            for ($i = 1; $i <= $total; $i++) {
                if ($i == $id) {
                    echo "<li class='pagination active'><a style='color:white;background-color : #033c73;'>" . $i . "</a></li>";
                } else {
                    echo "<li><a href='?id=" . $i . "'>" . $i . "</a></li>";
                }
            }
            echo "</ul></center>";
            ?>

            <br />

            <div class="alert alert-default" style="background-color:#033c73;">
                <p style="color:white;text-align:center;">
                    &copy 2024 CML PAINT TRADING Shop | All Rights Reserved

                </p>

            </div>

        </div>
    </div>
    </div>
    </div>
    <!-- /#wrapper -->


    <!-- Mediul Modal -->
    <div class="modal fade" id="setAccount" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
        <div class="modal-dialog modal-sm">
            <div style="color:white;background-color:#008CBA" class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h2 style="color:white" class="modal-title" id="myModalLabel">Account Settings</h2>
                </div>
                <div class="modal-body">

                    <form enctype="multipart/form-data" method="post" action="settings.php">
                        <fieldset>
                            <p>Firstname:</p>
                            <div class="form-group">

                                <input class="form-control" placeholder="Firstname" name="user_firstname" type="text" value="<?php echo $user_firstname; ?>" required>
                            </div>
                            <p>Lastname:</p>
                            <div class="form-group">
                                <input class="form-control" placeholder="Lastname" name="user_lastname" type="text" value="<?php echo $user_lastname; ?>" required>
                            </div>

                            <p>Address:</p>
                            <div class="form-group">

                                <input class="form-control" placeholder="Address" name="user_address" type="text" value="<?php echo $user_address; ?>" required>


                            </div>

                            <p>Password:</p>
                            <div class="form-group">

                                <input class="form-control" placeholder="Password" name="user_password" type="password" value="<?php echo $user_password; ?>" required>
                            </div>

                            <div class="form-group">
                                <input class="form-control hide" name="user_id" type="text" value="<?php echo $user_id; ?>" required>
                            </div>
                        </fieldset>


                </div>
                <div class="modal-footer">

                    <button class="btn btn-block btn-success btn-md" name="user_save">Save</button>

                    <button type="button" class="btn btn-block btn-danger btn-md" data-dismiss="modal">Cancel</button>


                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#priceinput').keypress(function(event) {
                return isNumber(event, this)
            });
        });

        function isNumber(evt, element) {

            var charCode = (evt.which) ? evt.which : event.keyCode

            if (
                (charCode != 45 || $(element).val().indexOf('-') != -1) &&
                (charCode != 46 || $(element).val().indexOf('.') != -1) &&
                (charCode < 48 || charCode > 57))
                return false;

            return true;
        }

        function filterText() {
            const searchValue = document.getElementById('search').value.toLowerCase();
            const panels = document.querySelectorAll('.panel-item');

            panels.forEach(panel => {
                const itemName = panel.querySelector('textarea').value.toLowerCase();
                if (itemName.includes(searchValue)) {
                    panel.style.display = '';
                } else {
                    panel.style.display = 'none';
                }
            });
        }
    </script>

    <script>
        // Function to update options in filter2 and filter3 based on filter1 and filter2 selections
        function updateFilterOptions() {
            var filter1 = document.getElementById('filter1').value.toLowerCase();
            var filter2 = document.getElementById('filter2').value.toLowerCase();
            var filter3Select = document.getElementById('filter3');

            var filter3Options = [];

            if (filter1 === 'wall') {
                if (filter2 === 'interior') {
                    filter3Options = ['Flat/Matte', 'Gloss', 'Primer'];
                } else if (filter2 === 'exterior') {
                    filter3Options = ['Acrytex', 'Primer'];
                } else if (filter2 === 'tools') {
                    filter3Options = ['Brush', 'Tape'];
                }
            } else if (filter1 === 'wood') {
                if (filter2 === 'interior') {
                    filter3Options = ['QDE', 'Oil Paint'];
                } else if (filter2 === 'exterior') {
                    filter3Options = ['Enamel'];
                } else if (filter2 === 'tools') {
                    filter3Options = ['Brush', 'Tape'];
                }
            } else if (filter1 === 'metal') {
                if (filter2 === 'interior') {
                    filter3Options = ['Alkyds'];
                } else if (filter2 === 'exterior') {
                    filter3Options = ['Acrylic', 'Latex'];
                } else if (filter2 === 'tools') {
                    filter3Options = ['Brush', 'Tape'];
                }
            }

            // Update filter3 options
            filter3Select.innerHTML = ''; // Clear current options

            for (var i = 0; i < filter3Options.length; i++) {
                var option = document.createElement('option');
                option.value = filter3Options[i].toLowerCase();
                option.textContent = filter3Options[i];
                filter3Select.appendChild(option);
            }

            filterItems();
        }

        // Function to filter items based on filter1, filter2, and filter3 selections
        function filterItems() {
            var filter1 = document.getElementById('filter1').value.toLowerCase();
            var filter2 = document.getElementById('filter2').value.toLowerCase();
            var filter3 = document.getElementById('filter3').value.toLowerCase();
            var panels = document.getElementsByClassName('panel-item');

            Array.from(panels).forEach(panel => {
                const itemType = panel.getAttribute("data-type").toLowerCase();
                if (itemType.includes(filter1) || itemType.includes(filter2) || itemType.includes(filter3)) {
                    panel.style.display = "";
                } else {
                    panel.style.display = "none";
                }
            });
        }

        // Initialize filter2 options based on filter1 on page load
        document.addEventListener('DOMContentLoaded', updateFilterOptions);
    </script>

</body>

</html>
