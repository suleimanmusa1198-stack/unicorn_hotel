<?php
session_start();
// Include database configuration for room data
include_once 'includes/config.php';

// Get available rooms for display
$sql = "SELECT * FROM rooms WHERE available = 1 ORDER BY price";
$result = mysqli_query($conn, $sql);
$rooms = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Close connection
mysqli_close($conn);

// Room image mapping
$room_images = [
    'standard' => 'assets/images/standard room.jpeg',
    'deluxe' => 'assets/images/delux room.jpeg',
    'suite' => 'assets/images/suite room.jpeg',
    'presidential' => 'assets/images/presidential room.jpeg'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unicorn Hotel Damaturu - Luxury Stays</title>
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
                    <li><a href="#home">Home</a></li>
                    <li><a href="#rooms">Rooms</a></li>
                    <li><a href="#amenities">Amenities</a></li>
                    <li><a href="#gallery">Gallery</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <?php if(isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                        <li class="user-menu">
                            <a href="user/dashboard.php" class="user-welcome">
                                <i class="fas fa-user"></i> Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <div class="user-dropdown">
                                <a href="user/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                                <a href="user/bookings.php"><i class="fas fa-calendar-check"></i> My Bookings</a>
                                <a href="user/profile.php"><i class="fas fa-user-cog"></i> Profile</a>
                                <a href="user/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </div>
                        </li>
                    <?php elseif(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                        <li>
                            <a href="admin/dashboard.php" class="btn btn-primary">
                                <i class="fas fa-cog"></i> Admin Panel
                            </a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="user/login.php" class="btn btn-outline">Login</a>
                        </li>
                        <li>
                            <a href="user/register.php" class="btn btn-primary">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="container">
            <div class="hero-content">
                <h2>Experience Luxury & Comfort</h2>
                <p>Welcome to Unicorn Hotel Damaturu, where exceptional service meets unparalleled comfort in the heart of the city.</p>
                <div class="hero-buttons">
                    <a href="#rooms" class="btn btn-secondary">Explore Rooms</a>
                    <a href="#contact" class="btn btn-outline">Contact Us</a>
                    <?php if(isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                        <a href="user/dashboard.php" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt"></i> My Dashboard
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

  <!-- Booking Form -->
<div class="container">
    <form class="booking-form" id="bookingForm" action="process_booking.php" method="POST">
        <h3>Check Availability & Book Now</h3>
        
        <!-- Hidden field for room ID when booking from room card -->
        <input type="hidden" id="selected_room_id" name="selected_room_id" value="">
        
        <div class="form-group">
            <div class="form-control">
                <label for="checkin"><i class="fas fa-calendar-alt"></i> Check-In Date</label>
                <input type="date" id="checkin" name="checkin" required>
            </div>
            <div class="form-control">
                <label for="checkout"><i class="fas fa-calendar-alt"></i> Check-Out Date</label>
                <input type="date" id="checkout" name="checkout" required>
            </div>
            <div class="form-control">
                <label for="guests"><i class="fas fa-user"></i> Guests</label>
                <select id="guests" name="guests" required>
                    <option value="1">1 Guest</option>
                    <option value="2" selected>2 Guests</option>
                    <option value="3">3 Guests</option>
                    <option value="4">4 Guests</option>
                    <option value="5">5+ Guests</option>
                </select>
            </div>
            <div class="form-control">
                <label for="room-type"><i class="fas fa-bed"></i> Room Type</label>
                <select id="room-type" name="room_type" required>
                    <option value="standard">Standard Room</option>
                    <option value="deluxe">Deluxe Room</option>
                    <option value="suite">Executive Suite</option>
                    <option value="presidential">Presidential Suite</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">
            <i class="fas fa-search"></i> Check Availability & Book Now
        </button>
        
        <!-- Quick Book Buttons -->
        <div class="quick-book-actions">
            <h4>Quick Book for Next Weekend</h4>
            <div class="quick-buttons">
                <button type="button" class="btn btn-outline quick-book-btn" data-days="2">
                    Book for 2 Nights
                </button>
                <button type="button" class="btn btn-outline quick-book-btn" data-days="3">
                    Book for 3 Nights
                </button>
                <button type="button" class="btn btn-outline quick-book-btn" data-days="7">
                    Book for 1 Week
                </button>
            </div>
        </div>
    </form>
</div>
    <!-- Rooms Section -->
<section id="rooms" class="section-padding">
    <div class="container">
        <div class="section-title">
            <h2>Our Rooms & Suites</h2>
            <p>Discover our carefully curated selection of rooms designed for comfort and luxury.</p>
        </div>

        <div class="rooms-grid">
            <?php if(empty($rooms)): ?>
                <div class="empty-state">
                    <i class="fas fa-bed"></i>
                    <h3>No Rooms Available</h3>
                    <p>Please check back later for available rooms.</p>
                </div>
            <?php else: ?>
                <?php foreach($rooms as $room): ?>
                <div class="room-card">
                    <div class="room-image">
                        <img src="<?php echo isset($room_images[$room['room_type']]) ? $room_images[$room['room_type']] : 'assets/images/room1.jpeg'; ?>" alt="<?php echo ucfirst($room['room_type']); ?> Room">
                        <?php if($room['room_type'] == 'standard'): ?>
                            <div class="room-badge">Most Popular</div>
                        <?php elseif($room['room_type'] == 'suite' || $room['room_type'] == 'presidential'): ?>
                            <div class="room-badge">Luxury</div>
                        <?php endif; ?>
                    </div>
                    <div class="room-details">
                        <h3><?php echo ucfirst($room['room_type']); ?> Room</h3>
                        <p><?php echo $room['description']; ?></p>
                        <ul class="room-features">
                            <?php 
                            $amenities = explode(',', $room['amenities']);
                            foreach(array_slice($amenities, 0, 4) as $amenity):
                                if(trim($amenity)):
                            ?>
                            <li><i class="fas fa-check"></i> <?php echo htmlspecialchars(trim($amenity)); ?></li>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </ul>
                        <div class="room-price">
                            <span class="price">₦<?php echo number_format($room['price'], 2); ?>/night</span>
                            <!-- Updated Book Now button -->
                            <button type="button" class="btn btn-primary book-now-btn" 
                                    data-room-type="<?php echo $room['room_type']; ?>"
                                    data-room-id="<?php echo $room['id']; ?>"
                                    data-room-number="<?php echo $room['room_number']; ?>"
                                    data-room-price="<?php echo $room['price']; ?>">
                                Book Now
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
    <!-- Amenities Section -->
    <section id="amenities" class="section-padding bg-light">
        <div class="container">
            <div class="section-title">
                <h2>Hotel Amenities</h2>
                <p>Enjoy our premium facilities designed for your comfort and convenience.</p>
            </div>
            <div class="amenities-grid">
                <div class="amenity-card">
                    <div class="amenity-icon">
                        <i class="fas fa-swimming-pool"></i>
                    </div>
                    <h3>Swimming Pool</h3>
                    <p>Relax in our temperature-controlled outdoor swimming pool.</p>
                </div>
                <div class="amenity-card">
                    <div class="amenity-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h3>Restaurant</h3>
                    <p>Enjoy local and international cuisine at our in-house restaurant.</p>
                </div>
                <div class="amenity-card">
                    <div class="amenity-icon">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <h3>Fitness Center</h3>
                    <p>Stay fit with our state-of-the-art gym equipment.</p>
                </div>
                <div class="amenity-card">
                    <div class="amenity-icon">
                        <i class="fas fa-wifi"></i>
                    </div>
                    <h3>Free Wi-Fi</h3>
                    <p>High-speed internet access throughout the hotel.</p>
                </div>
                <div class="amenity-card">
                    <div class="amenity-icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <h3>Parking</h3>
                    <p>Complimentary secure parking for all guests.</p>
                </div>
                <div class="amenity-card">
                    <div class="amenity-icon">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                    <h3>24/7 Service</h3>
                    <p>Round-the-clock concierge and room service.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section id="gallery" class="section-padding">
        <div class="container">
            <div class="section-title">
                <h2>Photo Gallery</h2>
                <p>Take a visual tour of our luxurious facilities and accommodations.</p>
            </div>
            <div class="gallery-grid">
                <div class="gallery-item">
                    <img src="assets/images/gallery1.jpg" alt="Hotel Lobby">
                </div>
                <div class="gallery-item">
                    <img src="assets/images/gallery2.jpg" alt="Restaurant">
                </div>
                <div class="gallery-item">
                    <img src="assets/images/gallery3.jpg" alt="Pool Area">
                </div>
                <div class="gallery-item">
                    <img src="assets/images/gallery4.jpg" alt="Spa">
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="section-padding bg-light">
        <div class="container">
            <div class="section-title">
                <h2>What Our Guests Say</h2>
                <p>Read about the experiences of our valued guests.</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"Exceptional service and luxurious accommodations. The staff went above and beyond to make our stay memorable!"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="author-info">
                            <h4>Aminu Suleiman Aji</h4>
                            <span>Business Traveler</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"The perfect getaway! Beautiful rooms, amazing amenities, and the most comfortable beds we've ever slept in."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="author-info">
                            <h4>Fatima A. Koso</h4>
                            <span>Family Vacation</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"As a frequent traveler, I can confidently say this is one of the best hotels I've stayed in. Highly recommended!"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="author-info">
                            <h4>Abdullahi Yusuf Idi</h4>
                            <span>Regular Guest</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section-padding">
        <div class="container">
            <div class="section-title">
                <h2>Contact Us</h2>
                <p>Get in touch with us for reservations or inquiries.</p>
            </div>
            <div class="contact-container">
                <div class="contact-info">
                    <h3>Get In Touch</h3>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-details">
                            <h4>Address</h4>
                            <p>123 Hotel Street, Damaturu, Yobe State</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-details">
                            <h4>Phone</h4>
                            <p>+234 801 234 5678</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-details">
                            <h4>Email</h4>
                            <p>info@unicornhoteldamaturu.com</p>
                        </div>
                    </div>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="contact-form">
                    <h3>Send Message</h3>
                    <form id="contactForm">
                        <div class="form-group">
                            <div class="form-control">
                                <input type="text" placeholder="Your Name" required>
                            </div>
                            <div class="form-control">
                                <input type="email" placeholder="Your Email" required>
                            </div>
                        </div>
                        <div class="form-control">
                            <input type="text" placeholder="Subject" required>
                        </div>
                        <div class="form-control">
                            <textarea placeholder="Your Message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <div class="logo">
                        <span class="logo-icon"><i class="fas fa-crown"></i></span>
                        <h3>Unicorn Hotel Damaturu</h3>
                    </div>
                    <p>Experience luxury and comfort in the heart of Damaturu. Our hotel offers premium accommodations with exceptional service.</p>
                </div>
                <div class="footer-column">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#rooms">Rooms & Suites</a></li>
                        <li><a href="#amenities">Amenities</a></li>
                        <li><a href="#gallery">Gallery</a></li>
                        <li><a href="#contact">Contact Us</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Contact Info</h4>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Hotel Street, Damaturu</li>
                        <li><i class="fas fa-phone"></i> +234 801 234 5678</li>
                        <li><i class="fas fa-envelope"></i> info@unicornhoteldamaturu.com</li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Newsletter</h4>
                    <p>Subscribe to our newsletter for updates and offers.</p>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Your Email" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2023 Unicorn Hotel Damaturu. All Rights Reserved. | National Diploma Project</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        // Function to preselect room type when clicking Book Now
        function preselectRoom(roomType) {
            document.getElementById('room-type').value = roomType;
            document.getElementById('bookingForm').scrollIntoView({ 
                behavior: 'smooth' 
            });
        }

        // Set minimum dates for booking form
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('checkin').min = today;
            
            // Update checkout min date when checkin changes
            document.getElementById('checkin').addEventListener('change', function() {
                document.getElementById('checkout').min = this.value;
            });
        });

        // Mobile menu functionality
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('nav ul').classList.toggle('show');
        });

        // User dropdown functionality
        const userMenu = document.querySelector('.user-menu');
        if(userMenu) {
            userMenu.addEventListener('click', function(e) {
                e.preventDefault();
                this.querySelector('.user-dropdown').classList.toggle('show');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if(!userMenu.contains(e.target)) {
                    userMenu.querySelector('.user-dropdown').classList.remove('show');
                }
            });
        }
    </script>

    <style>
        /* User Menu Styles */
        .user-menu {
            position: relative;
        }

        .user-welcome {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: var(--light);
            border-radius: 6px;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.3s;
        }

        .user-welcome:hover {
            background: var(--primary);
            color: white;
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s;
            z-index: 1000;
        }

        .user-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .user-dropdown a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            text-decoration: none;
            color: var(--dark);
            border-bottom: 1px solid var(--gray-light);
            transition: all 0.3s;
        }

        .user-dropdown a:last-child {
            border-bottom: none;
        }

        .user-dropdown a:hover {
            background: var(--light);
            color: var(--primary);
        }

        .user-dropdown a i {
            width: 16px;
            text-align: center;
        }

        /* Testimonials Styles */
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .testimonial-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .testimonial-content {
            font-style: italic;
            color: var(--gray);
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .author-info h4 {
            margin-bottom: 5px;
            color: var(--dark);
        }

        .author-info span {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
            grid-column: 1 / -1;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: var(--gray-light);
        }

        .empty-state h3 {
            margin-bottom: 10px;
            color: var(--dark);
        }

        /* Mobile Menu */
        @media (max-width: 768px) {
            nav ul {
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                display: none;
                padding: 20px;
            }

            nav ul.show {
                display: flex;
            }

            .user-dropdown {
                position: static;
                box-shadow: none;
                margin-top: 10px;
            }
        }
    </style>
</body>
</html>