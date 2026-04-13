<?php
define('ENVIRONMENT', 'development');
require_once 'index.php'; // Load logic if possible, but might exit. 
// Better use raw PDO to check DB

$host = 'localhost';
$user = 'root';
$pass = ''; // Default for Laragon

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "--- MASTER DB (bill_xml) ---\n";
    $stmt = $pdo->query("SELECT * FROM bill_xml.db_smtp");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    $stmt = $pdo->query("SELECT id, store_name, smtp_status FROM bill_xml.db_store WHERE id IN (1, 4)");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    echo "\n--- TENANT DB (bill_xml_st0004) ---\n";
    try {
        $stmt = $pdo->query("SELECT * FROM bill_xml_st0004.db_smtp");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

        $stmt = $pdo->query("SELECT id, store_name, smtp_status FROM bill_xml_st0004.db_store");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo "Tenant DB error: " . $e->getMessage() . "\n";
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
