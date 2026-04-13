<?php
$host = "localhost";
$user = "root";
$pass = "";
$master_db = "bill_xml";

$mysqli = new mysqli($host, $user, $pass, $master_db);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// 1. Update Master DB
echo "Updating master DB...\n";
$mysqli->query("UPDATE db_paymenttypes SET payment_type='เงินสด' WHERE payment_type='Cash'");
$mysqli->query("UPDATE db_paymenttypes SET payment_type='เช็ค' WHERE payment_type='Cheque'");
$mysqli->query("UPDATE db_paymenttypes SET payment_type='บัตรเครดิต/เดบิต' WHERE payment_type='Card'");

// 2. Get all stores with tenant DBs
$result = $mysqli->query("SELECT id, database_name FROM db_store WHERE database_name IS NOT NULL AND database_name != ''");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $db_name = $row['database_name'];
        echo "Updating tenant DB: $db_name...\n";
        
        $tenant_mysqli = new mysqli($host, $user, $pass, $db_name);
        if ($tenant_mysqli->connect_error) {
            echo "Failed to connect to $db_name: " . $tenant_mysqli->connect_error . "\n";
            continue;
        }
        
        $tenant_mysqli->query("UPDATE db_paymenttypes SET payment_type='เงินสด' WHERE payment_type='Cash'");
        $tenant_mysqli->query("UPDATE db_paymenttypes SET payment_type='เช็ค' WHERE payment_type='Cheque'");
        $tenant_mysqli->query("UPDATE db_paymenttypes SET payment_type='บัตรเครดิต/เดบิต' WHERE payment_type='Card'");
        
        $tenant_mysqli->close();
    }
}

$mysqli->close();
echo "Migration finished.\n";
?>
