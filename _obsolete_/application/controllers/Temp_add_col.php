<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Temp_add_col extends MY_Controller {
	public function __construct(){
		parent::__construct();
	}
	public function index(){
		if (!$this->db->field_exists('other_charges_type', 'db_salesreturn'))
		{
			$this->db->query("ALTER TABLE db_salesreturn ADD COLUMN other_charges_type VARCHAR(255) DEFAULT 'in_percentage' AFTER other_charges_input");
			echo "Column 'other_charges_type' added successfully.\n";
		} else {
			echo "Column 'other_charges_type' already exists.\n";
		}
	}
}
