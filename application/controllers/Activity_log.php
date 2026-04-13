<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Activity_log extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load_global();
		$this->load->model('activity_log_model', 'logs');
	}

	public function index() {
		if(!is_admin()){
			echo "Restricted";
			exit;
		}
		$this->permission_check('store_view'); 
		$data = $this->data;
		$data['page_title'] = "Activity Log";
		$this->load->view('activity-log-list', $data);
	}

	public function ajax_list() {
		$list = $this->logs->get_datatables();
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $log) {
			$no++;
			$row = array();
			$row[] = $no;
			$row[] = show_date($log->created_date);
            $row[] = $log->created_time;
            
            // Get User Name (Maybe join in model is better, but this works for now)
            $user = $this->db->select('username')->where('id', $log->user_id)->get('db_users')->row();
			$row[] = ($user) ? $user->username : 'Unknown';
            
			$row[] = $log->action;
			$row[] = $log->description;
			$row[] = $log->ip_address;
			
			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->logs->count_all(),
			"recordsFiltered" => $this->logs->count_filtered(),
			"data" => $data,
		);
		echo json_encode($output);
	}
}
