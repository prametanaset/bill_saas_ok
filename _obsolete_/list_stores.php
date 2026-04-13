<?php
$mysqli = new mysqli('localhost', 'root', '', 'bill_xml');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

$res = $mysqli->query("SELECT id, store_name, database_name, smtp_status FROM db_store");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}

$mysqli->close();
