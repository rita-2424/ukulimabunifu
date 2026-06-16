<?php
require_once 'marketplace_common.php';

marketplace_require_role('buyer');

$db = marketplace_db();
$order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
$buyer_id = (int) $_SESSION['user_id'];

if ($order_id <= 0) {
    header('Location: dashboard-buyer.php');
    exit();
}

$order_query = marketplace_query_result(
    $db,
    "SELECT o.order_id, o.quantity, o.produce_total, o.transport_fee, o.platform_fee, o.total, o.payment_option,
            o.payment_status, o.payment_reference, o.source_location, o.destination_location,
            l.crop_name, l.unit,
            buyer.email AS buyer_email
     FROM orders o
     JOIN listings l ON l.listing_id = o.listing_id
     JOIN users buyer ON buyer.user_id = o.buyer_id
     WHERE o.order_id = ? AND o.buyer_id = ?
     LIMIT 1",
    "ii",
    array($order_id, $buyer_id)
);

if (!$order_query || mysqli_num_rows($order_query) === 0) {
    header('Location: dashboard-buyer.php');
    exit();
}

$order = mysqli_fetch_assoc($order_query);
$payment_reference = !empty($order['payment_reference']) ? $order['payment_reference'] : ('ORD_' . $order['order_id'] . '_' . time());
$payment_complete = isset($_GET['complete']) && $_GET['complete'] == '1';
$reference_from_callback = trim((string) ($_GET['reference'] ?? ''));

if ($payment_complete && $order['payment_status'] !== 'paid' && $reference_from_callback !== '') {
    marketplace_execute(
        $db,
        "UPDATE orders
         SET payment_status = 'paid',
             payment_reference = ?,
             paid_at = NOW()
         WHERE order_id = ? AND buyer_id = ?",
        "sii",
        array($reference_from_callback, $order_id, $buyer_id)
    );

    header('Location: dashboard-buyer.php?payment=success&order_id=' . $order_id);
    exit();
}

$payment_option_label = $order['payment_option'] === 'pay_after_delivery' ? 'Pay After Delivery' : 'Pay Now';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pay for Order #<?php echo (int) $order['order_id']; ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    body.payment-page {
      --background: #f7faf7;
      background: #f7faf7 !important;
      background-image: none !important;
      font-size: 15px;
    }

    .payment-page .container {
      padding: 1.5rem !important;
    }

    .payment-page h1 {
      font-size: 1.25rem;
    }

    .payment-page .description {
      font-size: 0.88rem;
      margin-bottom: 1rem;
    }

    .payment-page .price {
      font-size: 1.55rem;
      margin-bottom: 1.2rem;
    }

    .payment-page .payment-summary p {
      font-size: 0.86rem;
      line-height: 1.45;
      margin-bottom: 0.45rem;
    }

    .payment-page .pay-button,
    .payment-page .alt-pay-button {
      font-size: 0.86rem;
    }
  </style>
</head>
<body class="payment-page">

<?php include 'nav.php'; ?>

<div class="main">
  <div class="container" style="max-width:520px;margin:0 auto;padding:0 1rem;">
    <h1 style="text-align:center;">Complete Your Payment</h1>
    <p class="description">
      Order #<?php echo (int) $order['order_id']; ?> for <?php echo marketplace_h($order['crop_name']); ?>.<br>
    </p>
    <div class="price">KSh <?php echo marketplace_money($order['total']); ?></div>

    <div class="payment-summary" style="margin:1rem 0;background:#f7f7f7;padding:1rem;border-radius:12px;">
      <p style="text-align:center;"><strong>Produce:</strong> <?php echo marketplace_h($order['crop_name']); ?></p>
      <p style="text-align:center;"><strong>Quantity:</strong> <?php echo marketplace_quantity($order['quantity']); ?> <?php echo marketplace_h($order['unit']); ?></p>
      <p><strong>Source:</strong> <?php echo marketplace_h($order['source_location'] ?: 'Pending'); ?></p>
      <p><strong>Destination:</strong> <?php echo marketplace_h($order['destination_location'] ?: 'Pending'); ?></p>
      <p><strong>Produce subtotal:</strong> KSh <?php echo marketplace_money($order['produce_total']); ?></p>
      <p><strong>Transport fee:</strong> KSh <?php echo marketplace_money($order['transport_fee']); ?></p>
      <p><strong>Platform fee:</strong> KSh <?php echo marketplace_money($order['platform_fee']); ?></p>
      <p><strong>Payment Option:</strong> <?php echo marketplace_h($payment_option_label); ?></p>
      <p><strong>Payment Status:</strong> <span class="<?php echo marketplace_status_badge_class($order['payment_status']); ?>"><?php echo $order['payment_status'] === 'paid' ? 'Paid to Admin' : 'Unpaid'; ?></span></p>
    </div>

    <?php if ($order['payment_status'] === 'paid') : ?>
      <div class="success-msg">This order has already been paid.</div>
      <a href="dashboard-buyer.php" class="btn-fl" style="display:block;text-align:center;margin-top:1rem;">Back to My Orders</a>
    <?php else : ?>
      <button
        id="payButton"
        class="pay-button"
        type="button"
        data-email="<?php echo marketplace_h($order['buyer_email']); ?>"
        data-amount="<?php echo marketplace_h($order['total']); ?>"
        data-reference="<?php echo marketplace_h($payment_reference); ?>"
        data-label="Payment for Order #<?php echo (int) $order['order_id']; ?>"
        data-success-url="payment.php?order_id=<?php echo (int) $order['order_id']; ?>&complete=1"
      >
        Pay Order with M-Pesa
      </button>

      <div class="divider">or pay with</div>

      <div class="alternative-payments" style="display:flex;justify-content:center;gap:1rem;">
        <button id="paypalButton" class="alt-pay-button paypal" type="button">PayPal</button>
        <button id="stripeButton" class="alt-pay-button stripe" type="button">Stripe</button>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script src="https://www.paypal.com/sdk/js?client-id=YOUR_PAYPAL_CLIENT_ID"></script>
<script src="https://js.stripe.com/v3/"></script>
<script src="payment-order.js"></script>

</body>
</html>
