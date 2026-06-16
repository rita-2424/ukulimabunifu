<?php
$config = require __DIR__ . '/database_config.php';
$db = mysqli_connect($config['host'], $config['username'], $config['password'], $config['database']);

if (!$db) {
    die('DB connect error: ' . mysqli_connect_error());
}

mysqli_set_charset($db, 'utf8mb4');

function column_exists($db, $table, $column)
{
    $table = mysqli_real_escape_string($db, $table);
    $column = mysqli_real_escape_string($db, $column);
    $result = mysqli_query($db, "SHOW COLUMNS FROM `$table` LIKE '$column'");

    return $result && mysqli_num_rows($result) > 0;
}

function table_exists($db, $table)
{
    $table = mysqli_real_escape_string($db, $table);
    $result = mysqli_query($db, "SHOW TABLES LIKE '$table'");

    return $result && mysqli_num_rows($result) > 0;
}

function run_sql($db, $sql, $success_message)
{
    if (mysqli_query($db, $sql)) {
        echo $success_message . "<br>";
        return true;
    }

    echo "Failed: " . mysqli_error($db) . "<br>";
    return false;
}

run_sql(
    $db,
    "ALTER TABLE `users`
     MODIFY COLUMN `role` ENUM('farmer', 'buyer', 'admin') NOT NULL DEFAULT 'buyer'",
    "Ensured admin role is allowed in users.role."
);

$listing_columns = array(
    'approved_price' => "ALTER TABLE `listings` ADD COLUMN `approved_price` DECIMAL(10,2) NULL DEFAULT NULL AFTER `price`",
    'admin_transport_fee' => "ALTER TABLE `listings` ADD COLUMN `admin_transport_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `approved_price`",
    'admin_platform_fee' => "ALTER TABLE `listings` ADD COLUMN `admin_platform_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `admin_transport_fee`",
    'approval_status' => "ALTER TABLE `listings` ADD COLUMN `approval_status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending' AFTER `status`",
    'approved_by' => "ALTER TABLE `listings` ADD COLUMN `approved_by` INT(11) NULL DEFAULT NULL AFTER `approval_status`",
    'approved_at' => "ALTER TABLE `listings` ADD COLUMN `approved_at` DATETIME NULL DEFAULT NULL AFTER `approved_by`",
    'admin_notes' => "ALTER TABLE `listings` ADD COLUMN `admin_notes` TEXT NULL DEFAULT NULL AFTER `approved_at`"
);

foreach ($listing_columns as $column => $sql) {
    if (column_exists($db, 'listings', $column)) {
        echo "Column listings.$column already exists.<br>";
        continue;
    }

    run_sql($db, $sql, "Added listings.$column.");
}

$order_columns = array(
    'farmer_total' => "ALTER TABLE `orders` ADD COLUMN `farmer_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `produce_total`",
    'admin_markup_total' => "ALTER TABLE `orders` ADD COLUMN `admin_markup_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `farmer_total`",
    'source_location' => "ALTER TABLE `orders` ADD COLUMN `source_location` VARCHAR(100) NULL DEFAULT NULL AFTER `total`",
    'destination_location' => "ALTER TABLE `orders` ADD COLUMN `destination_location` VARCHAR(100) NULL DEFAULT NULL AFTER `source_location`",
    'transport_option_id' => "ALTER TABLE `orders` ADD COLUMN `transport_option_id` INT(11) NULL DEFAULT NULL AFTER `destination_location`",
    'farmer_payout_status' => "ALTER TABLE `orders` ADD COLUMN `farmer_payout_status` ENUM('pending', 'disbursed') NOT NULL DEFAULT 'pending' AFTER `paid_at`",
    'farmer_payout_reference' => "ALTER TABLE `orders` ADD COLUMN `farmer_payout_reference` VARCHAR(100) NULL DEFAULT NULL AFTER `farmer_payout_status`",
    'farmer_paid_at' => "ALTER TABLE `orders` ADD COLUMN `farmer_paid_at` DATETIME NULL DEFAULT NULL AFTER `farmer_payout_reference`",
    'transport_payout_status' => "ALTER TABLE `orders` ADD COLUMN `transport_payout_status` ENUM('pending', 'disbursed') NOT NULL DEFAULT 'pending' AFTER `farmer_paid_at`",
    'transport_payout_reference' => "ALTER TABLE `orders` ADD COLUMN `transport_payout_reference` VARCHAR(100) NULL DEFAULT NULL AFTER `transport_payout_status`",
    'transport_paid_at' => "ALTER TABLE `orders` ADD COLUMN `transport_paid_at` DATETIME NULL DEFAULT NULL AFTER `transport_payout_reference`"
);

