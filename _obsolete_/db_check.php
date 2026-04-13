<?php
// Simple DB check script
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'bill_xml';

$link = mysqli_connect($hostname, $username, $password, $database);
if (!$link) {
    die("Connect Error: " . mysqli_connect_error());
}

function show_table($link, $table) {
    echo "\n--- Table: $table ---\n";
    $res = mysqli_query($link, "SHOW COLUMNS FROM $table");
    if (!$res) {
        echo "Error: " . mysqli_error($link) . "\n";
        return;
    }
    while ($row = mysqli_fetch_assoc($res)) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    echo "\nData (first 3):\n";
    $res = mysqli_query($link, "SELECT * FROM $table LIMIT 3");
    while ($row = mysqli_fetch_assoc($res)) {
        print_r($row);
    }
}

show_table($link, 'db_smtp');
show_table($link, 'db_store');

mysqli_close($link);
?>
