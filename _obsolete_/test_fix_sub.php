<?php
$mysqli = new mysqli("localhost", "root", "", "bill_xml");

echo "<pre>";
echo "=== Database Diagnostics ===\n\n";

// 1. Check db_offline_requests table
$r = $mysqli->query("SHOW TABLES LIKE 'db_offline_requests'");
echo "1. db_offline_requests: " . ($r->num_rows > 0 ? "EXISTS ✓" : "MISSING ✗") . "\n";

if ($r->num_rows == 0) {
    // Create the table
    $sql = "CREATE TABLE `db_offline_requests` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `store_id` int(11) DEFAULT NULL,
        `package_id` int(11) DEFAULT NULL,
        `plan_type` varchar(50) DEFAULT NULL,
        `amount` decimal(15,2) DEFAULT NULL,
        `payment_slip` varchar(255) DEFAULT NULL,
        `status` tinyint(1) DEFAULT '0' COMMENT '0=Pending,1=Approved,2=Rejected',
        `created_date` date DEFAULT NULL,
        `created_time` time DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($mysqli->query($sql)) {
        echo "   ✓ Table db_offline_requests CREATED successfully!\n";
    } else {
        echo "   ✗ Error creating table: " . $mysqli->error . "\n";
    }
} else {
    $r2 = $mysqli->query("SELECT COUNT(*) as cnt FROM db_offline_requests");
    $cnt = $r2->fetch_assoc()['cnt'];
    echo "   Existing rows: $cnt\n";
    $r3 = $mysqli->query("SELECT * FROM db_offline_requests ORDER BY id DESC LIMIT 5");
    while($row = $r3->fetch_assoc()) { print_r($row); }
}

// 2. Check store 6 subscription
echo "\n2. Store #6 details:\n";
$r4 = $mysqli->query("SELECT id, store_name, current_subscriptionlist_id, status FROM db_store WHERE id=6");
$s = $r4->fetch_assoc();
echo "   Store: {$s['store_name']}, Status: {$s['status']}, current_subscriptionlist_id: {$s['current_subscriptionlist_id']}\n";

if ($s['current_subscriptionlist_id']) {
    $r5 = $mysqli->query("SELECT * FROM db_subscription WHERE id=" . (int)$s['current_subscriptionlist_id']);
    $sub = $r5->fetch_assoc();
    echo "   Subscription expire_date: {$sub['expire_date']}\n";
    echo "   Today: " . date('Y-m-d') . "\n";
    if (!empty($sub) && $sub['expire_date'] < date('Y-m-d')) {
        echo "   STATUS: EXPIRED ✗\n";
    } else if (!empty($sub)) {
        echo "   STATUS: ACTIVE ✓\n";
    } else {
        echo "   STATUS: NO SUBSCRIPTION ✗\n";
    }
}

// 3. Show packages
echo "\n3. Available packages:\n";
$r6 = $mysqli->query("SELECT id, package_name, package_type, monthly_price, annual_price, max_invoices FROM db_package LIMIT 5");
while ($pkg = $r6->fetch_assoc()) {
    echo "   id={$pkg['id']} {$pkg['package_name']} ({$pkg['package_type']}) price={$pkg['monthly_price']}\n";
}

// 4. Action: Create/extend subscription for store 6 (extended 1 year)
echo "\n4. ACTION: Extending subscription for Store 6...\n";

// Find existing subscription
$pkg_id_free = null;
if ($s['current_subscriptionlist_id']) {
    $r_sub = $mysqli->query("SELECT * FROM db_subscription WHERE id=" . (int)$s['current_subscriptionlist_id']);
    $existing_sub = $r_sub->fetch_assoc();
    if ($existing_sub) {
        $new_expire = date('Y-m-d', strtotime('+1 year'));
        $mysqli->query("UPDATE db_subscription SET expire_date='$new_expire', payment_status='Paid', status=1, package_status='Active' WHERE id=" . (int)$s['current_subscriptionlist_id']);
        echo "   ✓ Updated existing subscription ID {$s['current_subscriptionlist_id']} - new expire: $new_expire\n";
    }
} else {
    // Create new subscription
    $r_pkg = $mysqli->query("SELECT * FROM db_package LIMIT 1");
    $pkg = $r_pkg->fetch_assoc();
    $new_expire = date('Y-m-d', strtotime('+1 year'));
    $mysqli->query("INSERT INTO db_subscription (store_id, package_id, subscription_name, subscription_date, trial_days, expire_date, max_warehouses, max_users, max_items, max_invoices, monthly_price, annual_price, payment_gross, payment_type, status, payment_status, package_status, created_date, created_time, created_by) 
                    VALUES (6, {$pkg['id']}, '{$pkg['package_name']}', '" . date('Y-m-d') . "', 0, '$new_expire', -1, -1, -1, -1, 0, 0, 0, 'Manual', 1, 'Paid', 'Active', '" . date('Y-m-d') . "', '" . date('H:i:s') . "', 'Admin')");
    $new_sub_id = $mysqli->insert_id;
    $mysqli->query("UPDATE db_store SET current_subscriptionlist_id=$new_sub_id, status=1 WHERE id=6");
    echo "   ✓ Created new subscription ID $new_sub_id - expire: $new_expire\n";
}

// Also ensure store status is active
$mysqli->query("UPDATE db_store SET status=1 WHERE id=6");
echo "   ✓ Store #6 status set to ACTIVE\n";

echo "\n=== DONE ===\n";
echo "</pre>\n";
echo '<p><a href="/bill_xml_saas/login">Go to Login</a></p>';
?>
