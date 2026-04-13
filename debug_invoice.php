<?php
include "application/config/database.php";
$dsn = 'mysql:host='.$db['default']['hostname'].';dbname='.$db['default']['database'];
try {
    $pdo = new PDO($dsn, $db['default']['username'], $db['default']['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Checking db_invoice table contents:\n";
    $stmt = $pdo->query("SELECT * FROM db_invoice");
    while ($row = $stmt->fetch(PDO::ATTR_ASSOC)) {
        print_r($row);
    }
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
