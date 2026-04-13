<?php
$mysqli = new mysqli("localhost", "root", "", "bill_xml");
$result = $mysqli->query("SELECT id, ip_address, timestamp, data FROM ci_sessions ORDER BY timestamp DESC LIMIT 5");
while($row = $result->fetch_assoc()){
    echo "ID: " . $row['id'] . " IP: " . $row['ip_address'] . " TS: ". date('Y-m-d H:i:s', $row['timestamp']) . "\nData: " . $row['data'] . "\n\n";
}
?>
