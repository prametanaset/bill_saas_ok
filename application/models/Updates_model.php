<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Updates_model extends MY_Model {

	public $app_version = null;
	public $db_version = null;
	public $version_check = array();

	public function __construct()
	{
		parent::__construct();
		//Do your magic here

		//$this->app_version = (float)app_version();

		//$this->version_check =array(2.8);

		$this->db_version = $this->get_current_version_of_db();

	}

	public function get_current_version_of_db(){

      return $this->db->select('version')->from('db_sitesettings')->get()->row()->version;

    }

	public function index()
	{	
		if($this->db_version <= 3.1){
			
			$result = $this->db->query("SHOW COLUMNS FROM `db_store` LIKE 'qty_decimals'");
            if(!$result->num_rows()){
				$this->db->query("ALTER TABLE `db_store` ADD COLUMN `qty_decimals` INT(5) DEFAULT 2 NULL");
            }

            // Sales VAT Columns
            $result = $this->db->query("SHOW COLUMNS FROM `db_sales` LIKE 'vat_amt'");
            if(!$result->num_rows()){
                $this->db->query("ALTER TABLE `db_sales` ADD COLUMN `vat_amt` DOUBLE(20,2) DEFAULT 0.00 NULL");
            }
            $result = $this->db->query("SHOW COLUMNS FROM `db_sales` LIKE 'taxable_amount'");
            if(!$result->num_rows()){
                $this->db->query("ALTER TABLE `db_sales` ADD COLUMN `taxable_amount` DOUBLE(20,2) DEFAULT 0.00 NULL");
            }
			
            // Update version to 3.2 to prevent re-running
            $this->db->query("UPDATE `db_sitesettings` SET `version` = 3.2 WHERE `id` = 1");
		}

	}

}
