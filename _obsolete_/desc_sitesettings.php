<?php
$mysqli = new mysqli('localhost', 'root', '', 'bill_xml');
$res = $mysqli->query("DESC db_sitesettings");
while($row = $res->fetch_assoc()){
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
