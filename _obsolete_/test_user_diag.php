<?php
$mysqli = new mysqli("localhost", "root", "", "bill_xml");

echo "<pre>";
echo "=== User Diagnostics ===\n\n";

$res = $mysqli->query("SELECT * FROM db_users WHERE email='dee@gmail.com'")->fetch_assoc(); 
echo "User id: " . $res['id'] . ", Store ID: " . $res['store_id'] . "\n";

$res2 = $mysqli->query("SELECT * FROM db_store WHERE id=" . (int)$res['store_id'])->fetch_assoc();
echo "Store Details: " . ($res2 ? "FOUND (ID: {$res2['id']}, Name: {$res2['store_name']})" : "NOT FOUND") . "\n";

if ($res2) {
    if (empty($res2['status']) || $res2['status'] == 0) {
        echo "WARNING: Store status is Inactive (0).\n";
    }
} else {
    echo "WARNING: User is associated with a non-existent store!\n";
}

echo "\nSession Data Check:\n";
// Let's assume store id in session might be stored differently in db_users vs what's in CI session
// If store is found, Store_profile::get_details() does: select * from db_store where upper(id)=upper('$id')
// Wait, the MY_Controller database switching logic!
// In MY_Controller line 38: $this->db->query("USE `{$tenant_db}`");
// Then it switches back to `bill_xml` in register_shutdown_function for session saving.
// BUT get_details() uses: $query1=$this->db->query("select * from db_store where upper(id)=upper('$id')");
// THIS QUERY DOES NOT PREFIX WITH `bill_xml.`!
// So it queries `db_store` inside the TENANT DATABASE, which is empty or doesn't have the store details if it's not synchronized, OR the table might be empty in the tenant DB.
echo "MY_Controller DB Switching check: Does the tenant DB have db_store data?\n";

if ($res['store_id'] && $res['store_id'] != 1) {
    $tenant_db = 'store_' . $res['store_id']; // Usually tenant db is store_X or from session
    echo "Assuming tenant DB is $tenant_db...\n";
    
    // Check if table exists in tenant db
    $chk = $mysqli->query("SHOW DATABASES LIKE '$tenant_db'");
    if ($chk->num_rows > 0) {
        $mysqli->select_db($tenant_db);
        $r_store = $mysqli->query("SELECT * FROM db_store WHERE id=" . (int)$res['store_id']);
        if ($r_store && $r_store->num_rows > 0) {
            echo "Tenant db_store HAS data.\n";
        } else {
            echo "Tenant db_store is EMPTY or doesn't have ID {$res['store_id']}!\n";
        }
    } else {
        echo "Tenant DB $tenant_db does not exist.\n";
    }
}

echo "</pre>";
?>
