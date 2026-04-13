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

// 2. The table creation SQL
$sql = "CREATE TABLE IF NOT EXISTS `db_receipts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `count_id` int(11) NOT NULL DEFAULT '0',
  `store_id` int(11) NOT NULL,
  `sales_id` int(11) NOT NULL,
  `receipt_code` varchar(50) NOT NULL,
  `receipt_date` date NOT NULL,
  `created_date` date NOT NULL,
  `created_time` varchar(20) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `system_ip` varchar(50) DEFAULT NULL,
  `system_name` varchar(50) DEFAULT NULL,
  `status` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

// 3. Loop and create
foreach($dbs as $target_db) {
    echo "Creating table in: $target_db <br>";
    $tenant_conn = new mysqli($host, $user, $pass, $target_db);
    if ($tenant_conn->connect_error) {
        echo "- Failed connecting to $target_db <br>";
        continue;
    }
    
    // Add receipt_init column to db_store if not exists
    $tenant_conn->query("ALTER TABLE `db_store` ADD `receipt_init` VARCHAR(20) DEFAULT 'RC-' AFTER `sales_return_init`;");

    if ($tenant_conn->query($sql) === TRUE) {
        echo "- Table created successfully<br>";
    } else {
        echo "- Error creating table: " . $tenant_conn->error . "<br>";
    }
    
    // Set default receipt_init = 'RC-' for existing stores
    $tenant_conn->query("UPDATE `db_store` SET `receipt_init` = 'RC-' WHERE `receipt_init` IS NULL OR `receipt_init` = '';");

    $tenant_conn->close();
}

$conn->close();
echo "Migration complete.";
?>
