<?php
require_once 'admin_shared.php';
require_once 'admin_layout.php';

$db = marketplace_db();
$summary = admin_fetch_marketplace_summary($db);
$flash_message = marketplace_pull_flash('admin_flash');

$pending_preview_result = mysqli_query(
    $db,
    "SELECT l.crop_name, l.subcounty, l.created_at, u.username AS farmer_name
     FROM listings l
     JOIN users u ON u.user_id = l.farmer_id
     WHERE l.approval_status = 'pending'
     ORDER BY l.created_at DESC
     LIMIT 5"
);

$orders_preview_result = mysqli_query(
    $db,
    "SELECT o.order_id, o.total, o.payment_status, l.crop_name, buyer.username AS buyer_name,
            COALESCE(os.status, 'pending') AS current_status
     FROM orders o
     JOIN listings l ON l.listing_id = o.listing_id
     JOIN users buyer ON buyer.user_id = o.buyer_id
     LEFT JOIN order_status os
        ON os.order_id = o.order_id
       AND os.status_id = (
            SELECT MAX(status_id)
            FROM order_status
            WHERE order_id = o.order_id
       )
     ORDER BY o.created_at DESC
     LIMIT 5"
);

$catalog_preview_result = mysqli_query(
    $db,
    "SELECT crop_name, category, approved_price, unit, subcounty
     FROM listings
     WHERE approval_status = 'approved' AND status = 'active'
     ORDER BY approved_at DESC, created_at DESC
     LIMIT 5"
);

$stock_preview_result = mysqli_query(
    $db,
    "SELECT su.remaining_quantity, su.created_at, l.crop_name, l.unit, u.username AS farmer_name
     FROM stock_updates su
     JOIN listings l ON l.listing_id = su.listing_id
     JOIN users u ON u.user_id = su.farmer_id
     ORDER BY su.created_at DESC
     LIMIT 5"
);

admin_page_start(
    'Admin Dashboard',
    'overview',
    'Use the sidebar to move between listing approvals, order handling, transport setup, and stock visibility.',
    $summary
);
?>

<?php if ($flash_message !== '') : ?>
  <div class="success-msg"><?php echo marketplace_h($flash_message); ?></div>
<?php endif; ?>

<section class="admin-summary admin-summary-tight">
  <div class="summary-card">
    <h3>Pending Listings</h3>
    <p><?php echo number_format((float) ($summary['pending_listings'] ?? 0)); ?></p>
  </div>
  <div class="summary-card">
    <h3>Approved Listings</h3>
    <p><?php echo number_format((float) ($summary['approved_listings'] ?? 0)); ?></p>
  </div>
  <div class="summary-card">
    <h3>Buyer Payments</h3>
    <p>KSh <?php echo marketplace_money($summary['buyer_payments'] ?? 0); ?></p>
  </div>
  <div class="summary-card">
    <h3>Admin Revenue</h3>
    <p>KSh <?php echo marketplace_money($summary['admin_revenue'] ?? 0); ?></p>
  </div>
</section>

