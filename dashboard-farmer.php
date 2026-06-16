<?php
require_once 'marketplace_common.php';

marketplace_require_role('farmer');

$db = marketplace_db();
$user_id = (int) $_SESSION['user_id'];

if (isset($_POST['update_status'])) {
    $order_id = (int) ($_POST['order_id'] ?? 0);
    $new_status = $_POST['new_status'] ?? 'pending';
    $allowed_statuses = array('pending', 'dispatched', 'delivered');

    if (in_array($new_status, $allowed_statuses, true)) {
        marketplace_execute(
            $db,
            "INSERT INTO order_status (order_id, status, updated_by)
             SELECT ?, ?, ?
             FROM orders o
             JOIN listings l ON l.listing_id = o.listing_id
             WHERE o.order_id = ? AND l.farmer_id = ?
             LIMIT 1",
            "isiii",
            array($order_id, $new_status, $user_id, $order_id, $user_id)
        );
        marketplace_set_flash('farmer_flash', 'Order status updated.');
    }

    header('location: dashboard-farmer.php');
    exit();
}

if (isset($_POST['submit_stock_update'])) {
    $order_id = (int) ($_POST['order_id'] ?? 0);
    $listing_id = (int) ($_POST['listing_id'] ?? 0);
    $remaining_quantity = (float) ($_POST['remaining_quantity'] ?? 0);
    $stock_note = trim((string) ($_POST['stock_note'] ?? ''));

    $order_result = marketplace_query_result(
        $db,
        "SELECT o.order_id, l.listing_id
         FROM orders o
         JOIN listings l ON l.listing_id = o.listing_id
         LEFT JOIN order_status os
           ON os.order_id = o.order_id
          AND os.status_id = (
                SELECT MAX(status_id)
                FROM order_status
                WHERE order_id = o.order_id
           )
         WHERE o.order_id = $order_id
           AND l.listing_id = ?
           AND l.farmer_id = ?
           AND COALESCE(os.status, 'pending') = 'delivered'
         LIMIT 1",
        "iii",
        array($order_id, $listing_id, $user_id)
    );

    if ($remaining_quantity < 0) {
        marketplace_set_flash('farmer_flash', 'Remaining stock cannot be negative.');
    } elseif (!$order_result || mysqli_num_rows($order_result) === 0) {
        marketplace_set_flash('farmer_flash', 'Only delivered orders can be used for stock updates.');
    } else {
        $status = $remaining_quantity > 0 ? 'active' : 'sold_out';
        marketplace_execute(
            $db,
            "UPDATE listings
             SET quantity = ?,
                 status = ?
             WHERE listing_id = ? AND farmer_id = ?",
            "dsii",
            array($remaining_quantity, $status, $listing_id, $user_id)
        );

        marketplace_execute(
            $db,
            "INSERT INTO stock_updates (listing_id, farmer_id, order_id, remaining_quantity, note)
             VALUES (?, ?, ?, ?, ?)",
            "iiids",
            array($listing_id, $user_id, $order_id, $remaining_quantity, $stock_note)
        );

        marketplace_set_flash('farmer_flash', 'Stock update sent to admin successfully.');
    }

    header('location: dashboard-farmer.php');
    exit();
}

$listings_result = mysqli_query(
    $db,
    "SELECT listing_id, crop_name, category, price, approved_price, admin_transport_fee, admin_platform_fee,
            quantity, unit, subcounty, status, approval_status, admin_notes, created_at
     FROM listings
     WHERE farmer_id = $user_id
     ORDER BY created_at DESC"
);

$orders_result = mysqli_query(
    $db,
    "SELECT o.order_id, o.listing_id, o.quantity, o.farmer_total, o.transport_fee, o.platform_fee, o.created_at,
            o.payment_status, o.farmer_payout_status, o.destination_location,
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
     WHERE l.farmer_id = $user_id
     ORDER BY o.created_at DESC"
);

