<?php
define('BASEPATH', 'debug');
require_once 'index.php'; // This might trigger a redirect or exit, so be careful.
// Instead of index.php, let's just use the CI logic if possible, 
// but since I can't easily, I'll just look at the database.php and use mysqli.

$db_config = [];
$db_file = 'application/config/database.php';
if (file_exists($db_file)) {
    // Mock enough of CI to load the config
    $active_group = 'default';
    $query_builder = TRUE;
    include $db_file;
    $db_config = $db['default'];
}

$conn = mysqli_connect($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Current Database: " . $db_config['database'] . "\n";

echo "\n--- db_store columns related to SMTP ---\n";
$res = mysqli_query($conn, "DESCRIBE db_store");
while($row = mysqli_fetch_assoc($res)) {
    if (stripos($row['Field'], 'smtp') !== false) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
}

echo "\n--- db_smtp columns ---\n";
$res = mysqli_query($conn, "DESCRIBE db_smtp");
if ($res) {
    while($row = mysqli_fetch_assoc($res)) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "db_smtp table not found.\n";
}

echo "\n--- Store Records ---\n";
$res = mysqli_query($conn, "SELECT id, store_name, smtp_status FROM db_store");
while($row = mysqli_fetch_assoc($res)) {
    echo "ID: " . $row['id'] . " | Name: " . $row['store_name'] . " | smtp_status: " . $row['smtp_status'] . "\n";
}

echo "\n--- SMTP Records ---\n";
$res = mysqli_query($conn, "SELECT * FROM db_smtp");
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}

mysqli_close($conn);
