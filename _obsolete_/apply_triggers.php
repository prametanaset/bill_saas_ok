<?php
// Script to apply the sync triggers to existing tenant databases
$host = 'localhost';
$user = 'root';
$pass = ''; // Assuming blank for Laragon default

$mysqli = new mysqli($host, $user, $pass);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$databases_query = $mysqli->query("SHOW DATABASES LIKE 'bill_xml_st%'");
if ($databases_query) {
    while ($row = $databases_query->fetch_array()) {
        $db_name = $row[0];
        echo "Applying triggers to database: $db_name\n";
        
        $mysqli->select_db($db_name);
        
        $trigger_users = "
        CREATE TRIGGER `sync_users_to_master` AFTER UPDATE ON `{$db_name}`.`db_users`
        FOR EACH ROW
        BEGIN
            UPDATE `bill_xml`.`db_users` 
            SET password = NEW.password, 
                first_name = NEW.first_name, 
                last_name = NEW.last_name, 
                mobile = NEW.mobile, 
                email = NEW.email, 
                status = NEW.status, 
                profile_picture = NEW.profile_picture
            WHERE id = NEW.id;
        END;
        ";
        
        $trigger_store = "
        CREATE TRIGGER `sync_store_to_master` AFTER UPDATE ON `{$db_name}`.`db_store`
        FOR EACH ROW
        BEGIN
            UPDATE `bill_xml`.`db_store` 
            SET store_name = NEW.store_name, 
                mobile = NEW.mobile, 
                email = NEW.email, 
                address = NEW.address, 
                city = NEW.city, 
                state = NEW.state, 
                country = NEW.country, 
                status = NEW.status
            WHERE id = NEW.id;
        END;
        ";
        
        // Drop them first just in case
        $mysqli->query("DROP TRIGGER IF EXISTS `{$db_name}`.`sync_users_to_master`");
        $mysqli->query("DROP TRIGGER IF EXISTS `{$db_name}`.`sync_store_to_master`");
        
        if ($mysqli->query($trigger_users)) {
            echo " - sync_users_to_master Trigger created successfully.\n";
        } else {
            echo " - Error creating sync_users_to_master: " . $mysqli->error . "\n";
        }
        
        if ($mysqli->query($trigger_store)) {
            echo " - sync_store_to_master Trigger created successfully.\n";
        } else {
            echo " - Error creating sync_store_to_master: " . $mysqli->error . "\n";
        }
    }
} else {
    echo "Error querying databases: " . $mysqli->error;
}

$mysqli->close();
