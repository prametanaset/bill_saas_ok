<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // Assuming blank for Laragon default
$db_name = 'bill_xml_st_test_hang';

$mysqli = new mysqli($host, $user, $pass);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$mysqli->query("DROP DATABASE IF EXISTS `{$db_name}`");
$mysqli->query("CREATE DATABASE `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$mysqli->select_db($db_name);

$sql_file = 'E:/laragon-8-xml/www/bill_xml_saas/bill_xml_fast.sql';
$sql_content = file_get_contents($sql_file);

// Executing raw


echo "Starting multi_query with optimizations...\n";
$start = microtime(true);

if ($mysqli->multi_query($sql_content)) {
    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());
    echo "Finished in " . round(microtime(true) - $start, 4) . " seconds.\n";
    if ($mysqli->error) {
        echo "Error at end: " . $mysqli->error . "\n";
    } else {
        echo "Success without errors.\n";
    }
} else {
    echo "Query Failed immediately: " . $mysqli->error . "\n";
}

$mysqli->close();
