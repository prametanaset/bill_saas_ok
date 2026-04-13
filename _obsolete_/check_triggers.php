<?php
$conn = mysqli_connect("localhost", "root", "", "bill_xml");
if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }

echo "--- Triggers in bill_xml ---\n";
$res = mysqli_query($conn, "SHOW TRIGGERS");
if ($res) {
    while($row = mysqli_fetch_assoc($res)) {
        echo "Table: " . $row['Table'] . " | Trigger: " . $row['Trigger'] . " | Event: " . $row['Event'] . " | Timing: " . $row['Timing'] . "\n";
        echo "Statement: " . $row['Statement'] . "\n\n";
    }
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>
