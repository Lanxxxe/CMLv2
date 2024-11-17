<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../index.php");
    exit;
}

require_once 'config.php';
require_once 'notificaiont_handler.php';

function sweetAlert($state, $title, $text) {
        echo "
        <DOCTYPE html>
        <html lang=\"en\">
        <head>
            <script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: '$state',
                    title: '$title',
                    text: '$text',
                    confirmButtonText: 'OK'
                }).then(e => {
                    window.location.href = './notifications.php';
                });
            </script>
        </body>
        </html>";
        exit;
}

// Add function to get detailed notification info
if (isset($_GET['get_details'])) {
    try {
        $id = $_GET['id'];
        $type = $_GET['type'];
        $handler = new NotificationHandler($DB_con);
        $notificationHtml = $handler->getNotificationContent($id, $type);
        echo $notificationHtml;
        exit;
    } catch(PDOException $e) {
        echo $e->getMessage();
    }
}

// Handle delete request
if(isset($_POST['delete_notification'])) {
    $id = $_POST['notification_id'];
    try {
        $stmt = $DB_con->prepare("DELETE FROM admin_notifications WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: notifications.php");
        exit;
    } catch(PDOException $e) {
        sweetAlert('error', 'Error', 'Failed to delete notification.');
    }
}


// Add endpoint for marking notifications as read
if(isset($_POST['mark_read'])) {
    $id = $_POST['notification_id'];
    try {
        $stmt = $DB_con->prepare("UPDATE admin_notifications SET status = 'read' WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: notifications.php");
        exit;
    } catch(PDOException $e) {
        sweetAlert('error', 'Error', 'Failed to mark notification as read.');
    }
}

if(isset($_POST['mark_all_read'])) {
    try {
        $stmt = $DB_con->prepare("UPDATE admin_notifications SET status = 'read' WHERE status = 'unread'");
        $stmt->execute();
        header("Location: notifications.php");
        exit;
    } catch(PDOException $e) {
        sweetAlert('error', 'Error', 'Failed to mark all notification as read.');
    }
}
// Get active tab from URL parameter, default to 'all'
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'all';

