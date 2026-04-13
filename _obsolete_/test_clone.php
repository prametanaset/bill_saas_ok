<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // Assuming blank for Laragon default
$master_db = 'bill_xml';
$tenant_db = 'bill_xml_st_test_hang';

$mysqli = new mysqli($host, $user, $pass);
$mysqli->query("DROP DATABASE IF EXISTS `{$tenant_db}`");
$mysqli->query("CREATE DATABASE `{$tenant_db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

$start = microtime(true);
$mysqli->select_db($master_db);
$tables_query = $mysqli->query("SHOW TABLES");

while ($row = $tables_query->fetch_array()) {
    $table_name = $row[0];
    // Create new table natively via MySQL, which syncs indices instantly
    if(!$mysqli->query("CREATE TABLE `{$tenant_db}`.`{$table_name}` LIKE `{$master_db}`.`{$table_name}`")) {
        echo "Error creating {$table_name}: " . $mysqli->error . "\n";
    }
    // Copy data for essential tables (all of them for now as per previous logic)
    if(!$mysqli->query("INSERT INTO `{$tenant_db}`.`{$table_name}` SELECT * FROM `{$master_db}`.`{$table_name}`")) {
        echo "Error inserting into {$table_name}: " . $mysqli->error . "\n";
    }
}

echo "Finished cloning in " . round(microtime(true) - $start, 4) . " seconds.\n";
$mysqli->close();
