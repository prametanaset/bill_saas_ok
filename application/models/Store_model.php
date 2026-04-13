<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_model extends MY_Model {

	var $table = 'db_store a';
	var $column_order = array(
								'a.id',
								'a.store_code',
								'a.store_name',
								'a.mobile',
								'a.address',
								'a.created_date',
								'a.created_by',
								'c.package_name',
								'b.expire_date',
								'a.status'); //set column field database for datatable orderable

	var $column_search = array(
								'a.id',
								'a.store_code',
								'a.store_name',
								'a.mobile',
								'a.address',
								'a.created_date',
								'a.created_by',
								'c.package_name',
								'b.expire_date',
								'a.status',
								); //set column field database for datatable searchable 

	var $order = array('a.id' => 'desc'); // default order 

	public function __construct()
	{
		parent::__construct();
        // Auto-update schema for soft delete
        if (!$this->db->field_exists('is_deleted', 'db_store')) {
            $this->db->query("ALTER TABLE `db_store` ADD `is_deleted` INT(1) DEFAULT 0 AFTER `status`");
        }
        if (!$this->db->field_exists('deleted_date', 'db_store')) {
            $this->db->query("ALTER TABLE `db_store` ADD `deleted_date` DATE DEFAULT NULL AFTER `is_deleted`");
        }
        if (!$this->db->field_exists('database_name', 'db_store')) {
            $this->db->query("ALTER TABLE `db_store` ADD `database_name` VARCHAR(100) DEFAULT NULL AFTER `store_code`");
        }
        if (!$this->db->field_exists('invoice_init', 'db_store')) {
            $this->db->query("ALTER TABLE `db_store` ADD `invoice_init` VARCHAR(10) DEFAULT 'IN'");
        }

	}

    public function delete_store_soft($id){
        $this->db->trans_begin();
        
        $data = array(
            'status' => 0,
            'is_deleted' => 1,
            'deleted_date' => date('Y-m-d')
        );
        $this->db->where('id', $id)->update($this->table, $data);
        
        // Auto-purge old deleted stores (10% chance to run)
        if(rand(1,10) == 1){
            $this->purge_deleted_stores();
        }

        if ($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            return "ไม่สำเร็จ";
        }else{
            $this->db->trans_commit();
            return "สำเร็จ";
        }
    }

    public function restore_store($id){
        $this->db->trans_begin();
        $data = array(
            'status' => 1,
            'is_deleted' => 0,
            'deleted_date' => NULL
        );
        $this->db->where('id', $id)->update($this->table, $data);

        if ($this->db->trans_status() === FALSE){
             $this->db->trans_rollback();
             return "ไม่สำเร็จ";
        }else{
             $this->db->trans_commit();
             return "สำเร็จ";
        }
    }

    private function _drop_tenant_dbs(array $db_names) {
        foreach ($db_names as $db_name) {
            if (!empty($db_name) && strpos($db_name, 'bill_xml_st') === 0) {
                // DROP DATABASE is a DDL statement and causes an implicit commit in MySQL.
                // We run this ONLY after our standard transaction is successfully full-committed.
                $this->db->query("DROP DATABASE IF EXISTS `{$db_name}`");
                log_message('debug', "Multi-Tenant: Dropped database {$db_name} during store deletion.");
            }
        }
    }

    public function purge_deleted_stores(){
        // Delete stores deleted more than 60 days ago
        $date_threshold = date('Y-m-d', strtotime('-60 days'));
        
        // 1. Get database names to drop
        $stores = $this->db->select('database_name')->where('is_deleted', 1)->where('deleted_date <', $date_threshold)->get('db_store')->result();
        $db_names = array();
        foreach($stores as $s) if(!empty($s->database_name)) $db_names[] = $s->database_name;
        
        // 2. Delete rows
        $this->db->where('is_deleted', 1);
        $this->db->where('deleted_date <', $date_threshold);
        if($this->db->delete($this->table)) {
            // 3. Drop DBs
            $this->_drop_tenant_dbs($db_names);
        }
    }

    public function empty_recycle_bin(){
        // 1. Get database names to drop
        $stores = $this->db->select('database_name')->where('is_deleted', 1)->get('db_store')->result();
        $db_names = array();
        foreach($stores as $s) if(!empty($s->database_name)) $db_names[] = $s->database_name;
        
        $this->db->trans_begin();
        $this->db->where('is_deleted', 1)->delete($this->table);
        
        if ($this->db->trans_status() === FALSE){
             $this->db->trans_rollback();
             return "ไม่สำเร็จ";
        }else{
             $this->db->trans_commit();
             // 3. Drop DBs after commit
             $this->_drop_tenant_dbs($db_names);
             return "สำเร็จ";
        }
    }

	private function _get_datatables_query()
	{
		$this->db->select("a.id, a.store_code, a.store_name, a.mobile, a.address, a.created_date, a.created_by, a.status, c.package_name, b.expire_date");
		$this->db->from($this->table);

		$this->db->join('db_subscription b','b.id=a.current_subscriptionlist_id','left');
		$this->db->join('db_package c','c.id=b.package_id','left');

        // Filter by is_deleted based on POST parameter or default to 0
        if($this->input->post('is_deleted') == 1){
            $this->db->where('a.is_deleted', 1);
        } else {
            $this->db->where('a.is_deleted', 0);
        }

		//echo $this->db->get_compiled_select();exit;

		$i = 0;
	
		foreach ($this->column_search as $item) // loop column 
		{
			if($_POST['search']['value']) // if datatable send POST for search
			{
				
				if($i===0) // first loop
				{
					$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
					$this->db->like($item, $_POST['search']['value']);
				}
				else
				{
					$this->db->or_like($item, $_POST['search']['value']);
				}

				if(count($this->column_search) - 1 == $i) //last loop
					$this->db->group_end(); //close bracket
			}
			$i++;
		}
		
		if(isset($_POST['order'])) // here order processing
		{
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} 
		else if(isset($this->order))
		{
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	function get_datatables()
	{
		$this->_get_datatables_query();
		if($_POST['length'] != -1)
		$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	function count_filtered()
	{
		$this->_get_datatables_query();
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function count_all()
	{
		$this->db->from($this->table);
		return $this->db->count_all_results();
	}

	public function get_store_sum_count(){
		$CI =& get_instance();
		$CUR_DATE = date("Y-m-d");
		
		$data = array();
		
		// 1. Total Subscriptions (Not deleted, excluding Master)
		$data['tot_subs'] = $this->db->where('is_deleted',0)->where('id!=',1)->count_all_results('db_store');
		
		// 2. Active Stores
		$data['tot_active'] = $this->db->where('is_deleted',0)->where('status',1)->where('id!=',1)->count_all_results('db_store');
		
		// 3. Expired Stores
		$this->db->select('count(a.id) as tot_expired');
		$this->db->from('db_store a');
		$this->db->join('db_subscription b','b.id=a.current_subscriptionlist_id','left');
		$this->db->where('a.is_deleted',0);
		$this->db->where('a.id!=',1);
		$this->db->where('b.expire_date <', $CUR_DATE);
		$data['tot_expired'] = $this->db->get()->row()->tot_expired;
		
		// 4. Free Stores
		$this->db->select('count(a.id) as tot_free');
		$this->db->from('db_store a');
		$this->db->join('db_subscription b','b.id=a.current_subscriptionlist_id','left');
		$this->db->join('db_package c','c.id=b.package_id','left');
		$this->db->where('a.is_deleted',0);
		$this->db->where('a.id!=',1);
		$this->db->where('upper(c.package_type)', 'FREE');
		$data['tot_free'] = $this->db->get()->row()->tot_free;
		
		return $data;
	}

	public function store_making_codes(){
		 /*Create Store Code*/
		$this->db->query("ALTER TABLE db_store AUTO_INCREMENT = 1");
        $store_id=$this->db->query('select max(id)+1 as store_id from db_store')->row()->store_id;
        // Fetch Global Defaults
        $global = $this->db->select('*')->get('db_sitesettings')->row();

		$data = array();
        $data['store_code'] = 'ST'.str_pad($store_id, 4, '0', STR_PAD_LEFT);
        $data['category_init'] ="CT-";
        $data['item_init'] ="IT-";
        $data['supplier_init'] ="SU-";
        $data['purchase_init'] ="PU-";
        $data['purchase_return_init'] ="PR-";
        $data['customer_init'] ="CU-";
        $data['sales_init'] ="SL-";
        $data['sales_return_init'] ="SR-";
        $data['receipt_init'] ="RC-";
        $data['sales_debit_init'] ="SD-";
        $data['expense_init'] ="EX-";
        $data['accounts_init'] ="AC-";
        $data['quotation_init'] ="QT-";
        $data['money_transfer_init'] ="MT-";
        $data['sales_payment_init'] ="SP-";
        $data['sales_return_payment_init'] ="SRP-";
        $data['purchase_payment_init'] ="PP-";
        $data['purchase_return_payment_init'] ="PRP-";
        $data['expense_payment_init'] ="XP-";
        $data['cust_advance_init'] ="ADV-";
        $data['invoice_init'] ="IN";
        
        // Use Global Defaults
        $data['language_id'] = $global->language_id ?? 1;
        $data['sales_discount'] =0;
        $data['change_return'] =1;
        $data['mrp_column'] =1;
        $data['show_signature'] =0;
        $data['previous_balance_bit'] =1;
        $data['round_off'] = $global->round_off ?? 1;
        $data['sales_invoice_format_id'] =4;
        $data['pos_invoice_format_id'] =1;
        $data['sales_invoice_footer_text'] ='เอกสารฉบับนี้ได้จัดทำและส่งข้อมูลให้แก่สรรพากรด้วยวิธีการอิเลคทรอนิคส์';
        $data['invoice_terms'] ='';
        $data['t_and_c_status'] =1;
        $data['t_and_c_status_pos'] =1;
        $data['qty_decimals'] = $global->qty_decimals ?? 2;
        $data['decimals'] = $global->decimals ?? 2;
        
        // System Settings Defaults
        $data['currency_id'] = $global->currency_id ?? 1;
        $data['currency_placement'] = $global->currency_placement ?? 'Right';
        $data['timezone'] = $global->timezone ?? 'Asia/Bangkok';
        $data['date_format'] = $global->date_format ?? 'dd-mm-yyyy';
        $data['time_format'] = $global->time_format ?? '24';

        $data['number_to_words'] ='Default';
        $data['default_account_id'] ='';
        return $data;
	}

    public function provision_tenant_database($store_id, $store_code) {
        // Prevent PHP from timing out during slow Windows NTFS file creation (1-3 minutes for 95 tables)
        set_time_limit(0);
        ignore_user_abort(true);
        // Turn off output buffering to prevent proxy timeouts
        while (ob_get_level()) { ob_end_clean(); }

        $db_name = "bill_xml_" . strtolower($store_code);
        
        // 1. Create the database
        // Drop first in case a previous attempt failed and left a dirty schema, preventing thousands of duplicate key exceptions
        $this->db->query("DROP DATABASE IF EXISTS `{$db_name}`");
        $this->db->query("CREATE DATABASE `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // 2. Import the base SQL file safely without exploding large strings
        $sql_file = FCPATH . 'bill_xml.sql';
        if (file_exists($sql_file)) {
            $mysqli = new mysqli($this->db->hostname, $this->db->username, $this->db->password, $db_name);
            if ($mysqli->connect_error) {
                log_message('error', "Multi-Tenant: Failed to connect to new DB {$db_name}: " . $mysqli->connect_error);
                return false;
            }
            
            // Execute the entire SQL dump via multi_query for 100x performance
            $sql_content = file_get_contents($sql_file);
            if (!empty($sql_content)) {
                if ($mysqli->multi_query($sql_content)) {
                    do {
                        // Keep fetching results to clear the buffer and prevent "commands out of sync" errors
                        if ($result = $mysqli->store_result()) {
                            $result->free();
                        }
                    } while ($mysqli->more_results() && $mysqli->next_result());
                    
                    // Create db_smtp table if it doesn't exist
                    $mysqli->query("CREATE TABLE IF NOT EXISTS `db_smtp` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `store_id` int(11) DEFAULT NULL,
                          `smtp_host` varchar(255) DEFAULT NULL,
                          `smtp_port` varchar(10) DEFAULT NULL,
                          `smtp_user` varchar(255) DEFAULT NULL,
                          `smtp_pass` varchar(255) DEFAULT NULL,
                          `smtp_encryption` varchar(10) DEFAULT NULL,
                          `smtp_status` int(1) DEFAULT 0,
                          `from_email` varchar(255) DEFAULT NULL,
                          `from_name` varchar(255) DEFAULT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

                    // Create db_email_logs table if it doesn't exist
                    $mysqli->query("CREATE TABLE IF NOT EXISTS `db_email_logs` (
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
                          `created_by` varchar(100) DEFAULT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");



                } else {
                    log_message('error', "Multi-Tenant: SQL Import failed: " . $mysqli->error);
                }
            }
            
            // Ensure the newly cloned database is clean of old data
            $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");
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
                'db_company', 'ci_sessions', 'db_tax', 'db_units', 'db_paymenttypes', 'db_permissions', 'db_roles', 'db_receipts'
            ];
            foreach($tables_to_truncate as $tbl) $mysqli->query("TRUNCATE TABLE `$tbl`");
            
            // Do not truncate db_customers. Delete all except ID 1 (Walk-in Customer) and the current store's customers
            $mysqli->query("DELETE FROM db_customers WHERE id != 1 AND store_id != {$store_id}");
            
            $mysqli->query("DELETE FROM db_store WHERE id != {$store_id}");
            $mysqli->query("DELETE FROM db_users WHERE store_id != {$store_id} AND id != 1");
            $mysqli->query("DELETE FROM db_warehouse WHERE store_id = {$store_id}");
            $mysqli->query("TRUNCATE TABLE db_warehouseitems");
            $mysqli->query("DELETE FROM db_userswarehouses WHERE user_id NOT IN (SELECT id FROM db_users)");
            $mysqli->query("DELETE FROM db_smsapi WHERE store_id != {$store_id}");
            $mysqli->query("DELETE FROM db_smstemplates WHERE store_id != {$store_id}");
            $mysqli->query("DELETE FROM db_tax WHERE store_id = {$store_id}"); // Modified to ensure clean state
            $mysqli->query("DELETE FROM db_units WHERE store_id = {$store_id}");
            $mysqli->query("DELETE FROM db_paymenttypes WHERE store_id = {$store_id}");
            
            // Sync current store configs, store info, and users from master to new tenant DB
            // Note: We used to sync db_tax, db_units, db_paymenttypes, ac_accounts from master, but now they are created directly.
            $master = new mysqli($this->db->hostname, $this->db->username, $this->db->password, $this->db->database);
            $tables_to_sync = ['db_store', 'db_users', 'db_sitesettings', 'db_customers', 'db_warehouse', 'db_smsapi', 'db_smstemplates', 'db_smtp', 'db_roles', 'db_permissions']; 
            foreach($tables_to_sync as $tbl){
                // For store and users, we MUST overwrite existing records in tenant DB if they exist with current store_id
                if($tbl == 'db_store'){
                    $mysqli->query("DELETE FROM db_store WHERE id = {$store_id}");
                }
                if($tbl == 'db_users'){
                    $mysqli->query("DELETE FROM db_users WHERE store_id = {$store_id}");
                }
                if($tbl == 'db_sitesettings'){ 
                    $mysqli->query("DELETE FROM db_sitesettings");
                }
                if($tbl == 'db_warehouse'){
                    $mysqli->query("DELETE FROM db_warehouse");
                }
                
                // Refined Fetch Logic
                if($tbl == 'db_customers'){
                    // Clear all and get specifically "ลูกค้าทั่วไป" from Master
                    $mysqli->query("DELETE FROM db_customers");
                    $mres = $master->query("SELECT * FROM db_customers WHERE customer_name = 'ลูกค้าทั่วไป' AND (store_id = 1 OR store_id IS NULL) LIMIT 1");
                } else {
                    // Standard sync logic
                    $mres = $master->query("SELECT * FROM `$tbl` WHERE " . ($tbl == 'db_users' ? "store_id = {$store_id}" : ($tbl == 'db_store' ? "id = {$store_id}" : ($tbl == 'db_sitesettings' ? "id = 1" : ($tbl == 'db_roles' ? "store_id = {$store_id}" : ($tbl == 'db_permissions' ? "store_id = {$store_id}" : "store_id = {$store_id}"))))));
                }
                
                if ($mres) {
                    while($mrow = $mres->fetch_assoc()){
                        $id = $mrow['id'];
                        // Check if exists (only for tables where we didn't just delete)
                        if($tbl != 'db_store' && $tbl != 'db_users' && $tbl != 'db_sitesettings' && $tbl != 'db_customers'){
                            // Special check for warehouse to prevent duplication by name
                            if($tbl == 'db_warehouse'){
                                $w_name = $mysqli->real_escape_string($mrow['warehouse_name']);
                                $w_type = $mysqli->real_escape_string($mrow['warehouse_type']);
                                $check = $mysqli->query("SELECT id FROM db_warehouse WHERE warehouse_name = '$w_name' AND warehouse_type = '$w_type' AND store_id = $store_id");
                            } else {
                                $check = $mysqli->query("SELECT id FROM `$tbl` WHERE id = $id");
                            }
                            if($check && $check->num_rows > 0) continue;
                        }

                        // Filter columns to only those that exist in the target table
                        $target_columns_res = $mysqli->query("SHOW COLUMNS FROM `$tbl`");
                        $target_columns = [];
                        while($col = $target_columns_res->fetch_assoc()){
                            $target_columns[] = $col['Field'];
                        }

                        $keys = [];
                        $values = [];
                        foreach($mrow as $key => $val){
                            if(in_array($key, $target_columns)){
                                $keys[] = $key;
                                // Map store_id to current tenant for shared defaults
                                if($key == 'store_id' && in_array($tbl, ['db_customers'])){
                                    $values[] = $store_id;
                                } else {
                                    $values[] = $val === null ? 'NULL' : "'" . $mysqli->real_escape_string($val) . "'";
                                }
                            }
                        }
                        
                        $mysqli->query("INSERT INTO `$tbl` (`" . implode("`,`", $keys) . "`) VALUES (" . implode(",", $values) . ")");
                    }
                }
            }
            $master->close();
            
            // --- CREATE CUSTOM TRIGGERS AFTER INITIAL SYNC ---
            // Move trigger creation here to avoid "Duplicate entry" errors when syncing initial users/stores
            $mysqli->query("CREATE TRIGGER `sync_users_insert_to_master` AFTER INSERT ON `{$db_name}`.`db_users`
            FOR EACH ROW
            BEGIN
                INSERT INTO `bill_xml`.`db_users` 
                (id, username, password, first_name, last_name, mobile, email, status, role_id, store_id, profile_picture, created_date, created_time, created_by, system_ip, system_name, default_warehouse_id)
                VALUES 
                (NEW.id, NEW.username, NEW.password, NEW.first_name, NEW.last_name, NEW.mobile, NEW.email, NEW.status, NEW.role_id, NEW.store_id, NEW.profile_picture, NEW.created_date, NEW.created_time, NEW.created_by, NEW.system_ip, NEW.system_name, NEW.default_warehouse_id);
            END;");

            $mysqli->query("CREATE TRIGGER `sync_users_update_to_master` AFTER UPDATE ON `{$db_name}`.`db_users`
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
            END;");

            $mysqli->query("CREATE TRIGGER `sync_users_delete_from_master` AFTER DELETE ON `{$db_name}`.`db_users`
            FOR EACH ROW
            BEGIN
                DELETE FROM `bill_xml`.`db_users` WHERE id = OLD.id;
            END;");

            $mysqli->query("CREATE TRIGGER `sync_store_to_master` AFTER UPDATE ON `{$db_name}`.`db_store`
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
            END;");

            $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
            
            // --- INITIALIZE STORE DEFAULTS DIRECTLY IN TENANT DB ---
            // Must be called AFTER truncation and AFTER sync to ensure a clean state and proper role availability
            $this->_initialize_tenant_defaults($mysqli, $store_id);

            $mysqli->close();
        } else {
            log_message('error', "Multi-Tenant: Base SQL file not found at {$sql_file}");
            return false;
        }

        // 3. Update the db_store record with the new db name
        // Use a direct raw query to avoid active record conflicts during transaction
        $update_query = "UPDATE db_store SET database_name = '{$db_name}' WHERE id = {$store_id}";
        $success = $this->db->query($update_query);
        
        $log_out = "Multi-Tenant: DB Name Update Query -> " . $update_query . "\n";
        $log_out .= "Success: " . ($success ? "Yes" : "No") . " | Affected Rows: " . $this->db->affected_rows() . "\n";
        file_put_contents(FCPATH . 'db_update_log.txt', $log_out, FILE_APPEND);
        
        return $db_name;
    }

    /**
     * PRIVATE INITIALIZATION HELPERS
     * Used within provision_tenant_database to setup a fresh store
     */
    private function _initialize_tenant_defaults($mysqli, $store_id) {
        log_message('error', "Multi-Tenant: Initializing defaults for store {$store_id}...");

        // 1. Copy Taxes from Superadmin (Store ID 1)
        $master_taxes = $this->db->where('store_id', 1)->get('db_tax')->result_array();
        if (!empty($master_taxes)) {
            log_message('error', "Multi-Tenant: Copying " . count($master_taxes) . " taxes from superadmin.");
            foreach ($master_taxes as $tax) {
                unset($tax['id']);
                $tax['store_id'] = $store_id;
                $this->_raw_mysqli_insert($mysqli, 'db_tax', $tax);
            }
        }

        // 2. Copy Units from Superadmin
        $master_units = $this->db->where('store_id', 1)->get('db_units')->result_array();
        if (!empty($master_units)) {
            log_message('error', "Multi-Tenant: Copying " . count($master_units) . " units from superadmin.");
            foreach ($master_units as $unit) {
                unset($unit['id']);
                $unit['store_id'] = $store_id;
                $this->_raw_mysqli_insert($mysqli, 'db_units', $unit);
            }
        }

        // 3. Copy Payment Types from Superadmin
        $master_payment_types = $this->db->where('store_id', 1)->get('db_paymenttypes')->result_array();
        if (!empty($master_payment_types)) {
            log_message('error', "Multi-Tenant: Copying " . count($master_payment_types) . " payment types from superadmin.");
            foreach ($master_payment_types as $pt) {
                unset($pt['id']);
                $pt['store_id'] = $store_id;
                $this->_raw_mysqli_insert($mysqli, 'db_paymenttypes', $pt);
            }
        }

        // 4. Copy Roles and Permissions from Master Store (ID 1)
        // Use CI's own DB connection which is already connected to bill_xml (Master)
        $master_roles = $this->db->where('store_id', 1)->get('db_roles')->result_array();
        if (!empty($master_roles)) {
            log_message('error', "Multi-Tenant: Found " . count($master_roles) . " roles in master.");
            foreach($master_roles as $role){
                $old_role_id = $role['id'];
                $role['store_id'] = $store_id;
                // Insert role and get new ID (if auto-increment) or use same ID
                $this->_raw_mysqli_insert($mysqli, 'db_roles', $role);
                
                // Copy permissions for this specific role
                $master_perms = $this->db->where('store_id', 1)->where('role_id', $old_role_id)->get('db_permissions')->result_array();
                if (!empty($master_perms)) {
                    log_message('error', "Multi-Tenant: Found " . count($master_perms) . " permissions for role {$old_role_id}.");
                    foreach($master_perms as $perm){
                        unset($perm['id']);
                        $perm['store_id'] = $store_id;
                        $perm['role_id'] = $old_role_id; // Keeping the role ID mapping
                        $this->_raw_mysqli_insert($mysqli, 'db_permissions', $perm);
                    }
                }
            }
        } else {
            log_message('error', "Multi-Tenant: WARNING: No roles found for store_id 1 in master DB.");
        }

        // 5. Initial Accounts
        $this->_create_tenant_accounts($mysqli, $store_id);
    }

    private function _raw_mysqli_insert($mysqli, $table, $data) {
        $keys = array_keys($data);
        $values = array_map(function($v) use ($mysqli) {
            return $v === null ? 'NULL' : "'" . $mysqli->real_escape_string($v) . "'";
        }, array_values($data));
        $sql = "INSERT INTO `$table` (`" . implode("`,`", $keys) . "`) VALUES (" . implode(",", $values) . ")";
        if (!$mysqli->query($sql)) {
            log_message('error', "Multi-Tenant: Insert failed on {$table}: " . $mysqli->error . " | SQL: " . substr($sql, 0, 200));
        }
    }

    private function _create_tenant_accounts($mysqli, $store_id) {
        $date = date("Y-m-d");
        $time = date("H:i:s");
        $ip = $this->input->ip_address();
        
        $accounts = [
            ['account_name' => 'รายรับ', 'account_code' => 'AC-'.str_pad($store_id, 2, '0', STR_PAD_LEFT).'-0001', 'sort_code' => '1', 'parent_id' => 0, 'note' => 'บัญชีรายรับเริ่มต้น'],
            ['account_name' => 'รายจ่ายซื้อสินค้า', 'account_code' => 'AC-'.str_pad($store_id, 2, '0', STR_PAD_LEFT).'-0002', 'sort_code' => '2', 'parent_id' => 0, 'note' => 'บัญชีรายจ่ายเริ่มต้น'],
            ['account_name' => 'รายจ่ายดำเนินงาน', 'account_code' => 'AC-'.str_pad($store_id, 2, '0', STR_PAD_LEFT).'-0003', 'sort_code' => '3', 'parent_id' => 0, 'note' => 'บัญชีค่าใช้จ่ายเริ่มต้น'],
            ['account_name' => 'เงินฝากธนาคาร', 'account_code' => 'AC-'.str_pad($store_id, 2, '0', STR_PAD_LEFT).'-0004', 'sort_code' => '4', 'parent_id' => 0, 'note' => 'บัญชีเงินฝากธนาคารเริ่มต้น'],
        ];

        $i = 1;
        foreach ($accounts as $acc) {
            $data = array_merge($acc, [
                'count_id'     => $i++,
                'store_id'     => $store_id,
                'balance'      => 0,
                'created_date' => $date,
                'created_time' => $time,
                'created_by'   => 'System',
                'system_ip'    => $ip,
                'system_name'  => 'billtax',
                'status'       => 1,
                'delete_bit'   => 0
            ]);
            $this->_raw_mysqli_insert($mysqli, 'ac_accounts', $data);
        }
    }

	public function create_url_sms_api($store_id){
		$q1=$this->db->select("*")->where("store_id",$store_id)->get("db_smsapi");
		if($q1->num_rows()==0){
			$insertArray = [
			   [
			      'store_id' => $store_id,
			      'info' => 'url',
			      'key' => 'weblink',
			      'key_value' => 'http://example.com/sendmessage',
			   ],
			   [
			      'store_id' => $store_id,
			      'info' => 'mobile',
			      'key' => 'mobiles',
			      'key_value' => '',
			   ],
			   [
			      'store_id' => $store_id,
			      'info' => 'message',
			      'key' => 'message',
			      'key_value' => '',
			   ],
			   
			];
			if(!$this->db->insert_batch('db_smsapi', $insertArray)){
				return false;
			}
		}
		return true;
	}

	public function create_url_sms_templates($store_id){
		$q1=$this->db->select("*")->where("store_id",$store_id)->get("db_smstemplates");
		if($q1->num_rows()==0){
			$insertArray = [
			   [
			      'store_id' => $store_id,
			      'template_name' => 'GREETING TO CUSTOMER ON SALES',
			      'content' => "Hi {{customer_name}},
Your sales Id is {{sales_id}},
Sales Date {{sales_date}},
Total amount  {{sales_amount}},
You have paid  {{paid_amt}},
and due amount is  {{due_amt}}
Thank you Visit Again",
			      'variables' => "{{customer_name}}                          
{{sales_id}}
{{sales_date}}
{{sales_amount}}
{{paid_amt}}
{{due_amt}}
{{store_name}}
{{store_mobile}}
{{store_address}}
{{store_website}}
{{store_email}}
",
				//'status'	=> 1,
				'undelete_bit'	=> 1,
			   ],
			   [
			      'store_id' => $store_id,
			      'template_name' => 'GREETING TO CUSTOMER ON SALES RETURN',
			      'content' => "Hi {{customer_name}},
Your sales return Id is {{return_id}},
Return Date {{return_date}},
Total amount  {{return_amount}},
We paid  {{paid_amt}},
and due amount is  {{due_amt}}
Thank you Visit Again",
			      'variables' => "{{customer_name}}                          
{{return_id}}
{{return_date}}
{{return_amount}}
{{paid_amt}}
{{due_amt}}
{{company_name}}
{{company_mobile}}
{{company_address}}
{{company_website}}
{{company_email}}
",
				//'status'	=> 1,
				'undelete_bit'	=> 1,
			   ],
			   
			];
			if(!$this->db->insert_batch('db_smstemplates', $insertArray)){
				return false;
			}
		}
		return true;
	}

	public function save_registration(){
		extract($this->security->xss_clean(html_escape(array_merge($this->data,$this->store_making_codes(),$_POST,$_GET))));
		$country = $this->db->select("country")->where("id",$country)->get("db_country")->row()->country;
		$state = $this->db->select("state")->where("id",$state)->get("db_states")->row()->state;

		$this->db->query("ALTER TABLE db_store AUTO_INCREMENT = 1");
		$this->db->trans_begin();
		$data = array(
		    				'store_code'				=> $store_code,
		    				'store_name'				=> $store_name,
		    				'store_website'				=> '',
		    				'mobile'					=> $mobile,
		    				'phone'						=> '',
		    				'email'						=> $email,
		    				'country'					=> $country,
		    				'state'						=> $state,
		    				'city'						=> $city,
		    				'address'					=> ' ',
		    				'postcode'					=> '',
		    				'bank_details'				=> '',
		    				'category_init'				=> $category_init,
		    				'item_init'					=> $item_init,
		    				'supplier_init'				=> $supplier_init,
		    				'purchase_init'				=> $purchase_init,
		    				'purchase_return_init'		=> $purchase_return_init,
		    				'customer_init'				=> $customer_init,
		    				'sales_init'				=> $sales_init,
		    				'sales_return_init'			=> $sales_return_init,
		    				'receipt_init'				=> $receipt_init,
		    				'sales_debit_init'			=> $sales_debit_init,
		    				'expense_init'				=> $expense_init,
		    				'quotation_init'			=> $quotation_init,
		    				'money_transfer_init'		=> $money_transfer_init,
		    				'accounts_init'				=> $accounts_init,
		    				'currency_id'				=> (isset($currency) && !empty($currency)) ? $currency : $currency_id,
		    				'currency_placement'		=> $currency_placement,
		    				'timezone'					=> $timezone,
		    				'date_format'				=> $date_format,
		    				'time_format'				=> $time_format,
		    				'sales_discount'			=> $sales_discount,
		    				'change_return'				=> $change_return,
		    				'mrp_column'				=> $mrp_column,
		    				'show_signature'				=> $show_signature,
		    				'previous_balance_bit'		=> $previous_balance_bit,
		    				'sales_invoice_format_id'	=> $sales_invoice_format_id,
		    				'pos_invoice_format_id'		=> $pos_invoice_format_id,
		    				'sales_invoice_footer_text'	=> $sales_invoice_footer_text,
		    				'invoice_terms'				=> $invoice_terms,
		    				'round_off'					=> $round_off,
		    				'decimals'					=> $decimals,
		    				'qty_decimals'					=> $qty_decimals,
		    				'sales_payment_init'		=> $sales_payment_init,
		    				'sales_return_payment_init'	=> $sales_return_payment_init,
		    				'purchase_payment_init'		=> $purchase_payment_init,
		    				'purchase_return_payment_init'	=> $purchase_return_payment_init,
		    				'expense_payment_init'	=> $expense_payment_init,
		    				'cust_advance_init'	=> $cust_advance_init,
		    				'invoice_init'	=> $invoice_init,
		    			);


		
			$store_code_count = $this->db->where('upper(store_code)', strtoupper($store_code))->count_all_results('db_store');
			if($store_code_count > 0){
				echo "Sorry! Store Code Already Exist!\nPlease Change Store Code";exit();
			}
			$extra_info = array(
							'invoice_view'				=> 1,
		    				'sms_status'				=> 0,
		    				'language_id'				=> $language_id,
		    				/*System Info*/
		    				'created_date' 				=> $CUR_DATE,
		    				'created_time' 				=> $CUR_TIME,
		    				'created_by' 				=> $first_name,
		    				'system_ip' 				=> $SYSTEM_IP,
		    				'system_name' 				=> $SYSTEM_NAME,
		    				'status' 					=> 1,
		    			);

			$data=array_merge($data,$extra_info);
			
			$q1 = $this->db->insert('db_store', $data);
			if(!$q1){
				echo "failed";exit();
			}

			$store_id = $this->db->insert_id();
			$this->load->model('customers_model');

			$q2=$this->customers_model->create_walk_in_customer($store_id);

			$q3 = $this->create_default_warehouse($store_id,null,null);
			if(!$q3){
				echo "failed";exit();
			}

			//Create User
			if(!empty($email)){
				$query = $this->db->where('email', $email)->get('db_users')->num_rows();
				if($query > 0){ return "This Email ID already exist.";}
			}
			$info = array(
			    				'username' 				=> $first_name, 
			    				'last_name' 			=> $last_name, 
			    				'password' 				=> password_hash($password, PASSWORD_BCRYPT), 
			    				'mobile' 				=> $mobile,
			    				'email' 				=> $email,
			    				/*System Info*/
			    				'created_date' 			=> $CUR_DATE,
			    				'created_time' 			=> $CUR_TIME,
			    				'created_by' 			=> $CUR_USERNAME,
			    				'system_ip' 			=> $SYSTEM_IP,
			    				'system_name' 			=> $SYSTEM_NAME,
			    				'status' 				=> 1,
			    			);
			if(!empty($profile_picture)){
				$info['profile_picture'] = $profile_picture;
			}
			
			$info['role_id'] = store_admin_id();
			
			$info['store_id']=(store_module()) ? $store_id : $this->session->userdata('store_id');	
			$q1 = $this->db->insert('db_users', $info);
			if (!$q1){
				return "failed";
			}
			$user_id = $this->db->insert_id();

			//Update default warehouse id

			$warehouse_id = $this->db->select("id")->where("store_id",$store_id)->get("db_warehouse")->row()->id;

			$this->db->set("default_warehouse_id", $warehouse_id)->where("id",$user_id)->update("db_users");

			//UPDATE THE USER ID INTO STORE
			$this->db->set("user_id",$user_id)
						->where("id",$store_id)
						->update("db_store");
						
			if(warehouse_module() && isset($_POST['warehouses']) && $role_id!=1 && $role_id!=store_admin_id()){
				$warehouses_list = sizeof($_POST['warehouses']);
				foreach ($_POST['warehouses'] as $res => $val) {
					$warehouse_info = array ( 'user_id'=> $user_id, 'warehouse_id'=>$val );
					$q2 = $this->db->insert("db_userswarehouses",$warehouse_info);
					if (!$q2){
						return "failed";
					}
				}
			}

			if(!$this->create_url_sms_api($store_id)){
				return "failed";
			}
			if(!$this->create_url_sms_templates($store_id)){
				return "failed";
			}
			
			$this->db->trans_commit();
			$this->session->set_flashdata('success', 'Account created Succssfully!! Please Login!');
			return "success";
			

		
	}
	public function verify_and_save(){

		//Filtering XSS and html escape from user inputs 
		extract($this->security->xss_clean(html_escape(array_merge($this->data,$_POST,$_GET))));
		
		$this->db->trans_begin();

		
		$store_logo='';
		if(!empty($_FILES['store_logo']['name'])){
			$config['upload_path']          = './uploads/store/';
	        $config['allowed_types']        = 'gif|jpg|jpeg|png';
	        $config['max_size']             = 1000;
	        $config['max_width']            = 1000;
	        $config['max_height']           = 1000;

	        $this->load->library('upload', $config);

	        if ( ! $this->upload->do_upload('store_logo'))
	        {
	                $error = array('error' => $this->upload->display_errors());
	                return $error['error'];
	                exit();
	        }
	        else
	        {
	        	   $store_logo='uploads/store/'.$this->upload->data('file_name');
	        }
		}

		$signature='';
		if(!empty($_FILES['signature']['name'])){
			$config['upload_path']          = './uploads/signature/';
	        $config['allowed_types']        = 'gif|jpg|jpeg|png';
	        $config['max_size']             = 1000;
	        $config['max_width']            = 1000;
	        $config['max_height']           = 1000;

	        $this->load->library('upload', $config);

	        if ( ! $this->upload->do_upload('signature'))
	        {
	                $error = array('error' => $this->upload->display_errors());
	                return $error['error'];
	                exit();
	        }
	        else
	        {
	        	   $signature='uploads/signature/'.$this->upload->data('file_name');
	        }
		}

		$change_return = (isset($change_return)) ? 1 : 0;
		$mrp_column = (isset($mrp_column)) ? 1 : 0;
		$show_signature = (isset($show_signature)) ? 1 : 0;
		$previous_balance_bit = (isset($previous_balance_bit)) ? 1 : 0;
		$round_off = (isset($round_off)) ? 1 : 0;


		$data = array(
		    				'store_code'				=> $store_code,
		    				'store_name'				=> $store_name,
		    				'store_website'				=> $store_website,
		    				'mobile'					=> $mobile,
		    				'phone'						=> $phone,
		    				'email'						=> $email,
		    				'country'					=> $country,
		    				'state'						=> $state,
		    				'city'						=> $city,
		    				'address'					=> $address,
		    				'postcode'					=> $postcode,
		    				'bank_details'				=> $bank_details,
		    				'category_init'				=> $category_init,
		    				'item_init'					=> $item_init,
		    				'supplier_init'				=> $supplier_init,
		    				'purchase_init'				=> $purchase_init,
		    				'purchase_return_init'		=> $purchase_return_init,
		    				'customer_init'				=> $customer_init,
		    				'sales_init'				=> $sales_init,
		    				'sales_return_init'			=> $sales_return_init,
		    				'sales_debit_init'			=> $sales_debit_init,
		    				'expense_init'				=> $expense_init,
		    				'quotation_init'			=> $quotation_init,
		    				'money_transfer_init'		=> $money_transfer_init,
		    				'accounts_init'				=> $accounts_init,
		    				'currency_id'				=> $currency,
		    				'currency_placement'		=> $currency_placement,
		    				'timezone'					=> $timezone,
		    				'date_format'				=> $date_format,
		    				'time_format'				=> $time_format,
		    				'sales_discount'			=> $sales_discount,
		    				'change_return'				=> $change_return,
		    				'mrp_column'				=> $mrp_column,
		    				'show_signature'				=> $show_signature,
		    				'previous_balance_bit'				=> $previous_balance_bit,
		    				'sales_invoice_format_id'	=> $sales_invoice_format_id,
		    				'pos_invoice_format_id'		=> $pos_invoice_format_id,
		    				'sales_invoice_footer_text'	=> $sales_invoice_footer_text,
		    				'invoice_terms'				=> $invoice_terms,
		    				'round_off'					=> $round_off,
		    				'decimals'					=> $decimals,
		    				'qty_decimals'					=> $qty_decimals,
		    				'sales_payment_init'		=> $sales_payment_init,
		    				'sales_return_payment_init'	=> $sales_return_payment_init,
		    				'purchase_payment_init'		=> $purchase_payment_init,
		    				'purchase_return_payment_init'	=> $purchase_return_payment_init,
		    				'expense_payment_init'	=> $expense_payment_init,
		    				'cust_advance_init'	=> $cust_advance_init,
		    				't_and_c_status'	=> $t_and_c_status,
		    				't_and_c_status_pos'	=> $t_and_c_status_pos,
		    				'number_to_words'	=> $number_to_words,
		    				'default_account_id'	=> (!empty($default_account_id))?$default_account_id:null,
		    			);

		if(!empty($store_logo)){
			$data['store_logo']=$store_logo;
		}

		if(!empty($signature)){
			$data['signature']=$signature;
		}

		/*custom helper*/
		if(gst_number()){
			$data['gst_no']=$gst_no;
		}
		if(vat_number()){
			$data['vat_no']=$vat_no;
		}
		if(pan_number()){
			$data['pan_no']=$pan_no;
		}
		/*end*/

		


		if($command=='save'){
			$store_code_count = $this->db->where('upper(store_code)', strtoupper($store_code))->count_all_results('db_store');
			if($store_code_count > 0){
				echo "Sorry! Store Code Already Exist!\nPlease Change Store Code";exit();
			}
			$extra_info = array(
							'invoice_view'				=> 1,
		    				'sms_status'				=> 0,
		    				'language_id'				=> $language_id,
		    				/*System Info*/
		    				'created_date' 				=> $CUR_DATE,
		    				'created_time' 				=> $CUR_TIME,
		    				'created_by' 				=> $CUR_USERNAME,
		    				'system_ip' 				=> $SYSTEM_IP,
		    				'system_name' 				=> $SYSTEM_NAME,
		    				'status' 					=> 1,
		    			);
			$data=array_merge($data,$extra_info);
			$q1 = $this->db->insert('db_store', $data);
			$store_id = $this->db->insert_id();
			$this->load->model('customers_model');
			$q2=$this->customers_model->create_walk_in_customer($store_id);

			if(!$this->create_url_sms_api($store_id)){
				return "failed";
			}
			if(!$this->create_url_sms_templates($store_id)){
				return "failed";
			}
			$q3 = $this->create_default_warehouse($store_id,null,null);
			if(!$q3){
				echo "failed";exit();
			}
			if($q1){
				$this->db->trans_commit();
				$this->session->set_flashdata('success', 'Success!! Record Saved Successfully! ');
				echo "success";
			}

		}
		
		exit();
	}

	//Get store_details
	public function get_details($id){
		$data=$this->data;

		$query1 = $this->db->where('upper(id)', strtoupper($id))->get('db_store');
		if($query1->num_rows() == 0){
			show_404();exit;
		}
		else{
			/* QUERY 1*/
			$query=$this->db->query("select * from db_sitesettings order by id asc limit 1");
			$query=$query->row();
			$data['q_id']=$query1->row()->id;
			return array_merge($data,$query1->row_array());
			return $data;
		}
	}


	public function update_status($id,$status){
       if (set_status_of_table($id,$status,'db_store')){
            echo "success";
        }
        else{
            echo "failed";
        }
	}
	public function delete_store_from_table($ids){
        // 1. Get database names to drop
        $stores = $this->db->select('database_name')->where_in('id', $ids)->where('id!=1')->get('db_store')->result();
        $db_names = array();
        foreach($stores as $s) if(!empty($s->database_name)) $db_names[] = $s->database_name;
        
        $this->db->trans_begin();
        $query1=$this->db->where_in('id',$ids)->where('id!=1')->delete('db_store');
        if ($query1){
            $this->db->trans_commit();
            // 3. Drop databases on commit success
            $this->_drop_tenant_dbs($db_names);
            return "success";
        }
        else{
            return "failed";
        }	
	}
	public function create_default_warehouse($store_id,$mobile='',$email='',$enable_flashdata=true){
		$created_date = date("Y-m-d");
		
		$data = array(
            'store_id'       => $store_id,
            'warehouse_type' => 'System',
            'warehouse_name' => 'System Warehouse',
            'mobile'         => $mobile,
            'email'          => $email,
            'created_date'   => $created_date
        );

        // Check if status column exists and set it to 1 (Active)
        if($this->db->field_exists('status', 'db_warehouse')){
            $data['status'] = 1;
        }

		if ($this->db->insert('db_warehouse', $data)){
				if($enable_flashdata){
					$this->session->set_flashdata('success', 'Success!! New Warehouse Created Succssfully!!');
				}
		        return "success";
		}
		else{
		        return "failed";
		}
	}


	public function create_store_registration($inputs){
		// 1. Prepare Store Data
		$codes = $this->store_making_codes();
		$store_data = array_merge($codes, $inputs);
		
		// Ensure specific fields are set and clean
		$store_data['store_code'] = $inputs['store_code'] ?? $codes['store_code'];
		$store_data['created_date'] = date("Y-m-d");
		$store_data['created_time'] = date("H:i:s");
		$store_data['created_by']   = 'System';
		$store_data['status']       = 1;
		$store_data['invoice_view'] = 1;
		$store_data['language_id']  = 2;
		
		// Handle Country/State lookup if IDs are provided
		if(isset($store_data['country']) && is_numeric($store_data['country'])){
			$q = $this->db->select("country")->where("id",$store_data['country'])->get("db_country");
			if($q->num_rows() > 0){
				$store_data['country'] = $q->row()->country;
			}
		}
		if(isset($store_data['state']) && is_numeric($store_data['state'])){
			$q = $this->db->select("state")->where("id",$store_data['state'])->get("db_states");
			if($q->num_rows() > 0){
				$store_data['state'] = $q->row()->state;
			}
		}

		// Remove fields that strictly belong to User table or are not in db_store
		$user_password = $store_data['password'];
		$contact_name  = $store_data['contact_name']; // Extract for User creation
		$package_id = $store_data['package_id'] ?? null; 
		$payment_method = $store_data['payment_method'] ?? 'trial';
		$payment_slip = $store_data['payment_slip'] ?? null;

		unset($store_data['password']);
		unset($store_data['cpassword']);
		unset($store_data['contact_name']); // Remove from store insert
		unset($store_data['package_id']); 
		unset($store_data['payment_method']); 
		unset($store_data['payment_slip']);
		unset($store_data['stripeToken']);
		unset($store_data['stripeEmail']);
		
		// --- VALIDATION AND UNIQUE CHECKS ---
		// Check Store Code uniqueness
		$count = $this->db->where('store_code', $store_data['store_code'])->count_all_results('db_store');
		if($count > 0){
			return "Store Code already exists, please try again.";
		}

		// Check Email uniqueness (db_users)
		if($this->db->where('email', $inputs['email'])->count_all_results('db_users') > 0){
			return "This Email is already registered!";
		}

        // Check Mobile uniqueness (db_users & db_store)
         if($this->db->where('mobile', $inputs['mobile'])->count_all_results('db_users') > 0){
			return "This Mobile Number is already registered!";
		}

		$this->db->trans_begin();

		try {
		    
		    // DEBUG: Dump Inputs to Screen
		    // ERROR will appear as "Registration Failed: System Error: {"info":...}"
		    // THIS IS TEMPORARY FOR DEBUGGING
		    // throw new Exception(json_encode($store_data));
		    
			// Explicitly unset contact_name again to be absolutely sure
			if(isset($store_data['contact_name'])) {
				unset($store_data['contact_name']);
			}
			
			// Determine Status based on Package Logic
			// Default: Pending/Inactive (0)
			$store_status = 0; 
			// 6. Create Subscription Entry - STRICT CHECK
			if(!$package_id){
			    throw new Exception("System Error: No Package ID received.");
			}

			// DEBUG: Log Package ID
			log_message('error', 'Store Reg - Package ID: ' . $package_id);

			$pkg_query = $this->db->select('package_type, monthly_price, annual_price, package_name, trial_days, max_warehouses, max_users, max_items, max_invoices')->where('id', $package_id)->get('db_package');
			    
			// DEBUG: Log Package Query Results
			log_message('error', 'Store Reg - Pkg Rows: ' . $pkg_query->num_rows());
			
			if($pkg_query->num_rows() == 0){
			    throw new Exception("System Error: Selected Package (ID: $package_id) not found in database.");
			}

			$pkg = $pkg_query->row();
			
			// If "Free" or price is 0 -> Auto Active (1)
			if(strtoupper($pkg->package_type) == 'FREE' || ($pkg->monthly_price == 0 && $pkg->annual_price == 0)){
			    $store_status = 1; 
			}

			// FORCE INACTIVE FOR BANK TRANSFER (OFFLINE PAYMENT)
			if($payment_method == 'bank_transfer'){
			    $store_status = 0;
			}

			// Stripe signup must be charged before activating account.
			// Signup flow has no plan_type selector, so use monthly if available, else annual.
			$stripe_charge_id = null;
			if($payment_method == 'stripe'){
				$stripe_amount = ((float)$pkg->monthly_price > 0) ? (float)$pkg->monthly_price : (float)$pkg->annual_price;
				if($stripe_amount <= 0){
					throw new Exception("Invalid package amount for Stripe payment.");
				}
				$stripe_token = $inputs['stripeToken'] ?? '';
				if(empty($stripe_token)){
					throw new Exception("Stripe token missing.");
				}

				$this->config->load('stripe');
				$stripe_secret_key = $this->config->item('stripe_api_key');
				$stripe_currency = $this->config->item('stripe_currency') ?: 'thb';
				if(empty($stripe_secret_key)){
					throw new Exception("Stripe API key is missing.");
				}

				require_once APPPATH . 'libraries/stripe/init.php';
				\Stripe\Stripe::setApiKey($stripe_secret_key);

				try {
					$charge = \Stripe\Charge::create([
						'amount' => (int) round($stripe_amount * 100),
						'currency' => $stripe_currency,
						'source' => $stripe_token,
						'description' => 'Signup subscription for ' . $pkg->package_name . ' [' . ($inputs['email'] ?? '') . ']',
						'metadata' => [
							'email' => (string)($inputs['email'] ?? ''),
							'mobile' => (string)($inputs['mobile'] ?? ''),
							'package_id' => (string)$package_id
						]
					]);
					if($charge->status !== 'succeeded'){
						throw new Exception('Stripe charge failed: ' . ($charge->failure_message ?? 'unknown'));
					}
					$stripe_charge_id = $charge->id;
					$store_status = 1;
				} catch (\Exception $e) {
					throw new Exception("Stripe payment failed: " . $e->getMessage());
				}
			}

            // Continue with logic...
			$store_data['status'] = $store_status;
			
			// Remove package_id from store_data if it's not a column in db_store (Safety Measure)
			// Assuming db_store might NOT have package_id since we use subscription table
			// But wait, if it DOES have it, we want it. 
			// Users previous error was "Unknown column status". 
			// If "Unknown column package_id" didn't happen, maybe it exists?
			// Let's keep it for now but Monitor logs.
			
			// Log data for debugging


			// SAFETY FILTER: Only keep fields that actually exist in db_store
			$valid_columns = $this->db->list_fields('db_store');
			$filtered_data = array();
			foreach($store_data as $key => $val) {
				if(in_array($key, $valid_columns)) {
					$filtered_data[$key] = $val;
				}
			}
			$store_data = $filtered_data;



			// 2. Insert Store
			if(!$this->db->insert('db_store', $store_data)){
				$error = $this->db->error();
				throw new Exception("Failed to create store: " . $error['message']);
			}
			$store_id = $this->db->insert_id();

			// 3. Create Walk-in Customer
			$this->load->model('customers_model');
			$this->customers_model->create_walk_in_customer($store_id);

			// 4. Create Default Warehouse
			if($this->create_default_warehouse($store_id, '', '', false) != 'success'){
				throw new Exception("Failed to create warehouse");
			}
			
			// 5. Create User
			$user_data = array(
				'username' => $store_data['store_name'], 
				'first_name' => $contact_name,
				'last_name' => '',
				'password' => password_hash($user_password, PASSWORD_BCRYPT),
				'mobile' => $store_data['mobile'],
				'email' => $store_data['email'],
				'created_date' => date("Y-m-d"),
				'created_time' => date("H:i:s"),
				'status' => 1, // Restored status to ensure user is Active
				'role_id' => store_admin_id(), 
				'store_id' => $store_id,
			);

			if(!$this->db->insert('db_users', $user_data)){
				throw new Exception("Failed to create user");
			}
			$user_id = $this->db->insert_id();

			// 6. Create Subscription Entry
				// Calculate Amount
				$payment_gross = ($pkg->monthly_price > 0) ? $pkg->monthly_price : $pkg->annual_price;

				$subscription_data = array(
					'store_id' => $store_id,
					'package_id' => $package_id,
					'subscription_name' => $pkg->package_name,
					'subscription_date' => date("Y-m-d"),
					'trial_days' => $pkg->trial_days,
					'expire_date' => date('Y-m-d', strtotime("+$pkg->trial_days days")),
					'max_warehouses' => $pkg->max_warehouses,
					'max_users' => $pkg->max_users,
					'max_items' => $pkg->max_items,
					'max_invoices' => $pkg->max_invoices,
					'package_status' => 1, // Active by default (Trial)
					'payment_status' => ($store_status==1) ? 'Paid' : 'Unpaid',
                    'payment_gross' => $payment_gross, // Added payment_gross
					'created_date' => date("Y-m-d"),
					'created_time' => date("H:i:s"),
					'created_by' => 'System'
				);

				// Process Payment if Stripe
				if($payment_method == 'stripe'){
					$subscription_data['payment_type'] = 'Stripe';
					$subscription_data['payment_status'] = 'Paid';
					$subscription_data['package_status'] = 1;
				} 

				else if ($payment_method == 'bank_transfer') {
					$subscription_data['payment_type'] = 'Bank Transfer';
					$subscription_data['payment_status'] = 'Unpaid'; // Explicitly set Unpaid
					$subscription_data['package_status'] = 0; // Explicitly set Inactive

                    // INSERT INTO db_offline_requests
                    $off_data = array(
                        'store_id' => $store_id,
                        'package_id' => $package_id,
                        'status' => 0, // Pending
                        'payment_slip' => $payment_slip,
                        'created_date' => date("Y-m-d"),
                        'created_time' => date("H:i:s"),
                        // Determine Amount and Plan Type
                        'amount' => ($pkg->monthly_price > 0) ? $pkg->monthly_price : $pkg->annual_price,
                        'plan_type' => ($pkg->monthly_price > 0) ? 'Monthly' : 'Annually', // Simple Assumption
                    );
                    $this->db->insert('db_offline_requests', $off_data);

                    // --- Send Email Notification to Superadmin ---
                    try {
                        $this->load->model('Email_model');
                        $admin_store = $this->db->select('store_name, email')->where('id', 1)->get('db_store')->row();
                        $store_info = array('store_name' => $inputs['store_name']);
                        
                        if ($admin_store) {
                            $to_email = $admin_store->email;
                            
                            // Fallback to SMTP user if store email is placeholder
                            if ($to_email == 'superadmin@example.com') {
                                $smtp = $this->db->select('smtp_user')->where('store_id', 1)->get('db_smtp')->row();
                                if ($smtp && !empty($smtp->smtp_user)) {
                                    $to_email = $smtp->smtp_user;
                                }
                            }

                            if (!empty($to_email)) {
                                $subject = "New Store Signup & Offline Request: " . $inputs['store_name'];
                                $msg = "Dear Superadmin,<br><br>A new store has registered and submitted an offline payment request:<br><br>" .
                                       "<b>Store:</b> " . $inputs['store_name'] . "<br>" .
                                       "<b>Package:</b> " . ($pkg->package_name ?? 'N/A') . "<br>" .
                                       "<b>Amount:</b> " . $off_data['amount'] . "<br>" .
                                       "<b>Plan Type:</b> " . $off_data['plan_type'] . "<br><br>" .
                                       "Please login to Superadmin to review and approve the request.<br><br>Best regards,<br>System Notification";
                                
                                $this->Email_model->send_email_with_store_id([
                                    'to' => $to_email,
                                    'subject' => $subject,
                                    'message' => $msg
                                ], 1);
                            }
                        }
                    } catch (Exception $e) {
                        log_message('error', "Failed to send offline signup notification: " . $e->getMessage());
                    }
                    // ----------------------------------------------
				}

				if(!$this->db->insert('db_subscription', $subscription_data)){
					throw new Exception("Failed to create subscription");
				}
				$subscription_id = $this->db->insert_id();

				// Update Store with current subscription link
				$this->db->set('current_subscriptionlist_id', $subscription_id)
						 ->where('id', $store_id)
						 ->update('db_store');


			//Update default warehouse id
			$warehouse_id = $this->db->select("id")->where("store_id",$store_id)->get("db_warehouse")->row()->id;
			$this->db->set("default_warehouse_id", $warehouse_id)->where("id",$user_id)->update("db_users");

			//UPDATE THE USER ID INTO STORE
			$this->db->set("user_id",$user_id)->where("id",$store_id)->update("db_store");


            // In-database defaults moved to provision_tenant_database
            // $this->copy_default_settings($store_id); 

			$this->db->trans_commit();

            // MULTI-TENANT: Provision new database automatically (AFTER commit)
            $db_name = $this->provision_tenant_database($store_id, $store_data['store_code']);

			return "success";
		} catch (Exception $e) {
			$this->db->trans_rollback();
			log_message('error', 'Registration Failed: ' . $e->getMessage());
			return "Registration Failed: " . $e->getMessage();
		}
	}
	public function copy_default_settings($new_store_id) {
    // 1. Copy Taxes from Superadmin (Store ID 1)
    $taxes = $this->db->get_where('db_tax', ['store_id' => 1, 'status' => 1])->result_array();
    foreach ($taxes as $tax) {
        unset($tax['id']); // Remove ID to generate new one
        $tax['store_id'] = $new_store_id; // Set new Store ID
        $this->db->insert('db_tax', $tax);
    }
 
    // 2. Copy Units
    $units = $this->db->get_where('db_units', ['store_id' => 1, 'status' => 1])->result_array();
    foreach ($units as $unit) {
        unset($unit['id']);
        $unit['store_id'] = $new_store_id;
        $this->db->insert('db_units', $unit);
    }
 
    // 3. Copy Payment Types
    $payment_types = $this->db->get_where('db_paymenttypes', ['store_id' => 1, 'status' => 1])->result_array();
    foreach ($payment_types as $pt) {
        unset($pt['id']);
        $pt['store_id'] = $new_store_id;
        $this->db->insert('db_paymenttypes', $pt);
    }
    
    // 4. Create Default Accounts
    $this->create_default_accounts($new_store_id);
}

private function create_default_accounts($store_id) {
    $date = date("Y-m-d");
    $time = date("H:i:s");
    $ip = $this->input->ip_address();
    
    $accounts = [
        [
            'account_name' => 'รายรับ',
            'account_code' => 'AC-'.str_pad($store_id, 2, '0', STR_PAD_LEFT).'-0001',
            'sort_code'    => '1',
            'parent_id'    => 0,
            'note'         => 'บัญชีรายรับเริ่มต้น',
        ],
        [
            'account_name' => 'รายจ่ายซื้อสินค้า',
            'account_code' => 'AC-'.str_pad($store_id, 2, '0', STR_PAD_LEFT).'-0002',
            'sort_code'    => '2',
            'parent_id'    => 0,
            'note'         => 'บัญชีรายจ่ายเริ่มต้น',
        ],
        [
            'account_name' => 'รายจ่ายดำเนินงาน',
            'account_code' => 'AC-'.str_pad($store_id, 2, '0', STR_PAD_LEFT).'-0003',
            'sort_code'    => '3',
            'parent_id'    => 0,
            'note'         => 'บัญชีค่าใช้จ่ายเริ่มต้น',
        ],
        [
            'account_name' => 'เงินฝากธนาคาร',
            'account_code' => 'AC-'.str_pad($store_id, 2, '0', STR_PAD_LEFT).'-0004',
            'sort_code'    => '4',
            'parent_id'    => 0,
            'note'         => 'บัญชีเงินฝากธนาคารเริ่มต้น',
        ],
    ];

    foreach ($accounts as $acc) {
        $data = array_merge($acc, [
            'count_id'     => (function_exists('get_count_id')) ? get_count_id('ac_accounts') : 0, 
            'store_id'     => $store_id,
            'balance'      => 0,
            'created_date' => $date,
            'created_time' => $time,
            'created_by'   => 'System',
            'system_ip'    => $ip,
            'system_name'  => 'flow',
            'status'       => 1,
            'delete_bit'   => 0
        ]);
        $this->db->insert('ac_accounts', $data);
    }
}
}