// Function to get notifications based on type
function getNotifications($DB_con, $type = null) {
    $query = 'SELECT * FROM admin_notifications_views';
    
    if ($type === 'ordered') {
        $query .= " WHERE ntype = 'ordered' OR ntype = 'confirmed' OR ntype = 'cancelled'";
    } elseif ($type === 'order_request') {
        $query .= " WHERE ntype = 'requested'";
    } elseif ($type === 'return_request') {
        $query .= " WHERE ntype IN ('return request', 'returned', 'return rejected', 'return deleted')";
    }
    
    $query .= ' ORDER BY id DESC';
    
    $stmt = $DB_con->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get icon class based on notification type
function getNotificationIcon($type) {
    switch ($type) {
        case 'ordered':
            return 'fa-shopping-cart';
        case 'requested':
            return 'fa-clock-o';
        case 'confirmed':
            return 'fa-check-circle';
        case 'cancelled':
            return 'fa-times-circle';
        case 'return request':
        case 'returned':
            return 'fa-undo';
        case 'return rejected':
            return 'fa-ban';
        case 'return deleted':
            return 'fa-trash';
        default:
            return 'fa-bell';
    }
}

// Function to get icon background color
function getIconColor($type) {
    switch ($type) {
        case 'ordered':
            return '#4CAF50';
        case 'requested':
            return '#2196F3';
        case 'confirmed':
            return '#4CAF50';
        case 'cancelled':
            return '#F44336';
        case 'return request':
        case 'returned':
            return '#FF9800';
        case 'return rejected':
            return '#F44336';
        case 'return deleted':
            return '#9E9E9E';
        default:
            return '#2196F3';
    }
}

function getUnreadCount($DB_con, $type = null) {
    $query = 'SELECT COUNT(*) FROM admin_notifications WHERE status = "unread"';
    
    if ($type === 'ordered') {
        $query .= " AND (ntype = 'ordered' OR ntype = 'confirmed' OR ntype = 'cancelled')";
    } elseif ($type === 'order_request') {
        $query .= " AND ntype = 'requested'";
    } elseif ($type === 'return_request') {
        $query .= " AND ntype IN ('return request', 'returned', 'return rejected', 'return deleted')";
    }
    
    $stmt = $DB_con->prepare($query);
    $stmt->execute();
    return $stmt->fetchColumn();
}

$notifications = getNotifications($DB_con, $activeTab !== 'all' ? $activeTab : null);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CML Paint Trading - Notifications</title>
    <link rel="shortcut icon" href="../assets/img/logo.png" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="css/local.css" />
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>

        .notification-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .notification-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .notification-card.unread {
            background-color: #f8f9fa;
            border-left: 4px solid #033c73;
        }

        .modal-body table {
            width: 100%;
            margin-bottom: 1rem;
        }

        .modal-body table th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
        }

        .modal-body table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }

        .detail-section {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            background-color: #f8f9fa;
        }

        .detail-section h5 {
            color: #033c73;
            margin-bottom: 15px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-confirmed { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }

        .image-preview {
            max-width: 200px;
            border-radius: 4px;
            margin-top: 10px;
        }

        .notifications-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .tabs-container {
            margin-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
        }

        .nav-tabs {
            border: none;
            display: flex;
            justify-content: flex-start;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #495057;
            padding: 12px 20px;
            margin-right: 4px;
            font-weight: 500;
        }

        .nav-tabs .nav-link.active {
            color: #033c73;
            border-bottom: 2px solid #033c73;
            background: none;
        }

        .notification-card {
            display: flex;
            align-items: flex-start;
            background: #fff;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .notification-icon i {
            color: white;
            font-size: 1.2em;
        }

        .notification-content {
            flex-grow: 1;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .notification-title {
            font-size: 1.1em;
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
        }

        .unread-indicator {
            width: 8px;
            height: 8px;
            background-color: #4CAF50;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
        }

        .notification-time {
            color: #757575;
            font-size: 0.9em;
        }

        .notification-message {
            color: #666;
            line-height: 1.4;
        }

        .notification-actions {
            margin-left: 15px;
        }

        .delete-btn {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            padding: 5px;
        }

        .delete-btn:hover {
            color: #c82333;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .notification-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            font-size: 11px;
            font-weight: 600;
            line-height: 2;
            color: #fff;
            background-color: #dc3545;
            border-radius: 10px;
            margin-left: 5px;
        }

        .nav-link {
            display: inline-flex;
            align-items: center;
        }

        .nav-link.active .notification-badge {
            background-color: #033c73;
        }

        .nav-item:last-child {
            margin-left: auto;
        }

        .payment-proof img {
            width: 300px;
            height: 300px;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <?php include("navigation.php"); ?>
        
        <div id="page-wrapper">
            <div class="notifications-container">
                <div class="page-header">
                    <h2><i class="fa fa-bell"></i> Notifications</h2>
                </div>

                <div class="tabs-container">
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $activeTab === 'all' ? 'active' : ''; ?>" href="?tab=all">
                                All
                                <?php $count = getUnreadCount($DB_con); if($count > 0): ?>
                                    <span class="notification-badge"><?php echo $count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $activeTab === 'ordered' ? 'active' : ''; ?>" href="?tab=ordered">
                                Orders
                                <?php $count = getUnreadCount($DB_con, 'ordered'); if($count > 0): ?>
                                    <span class="notification-badge"><?php echo $count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $activeTab === 'order_request' ? 'active' : ''; ?>" href="?tab=order_request">
                                Order Requests
                                <?php $count = getUnreadCount($DB_con, 'order_request'); if($count > 0): ?>
                                    <span class="notification-badge"><?php echo $count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $activeTab === 'return_request' ? 'active' : ''; ?>" href="?tab=return_request">
                                Return Requests
                                <?php $count = getUnreadCount($DB_con, 'return_request'); if($count > 0): ?>
                                    <span class="notification-badge"><?php echo $count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <button type="button" id="markAllRead" class="btn btn-primary">
                                <i class="fa fa-check-circle"></i> Mark all as Read
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="notifications-content">
                    <?php if (count($notifications) > 0): ?>
                        <?php foreach($notifications as $notification): ?>
                            <div class="notification-card" data-id="<?php echo $notification['id']; ?>" data-type="<?php echo $notification['ntype']; ?>">
                                <div class="notification-icon" 
                                     style="background-color: <?php echo getIconColor($notification['ntype']); ?>">
                                    <i class="fa <?php echo getNotificationIcon($notification['ntype']); ?>"></i>
                                </div>
                                
                                <div class="notification-content">
                                    <div class="notification-header">
                                        <div class="notification-title">
                                            <?php if($notification['status'] == 'unread'): ?>
                                                <span class="unread-indicator"></span>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($notification['head_msg']); ?>
                                        </div>
                                        <span class="notification-time">
                                            <?php echo date('g:i A', strtotime($notification['created_at'])); ?>
                                        </span>
                                    </div>
                                    <div class="notification-message">
                                        <?php echo htmlspecialchars($notification['message']); ?>
                                    </div>
                                </div>
                                
                                <div class="notification-actions">
                                    <form method="POST" class="confirmDeleteForm" style="display: inline;">
                                        <input type="hidden" name="notification_id" 
                                               value="<?php echo $notification['id']; ?>">
                                        <input type="hidden" name="delete_notification" 
                                               value="1">
                                        <button type="button" class="delete-btn">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fa fa-bell-slash fa-3x"></i>
                            <h3>No notifications</h3>
                            <p>You're all caught up! There are no notifications to display.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="alert alert-default" style="background-color:#033c73;">
                <p style="color:white;text-align:center;">
                    &copy 2024 CML Paint Trading Shop | All Rights Reserved
                </p>
            </div>
        </div>
    </div>

    <!-- Notification Detail Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Notification Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="modalContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Handle notification card click
        $('.notification-card').click(function(e) {
            if ($(e.target).closest('.delete-btn').length) {
               e.preventDefault();
                 Swal.fire({
                    title: 'Delete Notification',
                    text: "Are you sure you want to delete this notification?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if(result.isConfirmed) {
                        $(e.target).closest('.confirmDeleteForm').submit();
                    }
                });
            }

            if (!$(e.target).closest('.delete-btn').length) {
                const notificationId = $(this).data('id');
                const notificationType = $(this).data('type');
                
                // Mark as read
                $.post('notifications.php', {
                    mark_read: true,
                    notification_id: notificationId
                }).done(() => {
                    $(this).removeClass('unread');
                    $(this).find('.unread-indicator').remove();
                });
                
                console.log(notificationType);
                // Fetch and show details
                $.get('notifications.php', {
                    get_details: true,
                    id: notificationId,
                    type: notificationType
                }).done(function(response) {
                    const data = response;
                    let modalContent = data;
                    
                    $('#modalContent').html(modalContent);
                    $('#notificationModal').modal('show');
                });
            }
        });

        $('#notificationModal').on('hidden.bs.modal', function() {
            window.location.reload();
        });
        
        // Handle Mark All as Read button
        $('#markAllRead').click(function() {
             Swal.fire({
                title: 'Mark all as read',
                text: "Are you sure you want to mark all notification as read?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, mark all',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if(result.isConfirmed) {
                    const form = document.createElement('form');
                    form.setAttribute('method', 'post');
                    form.setAttribute('action', 'notifications.php');

                    const hidden = document.createElement('input');
                    hidden.setAttribute('type', 'hidden');
                    hidden.setAttribute('name', 'mark_all_read');
                    hidden.setAttribute('value', '1');
                    form.appendChild(hidden);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
    </script>
</body>
</html>
