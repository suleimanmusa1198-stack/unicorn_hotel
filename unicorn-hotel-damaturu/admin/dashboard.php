<?php
// Start session and check authentication
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    header("location: login.php");
    exit();
}

// Include database configuration
include_once '../includes/config.php';

// Get dashboard statistics
$stats = [];

// Total bookings
$sql = "SELECT COUNT(*) as total FROM reservations";
$result = mysqli_query($conn, $sql);
$stats['total_bookings'] = mysqli_fetch_assoc($result)['total'];

// Today's bookings
$sql = "SELECT COUNT(*) as total FROM reservations WHERE DATE(created_at) = CURDATE()";
$result = mysqli_query($conn, $sql);
$stats['today_bookings'] = mysqli_fetch_assoc($result)['total'];

// Total revenue
$sql = "SELECT SUM(total_amount) as total FROM reservations WHERE status IN ('confirmed', 'checked_in', 'checked_out')";
$result = mysqli_query($conn, $sql);
$stats['total_revenue'] = mysqli_fetch_assoc($result)['total'] ?? 0;

// Available rooms
$sql = "SELECT COUNT(*) as total FROM rooms WHERE available = 1";
$result = mysqli_query($conn, $sql);
$stats['available_rooms'] = mysqli_fetch_assoc($result)['total'];

// Recent bookings
$sql = "SELECT r.booking_ref, r.guest_name, r.check_in, r.check_out, r.total_amount, r.status, 
               rm.room_type, rm.room_number
        FROM reservations r 
        LEFT JOIN rooms rm ON r.room_id = rm.id 
        ORDER BY r.created_at DESC 
        LIMIT 5";
$recent_bookings = mysqli_query($conn, $sql);

// Room occupancy
$sql = "SELECT room_type, COUNT(*) as count FROM rooms GROUP BY room_type";
$room_types = mysqli_query($conn, $sql);

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Unicorn Hotel Admin</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="admin-sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>Unicorn Hotel</h2>
                <p>Admin Panel</p>
            </div>
            <div class="sidebar-menu">
                <a href="dashboard.php" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="manage_bookings.php" class="menu-item">
                    <i class="fas fa-calendar-check"></i>
                    <span>Manage Bookings</span>
                </a>
                <a href="manage_rooms.php" class="menu-item">
                    <i class="fas fa-bed"></i>
                    <span>Manage Rooms</span>
                </a>
                <a href="manage_users.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Manage Users</span>
                </a>
                <a href="reports.php" class="menu-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
                <a href="settings.php" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            <!-- Header -->
            <div class="admin-header">
                <div class="header-left">
                    <h1>Dashboard</h1>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['admin_full_name'], 0, 1)); ?>
                        </div>
                        <div class="user-details">
                            <div class="user-name"><?php echo $_SESSION['admin_full_name']; ?></div>
                            <div class="user-role"><?php echo ucfirst($_SESSION['admin_role']); ?></div>
                        </div>
                    </div>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                    <div class="mobile-menu-btn" id="mobileMenuBtn">
                        <i class="fas fa-bars"></i>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="admin-content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_bookings']; ?></h3>
                            <p>Total Bookings</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon success">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['today_bookings']; ?></h3>
                            <p>Today's Bookings</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon warning">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-info">
                            <h3>₦<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon error">
                            <i class="fas fa-bed"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['available_rooms']; ?></h3>
                            <p>Available Rooms</p>
                        </div>
                    </div>
                </div>

                <div class="content-row">
                    <div class="content-col">
                        <!-- Recent Bookings -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Recent Bookings</h3>
                                <a href="manage_bookings.php" class="btn btn-outline btn-sm">View All</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Booking Ref</th>
                                                <th>Guest Name</th>
                                                <th>Room</th>
                                                <th>Check-in</th>
                                                <th>Status</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($booking = mysqli_fetch_assoc($recent_bookings)): ?>
                                            <tr>
                                                <td><?php echo $booking['booking_ref']; ?></td>
                                                <td><?php echo $booking['guest_name']; ?></td>
                                                <td><?php echo $booking['room_type'] . ' (' . $booking['room_number'] . ')'; ?></td>
                                                <td><?php echo date('M j, Y', strtotime($booking['check_in'])); ?></td>
                                                <td>
                                                    <?php 
                                                    $status_class = '';
                                                    switch($booking['status']){
                                                        case 'confirmed':
                                                            $status_class = 'badge-success';
                                                            break;
                                                        case 'checked_in':
                                                            $status_class = 'badge-primary';
                                                            break;
                                                        case 'checked_out':
                                                            $status_class = 'badge-info';
                                                            break;
                                                        case 'cancelled':
                                                            $status_class = 'badge-error';
                                                            break;
                                                        default:
                                                            $status_class = 'badge-warning';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($booking['status']); ?></span>
                                                </td>
                                                <td>₦<?php echo number_format($booking['total_amount'], 2); ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="content-col">
                        <!-- Room Distribution -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Room Distribution</h3>
                            </div>
                            <div class="card-body">
                                <div class="room-distribution">
                                    <?php while($room_type = mysqli_fetch_assoc($room_types)): ?>
                                    <div class="room-type-item">
                                        <div class="room-type-info">
                                            <span class="room-type-name"><?php echo ucfirst($room_type['room_type']); ?> Rooms</span>
                                            <span class="room-type-count"><?php echo $room_type['count']; ?></span>
                                        </div>
                                        <div class="room-type-bar">
                                            <div class="room-type-progress" style="width: <?php echo ($room_type['count'] / $stats['available_rooms']) * 100; ?>%"></div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Quick Actions</h3>
                            </div>
                            <div class="card-body">
                                <div class="quick-actions">
                                    <a href="manage_bookings.php?action=new" class="quick-action-btn">
                                        <i class="fas fa-plus-circle"></i>
                                        <span>New Booking</span>
                                    </a>
                                    <a href="manage_rooms.php?action=add" class="quick-action-btn">
                                        <i class="fas fa-bed"></i>
                                        <span>Add Room</span>
                                    </a>
                                    <a href="reports.php" class="quick-action-btn">
                                        <i class="fas fa-chart-pie"></i>
                                        <span>View Reports</span>
                                    </a>
                                    <a href="settings.php" class="quick-action-btn">
                                        <i class="fas fa-cog"></i>
                                        <span>Settings</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .content-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .room-distribution {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .room-type-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .room-type-info {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
        }
        
        .room-type-bar {
            height: 8px;
            background-color: var(--gray-light);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .room-type-progress {
            height: 100%;
            background-color: var(--primary);
            border-radius: 4px;
            transition: width 0.3s;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .quick-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-color: var(--light);
            border-radius: 8px;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.3s;
            text-align: center;
        }
        
        .quick-action-btn:hover {
            background-color: var(--primary);
            color: white;
            transform: translateY(-3px);
        }
        
        .quick-action-btn i {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }
        
        .quick-action-btn span {
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        @media (max-width: 992px) {
            .content-row {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>