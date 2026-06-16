<?php
require_once 'admin_shared.php';
require_once 'admin_layout.php';

$db = marketplace_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_transport_option'])) {
        $option_name = trim((string) ($_POST['option_name'] ?? ''));
        $transport_type = trim((string) ($_POST['transport_type'] ?? ''));
        $owner_name = trim((string) ($_POST['owner_name'] ?? ''));
        $owner_phone = trim((string) ($_POST['owner_phone'] ?? ''));
        $notes = trim((string) ($_POST['notes'] ?? ''));

        if ($option_name === '' || $transport_type === '' || $owner_name === '') {
            marketplace_set_flash('transport_flash', 'Option name, transport type, and owner name are required.');
        } else {
            $option_name_sql = mysqli_real_escape_string($db, $option_name);
            $transport_type_sql = mysqli_real_escape_string($db, $transport_type);
            $owner_name_sql = mysqli_real_escape_string($db, $owner_name);
            $owner_phone_sql = mysqli_real_escape_string($db, $owner_phone);
            $notes_sql = mysqli_real_escape_string($db, $notes);

            mysqli_query(
                $db,
                "INSERT INTO transport_options (option_name, transport_type, owner_name, owner_phone, notes)
                 VALUES ('$option_name_sql', '$transport_type_sql', '$owner_name_sql', '$owner_phone_sql', '$notes_sql')"
            );

            marketplace_set_flash('transport_flash', 'Transport option added successfully.');
        }

        header('location: admin-transport.php');
        exit();
    }

    if (isset($_POST['toggle_transport_status'])) {
        $transport_option_id = (int) ($_POST['transport_option_id'] ?? 0);
        $new_status = $_POST['new_status'] ?? 'inactive';
        $new_status = $new_status === 'inactive' ? 'inactive' : 'active';

        mysqli_query(
            $db,
            "UPDATE transport_options
             SET status = '$new_status'
             WHERE transport_option_id = $transport_option_id"
        );

        marketplace_set_flash('transport_flash', 'Transport option status updated.');
        header('location: admin-transport.php');
        exit();
    }
}

$summary = admin_fetch_marketplace_summary($db);
$transport_summary = array(
    'active_options' => 0,
    'lorries' => 0,
    'motorbikes' => 0,
    'pending_transport_payouts' => 0
);

$option_summary_result = mysqli_query(
    $db,
    "SELECT
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_options,
        SUM(CASE WHEN LOWER(transport_type) = 'lorry' THEN 1 ELSE 0 END) AS lorries,
        SUM(CASE WHEN LOWER(transport_type) = 'motorbike' THEN 1 ELSE 0 END) AS motorbikes
     FROM transport_options"
);
if ($option_summary_result) {
    $transport_summary = array_merge($transport_summary, mysqli_fetch_assoc($option_summary_result));
}

$payout_summary_result = mysqli_query(
    $db,
    "SELECT COALESCE(SUM(CASE WHEN payment_status = 'paid' AND transport_payout_status = 'pending' THEN transport_fee ELSE 0 END), 0) AS pending_transport_payouts
     FROM orders"
);
if ($payout_summary_result) {
    $transport_summary = array_merge($transport_summary, mysqli_fetch_assoc($payout_summary_result));
}

$transport_options_result = mysqli_query(
    $db,
    "SELECT t.transport_option_id, t.option_name, t.transport_type, t.owner_name, t.owner_phone, t.status, t.notes, t.created_at,
            COUNT(o.order_id) AS assigned_orders,
            COALESCE(SUM(CASE WHEN o.transport_payout_status = 'disbursed' THEN o.transport_fee ELSE 0 END), 0) AS disbursed_total
     FROM transport_options t
     LEFT JOIN orders o ON o.transport_option_id = t.transport_option_id
     GROUP BY t.transport_option_id
     ORDER BY t.status DESC, t.transport_type ASC, t.option_name ASC"
);

$route_result = mysqli_query(
    $db,
    "SELECT o.order_id, l.crop_name, o.quantity, l.unit, o.source_location, o.destination_location,
            t.option_name, t.transport_type
     FROM orders o
     JOIN listings l ON l.listing_id = o.listing_id
     LEFT JOIN transport_options t ON t.transport_option_id = o.transport_option_id
     WHERE o.transport_option_id IS NOT NULL
     ORDER BY o.created_at DESC
     LIMIT 12"
);

$flash_message = marketplace_pull_flash('transport_flash');

admin_page_start(
    'Transport',
    'transport',
    'Maintain the delivery options admins assign to orders, and keep an eye on live route assignments across the marketplace.',
    $summary
);
?>

<?php if ($flash_message !== '') : ?>
  <div class="success-msg"><?php echo marketplace_h($flash_message); ?></div>
<?php endif; ?>

<section class="admin-summary admin-summary-tight">
  <div class="summary-card">
    <h3>Active Options</h3>
    <p><?php echo number_format((float) ($transport_summary['active_options'] ?? 0)); ?></p>
  </div>
  <div class="summary-card">
    <h3>Lorries</h3>
    <p><?php echo number_format((float) ($transport_summary['lorries'] ?? 0)); ?></p>
  </div>
  <div class="summary-card">
    <h3>Motorbikes</h3>
    <p><?php echo number_format((float) ($transport_summary['motorbikes'] ?? 0)); ?></p>
  </div>
  <div class="summary-card">
    <h3>Pending Transport Payouts</h3>
    <p>KSh <?php echo marketplace_money($transport_summary['pending_transport_payouts'] ?? 0); ?></p>
  </div>
