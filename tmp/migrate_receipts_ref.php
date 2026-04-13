<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$user = 'root';
$pass = ''; // Assuming standard Laragon setup
$db = 'bill_xml';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Get all tenant databases
$dbs = array($db); // Include master

$result = $conn->query("SELECT database_name FROM db_store WHERE database_name IS NOT NULL AND database_name != ''");
if ($result) {
    while($row = $result->fetch_assoc()) {
        $dbs[] = $row['database_name'];
    }
}

// 3. Loop and create
foreach($dbs as $target_db) {
    echo "Altering table in: $target_db <br>";
    $tenant_conn = new mysqli($host, $user, $pass, $target_db);
    if ($tenant_conn->connect_error) {
        echo "- Failed connecting to $target_db <br>";
        continue;
    }
    
    // Add reference_no
    $check_col = $tenant_conn->query("SHOW COLUMNS FROM `db_receipts` LIKE 'reference_no'");
    if ($check_col->num_rows == 0) {
        if ($tenant_conn->query("ALTER TABLE `db_receipts` ADD `reference_no` VARCHAR(50) DEFAULT NULL AFTER `receipt_date`;")) {
            echo "- Add reference_no success<br>";
        } else {
            echo "- Error adding reference_no: " . $tenant_conn->error . "<br>";
        }
    }

    $tenant_conn->close();
}

$conn->close();
echo "Migration complete.";
?>
