<?php
define('BASEPATH', 'foo');
require 'application/config/database.php';
$db_info = $db['default'];
$mysqli = new mysqli($db_info['hostname'], $db_info['username'], $db_info['password'], 'bill_xml');
$result = $mysqli->query("DESCRIBE db_subscription");
while($row = $result->fetch_assoc()){
    print_r($row);
}
$result = $mysqli->query("DESCRIBE db_offline_requests");
while($row = $result->fetch_assoc()){
    print_r($row);
}
