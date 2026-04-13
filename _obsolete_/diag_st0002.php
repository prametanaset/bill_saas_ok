<?php
$mysqli = new mysqli('localhost', 'root', '', 'bill_xml');
$res = $mysqli->query("SELECT id, store_code, store_name FROM db_store WHERE store_code='ST0002'");
if($row = $res->fetch_assoc()){
    echo "Master DB ST0002: " . print_r($row, true) . "\n";
} else {
    echo "Master DB ST0002 not found\n";
}

$mysqli_tenant = new mysqli('localhost', 'root', '', 'bill_xml_st0002');
if($mysqli_tenant->connect_error){
    echo "Tenant DB bill_xml_st0002 connection error\n";
} else {
    $res = $mysqli_tenant->query("SELECT id, store_code, store_name FROM db_store");
    while($row = $res->fetch_assoc()){
        echo "Tenant DB ST0002 Store: " . print_r($row, true) . "\n";
    }
    
    $res = $mysqli_tenant->query("SELECT id, username, email FROM db_users");
    while($row = $res->fetch_assoc()){
        echo "Tenant DB ST0002 User: " . print_r($row, true) . "\n";
    }
}
