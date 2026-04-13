<?php
$mysqli = new mysqli('localhost', 'root', '');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Transactional tables where NO data should exist for a new store
$tables_to_truncate = [
    'ac_moneydeposits', 'ac_moneytransfer', 'ac_transactions',
    'db_activity_log', 'db_bankdetails', 'db_cobpayments',
    'db_combo_items', 'db_coupons', 'db_custadvance',
    'db_customer_coupons', 'db_customer_payments', 
    'db_expense', 'db_expense_category', 'db_fivemojo',
    'db_hold', 'db_holditems', 'db_instamojopayments',
    'db_items', 'db_paypalpaylog', 'db_purchase',
    'db_purchaseitems', 'db_purchaseitemsreturn', 'db_purchasepayments',
    'db_purchasepaymentsreturn', 'db_purchasereturn', 'db_quotation',
    'db_quotationitems', 'db_sales', 'db_salesdebit',
    'db_salesitems', 'db_salesitemsdebit', 'db_salesitemsreturn',
    'db_salespayments', 'db_salespaymentsdebit', 'db_salespaymentsreturn',
    'db_salesreturn', 'db_shippingaddress',
    'db_sobpayments', 'db_stockadjustment',
    'db_stockadjustmentitems', 'db_stockentry', 'db_stocktransfer',
    'db_stocktransferitems', 'db_stripepayments',
    'db_supplier_payments', 'db_suppliers',
    'db_variants', 'temp_holdinvoice',
    'ac_accounts', 'db_brands', 'db_category', 'db_offline_requests', 'db_subscription',
    'db_company', 'ci_sessions'
];

// Tables with configuration/defaults that MUST NOT be deleted if they belong to the store
$tables_to_delete_others = [
    'db_warehouse', 'db_warehouseitems', 'db_userswarehouses',
    'db_smsapi', 'db_smstemplates', 'db_tax', 'db_units', 'db_paymenttypes'
];

