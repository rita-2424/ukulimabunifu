document.addEventListener('DOMContentLoaded', function () {
  const payButton = document.getElementById('payButton');

  payButton.addEventListener('click', payWithPaystack);

  function payWithPaystack() {
    const ref = 'DON_' + Date.now();

    const handler = PaystackPop.setup({
      key: 'pk_test_80c4e32c87c2c7254b4fbbedb4efa0332a49ac15',
      email: 'customer@example.com',
      amount: 1000,
      currency: 'KES',
      ref: ref,
      label: "Donation Payment",

      callback: function (response) {
        // ✅ Redirect to quotation page
        window.location.href = "quotation.html";
      },

      onClose: function () {
        alert('Payment was not completed. You can try again.');
      }
    });

    handler.openIframe();
  }
});