</section>

<div class="transport-layout" style="margin-top:1.5rem;">
  <aside class="panel-card">
    <h3>Add Transport Option</h3>
    <p class="muted-note" style="margin-bottom:1rem;">Create delivery choices the admin can later assign to a specific order.</p>

    <form method="post">
      <label>
        Option name
        <input type="text" name="option_name" placeholder="e.g. Kiambu Lorry Team" required>
      </label>
      <label>
        Transport type
        <select name="transport_type" required>
          <option value="">Select type</option>
          <option value="lorry">Lorry</option>
          <option value="motorbike">Motorbike</option>
          <option value="pickup">Pickup</option>
          <option value="van">Van</option>
        </select>
      </label>
      <label>
        Owner name
        <input type="text" name="owner_name" placeholder="Transport owner or team lead" required>
      </label>
      <label>
        Owner phone
        <input type="text" name="owner_phone" placeholder="Optional phone number">
      </label>
      <label>
        Notes
        <textarea name="notes" rows="4" placeholder="Capacity, route coverage, or handling notes"></textarea>
      </label>
      <button type="submit" name="add_transport_option" class="btn-fl">Save Transport Option</button>
    </form>
  </aside>

  <section class="fl-section" style="margin:0;">
    <h3>Registered Transport Options</h3>
    <?php if (!$transport_options_result || mysqli_num_rows($transport_options_result) === 0) : ?>
      <p>No transport options configured yet.</p>
    <?php else : ?>
      <div class="table-wrapper">
        <table class="fl-table">
          <tr>
            <th>Option</th>
            <th>Owner</th>
            <th>Status</th>
            <th>Assigned Orders</th>
            <th>Transport Disbursed</th>
            <th>Notes</th>
            <th>Action</th>
          </tr>
          <?php while ($option = mysqli_fetch_assoc($transport_options_result)) : ?>
            <tr>
              <td>
                <strong><?php echo marketplace_h($option['option_name']); ?></strong><br>
                <span class="muted-note"><?php echo ucfirst(marketplace_h($option['transport_type'])); ?></span>
              </td>
              <td>
                <?php echo marketplace_h($option['owner_name']); ?><br>
                <span class="muted-note"><?php echo $option['owner_phone'] !== '' ? marketplace_h($option['owner_phone']) : 'No phone saved'; ?></span>
              </td>
              <td>
                <span class="<?php echo marketplace_status_badge_class($option['status'] === 'active' ? 'approved' : 'pending'); ?>">
                  <?php echo ucfirst(marketplace_h($option['status'])); ?>
                </span>
              </td>
              <td><?php echo number_format((float) $option['assigned_orders']); ?></td>
              <td>KSh <?php echo marketplace_money($option['disbursed_total']); ?></td>
              <td><?php echo $option['notes'] !== '' ? marketplace_h($option['notes']) : '<span class="muted-note">No notes</span>'; ?></td>
              <td>
                <form method="post">
                  <input type="hidden" name="transport_option_id" value="<?php echo (int) $option['transport_option_id']; ?>">
                  <input type="hidden" name="new_status" value="<?php echo $option['status'] === 'active' ? 'inactive' : 'active'; ?>">
                  <button type="submit" name="toggle_transport_status" class="btn-fl-small">
                    <?php echo $option['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                  </button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </table>
      </div>
    <?php endif; ?>
  </section>
</div>

<section class="fl-section">
  <div class="section-head">
    <div>
      <h3>Recent Assigned Routes</h3>
      <p class="muted-note">A quick view of where produce is moving and which transport option is handling the trip.</p>
    </div>
  </div>

  <?php if (!$route_result || mysqli_num_rows($route_result) === 0) : ?>
    <p>No routes assigned yet.</p>
  <?php else : ?>
    <div class="table-wrapper">
      <table class="fl-table">
        <tr>
          <th>Order</th>
          <th>Produce</th>
          <th>Route</th>
          <th>Transport</th>
        </tr>
        <?php while ($route = mysqli_fetch_assoc($route_result)) : ?>
          <tr>
            <td>#<?php echo (int) $route['order_id']; ?></td>
            <td><?php echo marketplace_h($route['crop_name']); ?> (<?php echo marketplace_quantity($route['quantity']); ?> <?php echo marketplace_h($route['unit']); ?>)</td>
            <td class="route-text">
              <strong>From:</strong> <?php echo marketplace_h($route['source_location'] ?: 'Not set'); ?><br>
              <strong>To:</strong> <?php echo marketplace_h($route['destination_location'] ?: 'Not set'); ?>
            </td>
            <td>
              <?php if (!empty($route['option_name'])) : ?>
                <strong><?php echo marketplace_h($route['option_name']); ?></strong><br>
                <span class="muted-note"><?php echo ucfirst(marketplace_h($route['transport_type'])); ?></span>
              <?php else : ?>
                <span class="muted-note">No option linked</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>
  <?php endif; ?>
</section>

<?php admin_page_end(); ?>
