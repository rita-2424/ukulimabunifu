<?php
require_once 'admin_shared.php';
require_once 'admin_layout.php';

$db = marketplace_db();
admin_handle_marketplace_post($db, 'admin-orders.php');

$summary = admin_fetch_marketplace_summary($db);
$transport_options = admin_fetch_transport_options($db);
$flash_message = marketplace_pull_flash('admin_flash');

$orders_result = mysqli_query(
    $db,
    "SELECT o.order_id, o.quantity, o.produce_total, o.farmer_total, o.admin_markup_total, o.transport_fee, o.platform_fee,
            o.total, o.payment_status, o.payment_option, o.payment_reference, o.created_at, o.source_location,
            o.destination_location, o.transport_option_id, o.farmer_payout_status, o.farmer_payout_reference,
            o.transport_payout_status, o.transport_payout_reference,
            l.crop_name, l.unit,
            buyer.username AS buyer_name,
            farmer.username AS farmer_name,
            t.option_name AS transport_name,
            t.transport_type,
            COALESCE(os.status, 'pending') AS current_status
     FROM orders o
     JOIN listings l ON l.listing_id = o.listing_id
     JOIN users buyer ON buyer.user_id = o.buyer_id
     JOIN users farmer ON farmer.user_id = l.farmer_id
     LEFT JOIN transport_options t ON t.transport_option_id = o.transport_option_id
     LEFT JOIN order_status os
        ON os.order_id = o.order_id
       AND os.status_id = (
            SELECT MAX(status_id)
            FROM order_status
            WHERE order_id = o.order_id
       )
     ORDER BY o.created_at DESC
     LIMIT 20"
);

admin_page_start(
    'Orders & Payouts',
    'orders',
    'Track buyer payments, assign transport, and disburse amounts to farmers and delivery partners.',
    $summary
);
?>

<?php if ($flash_message !== '') : ?>
  <div class="success-msg"><?php echo marketplace_h($flash_message); ?></div>
<?php endif; ?>

<section class="admin-summary admin-summary-tight">
  <div class="summary-card">
    <h3>Buyer Payments</h3>
    <p>KSh <?php echo marketplace_money($summary['buyer_payments'] ?? 0); ?></p>
  </div>
  <div class="summary-card">
    <h3>Farmer Payouts Pending</h3>
    <p>KSh <?php echo marketplace_money($summary['pending_farmer_payouts'] ?? 0); ?></p>
  </div>
  <div class="summary-card">
    <h3>Transport Payouts Pending</h3>
    <p>KSh <?php echo marketplace_money($summary['pending_transport_payouts'] ?? 0); ?></p>
  </div>
  <div class="summary-card">
    <h3>Admin Revenue</h3>
    <p>KSh <?php echo marketplace_money($summary['admin_revenue'] ?? 0); ?></p>
  </div>
</section>

