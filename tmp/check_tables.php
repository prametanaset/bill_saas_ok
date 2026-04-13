<?php
define('BASEPATH', 'dummy');
define('ENVIRONMENT', 'development');
require_once('application/config/database.php');
$dsn = "mysql:host=" . $db['default']['hostname'] . ";dbname=" . $db['default']['database'];
try {
    $pdo = new PDO($dsn, $db['default']['username'], $db['default']['password']);
    echo "db_sales: ";
    $stmt = $pdo->query("DESC db_sales");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    print_r($cols);
    
    echo "\ndb_salesreturn: ";
    $stmt = $pdo->query("DESC db_salesreturn");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    print_r($cols);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
