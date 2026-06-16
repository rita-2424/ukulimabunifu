<?php
require_once 'marketplace_common.php';
?>
<nav class="navbar" style="background: linear-gradient(rgba(255,255,255,0.9), rgba(167, 207, 160, 0.16)) !important; backdrop-filter: blur(6px); color: #333;">
  <div class="nav-logo"><a href="index.php">FarmerLink</a></div>
  <ul class="nav-links">
    <li><a href="about.html">How It Works</a></li>

    <?php if (isset($_SESSION['username'])): ?>
      <?php if ($_SESSION['role'] === 'admin'): ?>
        <li><a href="admin-dashboard.php">Admin Dashboard</a></li>
        <li><a href="admin-transport.php">Transport</a></li>
      <?php elseif ($_SESSION['role'] === 'farmer'): ?>
        <li><a href="dashboard-farmer.php">Farmer Dashboard</a></li>
        <li><a href="post-listing.php">Post Listing</a></li>
      <?php else: ?>
        <li><a href="dashboard-buyer.php">Buyer Dashboard</a></li>
        <li><a href="browse.php">Browse Produce</a></li>
      <?php endif; ?>
      <li><span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span></li>
      <li><a href="logout.php" class="nav-btn">Logout</a></li>
    <?php else: ?>
      <li><a href="browse.php">Browse Produce</a></li>
      <li><a href="login.php">Login</a></li>
      <li><a href="register.php">Sign Up</a></li>
    <?php endif; ?>
  </ul>
</nav>
<script>
document.addEventListener('DOMContentLoaded', function(){
  var btn = document.querySelector('.nav-toggle');
  var links = document.querySelector('.nav-links');
  if (!btn || !links) return;
  btn.addEventListener('click', function(){
    links.classList.toggle('open');
  });
});
</script>
