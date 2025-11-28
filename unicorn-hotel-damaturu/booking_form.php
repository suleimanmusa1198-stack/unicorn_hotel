<?php
// Start session and include files
session_start();
include_once 'includes/config.php';
include_once 'includes/functions.php';

// Redirect if no booking details
if(!isset($_SESSION['booking_details'])){
    header("location: index.php");
    exit();
}

$booking_details = $_SESSION['booking_details'];
$room_id = $booking_details['room_id'];

// Get room details
$sql = "SELECT * FROM rooms WHERE id = ?";
if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $room_id);
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        $room = mysqli_fetch_array($result, MYSQLI_ASSOC);
    }
    mysqli_stmt_close($stmt);
}

// Process form when submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate and sanitize inputs
    $full_name = sanitize_input($_POST["full_name"]);
    $email = sanitize_input($_POST["email"]);
    $phone = sanitize_input($_POST["phone"]);
    $address = sanitize_input($_POST["address"]);
    $special_requests = sanitize_input($_POST["special_requests"]);
    
    $errors = [];
    
    // Validate required fields
    if(empty($full_name)){
        $errors[] = "Please enter your full name.";
    }
    
    if(empty($email)){
        $errors[] = "Please enter your email address.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[] = "Please enter a valid email address.";
    }
    
    if(empty($phone)){
        $errors[] = "Please enter your phone number.";
    }
    
    // If no errors, process booking
    if(empty($errors)){
        $booking_ref = generate_booking_ref();
        $room_id = $booking_details['room_id'];
        $check_in = $booking_details['check_in'];
        $check_out = $booking_details['check_out'];
        $guests = $booking_details['guests'];
        $total_amount = $booking_details['total_amount'];
        
        // Insert reservation into database
        $sql = "INSERT INTO reservations (booking_ref, room_id, guest_name, guest_email, guest_phone, guest_address, special_requests, check_in, check_out, guests, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "sississsiid", $booking_ref, $room_id, $full_name, $email, $phone, $address, $special_requests, $check_in, $check_out, $guests, $total_amount);
            
            if(mysqli_stmt_execute($stmt)){
                $reservation_id = mysqli_insert_id($conn);
                
                // Send confirmation email
                send_booking_confirmation($email, [
                    'booking_ref' => $booking_ref,
                    'guest_name' => $full_name,
                    'check_in' => $check_in,
                    'check_out' => $check_out,
                    'total_amount' => $total_amount
                ]);
                
                // Store success data in session
                $_SESSION['booking_success'] = [
                    'booking_ref' => $booking_ref,
                    'reservation_id' => $reservation_id,
                    'guest_name' => $full_name,
                    'email' => $email
                ];
                
                // Clear booking details
                unset($_SESSION['booking_details']);
                
                // Redirect to confirmation page
                header("location: confirmation.php");
                exit();
            } else{
                $errors[] = "Something went wrong. Please try again.";
            }
            
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Booking - Unicorn Hotel Damaturu</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <div class="logo">
                <span class="logo-icon"><i class="fas fa-crown"></i></span>
                <h1>Unicorn Hotel Damaturu</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="index.php#rooms">Rooms</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Booking Form Section -->
    <section class="section-padding">
        <div class="container">
            <div class="section-title">
                <h2>Complete Your Booking</h2>
                <p>Please provide your details to complete the reservation.</p>
            </div>

            <div class="booking-container">
                <div class="booking-summary">
                    <h3>Booking Summary</h3>
                    <div class="summary-card">
                        <div class="summary-item">
                            <span class="summary-label">Room Type:</span>
                            <span class="summary-value"><?php echo ucfirst($booking_details['room_type']); ?> Room</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Check-in:</span>
                            <span class="summary-value"><?php echo format_date($booking_details['check_in']); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Check-out:</span>
                            <span class="summary-value"><?php echo format_date($booking_details['check_out']); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Nights:</span>
                            <span class="summary-value"><?php echo $booking_details['nights']; ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Guests:</span>
                            <span class="summary-value"><?php echo $booking_details['guests']; ?></span>
                        </div>
                        <div class="summary-item total">
                            <span class="summary-label">Total Amount:</span>
                            <span class="summary-value">₦<?php echo number_format($booking_details['total_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>

                <div class="booking-form-container">
                    <h3>Guest Information</h3>
                    
                    <?php
                    // Display errors if any
                    if(isset($errors) && !empty($errors)){
                        echo '<div class="alert alert-error">';
                        foreach($errors as $error){
                            echo '<p>' . $error . '</p>';
                        }
                        echo '</div>';
                    }
                    ?>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <div class="form-control">
                                <label for="full_name">Full Name *</label>
                                <input type="text" id="full_name" name="full_name" value="<?php echo isset($_POST['full_name']) ? $_POST['full_name'] : ''; ?>" required>
                            </div>
                            <div class="form-control">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-control">
                                <label for="phone">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : ''; ?>" required>
                            </div>
                            <div class="form-control">
                                <label for="address">Address</label>
                                <input type="text" id="address" name="address" value="<?php echo isset($_POST['address']) ? $_POST['address'] : ''; ?>">
                            </div>
                        </div>
                        <div class="form-control">
                            <label for="special_requests">Special Requests</label>
                            <textarea id="special_requests" name="special_requests" rows="4"><?php echo isset($_POST['special_requests']) ? $_POST['special_requests'] : ''; ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Complete Booking</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <style>
        .booking-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 40px;
        }
        
        .booking-summary {
            background-color: var(--light);
            padding: 30px;
            border-radius: 12px;
            height: fit-content;
        }
        
        .summary-card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .summary-item.total {
            border-bottom: none;
            font-weight: bold;
            font-size: 1.1rem;
            color: var(--primary);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border-left: 4px solid var(--error);
            color: var(--error);
        }
        
        @media (max-width: 768px) {
            .booking-container {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <?php include_once 'includes/footer.php'; ?>
</body>
</html>