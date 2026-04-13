<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_logs extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('email_model', 'email');
	}

	public function index(){
		$this->permission_check('send_email'); // Reusing send_email permission for history
		$data = $this->data;
		$data['page_title'] = "Email History";
		$this->load->view('email_logs_list', $data);
	}

	public function ajax_list()
	{
		$this->db->select('*')
				 ->from('db_email_logs')
				 ->where('store_id', get_current_store_id());

		if(!empty($_POST['search']['value'])) {
			$search = $_POST['search']['value'];
			$this->db->group_start()
					 ->like('email_to', $search)
					 ->or_like('subject', $search)
					 ->or_like('status', $search)
					 ->group_end();
		}

		$this->db->order_by('id', 'desc');
		
		if(isset($_POST['length']) && $_POST['length'] != -1) {
			$this->db->limit($_POST['length'], $_POST['start']);
		}

		$query = $this->db->get();
		$data = array();
		header('Content-Type: application/json');

		foreach ($query->result() as $res) {
			$row = array();
			$row[] = show_date($res->created_date) . ' ' . $res->created_time;
			$row[] = $res->email_to;
			$row[] = $res->subject;
			
			$status_class = ($res->status == 'Sent') ? 'label-success' : 'label-danger';
			$row[] = '<span class="label '.$status_class.'">'.$res->status.'</span>';
			
			$action = '<div class="btn-group" title="View Action">';
			$action .= '<a class="btn btn-primary btn-xs" title="View Details" onclick="view_details('.$res->id.')"><i class="fa fa-fw fa-eye"></i></a>';
			$action .= '<a class="btn btn-danger btn-xs" title="Delete Log" onclick="delete_log('.$res->id.')"><i class="fa fa-fw fa-trash"></i></a>';
			$action .= '</div>';
			$row[] = $action;
			$data[] = $row;
		}

		$draw = (isset($_POST['draw'])) ? $_POST['draw'] : 0;
		$output = array(
			"draw" => $draw,
			"recordsTotal" => $this->db->where('store_id', get_current_store_id())->count_all_results('db_email_logs'),
			"recordsFiltered" => $this->db->where('store_id', get_current_store_id())->count_all_results('db_email_logs'),
			"data" => $data,
		);
		echo json_encode($output);
	}

	public function get_details() {
		$id = $this->input->post('id');
		$log = $this->db->where('id', $id)->where('store_id', get_current_store_id())->get('db_email_logs')->row();
		if($log) {
			echo json_encode([
				'status' => 'success',
				'data' => $log
			]);
		} else {
			echo json_encode(['status' => 'failed']);
		}
	}

	public function delete_log() {
		$id = $this->input->post('id');
		$q1 = $this->db->where('id', $id)->where('store_id', get_current_store_id())->delete('db_email_logs');
		if($q1) {
			echo "success";
		} else {
			echo "failed";
		}
	}
}
?>