<section class="admin-overview-grid">
  <article class="fl-section admin-overview-panel">
    <div class="section-head">
      <div>
        <h3>Listing Reviews</h3>
        <p class="muted-note">New farmer listings waiting for admin pricing and approval.</p>
      </div>
      <a href="admin-listings.php" class="pill-link">Open Reviews</a>
    </div>
    <?php if (!$pending_preview_result || mysqli_num_rows($pending_preview_result) === 0) : ?>
      <p>No listings are waiting for review.</p>
    <?php else : ?>
      <div class="table-wrapper">
        <table class="fl-table">
          <tr>
            <th>Produce</th>
            <th>Farmer</th>
            <th>Source</th>
            <th>Submitted</th>
          </tr>
          <?php while ($listing = mysqli_fetch_assoc($pending_preview_result)) : ?>
            <tr>
              <td><?php echo marketplace_h($listing['crop_name']); ?></td>
              <td><?php echo marketplace_h($listing['farmer_name']); ?></td>
              <td><?php echo marketplace_h($listing['subcounty']); ?></td>
              <td><?php echo marketplace_h($listing['created_at']); ?></td>
            </tr>
          <?php endwhile; ?>
        </table>
      </div>
    <?php endif; ?>
  </article>

  <article class="fl-section admin-overview-panel">
    <div class="section-head">
      <div>
        <h3>Orders & Payouts</h3>
        <p class="muted-note">Buyer collections, order progress, and farmer or transport disbursements.</p>
      </div>
      <a href="admin-orders.php" class="pill-link">Open Orders</a>
    </div>
    <?php if (!$orders_preview_result || mysqli_num_rows($orders_preview_result) === 0) : ?>
      <p>No orders have been placed yet.</p>
    <?php else : ?>
      <div class="table-wrapper">
        <table class="fl-table">
          <tr>
            <th>Order</th>
            <th>Buyer</th>
            <th>Total</th>
            <th>Status</th>
          </tr>
          <?php while ($order = mysqli_fetch_assoc($orders_preview_result)) : ?>
            <tr>
              <td>#<?php echo (int) $order['order_id']; ?> - <?php echo marketplace_h($order['crop_name']); ?></td>
              <td><?php echo marketplace_h($order['buyer_name']); ?></td>
              <td>KSh <?php echo marketplace_money($order['total']); ?></td>
              <td>
                <span class="<?php echo marketplace_status_badge_class($order['payment_status']); ?>">
                  <?php echo ucfirst(marketplace_h($order['payment_status'])); ?>
                </span>
                <div class="muted-note" style="margin-top:0.35rem;"><?php echo ucfirst(marketplace_h($order['current_status'])); ?></div>
              </td>
            </tr>
          <?php endwhile; ?>
        </table>
      </div>
    <?php endif; ?>
  </article>

  <article class="fl-section admin-overview-panel">
    <div class="section-head">
      <div>
        <h3>Approved Catalog</h3>
        <p class="muted-note">Produce that buyers can already browse in the marketplace.</p>
      </div>
      <a href="admin-catalog.php" class="pill-link">Open Catalog</a>
    </div>
    <?php if (!$catalog_preview_result || mysqli_num_rows($catalog_preview_result) === 0) : ?>
      <p>No approved listings yet.</p>
    <?php else : ?>
      <div class="table-wrapper">
        <table class="fl-table">
          <tr>
            <th>Produce</th>
            <th>Category</th>
            <th>Buyer Price</th>
            <th>Source</th>
          </tr>
          <?php while ($listing = mysqli_fetch_assoc($catalog_preview_result)) : ?>
            <tr>
              <td><?php echo marketplace_h($listing['crop_name']); ?></td>
              <td><?php echo marketplace_h($listing['category']); ?></td>
              <td>KSh <?php echo marketplace_money($listing['approved_price']); ?> / <?php echo marketplace_h($listing['unit']); ?></td>
              <td><?php echo marketplace_h($listing['subcounty']); ?></td>
            </tr>
          <?php endwhile; ?>
        </table>
      </div>
    <?php endif; ?>
  </article>

  <article class="fl-section admin-overview-panel">
    <div class="section-head">
      <div>
        <h3>Stock Updates</h3>
        <p class="muted-note">Recent inventory updates submitted by farmers after orders are fulfilled.</p>
      </div>
      <a href="admin-stock.php" class="pill-link">Open Stock</a>
    </div>
    <?php if (!$stock_preview_result || mysqli_num_rows($stock_preview_result) === 0) : ?>
      <p>No stock updates have been submitted yet.</p>
    <?php else : ?>
      <div class="table-wrapper">
        <table class="fl-table">
          <tr>
            <th>Farmer</th>
            <th>Produce</th>
            <th>Remaining</th>
            <th>Submitted</th>
          </tr>
          <?php while ($update = mysqli_fetch_assoc($stock_preview_result)) : ?>
            <tr>
              <td><?php echo marketplace_h($update['farmer_name']); ?></td>
              <td><?php echo marketplace_h($update['crop_name']); ?></td>
              <td><?php echo marketplace_quantity($update['remaining_quantity']); ?> <?php echo marketplace_h($update['unit']); ?></td>
              <td><?php echo marketplace_h($update['created_at']); ?></td>
            </tr>
          <?php endwhile; ?>
        </table>
      </div>
    <?php endif; ?>
  </article>
</section>

<?php admin_page_end(); ?>
