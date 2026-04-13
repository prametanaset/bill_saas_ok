<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Temp_check extends MY_Controller {
	public function __construct(){
		parent::__construct();
	}
	public function index(){
		$fields = $this->db->list_fields('db_salesreturn');
		foreach ($fields as $field)
		{
		   echo $field . "\n";
		}
		public function add_column(){
		$this->db->query("ALTER TABLE db_salesreturn ADD COLUMN other_charges_type VARCHAR(255) DEFAULT 'in_percentage' AFTER other_charges_input");
		echo "Column added successfully";
	}
}

