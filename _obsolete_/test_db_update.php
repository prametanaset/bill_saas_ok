<?php
$mysqli = new mysqli('localhost', 'root', '', 'bill_xml');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

echo "--- Master (bill_xml) ---\n";
$res = $mysqli->query("SELECT * FROM db_smtp");
if ($res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "db_smtp is EMPTY in Master\n";
}

$res = $mysqli->query("SELECT database_name FROM db_store WHERE id = 1");
if ($row = $res->fetch_assoc()) {
    $db_name = $row['database_name'];
    echo "\n--- Tenant ($db_name) ---\n";
    $mysqli->select_db($db_name);
    $res2 = $mysqli->query("SELECT * FROM db_smtp");
    if ($res2 && $res2->num_rows > 0) {
        while($row2 = $res2->fetch_assoc()) {
            print_r($row2);
        }
    } else {
        echo "db_smtp is EMPTY in Tenant ($db_name)\n";
    }
}
$mysqli->close();
