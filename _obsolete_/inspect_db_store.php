<?php
$mysqli = new mysqli('localhost', 'root', '', 'bill_xml');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

$res = $mysqli->query("DESCRIBE db_store");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}

echo "--- Checking if smtp_status exists in db_store ---\n";
$res = $mysqli->query("SHOW COLUMNS FROM db_store LIKE 'smtp_status'");
if ($row = $res->fetch_assoc()) {
    echo "COLUMNS FOUND: " . print_r($row, true) . "\n";
} else {
    echo "COLUMNS NOT FOUND in bill_xml.db_store\n";
}

$mysqli->close();
