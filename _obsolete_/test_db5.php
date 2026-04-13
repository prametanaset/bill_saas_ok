<?php
$mysqli = new mysqli("localhost", "root", "", "bill_xml_st0002");
$res = $mysqli->query("SELECT id, store_name, status FROM db_store");
while($r = $res->fetch_assoc()) {
    echo "Store ID: {$r['id']} | Name: {$r['store_name']} | Status: {$r['status']}\n";
}
echo "---\n";
$res2 = $mysqli->query("SELECT id, username, store_id, status FROM db_users WHERE id=2");
while($r2 = $res2->fetch_assoc()) {
    echo "User ID: {$r2['id']} | Name: {$r2['username']} | Store ID: {$r2['store_id']} | Status: {$r2['status']}\n";
}
?>
