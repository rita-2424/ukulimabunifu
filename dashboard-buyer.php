<?php
require_once 'marketplace_common.php';

marketplace_require_role('buyer');

$db = marketplace_db();
$user_id = (int) $_SESSION['user_id'];

if (isset($_GET['payment']) && $_GET['payment'] === 'success') {
    $paid_order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
    marketplace_set_flash(
        'buyer_flash_success',
        $paid_order_id > 0 ? "Payment received successfully for order #$paid_order_id." : 'Payment received successfully.'
    );
    header('location: dashboard-buyer.php');
    exit();
}

$orders_result = mysqli_query(
    $db,
    "SELECT o.order_id, o.quantity, o.produce_total, o.transport_fee, o.platform_fee, o.total, o.created_at,
            o.payment_option, o.payment_status, o.source_location, o.destination_location,
            l.crop_name, l.unit,
            t.option_name AS transport_name,
            COALESCE(os.status, 'pending') AS current_status
     FROM orders o
     JOIN listings l ON l.listing_id = o.listing_id
     LEFT JOIN transport_options t ON t.transport_option_id = o.transport_option_id
     LEFT JOIN order_status os
        ON os.order_id = o.order_id
       AND os.status_id = (
            SELECT MAX(status_id)
            FROM order_status
            WHERE order_id = o.order_id
       )
     WHERE o.buyer_id = $user_id
     ORDER BY o.created_at DESC"
);

$recent_listings_result = mysqli_query(
    $db,
    "SELECT listing_id, crop_name, category, price, approved_price, unit, quantity, subcounty
     FROM listings
     WHERE status = 'active' AND approval_status = 'approved'
     ORDER BY created_at DESC
     LIMIT 6"
);

$flash_message = marketplace_pull_flash('buyer_flash_success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FarmerLink - Buyer Dashboard</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="dashboard.css">
  <link rel="stylesheet" href="listing.css">
  <style>
    body {
      background: linear-gradient(rgba(255,255,255,0.92), rgba(255,255,255,0.82)), url('photos/bg2.jpeg') center/cover fixed !important;
      color: #333;
    }
  </style>
</head>
<body>

<?php include 'nav.php'; ?>

<div class="main">
  <div class="fl-section">
    <?php if ($flash_message !== '') : ?>
      <div class="success-msg"><?php echo marketplace_h($flash_message); ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['order_placed']) && $_GET['order_placed'] == '1') : ?>
      <div class="success-msg">
        Order #<?php echo (int) ($_GET['order_id'] ?? 0); ?> placed successfully.
        <?php if (($_GET['payment_option'] ?? '') === 'pay_after_delivery') : ?>
          Payment is pending moderator collection after delivery.
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <h3>My Orders</h3>
    <p class="muted-note" style="margin-bottom:1rem;">

    <?php if (!$orders_result || mysqli_num_rows($orders_result) === 0) : ?>
      <p>You have no orders yet. <a href="browse.php">Browse produce</a> to place your first order.</p>
    <?php else : ?>
      <div class="table-wrapper">
        <table class="fl-table">
          <tr>
            <th>Order</th>
            <th>Produce</th>
            <th>Route</th>
            <th>Fees</th>
            <th>Total</th>
            <th>Payment Option</th>
            <th>Payment</th>
            <th>Status</th>
            <th>Transport</th>
            <th>Action</th>
          </tr>
          <?php while ($row = mysqli_fetch_assoc($orders_result)) : ?>
            <?php
              $payment_option_label = $row['payment_option'] === 'pay_after_delivery' ? 'After Delivery' : 'Pay Now';
              $payment_status_label = $row['payment_status'] === 'paid' ? 'Paid' : 'Awaiting Payment';
            ?>
            <tr>
              <td>#<?php echo (int) $row['order_id']; ?></td>
              <td>
                <?php echo marketplace_h($row['crop_name']); ?><br>
                <span class="muted-note"><?php echo marketplace_quantity($row['quantity']); ?> <?php echo marketplace_h($row['unit']); ?></span>
              </td>
              <td>
                From <?php echo marketplace_h($row['source_location'] ?: 'source pending'); ?><br>
                To <?php echo marketplace_h($row['destination_location'] ?: 'destination pending'); ?>
              </td>
              <td>KSh <?php echo marketplace_money(((float) $row['transport_fee']) + ((float) $row['platform_fee'])); ?></td>
              <td>KSh <?php echo marketplace_money($row['total']); ?></td>
              <td><?php echo marketplace_h($payment_option_label); ?></td>
              <td>
                <span class="<?php echo marketplace_status_badge_class($row['payment_status']); ?>">
                  <?php echo marketplace_h($payment_status_label); ?>
                </span>
              </td>
              <td>
                <span class="<?php echo marketplace_status_badge_class($row['current_status']); ?>">
                  <?php echo ucfirst(marketplace_h($row['current_status'])); ?>
                </span>
              </td>
              <td><?php echo !empty($row['transport_name']) ? marketplace_h($row['transport_name']) : 'Assigning transport'; ?></td>
              <td>
                <?php if ($row['payment_status'] !== 'paid') : ?>
                  <a href="payment.php?order_id=<?php echo (int) $row['order_id']; ?>" class="btn-fl-small">Pay Now</a>
                <?php else : ?>
                  Paid
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <div class="fl-section">
    <h3>Produce You Can Order</h3>
    <a href="browse.php" class="btn-fl-small">View All Listings</a>

    <?php if (!$recent_listings_result || mysqli_num_rows($recent_listings_result) === 0) : ?>
      <p style="margin-top:1rem;">No approved produce listings are available right now.</p>
    <?php else : ?>
      <div class="listings-grid">
        <?php while ($row = mysqli_fetch_assoc($recent_listings_result)) : ?>
          <div class="listing-card">
            <div class="listing-card-body">
              <span class="fl-tag"><?php echo marketplace_h($row['category']); ?></span>
              <h4><?php echo marketplace_h($row['crop_name']); ?></h4>
              <p class="listing-meta">
                Source: <?php echo marketplace_h($row['subcounty']); ?><br>
                <?php echo marketplace_quantity($row['quantity']); ?> <?php echo marketplace_h($row['unit']); ?> available
              </p>
              <p class="listing-price">KSh <?php echo marketplace_money(marketplace_public_price($row)); ?> / <?php echo marketplace_h($row['unit']); ?></p>
            </div>
            <div class="listing-card-footer">
              <span class="farmer-name">Buyer-safe catalog</span>
              <a href="listing.php?id=<?php echo (int) $row['listing_id']; ?>" class="btn-fl">View and Order</a>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
