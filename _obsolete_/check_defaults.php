<?php
$m = new mysqli('localhost', 'root', '', 'bill_xml');
$tables = ['db_tax', 'db_units', 'db_paymenttypes', 'db_customers'];
foreach($tables as $t){
    echo "\n--- Table: $t ---\n";
    $res = $m->query("SELECT * FROM $t WHERE store_id=1 LIMIT 5");
    if($res){
        while($row = $res->fetch_assoc()){
            print_r($row);
        }
    } else {
        echo "Query failed or table not found.\n";
    }
}
