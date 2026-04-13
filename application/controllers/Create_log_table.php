<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Create_log_table extends CI_Controller {

	public function index()
	{
		$sql = "CREATE TABLE IF NOT EXISTS `db_activity_log` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_id` int(11) NOT NULL,
		  `action` varchar(255) NOT NULL,
		  `item_id` int(11) DEFAULT NULL,
		  `description` text,
		  `created_date` date NOT NULL,
		  `created_time` varchar(50) NOT NULL,
		  `ip_address` varchar(50) NOT NULL,
		  `status` int(1) DEFAULT 1,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

		if ($this->db->query($sql)) {
			echo "Table db_activity_log created successfully or already exists.";
		} else {
			echo "Error creating table: " . $this->db->error()['message'];
		}
	}
}
