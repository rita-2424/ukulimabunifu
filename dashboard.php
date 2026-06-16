<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>FarmerLink - Farmer to Buyer Marketplace</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
  <style>
    body {
      background: linear-gradient(rgba(255,255,255,0.9), rgba(167,207,160,0.16)), url('photos/bg2.jpeg') center/cover fixed !important;
      color: #333; /* Ensure dark text */
    }
    .navbar {
      background: linear-gradient(rgba(255,255,255,0.9), rgba(167, 207, 160, 0.16)) !important;
      backdrop-filter: blur(6px);
      color: #333;
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <div class="nav-logo"><a href="index.php">FarmerLink</a></div>
  <ul class="nav-links">
    <li><a href="about.html">How It Works</a></li>
    
    <?php if (isset($_SESSION['username'])): ?>
      <li><span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span></li>
      <li><a href="logout.php" class="nav-btn">Logout</a></li>
    <?php else: ?>
      <li><a href="login.php">Login</a></li>
      <li><a href="register.php">Sign Up</a></li>
    <?php endif; ?>
  </ul>
</nav>

</div>

<section class="landing-hero grid-2">
  <div class="hero-copy">
    <h1 class="hero-tagline">FarmerLink — Direct from Farm to Table</h1>
    <p class="hero-subtitle">Connect farmers with buyers. Eliminate middlemen. Get fair prices for fresh produce.</p>
    <div class="hero-cta-row">
      <a href="register.php" class="btn-primary hero-cta">Get Started</a>
      <?php if (isset($_SESSION['username'])): ?>
        <a href="browse.php" class="btn-secondary hero-cta">Browse Listings</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="features-grid">
<div class="feature-card with-bg" style="background-image: linear-gradient(rgba(161, 223, 152, 0.63), rgba(177,224,169,0.7)), url('photos/WhatsApp Image 2026-04-16 at 21.13.53 (1).jpeg');">
    <h3>Zero Commission</h3>
    <p>Direct farmer-to-buyer marketplace with no middlemen. Farmers keep more of their earnings.</p>
  </div>
<div class="feature-card with-bg" style="background-image: linear-gradient(rgba(161, 223, 152, 0.63), rgba(177,224,169,0.7)), url('photos/WhatsApp Image 2026-04-16 at 21.13.53 (1).jpeg');">
    <h3>Real-Time Orders</h3>
    <p>Track orders from Pending to Dispatched to Delivered. Stay updated every step of the way.</p>
  </div>
<div class="feature-card with-bg" style="background-image: linear-gradient(rgba(161, 223, 152, 0.63), rgba(177, 224, 169, 0.7)), url('photos/WhatsApp Image 2026-04-16 at 21.13.53 (1).jpeg');">
    <h3>Simple & Accessible</h3>
    <p>Farmers post listings in minutes. Buyers find fresh produce and farmer contact info instantly.</p>
  </div>
</section>

<div class="main">
  <?php if (!isset($_SESSION['username'])): ?>
    <div class="auth-cta">
      <a href="register.php" class="btn-primary">Sign Up</a>
      <p class="small-link">Already have an account? <a href="login.php">Sign In</a></p>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
