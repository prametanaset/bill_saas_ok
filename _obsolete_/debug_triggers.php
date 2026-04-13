<?php
// Debug Triggers Script
$mysqli = new mysqli('localhost', 'root', '', 'bill_xml');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "--- bill_xml triggers ---\n";
$result = $mysqli->query("SHOW TRIGGERS");
if ($result) {
    while($row = $result->fetch_assoc()){
        echo "Trigger: " . $row['Trigger'] . " | Table: " . $row['Table'] . " | Event: " . $row['Event'] . " | Timing: " . $row['Timing'] . "\n";
        echo "Statement: " . $row['Statement'] . "\n\n";
    }
} else {
    echo "Error: " . $mysqli->error;
}

$mysqli->select_db('bill_xml_st0004');
echo "\n--- bill_xml_st0004 triggers ---\n";
$result = $mysqli->query("SHOW TRIGGERS");
if ($result) {
    while($row = $result->fetch_assoc()){
        echo "Trigger: " . $row['Trigger'] . " | Table: " . $row['Table'] . " | Event: " . $row['Event'] . " | Timing: " . $row['Timing'] . "\n";
        echo "Statement: " . $row['Statement'] . "\n\n";
    }
}

$mysqli->close();
?>
