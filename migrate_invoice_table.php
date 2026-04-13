<?php
$mysqli = new mysqli("localhost", "root", "", "bill_saas");

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

// Add invoice_init to db_store
$sql1 = "ALTER TABLE db_store ADD COLUMN IF NOT EXISTS invoice_init varchar(100) DEFAULT 'IN'";
if ($mysqli->query($sql1)) {
    echo "Column invoice_init added/verified successfully.\n";
} else {
    echo "Error adding column: " . $mysqli->error . "\n";
}

// Create db_invoice table
$sql2 = "CREATE TABLE IF NOT EXISTS db_invoice (
  id int(11) NOT NULL AUTO_INCREMENT,
  count_id int(11) DEFAULT NULL,
  store_id int(11) DEFAULT NULL,
  sales_id int(11) DEFAULT NULL,
  invoice_code varchar(100) DEFAULT NULL,
  invoice_date date DEFAULT NULL,
  reference_no varchar(100) DEFAULT NULL,
  created_date date DEFAULT NULL,
  created_time varchar(20) DEFAULT NULL,
  created_by varchar(100) DEFAULT NULL,
  system_ip varchar(100) DEFAULT NULL,
  system_name varchar(100) DEFAULT NULL,
  status int(1) DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($sql2)) {
    echo "Table db_invoice created/verified successfully.\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}

// Initialize existing stores with 'IN' prefix if they don't have it
$sql3 = "UPDATE db_store SET invoice_init='IN' WHERE invoice_init IS NULL OR invoice_init=''";
if ($mysqli->query($sql3)) {
    echo "Initialized invoice_init for existing stores.\n";
}

$mysqli->close();
?>
