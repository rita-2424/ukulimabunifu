<?php
require_once 'marketplace_common.php';

$db = marketplace_db();

if (!isset($_GET['id'])) {
    header('location: browse.php');
    exit();
}

$listing_id = (int) $_GET['id'];

$result = marketplace_query_result(
    $db,
    "SELECT listing_id, crop_name, category, price, approved_price, admin_transport_fee, admin_platform_fee,
            unit, quantity, subcounty, description, delivery_available, status, approval_status, admin_notes
     FROM listings
     WHERE listing_id = ?
       AND status = 'active'
       AND approval_status = 'approved'
     LIMIT 1",
    "i",
    array($listing_id)
);

if (!$result || mysqli_num_rows($result) === 0) {
    header('location: browse.php');
    exit();
}

$listing = mysqli_fetch_assoc($result);
$errors = array();
$selected_payment_option = 'pay_now';

if (isset($_POST['place_order'])) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
        header('location: login.php');
        exit();
    }

    $buyer_id = (int) $_SESSION['user_id'];
    $buyer_result = marketplace_query_result($db, "SELECT county FROM users WHERE user_id = ? LIMIT 1", "i", array($buyer_id));
    $buyer = $buyer_result ? mysqli_fetch_assoc($buyer_result) : null;

    $quantity = (float) ($_POST['quantity'] ?? 0);
    $selected_payment_option = $_POST['payment_option'] ?? 'pay_now';
    $allowed_payment_options = array('pay_now', 'pay_after_delivery');

    if ($quantity <= 0) {
        $errors[] = 'Please enter a valid quantity.';
    } elseif ($quantity > (float) $listing['quantity']) {
        $errors[] = 'Quantity exceeds the currently available stock.';
    }

    if (!in_array($selected_payment_option, $allowed_payment_options, true)) {
        $errors[] = 'Choose a valid payment option.';
    }

    if (!$buyer || empty($buyer['county'])) {
        $errors[] = 'Your buyer location is missing. Update your account location before ordering.';
    }

    if (count($errors) === 0) {
        $farmer_total = $quantity * (float) $listing['price'];
        $produce_total = $quantity * marketplace_public_price($listing);
        $admin_markup_total = $produce_total - $farmer_total;
        $transport_fee = (float) $listing['admin_transport_fee'];
        $platform_fee = (float) $listing['admin_platform_fee'];
        $total = $produce_total + $transport_fee + $platform_fee;
        $source_location = (string) $listing['subcounty'];
        $destination_location = (string) $buyer['county'];

        marketplace_execute(
            $db,
            "INSERT INTO orders (
                listing_id, buyer_id, quantity, produce_total, farmer_total, admin_markup_total,
                transport_fee, platform_fee, total, source_location, destination_location,
                payment_option, payment_status
             ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, 'unpaid'
             )",
            "iidddddddsss",
            array(
                $listing_id, $buyer_id, $quantity, $produce_total, $farmer_total, $admin_markup_total,
                $transport_fee, $platform_fee, $total, $source_location, $destination_location,
                $selected_payment_option
            )
        );

        $order_id = mysqli_insert_id($db);

        marketplace_execute(
            $db,
            "INSERT INTO order_status (order_id, status, updated_by)
             VALUES (?, 'pending', ?)",
            "ii",
            array($order_id, $buyer_id)
        );

        if ($selected_payment_option === 'pay_now') {
            header('location: payment.php?order_id=' . $order_id);
        } else {
            header('location: dashboard-buyer.php?order_placed=1&payment_option=pay_after_delivery&order_id=' . $order_id);
        }
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FarmerLink - <?php echo marketplace_h($listing['crop_name']); ?></title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="listing.css?v=2">
</head>
<body class="listing-page">

<?php include 'nav.php'; ?>

