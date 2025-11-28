<?php
session_start();

// Redirect if already logged in
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true){
    header("location: dashboard.php");
    exit();
}

// Include database configuration
include_once '../includes/config.php';

// Define variables and initialize with empty values
$username = $password = "";
$error = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Check if username and password are empty
    if(empty(trim($_POST["username"])) || empty(trim($_POST["password"]))){
        $error = "Please enter username and password.";
    } else{
        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);
        
        // Prepare a select statement
        $sql = "SELECT id, username, password_hash, full_name, role, is_active FROM admin_users WHERE username = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Store result
                mysqli_stmt_store_result($stmt);
                
                // Check if username exists, if yes then verify password
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $full_name, $role, $is_active);
                    if(mysqli_stmt_fetch($stmt)){
                        if($is_active && password_verify($password, $hashed_password)){
                            // Password is correct, start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["admin_logged_in"] = true;
                            $_SESSION["admin_id"] = $id;
                            $_SESSION["admin_username"] = $username;
                            $_SESSION["admin_full_name"] = $full_name;
                            $_SESSION["admin_role"] = $role;
                            
                            // Record login activity
                            $activity_sql = "INSERT INTO admin_activities (admin_id, activity_type, description) VALUES (?, 'login', 'Admin logged in')";
                            if($activity_stmt = mysqli_prepare($conn, $activity_sql)){
                                mysqli_stmt_bind_param($activity_stmt, "i", $id);
                                mysqli_stmt_execute($activity_stmt);
                                mysqli_stmt_close($activity_stmt);
                            }
                            
                            // Redirect user to dashboard page
                            header("location: dashboard.php");
                            exit();
                        } else{
                            // Display an error message if password is not valid or account is inactive
                            if(!$is_active){
                                $error = "This account has been deactivated.";
                            } else{
                                $error = "The password you entered was not valid.";
                            }
                        }
                    }
                } else{
                    // Display an error message if username doesn't exist
                    $error = "No account found with that username.";
                }
            } else{
                $error = "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Unicorn Hotel Damaturu</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo .logo-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .login-logo h1 {
            color: var(--dark);
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .login-logo p {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control {
            margin-bottom: 15px;
        }
        
        .form-control label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--gray-light);
            border-radius: 8px;
            font-size: 1rem;
            transition: border 0.3s, box-shadow 0.3s;
        }
        
        .form-control input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-login:hover {
            background-color: var(--primary-dark);
        }
        
        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border-left: 4px solid var(--error);
            color: var(--error);
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-light);
        }
        
        .login-footer a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-logo">
                <div class="logo-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <h1>Unicorn Hotel Damaturu</h1>
                <p>Admin Portal</p>
            </div>
            
            <?php 
            if(!empty($error)){
                echo '<div class="alert-error">' . $error . '</div>';
            }        
            ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-control">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo $username; ?>" required autofocus>
                </div>
                
                <div class="form-control">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-login">Login</button>
            </form>
            
            <div class="login-footer">
                <p><a href="../index.php">← Back to Website</a></p>
            </div>
        </div>
    </div>
</body>
</html>