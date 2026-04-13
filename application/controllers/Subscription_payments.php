<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subscription_payments extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load_global();
		$this->load->model('subscription_payments_model', 'payments');
        $this->load->model('activity_log_model'); // Load Log Model
	}

	public function index() {
		if(!is_admin()){
			echo "Restricted";
			exit;
		}
		$this->permission_check('store_view'); // Reuse store_view permission or similar for now
		$data = $this->data;
		$data['page_title'] = $this->lang->line('subscription_payments');
		$this->load->view('subscription-payments-list', $data);
	}

	public function ajax_list() {
		$list = $this->payments->get_datatables();
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $payments) {
			$no++;
			$row = array();
			$row[] = $no;
			$row[] = $payments->store_name;
			$row[] = $payments->subscription_name;
			$row[] = show_date($payments->created_date);
			$row[] = isset($payments->created_time) ? $payments->created_time : '';
			$row[] = $payments->payment_type;
			$row[] = store_number_format($payments->payment_gross);
			
			$status = '';
			if($payments->payment_status == 'Paid'){
				$status = "<span class='label label-success'>Paid</span>";
			}else{
				$status = "<span class='label label-danger'>Pending</span>";
			}
			$row[] = $status;

             // Action Column
            $action = '<div class="btn-group" title="View Account">
                            <a class="btn btn-flat btn-primary" title="Delete" onclick="delete_payment('.$payments->id.')">
                                <i class="fa fa-trash-o"></i>
                            </a>
                        </div>';
            $row[] = $action;
			
			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->payments->count_all(),
			"recordsFiltered" => $this->payments->count_filtered(),
			"data" => $data,
		);
		// Output to JSON format
		echo json_encode($output);
	}

    public function delete_payment(){
        $this->permission_check_with_msg('store_delete'); // Check permission
        $id = $this->input->post('id');
        $reason = $this->input->post('reason');
        
        if(empty($reason)){
            echo "Reason is required";
            exit;
        }

        // Log before delete
        // Get details to log sensible info
        $payment = $this->db->select('b.store_name, a.payment_gross')->from('db_subscription a')->join('db_store b','b.id=a.store_id')->where('a.id', $id)->get()->row();
        $description = '';
        if($payment){
            $description = "Deleted Subscription Payment. Store: {$payment->store_name}, Amount: {$payment->payment_gross}. Reason: {$reason}";
        }
        
        $result = $this->payments->delete_payment_from_table(array($id));
        if($result == 'success'){
            $this->activity_log_model->log_action('DELETE', $id, $description);
            echo "success";
        }else{
            echo "failed";
        }
    }
}