<div class="fl-section">
  <p class="breadcrumb"><a href="browse.php">Browse</a> &gt; <?php echo marketplace_h($listing['crop_name']); ?></p>

  <div class="detail-layout">
    <div class="detail-main">
      <span class="fl-tag"><?php echo marketplace_h($listing['category']); ?></span>
      <h2><?php echo marketplace_h($listing['crop_name']); ?></h2>
      <p class="listing-meta">Source location: <?php echo marketplace_h($listing['subcounty']); ?></p>

      <div class="detail-stats">
        <div class="stat-box">
          <span class="stat-label">Buyer Price</span>
          <span class="stat-value">KSh <?php echo marketplace_money(marketplace_public_price($listing)); ?> / <?php echo marketplace_h($listing['unit']); ?></span>
        </div>
        <div class="stat-box">
          <span class="stat-label">Available</span>
          <span class="stat-value"><?php echo marketplace_quantity($listing['quantity']); ?> <?php echo marketplace_h($listing['unit']); ?></span>
        </div>
        <div class="stat-box">
          <span class="stat-label">Delivery</span>
          <span class="stat-value"><?php echo $listing['delivery_available'] ? 'A delivery fee of 250 or more is going to be charged for delivery' : 'Collection managed by the system'; ?></span>
        </div>
      </div>

      <?php if (!empty($listing['description'])) : ?>
        <h4>About this produce</h4>
        <p><?php echo marketplace_h($listing['description']); ?></p>
      <?php endif; ?>

      <div class="farmer-card">
        <h4>System managed sourcing</h4>
        <p>Your order will be placed. Your personal details stay private.</p>
        <?php if (!empty($listing['admin_notes'])) : ?>
          <p><strong>Moderator note:</strong> <?php echo marketplace_h($listing['admin_notes']); ?></p>
        <?php endif; ?>
        <p><strong>Transport fee:</strong> KSh <?php echo marketplace_money($listing['admin_transport_fee']); ?></p>
        <p><strong>Platform fee:</strong> KSh <?php echo marketplace_money($listing['admin_platform_fee']); ?></p>
      </div>
    </div>

    <div class="detail-sidebar">
      <div class="order-card">
        <h3>Place an Order</h3>

        <?php if (count($errors) > 0) : ?>
          <div class="error">
            <?php foreach ($errors as $error) : ?>
              <p><?php echo marketplace_h($error); ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if (!isset($_SESSION['user_id'])) : ?>
          <p>You need to <a href="login.php">log in</a> as a buyer to place an order.</p>
        <?php elseif ($_SESSION['role'] !== 'buyer') : ?>
          <p>Only buyer accounts can place orders from this page.</p>
        <?php else : ?>
          <form method="post" action="listing.php?id=<?php echo $listing_id; ?>">
            <div class="input-group">
              <label>Quantity (<?php echo marketplace_h($listing['unit']); ?>)</label>
              <input type="number" name="quantity" min="1" max="<?php echo marketplace_h($listing['quantity']); ?>" placeholder="e.g. 10" required>
            </div>

            <div class="input-group" style="margin-top:1rem;">
              <label>Payment option</label>
              <div style="display:grid;gap:0.75rem;margin-top:0.6rem;">
                <label style="display:flex;gap:0.6rem;align-items:flex-start;padding:0.85rem;border:1px solid #dcdcdc;border-radius:10px;cursor:pointer;">
                  <input type="radio" name="payment_option" value="pay_now" <?php echo $selected_payment_option === 'pay_now' ? 'checked' : ''; ?>>
                  <span>
                    <strong>Pay while ordering</strong><br>
                    Payment can be collected immediately before the order moves forward.
                  </span>
                </label>
                <label style="display:flex;gap:0.6rem;align-items:flex-start;padding:0.85rem;border:1px solid #dcdcdc;border-radius:10px;cursor:pointer;">
                  <input type="radio" name="payment_option" value="pay_after_delivery" <?php echo $selected_payment_option === 'pay_after_delivery' ? 'checked' : ''; ?>>
                  <span>
                    <strong>Pay after delivery</strong><br>
                    Place the order now, then settle the payment once delivery is completed.
                  </span>
                </label>
              </div>
            </div>

            <p class="price-note">Buyer price: KSh <?php echo marketplace_money(marketplace_public_price($listing)); ?> / <?php echo marketplace_h($listing['unit']); ?></p>
            <p class="price-note">Transport fee: KSh <?php echo marketplace_money($listing['admin_transport_fee']); ?>. Platform fee: KSh <?php echo marketplace_money($listing['admin_platform_fee']); ?>.</p>

            <button type="submit" name="place_order" class="btn-fl" style="width:100%;">Confirm Order</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

</body>
</html>
