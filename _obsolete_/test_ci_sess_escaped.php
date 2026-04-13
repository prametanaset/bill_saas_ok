<?php
$mysqli = new mysqli("localhost", "root", "", "bill_xml_st0002");
$res = $mysqli->query("INSERT INTO `bill_xml`.`ci_sessions` (id, ip_address, timestamp, data) VALUES ('test123456', '127.0.0.1', 123456, 'test')");
if($res){
    echo "INSERT BACKTICK DBNAME.TABLENAME WORKS\n";
} else {
    echo "INSERT FAILED: " . $mysqli->error . "\n";
}

$res2 = $mysqli->query("INSERT INTO `bill_xml.ci_sessions` (id, ip_address, timestamp, data) VALUES ('test123457', '127.0.0.1', 123456, 'test')");
if($res2){
    echo "INSERT BACKTICK DBNAME.TABLENAME_LITERAL WORKS\n";
} else {
    echo "INSERT FAILED 2: " . $mysqli->error . "\n";
}
?>
