<?php
require_once 'admin_shared.php';
require_once 'admin_layout.php';

$db = marketplace_db();
$summary = admin_fetch_marketplace_summary($db);
$flash_message = marketplace_pull_flash('admin_flash');

$approved_listings_result = mysqli_query(
    $db,
    "SELECT l.listing_id, l.crop_name, l.category, l.price, l.approved_price, l.admin_transport_fee, l.admin_platform_fee,
            l.quantity, l.unit, l.subcounty, l.approved_at, u.username AS farmer_name
     FROM listings l
     JOIN users u ON u.user_id = l.farmer_id
     WHERE l.approval_status = 'approved' AND l.status = 'active'
     ORDER BY l.approved_at DESC, l.created_at DESC"
);

admin_page_start(
    'Approved Catalog',
    'catalog',
    'Review the produce that is already visible to buyers, together with the buyer-facing prices and attached admin fees.',
    $summary
);
?>

<?php if ($flash_message !== '') : ?>
  <div class="success-msg"><?php echo marketplace_h($flash_message); ?></div>
<?php endif; ?>

<section class="admin-summary admin-summary-tight">
  <div class="summary-card">
    <h3>Approved Listings</h3>
    <p><?php echo number_format((float) ($summary['approved_listings'] ?? 0)); ?></p>
  </div>
  <div class="summary-card">
    <h3>Pending Listings</h3>
    <p><?php echo number_format((float) ($summary['pending_listings'] ?? 0)); ?></p>
  </div>
  <div class="summary-card">
    <h3>Admin Revenue</h3>
    <p>KSh <?php echo marketplace_money($summary['admin_revenue'] ?? 0); ?></p>
  </div>
</section>

<section class="fl-section">
  <div class="section-head">
    <div>
      <h3>Recently Approved Catalog</h3>
      <p class="muted-note">These listings are already visible on buyer-facing pages without exposing farmer identity to the public catalog view.</p>
    </div>
  </div>

  <?php if (!$approved_listings_result || mysqli_num_rows($approved_listings_result) === 0) : ?>
    <p>No approved listings yet.</p>
  <?php else : ?>
    <div class="table-wrapper">
      <table class="fl-table">
        <tr>
          <th>Produce</th>
          <th>Farmer</th>
          <th>Farmer Price</th>
          <th>Buyer Price</th>
          <th>Fees</th>
          <th>Source</th>
        </tr>
        <?php while ($listing = mysqli_fetch_assoc($approved_listings_result)) : ?>
          <tr>
            <td><?php echo marketplace_h($listing['crop_name']); ?><br><span class="muted-note"><?php echo marketplace_h($listing['category']); ?></span></td>
            <td><?php echo marketplace_h($listing['farmer_name']); ?></td>
            <td>KSh <?php echo marketplace_money($listing['price']); ?> / <?php echo marketplace_h($listing['unit']); ?></td>
            <td>KSh <?php echo marketplace_money($listing['approved_price']); ?> / <?php echo marketplace_h($listing['unit']); ?></td>
            <td>
              Transport: KSh <?php echo marketplace_money($listing['admin_transport_fee']); ?><br>
              Platform: KSh <?php echo marketplace_money($listing['admin_platform_fee']); ?>
            </td>
            <td><?php echo marketplace_h($listing['subcounty']); ?></td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>
  <?php endif; ?>
</section>

<?php admin_page_end(); ?>
