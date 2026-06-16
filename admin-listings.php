<?php
require_once 'admin_shared.php';
require_once 'admin_layout.php';

$db = marketplace_db();
admin_handle_marketplace_post($db, 'admin-listings.php');

$summary = admin_fetch_marketplace_summary($db);
$flash_message = marketplace_pull_flash('admin_flash');

$pending_listings_result = mysqli_query(
    $db,
    "SELECT l.listing_id, l.crop_name, l.category, l.price, l.approved_price, l.admin_transport_fee, l.admin_platform_fee,
            l.quantity, l.unit, l.subcounty, l.description, l.admin_notes, l.approval_status, l.created_at,
            u.username AS farmer_name, u.phone AS farmer_phone
     FROM listings l
     JOIN users u ON u.user_id = l.farmer_id
     WHERE l.approval_status = 'pending'
     ORDER BY l.created_at DESC"
);

admin_page_start(
    'Listing Reviews',
    'listings',
    'Approve farmer listings, set the buyer-facing price, and add admin transport or platform fees before publishing.',
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
    <h3>Admin Revenue</h3>
    <p>KSh <?php echo marketplace_money($summary['admin_revenue'] ?? 0); ?></p>
  </div>
</section>

<section class="fl-section">
  <div class="section-head">
    <div>
      <h3>Farmer Listings Awaiting Review</h3>
      <p class="muted-note">Approve the listing, publish a buyer price, or reject it if it should stay off the marketplace.</p>
    </div>
  </div>

  <?php if (!$pending_listings_result || mysqli_num_rows($pending_listings_result) === 0) : ?>
    <p>No listings are waiting for review.</p>
  <?php else : ?>
    <div class="table-wrapper">
      <table class="fl-table">
        <tr>
          <th>Produce</th>
          <th>Farmer</th>
          <th>Farmer Price</th>
          <th>Stock</th>
          <th>Source</th>
          <th>Approval Action</th>
        </tr>
        <?php while ($listing = mysqli_fetch_assoc($pending_listings_result)) : ?>
          <tr>
            <td>
              <strong><?php echo marketplace_h($listing['crop_name']); ?></strong><br>
              <span class="muted-note"><?php echo marketplace_h($listing['category']); ?></span>
              <?php if (!empty($listing['description'])) : ?>
                <div class="muted-note" style="margin-top:0.4rem;"><?php echo marketplace_h($listing['description']); ?></div>
              <?php endif; ?>
            </td>
            <td>
              <?php echo marketplace_h($listing['farmer_name']); ?><br>
              <span class="muted-note"><?php echo marketplace_h($listing['farmer_phone']); ?></span>
            </td>
            <td>KSh <?php echo marketplace_money($listing['price']); ?> / <?php echo marketplace_h($listing['unit']); ?></td>
            <td><?php echo marketplace_quantity($listing['quantity']); ?> <?php echo marketplace_h($listing['unit']); ?></td>
            <td><?php echo marketplace_h($listing['subcounty']); ?></td>
            <td>
              <form method="post" class="inline-form">
                <input type="hidden" name="listing_id" value="<?php echo (int) $listing['listing_id']; ?>">
                <label>
                  Buyer price
                  <input type="number" step="0.01" min="<?php echo marketplace_h($listing['price']); ?>" name="approved_price" value="<?php echo marketplace_h($listing['approved_price']); ?>" required>
                </label>
                <label>
                  Transport fee
                  <input type="number" step="0.01" min="0" name="transport_fee" value="<?php echo marketplace_h($listing['admin_transport_fee']); ?>" required>
                </label>
                <label>
                  Platform fee
                  <input type="number" step="0.01" min="0" name="platform_fee" value="<?php echo marketplace_h($listing['admin_platform_fee']); ?>" required>
                </label>
                <label>
                  Admin note
                  <textarea name="admin_notes" placeholder="Optional note for admin records"><?php echo marketplace_h($listing['admin_notes']); ?></textarea>
                </label>
                <label>
                  Decision
                  <select name="approval_action">
                    <option value="approve">Approve and publish</option>
                    <option value="reject">Reject listing</option>
                  </select>
                </label>
                <button type="submit" name="review_listing" class="btn-fl">Save Review</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>
  <?php endif; ?>
</section>

<?php admin_page_end(); ?>
