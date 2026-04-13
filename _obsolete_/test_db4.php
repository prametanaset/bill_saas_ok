<?php
$mysqli = new mysqli("localhost", "root", "", "bill_xml_st0002");
$res = $mysqli->query("SELECT id, username, email, status FROM db_users");
if($res){
    while($r = $res->fetch_assoc()) print_r($r);
} else {
    echo "db_users query failed: " . $mysqli->error;
}
?>
