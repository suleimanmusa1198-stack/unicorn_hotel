<?php
// Start session
session_start();

// Record logout activity if admin was logged in
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true){
    // Include database configuration
    include_once '../includes/config.php';
    
    $admin_id = $_SESSION['admin_id'];
    
    // Record logout activity
    $activity_sql = "INSERT INTO admin_activities (admin_id, activity_type, description) VALUES (?, 'logout', 'Admin logged out')";
    if($activity_stmt = mysqli_prepare($conn, $activity_sql)){
        mysqli_stmt_bind_param($activity_stmt, "i", $admin_id);
        mysqli_stmt_execute($activity_stmt);
        mysqli_stmt_close($activity_stmt);
    }
    
    mysqli_close($conn);
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header("location: login.php");
exit();
?>