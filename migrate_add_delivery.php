<?php

$config = require __DIR__ . '/database_config.php';
$db = mysqli_connect($config['host'], $config['username'], $config['password'], $config['database']);
if (!$db) {
    die('DB connect error: ' . mysqli_connect_error());
}

$res = mysqli_query($db, "SHOW COLUMNS FROM `listings` LIKE 'delivery_available'");
if ($res && mysqli_num_rows($res) > 0) {
    echo "Column delivery_available already exists.\n";
    exit;
}

$sql = "ALTER TABLE `listings` ADD COLUMN `delivery_available` TINYINT(1) NOT NULL DEFAULT 0 AFTER `description`";
if (mysqli_query($db, $sql)) {
    echo "Column delivery_available added successfully.\n";
} else {
    echo "Failed to add column: " . mysqli_error($db) . "\n";
}

mysqli_close($db);

?>
