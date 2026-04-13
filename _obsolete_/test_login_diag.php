<?php
/**
 * Login Diagnostic Script
 * Tests each step of the login process
 */

$mysqli = new mysqli("localhost", "root", "", "bill_xml");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "<pre>";
echo "=== LOGIN DIAGNOSTIC ===\n\n";

// 1. Check ci_sessions table exists
$r = $mysqli->query("SHOW TABLES LIKE 'ci_sessions'");
echo "1. ci_sessions table in bill_xml: " . ($r->num_rows > 0 ? "EXISTS ✓" : "MISSING ✗") . "\n";

// 2. Check ci_sessions columns
if ($r->num_rows > 0) {
    $r2 = $mysqli->query("DESCRIBE ci_sessions");
    echo "   Columns:\n";
    while ($row = $r2->fetch_assoc()) {
        echo "   - {$row['Field']} ({$row['Type']}) {$row['Key']}\n";
    }
}

// 3. Check db_users table (in bill_xml for super admin)
echo "\n2. Checking super admin user (id=1) in bill_xml:\n";
$r3 = $mysqli->query("SELECT id, username, email, status, role_id, store_id FROM db_users WHERE id=1 LIMIT 1");
if ($r3 && $r3->num_rows > 0) {
    $u = $r3->fetch_assoc();
    echo "   Found: username={$u['username']}, email={$u['email']}, status={$u['status']}, store_id={$u['store_id']}\n";
} else {
    echo "   NOT FOUND in bill_xml.db_users\n";
}

// 4. Check db_store table
echo "\n3. Checking db_store in bill_xml:\n";
$r4 = $mysqli->query("SELECT id, store_name, status, database_name FROM db_store LIMIT 5");
if ($r4 && $r4->num_rows > 0) {
    while ($row = $r4->fetch_assoc()) {
        echo "   Store #{$row['id']}: {$row['store_name']}, status={$row['status']}, database_name={$row['database_name']}\n";
    }
} else {
    echo "   No stores found\n";
}

// 5. Test the exact query from Login_model::verify_credentials
echo "\n4. Testing login query with email=dee@gmail.com:\n";
$email = 'dee@gmail.com';
$password = '123456'; // change if needed
$r5 = $mysqli->query("SELECT a.email,a.store_id,a.id,a.username,a.role_id,a.status,a.last_name
    FROM db_users a, db_roles b
    WHERE b.id=a.role_id
    AND (a.email='$email' or a.username='$email')
    AND a.password='" . md5($password) . "'");
if ($r5 && $r5->num_rows > 0) {
    echo "   ✓ Credentials VALID! User found.\n";
    $user = $r5->fetch_assoc();
    echo "   User: " . print_r($user, true) . "\n";
} else {
    echo "   ✗ Credentials INVALID - no rows returned\n";
    // Check if email exists at all
    $r5b = $mysqli->query("SELECT id, username, email, password, status FROM db_users WHERE email='$email' OR username='$email' LIMIT 1");
    if ($r5b && $r5b->num_rows > 0) {
        $u = $r5b->fetch_assoc();
        echo "   User email found but wrong password. Stored md5: {$u['password']}\n";
        echo "   Tested md5: " . md5($password) . "\n";
        echo "   Status: {$u['status']}\n";
    } else {
        echo "   Email not found in db_users at all\n";
    }
}

// 6. Check recent sessions
echo "\n5. Recent sessions:\n";
$r6 = $mysqli->query("SELECT id, ip_address, timestamp, SUBSTRING(data, 1, 200) as data FROM ci_sessions ORDER BY timestamp DESC LIMIT 3");
if ($r6 && $r6->num_rows > 0) {
    while ($row = $r6->fetch_assoc()) {
        echo "   Session: {$row['id']}\n";
        echo "   Time: " . date('Y-m-d H:i:s', $row['timestamp']) . "\n";
        echo "   Data: {$row['data']}\n\n";
    }
} else {
    echo "   No sessions found\n";
}

// 7. Check if bill_xml db_roles exists  
echo "\n6. Checking db_roles:\n";
$r7 = $mysqli->query("SELECT id, role_name FROM db_roles LIMIT 5");
if ($r7 && $r7->num_rows > 0) {
    while ($row = $r7->fetch_assoc()) {
        echo "   Role #{$row['id']}: {$row['role_name']}\n";
    }
} else {
    echo "   No roles found\n";
}

echo "\n=== END DIAGNOSTIC ===\n";
echo "</pre>";
?>
