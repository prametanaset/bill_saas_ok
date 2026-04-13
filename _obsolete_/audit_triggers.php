<?php
$mysqli = new mysqli("localhost", "root", "", "bill_xml");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "--- TRIGGERS in MASTER DB (bill_xml) ---\n";
$result = $mysqli->query("SHOW TRIGGERS");
while($row = $result->fetch_assoc()) {
    echo "Trigger: " . $row['Trigger'] . " | Event: " . $row['Event'] . " | Table: " . $row['Table'] . "\n";
    echo "Statement: " . $row['Statement'] . "\n---\n";
}

$mysqli->close();
?>
