<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Expressify - Express Yourself</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="dark-theme">
    <nav class="landing-navbar">
        <div class="nav-brand">
            <img src="logo.png" alt="Expressify Logo" class="logo">
            <h1>Expressify</h1>
        </div>
        <a href="auth.php" class="account-btn">
            <i class="fas fa-user"></i>
            Account
        </a>
    </nav>

    <div class="landing-container">
        <div class="hero-section">
            <div class="hero-content">
                <h1>Express Yourself with Expressify</h1>
                <p class="hero-subtitle">Connect, Share, and Discover in a Vibrant Community</p>
                <div class="hero-features">
                    <div class="feature">
                        <i class="fas fa-users"></i>
                        <h3>Connect</h3>
                        <p>Build meaningful connections with like-minded people</p>
                    </div>
                    <div class="feature">
                        <i class="fas fa-share-alt"></i>
                        <h3>Share</h3>
                        <p>Share your thoughts, moments, and creativity</p>
                    </div>
                    <div class="feature">
                        <i class="fas fa-compass"></i>
                        <h3>Discover</h3>
                        <p>Explore new perspectives and ideas</p>
                    </div>
                </div>
                <a href="auth.php" class="cta-button">Get Started</a>
            </div>
        </div>
    </div>

    <div class="about-section">
        <h2>About Expressify</h2>
        <p>Expressify is a social media platform that allows you to connect with friends, share your thoughts, and discover new ideas.</p>
        <p>It is a platform that is developed by a group of students from the Medicaps University, Indore as a part of their Project work.</p>
        <p>The main aim of the project is to provide a platform to the users to express their thoughts and ideas to the world.</p>
        <p>The project is developed using HTML, CSS, and JavaScript as frontend and PHP, MySQL as backend.</p>
        <p>The project is developed by <a href="https://github.com/Kazuto-013">Kazuto Kirigaya</a> which is Aditya Prakash Tripathy and <a href="https://github.com/">Abdul's github link</a> Abdul Tayyab Maksiwala </p>
    </div>

    <footer class="landing-footer">
        <div class="footer-content">
            <div class="footer-brand">
                <img src="assets/logo.png" alt="Expressify Logo" class="footer-logo">
                <p>Express yourself with Expressify</p>
            </div>
            <div class="footer-links">
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p><i class="fas fa-envelope"></i> support@expressify.com</p>
                </div>
                <div class="footer-section">
                    <h4>Follow Us</h4>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Expressify. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
