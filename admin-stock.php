<?php
require_once 'admin_shared.php';
require_once 'admin_layout.php';

$db = marketplace_db();
$summary = admin_fetch_marketplace_summary($db);
$flash_message = marketplace_pull_flash('admin_flash');

$stock_updates_result = mysqli_query(
    $db,
    "SELECT su.stock_update_id, su.remaining_quantity, su.note, su.created_at, su.order_id,
            l.crop_name, l.unit, u.username AS farmer_name
     FROM stock_updates su
     JOIN listings l ON l.listing_id = su.listing_id
     JOIN users u ON u.user_id = su.farmer_id
     ORDER BY su.created_at DESC"
);

admin_page_start(
    'Stock Updates',
    'stock',
    'Monitor inventory updates submitted by farmers so you know what can still be offered after partial or completed orders.',
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
</section>

<section class="fl-section">
  <div class="section-head">
    <div>
      <h3>Farmer Stock Updates</h3>
      <p class="muted-note">Farmers can report remaining stock after completed orders so you know what can still be offered to buyers.</p>
    </div>
  </div>

  <?php if (!$stock_updates_result || mysqli_num_rows($stock_updates_result) === 0) : ?>
    <p>No stock updates have been submitted yet.</p>
  <?php else : ?>
    <div class="table-wrapper">
      <table class="fl-table">
        <tr>
          <th>Farmer</th>
          <th>Order</th>
          <th>Produce</th>
          <th>Remaining Stock</th>
          <th>Note</th>
          <th>Submitted</th>
        </tr>
        <?php while ($update = mysqli_fetch_assoc($stock_updates_result)) : ?>
          <tr>
            <td><?php echo marketplace_h($update['farmer_name']); ?></td>
            <td>#<?php echo (int) $update['order_id']; ?></td>
            <td><?php echo marketplace_h($update['crop_name']); ?></td>
            <td><?php echo marketplace_quantity($update['remaining_quantity']); ?> <?php echo marketplace_h($update['unit']); ?></td>
            <td><?php echo !empty($update['note']) ? marketplace_h($update['note']) : '<span class="muted-note">No note</span>'; ?></td>
            <td><?php echo marketplace_h($update['created_at']); ?></td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>
  <?php endif; ?>
</section>

<?php admin_page_end(); ?>
