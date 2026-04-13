<?php
define('BASEPATH', 'dummy');
define('ENVIRONMENT', 'development');
require_once 'application/config/database.php';

$db_config = $db['default'];
$mysqli_master = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($mysqli_master->connect_error) {
    die("Connection failed: " . $mysqli_master->connect_error);
}

// Get all stores with their database names
$sql = "SELECT id, store_code, database_name FROM db_store WHERE database_name IS NOT NULL AND database_name != ''";
$result = $mysqli_master->query($sql);

if ($result->num_rows > 0) {
    while($store = $result->fetch_assoc()) {
        $db_name = $store['database_name'];
        echo "Updating triggers for Store {$store['store_code']} (DB: $db_name)...\n";
        
        $mysqli_tenant = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_name);
        
        if ($mysqli_tenant->connect_error) {
            echo "  Failed to connect to tenant DB: " . $mysqli_tenant->connect_error . "\n";
            continue;
        }

        // Drop old triggers if they exist
        $mysqli_tenant->query("DROP TRIGGER IF EXISTS `sync_users_to_master`;");
        $mysqli_tenant->query("DROP TRIGGER IF EXISTS `sync_users_insert_to_master`;");
        $mysqli_tenant->query("DROP TRIGGER IF EXISTS `sync_users_update_to_master`;");
        $mysqli_tenant->query("DROP TRIGGER IF EXISTS `sync_users_delete_from_master`;");

        // Create new triggers individually
        $t1 = "
        CREATE TRIGGER `sync_users_insert_to_master` AFTER INSERT ON `db_users`
        FOR EACH ROW
        BEGIN
            INSERT IGNORE INTO `bill_xml`.`db_users` 
            (id, username, password, first_name, last_name, mobile, email, status, role_id, store_id, profile_picture, created_date, created_time, created_by, system_ip, system_name, default_warehouse_id)
            VALUES 
            (NEW.id, NEW.username, NEW.password, NEW.first_name, NEW.last_name, NEW.mobile, NEW.email, NEW.status, NEW.role_id, NEW.store_id, NEW.profile_picture, NEW.created_date, NEW.created_time, NEW.created_by, NEW.system_ip, NEW.system_name, NEW.default_warehouse_id);
        END";

        $t2 = "
        CREATE TRIGGER `sync_users_update_to_master` AFTER UPDATE ON `db_users`
        FOR EACH ROW
        BEGIN
            UPDATE `bill_xml`.`db_users` 
            SET password = NEW.password, 
                first_name = NEW.first_name, 
                last_name = NEW.last_name, 
                mobile = NEW.mobile, 
                email = NEW.email, 
                status = NEW.status, 
                profile_picture = NEW.profile_picture,
                role_id = NEW.role_id,
                default_warehouse_id = NEW.default_warehouse_id
            WHERE id = NEW.id;
        END";

        $t3 = "
        CREATE TRIGGER `sync_users_delete_from_master` AFTER DELETE ON `db_users`
        FOR EACH ROW
        BEGIN
            DELETE FROM `bill_xml`.`db_users` WHERE id = OLD.id;
        END";

        if(!$mysqli_tenant->query($t1)) echo "  Error T1: " . $mysqli_tenant->error . "\n";
        if(!$mysqli_tenant->query($t2)) echo "  Error T2: " . $mysqli_tenant->error . "\n";
        if(!$mysqli_tenant->query($t3)) echo "  Error T3: " . $mysqli_tenant->error . "\n";
        
        $mysqli_tenant->close();
    }
} else {
    echo "No stores found to update.\n";
}

$mysqli_master->close();
?>
