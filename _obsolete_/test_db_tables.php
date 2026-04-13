<?php
$mysqli = new mysqli('localhost', 'root', '', 'bill_xml');
$res = $mysqli->query('SHOW TABLES');
while($row = $res->fetch_row()){
    echo $row[0] . "\n";
}
