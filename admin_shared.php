<?php
require_once 'marketplace_common.php';

marketplace_require_role('admin');

function admin_handle_marketplace_post($db, $redirect_url)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    if (isset($_POST['review_listing'])) {
        $admin_id = (int) $_SESSION['user_id'];
        $listing_id = (int) ($_POST['listing_id'] ?? 0);
        $action = $_POST['approval_action'] ?? 'approve';
        $approved_price = (float) ($_POST['approved_price'] ?? 0);
        $transport_fee = (float) ($_POST['transport_fee'] ?? 0);
        $platform_fee = (float) ($_POST['platform_fee'] ?? 0);
        $admin_notes = trim((string) ($_POST['admin_notes'] ?? ''));

        $listing_result = mysqli_query(
            $db,
            "SELECT listing_id, price FROM listings WHERE listing_id = $listing_id LIMIT 1"
        );
        $listing = $listing_result ? mysqli_fetch_assoc($listing_result) : null;

        if (!$listing) {
            marketplace_set_flash('admin_flash', 'Listing not found.');
        } elseif ($action === 'approve' && $approved_price < (float) $listing['price']) {
            marketplace_set_flash('admin_flash', 'Approved buyer price cannot be lower than the farmer price.');
        } elseif ($transport_fee < 0 || $platform_fee < 0) {
            marketplace_set_flash('admin_flash', 'Transport and platform fees must be zero or greater.');
        } else {
            $approval_status = $action === 'reject' ? 'rejected' : 'approved';
            $notes_sql = "'" . mysqli_real_escape_string($db, $admin_notes) . "'";

            mysqli_query(
                $db,
                "UPDATE listings
                 SET approval_status = '$approval_status',
                     approved_price = " . max($approved_price, (float) $listing['price']) . ",
                     admin_transport_fee = " . max($transport_fee, 0) . ",
                     admin_platform_fee = " . max($platform_fee, 0) . ",
                     approved_by = $admin_id,
                     approved_at = NOW(),
                     admin_notes = $notes_sql
                 WHERE listing_id = $listing_id"
            );

            marketplace_set_flash(
                'admin_flash',
                $approval_status === 'approved'
                    ? 'Listing approved and published to buyers.'
                    : 'Listing rejected and hidden from buyers.'
            );
        }

        header('location: ' . $redirect_url);
        exit();
    }

    if (isset($_POST['assign_transport'])) {
        $order_id = (int) ($_POST['order_id'] ?? 0);
        $transport_option_id = (int) ($_POST['transport_option_id'] ?? 0);

        if ($order_id <= 0 || $transport_option_id <= 0) {
            marketplace_set_flash('admin_flash', 'Choose a valid transport option before assigning delivery.');
        } else {
            mysqli_query(
                $db,
                "UPDATE orders
                 SET transport_option_id = $transport_option_id
                 WHERE order_id = $order_id"
            );
            marketplace_set_flash('admin_flash', 'Transport option assigned to the order.');
        }

        header('location: ' . $redirect_url);
        exit();
    }

    if (isset($_POST['record_payment'])) {
        $order_id = (int) ($_POST['order_id'] ?? 0);
        $reference = trim((string) ($_POST['payment_reference'] ?? ''));
        $reference = $reference !== '' ? $reference : ('ADMINPAY-' . $order_id . '-' . time());
        $reference_sql = mysqli_real_escape_string($db, $reference);

        mysqli_query(
            $db,
            "UPDATE orders
             SET payment_status = 'paid',
                 payment_reference = '$reference_sql',
                 paid_at = NOW()
             WHERE order_id = $order_id"
        );

        marketplace_set_flash('admin_flash', 'Buyer payment marked as received by admin.');
        header('location: ' . $redirect_url);
        exit();
    }

    if (isset($_POST['disburse_farmer'])) {
        $order_id = (int) ($_POST['order_id'] ?? 0);
        $reference = trim((string) ($_POST['farmer_payout_reference'] ?? ''));
        $reference = $reference !== '' ? $reference : ('FARMER-' . $order_id . '-' . time());
        $reference_sql = mysqli_real_escape_string($db, $reference);

        mysqli_query(
            $db,
            "UPDATE orders
             SET farmer_payout_status = 'disbursed',
                 farmer_payout_reference = '$reference_sql',
                 farmer_paid_at = NOW()
             WHERE order_id = $order_id AND payment_status = 'paid'"
        );

        marketplace_set_flash('admin_flash', 'Farmer payout recorded.');
        header('location: ' . $redirect_url);
        exit();
    }

    if (isset($_POST['disburse_transport'])) {
        $order_id = (int) ($_POST['order_id'] ?? 0);
        $reference = trim((string) ($_POST['transport_payout_reference'] ?? ''));
        $reference = $reference !== '' ? $reference : ('TRANSPORT-' . $order_id . '-' . time());
        $reference_sql = mysqli_real_escape_string($db, $reference);

        mysqli_query(
            $db,
            "UPDATE orders
             SET transport_payout_status = 'disbursed',
                 transport_payout_reference = '$reference_sql',
                 transport_paid_at = NOW()
             WHERE order_id = $order_id
               AND payment_status = 'paid'
               AND transport_option_id IS NOT NULL"
        );

        marketplace_set_flash('admin_flash', 'Transport payout recorded.');
        header('location: ' . $redirect_url);
        exit();
    }
}

function admin_fetch_marketplace_summary($db)
{
    $summary = array(
        'pending_listings' => 0,
        'approved_listings' => 0,
        'buyer_payments' => 0,
        'pending_farmer_payouts' => 0,
        'pending_transport_payouts' => 0,
        'admin_revenue' => 0
    );

    $listing_summary_result = mysqli_query(
        $db,
        "SELECT
            SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) AS pending_listings,
            SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) AS approved_listings
         FROM listings"
    );
    if ($listing_summary_result) {
        $summary = array_merge($summary, mysqli_fetch_assoc($listing_summary_result));
    }

    $payment_summary_result = mysqli_query(
        $db,
        "SELECT
            COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total ELSE 0 END), 0) AS buyer_payments,
            COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN admin_markup_total + platform_fee ELSE 0 END), 0) AS admin_revenue,
            COALESCE(SUM(CASE WHEN payment_status = 'paid' AND farmer_payout_status = 'pending' THEN farmer_total ELSE 0 END), 0) AS pending_farmer_payouts,
            COALESCE(SUM(CASE WHEN payment_status = 'paid' AND transport_payout_status = 'pending' THEN transport_fee ELSE 0 END), 0) AS pending_transport_payouts
         FROM orders"
    );
    if ($payment_summary_result) {
        $summary = array_merge($summary, mysqli_fetch_assoc($payment_summary_result));
    }

    return $summary;
}

function admin_fetch_transport_options($db)
{
    $transport_options = array();
    $transport_option_result = mysqli_query(
        $db,
        "SELECT transport_option_id, option_name, transport_type
         FROM transport_options
         WHERE status = 'active'
         ORDER BY transport_type ASC, option_name ASC"
    );

    if ($transport_option_result) {
        while ($option = mysqli_fetch_assoc($transport_option_result)) {
            $transport_options[] = $option;
        }
    }

    return $transport_options;
}
?>
