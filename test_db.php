<?php
define('BASEPATH', 'dummy');
$db = new stdClass();
try {
    // Mimic the query in Subscription.php
    $mysqli = new mysqli("localhost", "root", "", "bill_xml");
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    
    $sql = "SELECT
                id, package_name, package_type, description,
                monthly_price, annual_price, trial_days, plan_type,
                max_warehouses, max_users, max_items, max_invoices, max_etax_emails
            FROM db_package
            WHERE store_id = 1
            LIMIT 1";
    
    $result = $mysqli->query($sql);
    if (!$result) {
        echo "Error in db_package: " . $mysqli->error . "\n";
    } else {
        echo "db_package OK\n";
    }

    $sql = "SELECT max_etax_emails FROM db_subscription LIMIT 1";
    $result = $mysqli->query($sql);
    if (!$result) {
        echo "Error in db_subscription: " . $mysqli->error . "\n";
    } else {
        echo "db_subscription OK\n";
    }
    
    $mysqli->close();
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
