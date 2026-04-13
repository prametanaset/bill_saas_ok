<?php
define('BASEPATH', 'dummy');
define('ENVIRONMENT', 'development');
require_once 'application/config/database.php';

$db_config = $db['default'];
$mysqli = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$tenant_db = 'bill_xml_st0005';
$mysqli_tenant = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $tenant_db);

if ($mysqli_tenant->connect_error) {
    die("Connection to tenant DB failed: " . $mysqli_tenant->connect_error);
}

$user_id = 201;
echo "Restoring User ID: $user_id from $tenant_db to bill_xml\n";

$sql = "SELECT * FROM db_users WHERE id = $user_id";
$result = $mysqli_tenant->query($sql);

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    
    // Build insert query
    $columns = array_keys($user_data);
    $values = array_values($user_data);
    
    $escaped_values = array_map(function($val) use ($mysqli) {
        if ($val === null) return 'NULL';
        return "'" . $mysqli->real_escape_string($val) . "'";
    }, $values);
    
    $sql_insert = "INSERT INTO db_users (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $escaped_values) . ")";
    
    if ($mysqli->query($sql_insert)) {
        echo "Successfully restored user ID $user_id to master DB.\n";
    } else {
        echo "Error restoring user: " . $mysqli->error . "\n";
    }
} else {
    echo "User ID $user_id not found in tenant DB $tenant_db\n";
}

$mysqli->close();
$mysqli_tenant->close();
?>
