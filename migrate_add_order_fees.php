<?php

$config = require __DIR__ . '/database_config.php';
$db = mysqli_connect($config['host'], $config['username'], $config['password'], $config['database']);
if (!$db) {
    die('DB connect error: ' . mysqli_connect_error());
}

$columns = array(
    "produce_total" => "ALTER TABLE `orders` ADD COLUMN `produce_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `quantity`",
    "transport_fee" => "ALTER TABLE `orders` ADD COLUMN `transport_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `produce_total`",
    "platform_fee" => "ALTER TABLE `orders` ADD COLUMN `platform_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `transport_fee`"
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

mysqli_query($db, "UPDATE `orders` SET `produce_total` = `total` WHERE `produce_total` = 0");

mysqli_close($db);
?>
