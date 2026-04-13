<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate_email_logs extends CI_Controller {
	public function __construct(){
		parent::__construct();
        // Manually load needed libraries since CI_Controller is bare
        $this->load->database();
        $this->load->library('session');
	}

	public function index(){
        // Simple security check via GET param
        if ($this->input->get('key') != 'migrate123') {
            die("Access Denied. Use ?key=migrate123");
        }

		echo "<h2>Migrating Email Logs table to Tenant Databases</h2>";

        $stores = $this->db->select('id, store_code, database_name')->get('db_store')->result();
        echo "Found " . count($stores) . " stores in db_store.<br>";
        
        $table_sql = "CREATE TABLE IF NOT EXISTS `db_email_logs` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `store_id` int(11) DEFAULT NULL,
              `email_to` varchar(255) DEFAULT NULL,
              `email_cc` varchar(255) DEFAULT NULL,
              `email_bcc` varchar(255) DEFAULT NULL,
              `subject` varchar(255) DEFAULT NULL,
              `message` text,
              `attachment` varchar(255) DEFAULT NULL,
              `status` varchar(20) DEFAULT NULL,
              `error_msg` text,
              `created_date` date DEFAULT NULL,
              `created_time` varchar(20) DEFAULT NULL,
              `created_by" . "`" . " varchar(100) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        foreach ($stores as $store) {
            if ($store->id == 1) {
                // Master already has it, but let's ensure it's correct
                $this->db->query($table_sql);
                echo "Store ID 1 (Master): Table verified.<br>";
                continue;
            }

            if (!empty($store->database_name)) {
                $db_name = $store->database_name;
                
                // Attempt to create table in tenant DB
                try {
                    // Switch to tenant DB
                    $this->db->query("USE `{$db_name}`");
                    
                    // Create table
                    $this->db->query($table_sql);
                    
                    echo "Store {$store->store_code} ({$db_name}): Table created/verified.<br>";
                    
                    // Optional: Sync existing logs for this store from Master to Tenant
                    // (Since we only found 1 row, this is simple)
                    $this->db->query("USE `bill_xml` "); // Back to master
                    $logs = $this->db->where('store_id', $store->id)->get('db_email_logs')->result_array();
                    if(!empty($logs)) {
                        $this->db->query("USE `{$db_name}`");
                        foreach($logs as $log) {
                            $this->db->insert('db_email_logs', $log);
                        }
                        echo "--- Moved " . count($logs) . " records from Master to Tenant.<br>";
                    }
                } catch (Exception $e) {
                    echo "Store {$store->store_code} ({$db_name}): <span style='color:red'>Failed: " . $e->getMessage() . "</span><br>";
                }
            }
        }
        
        // Switch back to master at the end
        $this->db->query("USE `bill_xml` ");
        echo "<h3>Migration Complete!</h3>";
	}
}
?>