<section class="fl-section">
  <div class="section-head">
    <div>
      <h3>Orders, Transport, and Disbursements</h3>
      <p class="muted-note">Buyer payments come to admin first. You can then assign transport and release funds to the farmer and transporter.</p>
    </div>
  </div>

  <?php if (!$orders_result || mysqli_num_rows($orders_result) === 0) : ?>
    <p>No buyer orders yet.</p>
  <?php else : ?>
    <div class="table-wrapper">
      <table class="fl-table">
        <tr>
          <th>Order</th>
          <th>Route</th>
          <th>Buyer / Farmer</th>
          <th>Amounts</th>
          <th>Transport</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
        <?php while ($order = mysqli_fetch_assoc($orders_result)) : ?>
          <tr>
            <td>
              <strong>#<?php echo (int) $order['order_id']; ?></strong><br>
              <?php echo marketplace_h($order['crop_name']); ?><br>
              <span class="muted-note"><?php echo marketplace_quantity($order['quantity']); ?> <?php echo marketplace_h($order['unit']); ?></span>
            </td>
            <td class="route-text">
              <strong>From:</strong> <?php echo marketplace_h($order['source_location'] ?: 'Not set'); ?><br>
              <strong>To:</strong> <?php echo marketplace_h($order['destination_location'] ?: 'Not set'); ?><br>
              <strong>What:</strong> <?php echo marketplace_h($order['crop_name']); ?> (<?php echo marketplace_quantity($order['quantity']); ?> <?php echo marketplace_h($order['unit']); ?>)
            </td>
            <td>
              <strong>Buyer:</strong> <?php echo marketplace_h($order['buyer_name']); ?><br>
              <strong>Farmer:</strong> <?php echo marketplace_h($order['farmer_name']); ?>
            </td>
            <td class="route-text">
              <strong>Buyer total:</strong> KSh <?php echo marketplace_money($order['total']); ?><br>
              <strong>Farmer due:</strong> KSh <?php echo marketplace_money($order['farmer_total']); ?><br>
              <strong>Transport:</strong> KSh <?php echo marketplace_money($order['transport_fee']); ?><br>
              <strong>Markup + platform:</strong> KSh <?php echo marketplace_money(((float) $order['admin_markup_total']) + ((float) $order['platform_fee'])); ?>
            </td>
            <td class="route-text">
              <?php if (!empty($order['transport_name'])) : ?>
                <strong><?php echo marketplace_h($order['transport_name']); ?></strong><br>
                <span class="muted-note"><?php echo ucfirst(marketplace_h($order['transport_type'])); ?></span>
              <?php else : ?>
                <span class="muted-note">No transport assigned yet.</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="<?php echo marketplace_status_badge_class($order['payment_status']); ?>">
                Buyer <?php echo ucfirst(marketplace_h($order['payment_status'])); ?>
              </span>
              <br><br>
              <span class="<?php echo marketplace_status_badge_class($order['current_status']); ?>">
                <?php echo ucfirst(marketplace_h($order['current_status'])); ?>
              </span>
              <br><br>
              <span class="<?php echo marketplace_status_badge_class($order['farmer_payout_status']); ?>">
                Farmer <?php echo ucfirst(marketplace_h($order['farmer_payout_status'])); ?>
              </span>
              <br><br>
              <span class="<?php echo marketplace_status_badge_class($order['transport_payout_status']); ?>">
                Transport <?php echo ucfirst(marketplace_h($order['transport_payout_status'])); ?>
              </span>
            </td>
            <td>
              <div class="action-stack">
                <?php if ($order['payment_status'] !== 'paid') : ?>
                  <form method="post" class="inline-form">
                    <input type="hidden" name="order_id" value="<?php echo (int) $order['order_id']; ?>">
                    <label>
                      Buyer payment reference
                      <input type="text" name="payment_reference" placeholder="e.g. M-Pesa code or receipt number">
                    </label>
                    <button type="submit" name="record_payment" class="btn-fl">Mark Buyer Payment Received</button>
                  </form>
                <?php endif; ?>

                <form method="post" class="inline-form">
                  <input type="hidden" name="order_id" value="<?php echo (int) $order['order_id']; ?>">
                  <label>
                    Assign transport option
                    <select name="transport_option_id" required>
                      <option value="">Select transport option</option>
                      <?php foreach ($transport_options as $transport_option) : ?>
                        <option value="<?php echo (int) $transport_option['transport_option_id']; ?>" <?php echo ((int) $order['transport_option_id'] === (int) $transport_option['transport_option_id']) ? 'selected' : ''; ?>>
                          <?php echo marketplace_h($transport_option['option_name']); ?> (<?php echo marketplace_h($transport_option['transport_type']); ?>)
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </label>
                  <button type="submit" name="assign_transport" class="btn-fl">Save Transport</button>
                </form>

                <?php if ($order['payment_status'] === 'paid' && $order['farmer_payout_status'] !== 'disbursed') : ?>
                  <form method="post" class="inline-form">
                    <input type="hidden" name="order_id" value="<?php echo (int) $order['order_id']; ?>">
                    <div class="muted-note">Disburse KSh <?php echo marketplace_money($order['farmer_total']); ?> to the farmer.</div>
                    <input type="text" name="farmer_payout_reference" placeholder="Farmer payout reference">
                    <button type="submit" name="disburse_farmer" class="btn-fl">Disburse Farmer Payment</button>
                  </form>
                <?php endif; ?>

                <?php if ($order['payment_status'] === 'paid' && !empty($order['transport_option_id']) && $order['transport_payout_status'] !== 'disbursed') : ?>
                  <form method="post" class="inline-form">
                    <input type="hidden" name="order_id" value="<?php echo (int) $order['order_id']; ?>">
                    <div class="muted-note">Disburse KSh <?php echo marketplace_money($order['transport_fee']); ?> to the transporter.</div>
                    <input type="text" name="transport_payout_reference" placeholder="Transport payout reference">
                    <button type="submit" name="disburse_transport" class="btn-fl">Disburse Transport Payment</button>
                  </form>
                <?php endif; ?>

                <?php if (
                    $order['payment_status'] === 'paid' &&
                    $order['farmer_payout_status'] === 'disbursed' &&
                    ($order['transport_fee'] <= 0 || $order['transport_payout_status'] === 'disbursed')
                ) : ?>
                  <div class="muted-note">This order has already been settled.</div>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>
  <?php endif; ?>
</section>

<?php admin_page_end(); ?>
