<?php

$config = require __DIR__ . '/database_config.php';
$db = mysqli_connect($config['host'], $config['username'], $config['password'], $config['database']);
if (!$db) {
    die('DB connect error: ' . mysqli_connect_error());
}

$columns = array(
    "payment_option" => "ALTER TABLE `orders` ADD COLUMN `payment_option` ENUM('pay_now','pay_after_delivery') NOT NULL DEFAULT 'pay_now' AFTER `total`",
    "payment_status" => "ALTER TABLE `orders` ADD COLUMN `payment_status` ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid' AFTER `payment_option`",
    "payment_reference" => "ALTER TABLE `orders` ADD COLUMN `payment_reference` VARCHAR(100) NULL DEFAULT NULL AFTER `payment_status`",
    "paid_at" => "ALTER TABLE `orders` ADD COLUMN `paid_at` DATETIME NULL DEFAULT NULL AFTER `payment_reference`"
);

foreach ($columns as $column_name => $sql) {
    $res = mysqli_query($db, "SHOW COLUMNS FROM `orders` LIKE '$column_name'");

    if ($res && mysqli_num_rows($res) > 0) {
        echo "Column $column_name already exists.<br>";
        continue;
    }

    if (mysqli_query($db, $sql)) {
        echo "Column $column_name added successfully.<br>";
    } else {
        echo "Failed to add $column_name: " . mysqli_error($db) . "<br>";
    }
}

mysqli_close($db);
?>