foreach ($order_columns as $column => $sql) {
    if (column_exists($db, 'orders', $column)) {
        echo "Column orders.$column already exists.<br>";
        continue;
    }

    run_sql($db, $sql, "Added orders.$column.");
}

if (!table_exists($db, 'transport_options')) {
    run_sql(
        $db,
        "CREATE TABLE `transport_options` (
            `transport_option_id` INT(11) NOT NULL AUTO_INCREMENT,
            `option_name` VARCHAR(100) NOT NULL,
            `transport_type` VARCHAR(40) NOT NULL,
            `owner_name` VARCHAR(100) NOT NULL,
            `owner_phone` VARCHAR(30) DEFAULT NULL,
            `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            `notes` TEXT DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`transport_option_id`)
        )",
        "Created transport_options table."
    );
} else {
    echo "Table transport_options already exists.<br>";
}

if (!table_exists($db, 'stock_updates')) {
    run_sql(
        $db,
        "CREATE TABLE `stock_updates` (
            `stock_update_id` INT(11) NOT NULL AUTO_INCREMENT,
            `listing_id` INT(11) NOT NULL,
            `farmer_id` INT(11) NOT NULL,
            `order_id` INT(11) NOT NULL,
            `remaining_quantity` DECIMAL(10,2) NOT NULL,
            `note` TEXT DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`stock_update_id`)
        )",
        "Created stock_updates table."
    );
} else {
    echo "Table stock_updates already exists.<br>";
}

mysqli_query($db, "UPDATE `listings` SET `approved_price` = `price` WHERE `approved_price` IS NULL");
mysqli_query($db, "UPDATE `orders` SET `farmer_total` = `produce_total` WHERE `farmer_total` = 0");
mysqli_query($db, "UPDATE `orders` SET `admin_markup_total` = (`produce_total` - `farmer_total`) WHERE `admin_markup_total` = 0");
mysqli_query(
    $db,
    "UPDATE `orders` o
     JOIN `listings` l ON l.listing_id = o.listing_id
     SET o.source_location = l.subcounty
     WHERE (o.source_location IS NULL OR o.source_location = '')"
);
mysqli_query(
    $db,
    "UPDATE `orders` o
     JOIN `users` u ON u.user_id = o.buyer_id
     SET o.destination_location = u.county
     WHERE (o.destination_location IS NULL OR o.destination_location = '')"
);

$transport_count_result = mysqli_query($db, "SELECT COUNT(*) AS total FROM `transport_options`");
$transport_count = $transport_count_result ? (int) mysqli_fetch_assoc($transport_count_result)['total'] : 0;

if ($transport_count === 0) {
    mysqli_query(
        $db,
        "INSERT INTO `transport_options` (`option_name`, `transport_type`, `owner_name`, `owner_phone`, `notes`)
         VALUES
         ('Kiambu Lorry Fleet', 'lorry', 'Main Lorry Team', '', 'Suitable for bulk farm produce and longer delivery trips.'),
         ('Kiambu Motorbike Courier', 'motorbike', 'Main Rider Team', '', 'Suitable for lighter and urgent deliveries.')"
    );
    echo "Seeded default transport options.<br>";
}

echo "Admin marketplace migration completed.<br>";

mysqli_close($db);
?>
