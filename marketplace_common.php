<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function marketplace_db()
{
    static $db = null;

    if ($db instanceof mysqli) {
        return $db;
    }

    $config = require __DIR__ . '/database_config.php';

    $db = mysqli_connect(
        $config['host'],
        $config['username'],
        $config['password'],
        $config['database']
    );
    if (!$db) {
        die('Database connection failed: ' . mysqli_connect_error());
    }

    mysqli_set_charset($db, 'utf8mb4');

    return $db;
}

function marketplace_dashboard_url($role)
{
    if ($role === 'admin') {
        return 'admin-dashboard.php';
    }

    if ($role === 'farmer') {
        return 'dashboard-farmer.php';
    }

    return 'dashboard-buyer.php';
}

function marketplace_redirect_to_dashboard($role)
{
    header('location: ' . marketplace_dashboard_url($role));
    exit();
}

function marketplace_require_role($roles)
{
    if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
        header('location: login.php');
        exit();
    }

    $allowed_roles = is_array($roles) ? $roles : array($roles);

    if (!in_array($_SESSION['role'], $allowed_roles, true)) {
        marketplace_redirect_to_dashboard($_SESSION['role']);
    }
}

function marketplace_set_flash($key, $message)
{
    $_SESSION[$key] = $message;
}

function marketplace_pull_flash($key)
{
    if (!isset($_SESSION[$key])) {
        return '';
    }

    $message = $_SESSION[$key];
    unset($_SESSION[$key]);

    return $message;
}

function marketplace_h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function marketplace_execute($db, $sql, $types = '', $params = array())
{
    $stmt = mysqli_prepare($db, $sql);
    if (!$stmt) {
        return false;
    }

    if ($types !== '' && count($params) > 0) {
        $refs = array();
        foreach ($params as $key => $value) {
            $refs[$key] = &$params[$key];
        }
        mysqli_stmt_bind_param($stmt, $types, ...$refs);
    }

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return false;
    }

    return $stmt;
}

function marketplace_query_result($db, $sql, $types = '', $params = array())
{
    $stmt = marketplace_execute($db, $sql, $types, $params);
    if (!$stmt) {
        return false;
    }

    return mysqli_stmt_get_result($stmt);
}

function marketplace_money($value)
{
    return number_format((float) $value, 2);
}

function marketplace_quantity($value)
{
    return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
}

function marketplace_public_price($row)
{
    $approved_price = isset($row['approved_price']) ? (float) $row['approved_price'] : 0;
    $base_price = isset($row['price']) ? (float) $row['price'] : 0;

    return $approved_price > 0 ? $approved_price : $base_price;
}

function marketplace_status_badge_class($status)
{
    $normalized = strtolower((string) $status);

    if ($normalized === 'dispatched') {
        return 'status-dispatched';
    }

    if ($normalized === 'delivered' || $normalized === 'approved' || $normalized === 'paid' || $normalized === 'disbursed') {
        return 'status-delivered';
    }

    return 'status-pending';
}

function marketplace_approval_label($status)
{
    $normalized = strtolower((string) $status);

    if ($normalized === 'approved') {
        return 'Approved';
    }

    if ($normalized === 'rejected') {
        return 'Rejected';
    }

    return 'Pending Review';
}
?>
