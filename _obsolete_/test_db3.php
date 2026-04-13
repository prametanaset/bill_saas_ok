<?php
$mysqli = new mysqli("localhost", "root", "", "bill_xml_st0002");
$res = $mysqli->query("SELECT * FROM db_store");
if($res){
    echo "db_store present. Rows: " . $res->num_rows . "\n";
    print_r($res->fetch_assoc());
} else {
    echo "db_store query failed: " . $mysqli->error;
}
?>
