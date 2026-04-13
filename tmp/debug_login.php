<?php
define('BASEPATH', 'dummy');
define('ENVIRONMENT', 'development');
require_once 'application/config/database.php';

$db_config = $db['default'];
$mysqli = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Structure of ac_transactions:\n";

$sql = "DESCRIBE ac_transactions";
$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "Field: " . $row["Field"]. " - Type: " . $row["Type"]. "\n";
    }
} else {
    echo "Table not found.\n";
}

echo "Structure of db_salespayments:\n";

$sql = "DESCRIBE db_salespayments";
$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "Field: " . $row["Field"]. " - Type: " . $row["Type"]. "\n";
    }
} else {
    echo "Table not found.\n";
}

$mysqli->close();
?>
