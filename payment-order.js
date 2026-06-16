document.addEventListener('DOMContentLoaded', function () {
  const payButton = document.getElementById('payButton');
  const paypalButton = document.getElementById('paypalButton');
  const stripeButton = document.getElementById('stripeButton');

  if (!payButton) {
    return;
  }

  payButton.addEventListener('click', function () {
    const email = payButton.dataset.email || 'customer@example.com';
    const amountValue = parseFloat(payButton.dataset.amount || '0');
    const amount = Math.round(amountValue * 100);
    const ref = payButton.dataset.reference || ('ORD_' + Date.now());
    const label = payButton.dataset.label || 'Order Payment';
    const successUrl = payButton.dataset.successUrl || 'dashboard-buyer.php';

    if (!amount || amount < 100) {
      alert('Invalid payment amount for this order.');
      return;
    }

    if (typeof PaystackPop === 'undefined') {
      alert('Payment gateway could not load. Please try again.');
      return;
    }

    const handler = PaystackPop.setup({
      key: 'pk_test_80c4e32c87c2c7254b4fbbedb4efa0332a49ac15',
      email: email,
      amount: amount,
      currency: 'KES',
      ref: ref,
      label: label,
      callback: function (response) {
        window.location.href = successUrl + '&reference=' + encodeURIComponent(response.reference);
      },
      onClose: function () {
        alert('Payment was not completed. You can try again.');
      }
    });

    handler.openIframe();
  });

  if (paypalButton) {
    paypalButton.addEventListener('click', function () {
      alert('PayPal is not configured yet for order payments.');
    });
  }

  if (stripeButton) {
    stripeButton.addEventListener('click', function () {
      alert('Stripe is not configured yet for order payments.');
    });
  }
});