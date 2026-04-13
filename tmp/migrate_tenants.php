<?php
$mysqli = new mysqli("localhost", "root", "", "bill_xml");
if ($mysqli->connect_error) {
    die("Connect Error (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}

$sql = "SELECT database_name FROM db_store WHERE database_name IS NOT NULL AND database_name != ''";
$result = $mysqli->query($sql);

$databases = ['bill_xml']; // Include current DB just in case, though it's already done
while ($row = $result->fetch_assoc()) {
    $databases[] = $row['database_name'];
}

foreach ($databases as $db) {
    echo "Processing $db...\n";
    $conn = new mysqli("localhost", "root", "", $db);
    if ($conn->connect_error) {
        echo "FAILED to connect to $db: " . $conn->connect_error . "\n";
        continue;
    }
    
    // Check if column already exists
    $check_column = $conn->query("SHOW COLUMNS FROM db_sales LIKE 'total_amount'");
    if ($check_column && $check_column->num_rows == 0) {
        $alter_sql = "ALTER TABLE db_sales ADD COLUMN total_amount DECIMAL(14,2) DEFAULT 0.00 AFTER vat_amt";
        if ($conn->query($alter_sql)) {
            echo "  Column added to $db.db_sales.\n";
        } else {
            echo "  FAILED to add column to $db: " . $conn->error . "\n";
        }
    } else {
        echo "  Column already exists in $db.db_sales.\n";
    }
    
    // Always update to be sure data is correct
    $update_sql = "UPDATE db_sales SET total_amount = grand_total - vat_amt";
    if ($conn->query($update_sql)) {
        echo "  Updated total_amount in $db.db_sales.\n";
    } else {
        echo "  FAILED to update data in $db: " . $conn->error . "\n";
    }
    
    $conn->close();
}
$mysqli->close();
echo "Migration complete.\n";