$res = $mysqli->query("SHOW DATABASES LIKE 'bill_xml_st%'");
while($row = $res->fetch_row()){
    $db_name = $row[0];
    echo "Cleaning database: $db_name\n";
    $mysqli->select_db($db_name);
    
    // Disable foreign key checks
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // 1. Fully truncate transactional stuff
    foreach($tables_to_truncate as $table){
        if(!$mysqli->query("TRUNCATE TABLE `$table`")){
            echo "Failed to truncate $table in $db_name: " . $mysqli->error . "\n";
        }
    }
    
        // 2. Extract store code and ID
        $store_code = str_replace('bill_xml_', '', $db_name);
        $master = new mysqli('localhost', 'root', '', 'bill_xml');
        $stmt = $master->prepare("SELECT id FROM db_store WHERE LOWER(store_code) = ?");
        $stmt->bind_param("s", $store_code);
        $stmt->execute();
        $result = $stmt->get_result();
        if($s = $result->fetch_object()){
            $store_id = $s->id;
            
            // Do not truncate db_customers. Delete all except ID 1 (Walk-in Customer) and the current store's customers
            $mysqli->query("DELETE FROM db_customers WHERE id != 1 AND store_id != {$store_id}");

            // Delete all other stores and users in the tenant DB
            $mysqli->query("DELETE FROM db_store WHERE id != $store_id");
            $mysqli->query("DELETE FROM db_users WHERE store_id != $store_id AND id != 1");
            $mysqli->query("DELETE FROM db_warehouse WHERE store_id = {$store_id}");
            
            // Delete setup data belonging to other stores
            foreach($tables_to_delete_others as $table){
                // userwarehouses has user_id, not store_id, so wipe it if user not in db_users
                if($table == 'db_userswarehouses'){
                    $mysqli->query("DELETE FROM db_userswarehouses WHERE user_id NOT IN (SELECT id FROM db_users)");
                } else if($table == 'db_warehouseitems'){
                   // TRUNCATE is safer for warehouse items on clean slate
                   $mysqli->query("TRUNCATE TABLE db_warehouseitems");
                } else {
                    $mysqli->query("DELETE FROM `$table` WHERE store_id != $store_id");
                }
            }

            // If the tenant DB doesn't have the current store's data (because bill_xml.sql was static)
            // Let's copy the store's data FROM master DB TO tenant DB so they have it
            $tables_to_sync = ['db_store', 'db_users', 'db_sitesettings', 'db_customers', 'db_warehouse', 'db_smsapi', 'db_smstemplates', 'db_tax', 'db_units', 'db_paymenttypes'];
            foreach($tables_to_sync as $table) {
                // Overwrite existing records for synchronized tables
                if($table == 'db_store'){
                    $mysqli->query("DELETE FROM db_store WHERE id = {$store_id}");
                }
                if($table == 'db_users'){
                    $mysqli->query("DELETE FROM db_users WHERE store_id = {$store_id}");
                }
                if($table == 'db_sitesettings'){
                    $mysqli->query("DELETE FROM db_sitesettings");
                }
                if($table == 'db_warehouse'){
                    $mysqli->query("DELETE FROM db_warehouse");
                }

            // Refined Fetch Logic for defaults
            if($table == 'db_customers'){
                // Clear all and get specifically "ลูกค้าทั่วไป" from Master
                $mysqli->query("DELETE FROM db_customers");
                $master_res = $master->query("SELECT * FROM db_customers WHERE customer_name = 'ลูกค้าทั่วไป' AND (store_id = 1 OR store_id IS NULL) LIMIT 1");
            } else if(in_array($table, ['db_tax', 'db_units', 'db_paymenttypes'])){
                // Copy defaults from Superadmin (store_id=1)
                $mysqli->query("DELETE FROM `$table` WHERE store_id = {$store_id}");
                $master_res = $master->query("SELECT * FROM `$table` WHERE store_id = 1");
            } else {
                // Standard sync logic
                $master_res = $master->query("SELECT * FROM `$table` WHERE " . ($table == 'db_users' ? "store_id = {$store_id}" : ($table == 'db_store' ? "id = {$store_id}" : ($table == 'db_sitesettings' ? "id = 1" : "store_id = {$store_id}"))));
            }

            if(!$master_res) continue;

            while($master_row = $master_res->fetch_assoc()){
                // Check if exists in tenant (only for tables we didn't just delete)
                $id = $master_row['id'];
                if($table != 'db_store' && $table != 'db_users' && $table != 'db_sitesettings' && $table != 'db_customers' && !in_array($table, ['db_tax', 'db_units', 'db_paymenttypes'])){
                    // Special check for warehouse to prevent duplication by name
                    if($table == 'db_warehouse'){
                        $w_name = $mysqli->real_escape_string($master_row['warehouse_name']);
                        $w_type = $mysqli->real_escape_string($master_row['warehouse_type']);
                        $check = $mysqli->query("SELECT id FROM db_warehouse WHERE warehouse_name = '$w_name' AND warehouse_type = '$w_type' AND store_id = $store_id");
                    } else {
                        $check = $mysqli->query("SELECT id FROM `$table` WHERE id = $id");
                    }
                    if($check && $check->num_rows > 0) continue;
                }

                // Filter columns to only those that exist in the target table
                $target_columns_res = $mysqli->query("SHOW COLUMNS FROM `$table`");
                $target_columns = [];
                while($col = $target_columns_res->fetch_assoc()){
                    $target_columns[] = $col['Field'];
                }

                $keys = [];
                $values = [];
                foreach($master_row as $key => $val){
                    if(in_array($key, $target_columns)){
                        $keys[] = $key;
                        // Map store_id to current tenant for shared defaults
                        if($key == 'store_id' && in_array($table, ['db_tax', 'db_units', 'db_paymenttypes', 'db_customers'])){
                            $values[] = $store_id;
                        } else {
                            $values[] = $val === null ? 'NULL' : "'" . $mysqli->real_escape_string($val) . "'";
                        }
                    }
                }
                
                $q = "INSERT INTO `$table` (`" . implode("`,`", $keys) . "`) VALUES (" . implode(",", $values) . ")";
                $mysqli->query($q);
            }
        }
    }
    
    // Re-enable foreign key checks
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
    echo "Cleaned $db_name successfully.\n";
}
echo "All tenant databases cleaned and synced.\n";
