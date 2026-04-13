<?php
$mysqli = new mysqli("localhost", "root", "", "bill_xml");
echo "<pre>";

// Check db_offline_requests exists
$r = $mysqli->query("SHOW TABLES LIKE 'db_offline_requests'");
echo "1. db_offline_requests: " . ($r->num_rows > 0 ? "EXISTS ✓" : "MISSING ✗") . "\n";
if ($r->num_rows > 0) {
    $r2 = $mysqli->query("DESCRIBE db_offline_requests");
    echo "   Columns:\n";
    while ($row = $r2->fetch_assoc()) { echo "   - {$row['Field']} ({$row['Type']})\n"; }
    $r3 = $mysqli->query("SELECT * FROM db_offline_requests");
    echo "   Rows: " . $r3->num_rows . "\n";
    if ($r3->num_rows > 0) {
        while ($row = $r3->fetch_assoc()) { print_r($row); }
    }
}

// Check store 6 subscription
echo "\n2. Store #6 subscription:\n";
$r4 = $mysqli->query("SELECT id,store_name,current_subscriptionlist_id,status FROM db_store WHERE id=6");
$s = $r4->fetch_assoc();
print_r($s);

if ($s['current_subscriptionlist_id']) {
    $r5 = $mysqli->query("SELECT * FROM db_subscription WHERE id=" . (int)$s['current_subscriptionlist_id']);
    $sub = $r5->fetch_assoc();
    echo "   Subscription:\n";
    print_r($sub);
} else {
    echo "   No subscription linked!\n";
}

// Check if is_admin check works for store 6 user
echo "\n3. User dee@gmail.com:\n";
$r6 = $mysqli->query("SELECT id, username, email, store_id, role_id, status FROM db_users WHERE email='dee@gmail.com'");
print_r($r6->fetch_assoc());

echo "</pre>";
?>