$flash_message = marketplace_pull_flash('farmer_flash');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FarmerLink - Farmer Dashboard</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="dashboard.css">
  <style>
    body {
      background: linear-gradient(rgba(255,255,255,0.92), rgba(255,255,255,0.82)), url('photos/bg2.jpeg') center/cover fixed !important;
      color: #333;
    }
    .inline-update-form {
      display: grid;
      gap: 0.5rem;
      min-width: 220px;
    }
    .inline-update-form input,
    .inline-update-form textarea,
    .inline-update-form select {
      width: 100%;
    }
    .muted-note {
      color: #5f6b63;
      font-size: 0.84rem;
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
    <?php if (isset($_GET['posted']) && $_GET['posted'] == '1') : ?>
      <div class="success-msg">Listing submitted successfully and is now waiting for admin approval.</div>
    <?php endif; ?>

    <h3>My Listings <a href="post-listing.php" class="btn-fl-small">+ Add New</a></h3>
    <p class="muted-note" style="margin-bottom:1rem;">Admin reviews each listing, sets buyer-facing pricing, and adds transport and platform fees.</p>

    <?php if (!$listings_result || mysqli_num_rows($listings_result) === 0) : ?>
      <p>You have no listings yet. <a href="post-listing.php">Post your first produce</a>.</p>
    <?php else : ?>
      <div class="table-wrapper">
        <table class="fl-table">
          <tr>
            <th>Produce</th>
            <th>Farmer Price</th>
            <th>Buyer Price</th>
            <th>Quantity</th>
            <th>Source</th>
            <th>Approval</th>
            <th>Admin Setup</th>
          </tr>
          <?php while ($row = mysqli_fetch_assoc($listings_result)) : ?>
            <tr>
              <td>
                <strong><?php echo marketplace_h($row['crop_name']); ?></strong><br>
                <span class="muted-note"><?php echo marketplace_h($row['category']); ?></span>
              </td>
              <td>KSh <?php echo marketplace_money($row['price']); ?> / <?php echo marketplace_h($row['unit']); ?></td>
              <td>
                <?php if ($row['approval_status'] === 'approved') : ?>
                  KSh <?php echo marketplace_money($row['approved_price']); ?> / <?php echo marketplace_h($row['unit']); ?>
                <?php else : ?>
                  Pending admin review
                <?php endif; ?>
              </td>
              <td><?php echo marketplace_quantity($row['quantity']); ?> <?php echo marketplace_h($row['unit']); ?></td>
              <td><?php echo marketplace_h($row['subcounty']); ?></td>
              <td>
                <span class="<?php echo marketplace_status_badge_class($row['approval_status']); ?>">
                  <?php echo marketplace_approval_label($row['approval_status']); ?>
                </span>
                <br><br>
                <span class="muted-note">Listing status: <?php echo marketplace_h($row['status']); ?></span>
              </td>
              <td>
                Transport fee: KSh <?php echo marketplace_money($row['admin_transport_fee']); ?><br>
                Platform fee: KSh <?php echo marketplace_money($row['admin_platform_fee']); ?><br>
                <?php if (!empty($row['admin_notes'])) : ?>
                  <span class="muted-note"><?php echo marketplace_h($row['admin_notes']); ?></span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <div class="fl-section">
    <h3>Orders for My Produce</h3>
    <p class="muted-note" style="margin-bottom:1rem;">Admin handles the buyer and transporter. You can still update order progress and report your remaining stock after delivery.</p>

    <?php if (!$orders_result || mysqli_num_rows($orders_result) === 0) : ?>
      <p>No orders yet.</p>
    <?php else : ?>
      <div class="table-wrapper">
        <table class="fl-table">
          <tr>
            <th>Order</th>
            <th>Produce</th>
            <th>Destination</th>
            <th>Amount Due to Farmer</th>
            <th>Buyer Payment</th>
            <th>My Payout</th>
            <th>Status</th>
            <th>Transport</th>
            <th>Update Status</th>
            <th>Stock Update</th>
          </tr>
          <?php while ($row = mysqli_fetch_assoc($orders_result)) : ?>
            <tr>
              <td>#<?php echo (int) $row['order_id']; ?></td>
              <td>
                <?php echo marketplace_h($row['crop_name']); ?><br>
                <span class="muted-note"><?php echo marketplace_quantity($row['quantity']); ?> <?php echo marketplace_h($row['unit']); ?></span>
              </td>
              <td><?php echo marketplace_h($row['destination_location'] ?: 'Pending destination'); ?></td>
              <td>KSh <?php echo marketplace_money($row['farmer_total']); ?></td>
              <td>
                <span class="<?php echo marketplace_status_badge_class($row['payment_status']); ?>">
                  <?php echo $row['payment_status'] === 'paid' ? 'Paid to Admin' : 'Awaiting Buyer Payment'; ?>
                </span>
              </td>
              <td>
                <span class="<?php echo marketplace_status_badge_class($row['farmer_payout_status']); ?>">
                  <?php echo $row['farmer_payout_status'] === 'disbursed' ? 'Disbursed' : 'Pending Admin Payout'; ?>
                </span>
              </td>
              <td>
                <span class="<?php echo marketplace_status_badge_class($row['current_status']); ?>">
                  <?php echo ucfirst(marketplace_h($row['current_status'])); ?>
                </span>
              </td>
              <td><?php echo !empty($row['transport_name']) ? marketplace_h($row['transport_name']) : 'Admin assigning transport'; ?></td>
              <td>
                <?php if ($row['current_status'] !== 'delivered') : ?>
                  <form method="post" class="inline-update-form">
                    <input type="hidden" name="order_id" value="<?php echo (int) $row['order_id']; ?>">
                    <select name="new_status">
                      <option value="pending" <?php echo $row['current_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                      <option value="dispatched" <?php echo $row['current_status'] === 'dispatched' ? 'selected' : ''; ?>>Dispatched</option>
                      <option value="delivered" <?php echo $row['current_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    </select>
                    <button type="submit" name="update_status" class="btn-fl-small">Update</button>
                  </form>
                <?php else : ?>
                  Completed
                <?php endif; ?>
              </td>
              <td>
                <?php if ($row['current_status'] === 'delivered') : ?>
                  <form method="post" class="inline-update-form">
                    <input type="hidden" name="order_id" value="<?php echo (int) $row['order_id']; ?>">
                    <input type="hidden" name="listing_id" value="<?php echo (int) $row['listing_id']; ?>">
                    <input type="number" step="0.01" min="0" name="remaining_quantity" placeholder="Remaining stock" required>
                    <textarea name="stock_note" rows="3" placeholder="Optional note for admin"></textarea>
                    <button type="submit" name="submit_stock_update" class="btn-fl-small">Send Stock Update</button>
                  </form>
                <?php else : ?>
                  Available after delivery
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
