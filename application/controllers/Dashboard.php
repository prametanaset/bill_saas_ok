<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		if($this->get_current_version_of_db()!=app_version()){ redirect(base_url('updates/update_db')); }
	}
	public function dashboard_values(){
		$this->load->model('dashboard_model');//Model
		$data=$this->dashboard_model->breadboard_values();//Model->Method
		echo json_encode($data);
	}

	public function index($val='')
	{	
	
		$this->load->model('dashboard_model');//Model

		$data=array_merge($this->data,$this->dashboard_model->get_bar_chart(),$this->dashboard_model->get_pie_chart());
		if(is_admin()){
			// sync_stores_to_customers(); // Removed as it was undefined
			// $data = array_merge($data,$this->dashboard_model->get_subscription_chart()); // Removed as it was undefined
			
			// New Data for Super Admin Dashboard
			$this->load->model('store_model');
			if(method_exists($this->store_model, 'get_store_sum_count')){
				$data['sumcount'] = $this->store_model->get_store_sum_count();
			}
			$data['support_list'] = $this->dashboard_model->get_store_support_list();
			$data['all_stores_list_php'] = $this->dashboard_model->get_all_stores_list();
		}
		if(!is_admin()){
			$store = get_store_details();
			$data['store_setup_incomplete'] = (
				empty(trim((string)$store->store_name)) ||
				empty(trim((string)$store->phone)) ||
				empty(trim((string)$store->address)) ||
				empty(trim((string)$store->gst_no))
			);
			$data['store_setup_url'] = base_url('store_profile/update/'.get_current_store_id());
		} else {
			$data['store_setup_incomplete'] = false;
			$data['store_setup_url'] = '';
		}
		$data['page_title']=$this->lang->line('dashboard');
		if(isset($_POST['store_id'])){
			$data['store_id'] =$_POST['store_id'];
		}
		if(!$this->permissions('dashboard_view')){
			$this->load->view('role/dashboard_empty',$data);
		}
		else{
			$this->load->view('dashboard',$data);
		}
		
	}
	public function get_storewise_details($from='All'){
			// Show stores registered within the last 7 days
			$this->db->where("a.created_date > DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
			$this->db->where("a.id!=", 1); // Exclude Super Admin Store
			$this->db->order_by("a.id", "desc");
			$this->db->select("a.*, b.subscription_name, b.payment_status, b.expire_date, b.trial_days, b.sales_id");
			$this->db->from("db_store a");
			$this->db->join("db_subscription b", "b.id = a.current_subscriptionlist_id", "left");
			$q1=$this->db->get();

		        if($q1->num_rows()>0){
		          $i=1;
		          foreach ($q1->result() as $store){
		          	
					// Determine Status (Logic same as Dashboard view for consistency)
					$display_status = '';
					$label_class = 'label-default';

					$is_inactive = ($store->status == 0);
					$is_expired = (!empty($store->expire_date) && $store->expire_date < date('Y-m-d'));
					$is_pending = ($store->payment_status == 'Pending');
					$is_new = (strtotime($store->created_date) > strtotime('-7 days'));
					$is_upgraded = (isset($store->trial_days) && $store->trial_days == 0);

					if($is_expired){
						$display_status = 'Pending';
						$label_class = 'label-warning';
					}
					else if($is_inactive){
						$display_status = 'Inactive';
						$label_class = 'label-danger';
					}
					elseif($is_pending){
						$display_status = 'Pending Payment';
						$label_class = 'label-warning';
					}
					elseif($is_upgraded){
						$display_status = 'RenewPlan';
						$label_class = 'label-success';
					}
					elseif($is_new){
						$display_status = 'New Store';
						$label_class = 'label-info';
					}
					else{
						$display_status = 'Active';
						$label_class = 'label-success';
					}

		            echo "<tr>";
		            echo "<td>".$i++."</td>";
		            echo "<td>".$store->store_name."</td>";
		            echo "<td>".show_date($store->created_date)." / ".$store->created_time."</td>";
		            echo "<td>".(!empty($store->subscription_name) ? $store->subscription_name : 'No Package')."</td>";
		            echo "<td><span class='label $label_class'>$display_status</span></td>";
		            echo "<td>";
		            if(!empty($store->sales_id)){
		            	echo "<a href='".base_url("sales/invoice/$store->sales_id")."' target='_blank' class='btn btn-xs btn-success' title='View Invoice'><i class='fa fa-file-text-o'></i> ใบแจ้งหนี้</a>";
		            }
		            echo "</td>";
		            echo "</tr>";
		          }//foreach
		        }
		
	}

	public function ajax_list() {
		$this->load->model('dashboard_model','items');
		$list = $this->items->get_datatables();

		$data = array();
		$no = $_POST['start'];
		foreach ($list as $items) {
			$no++;
			$row = array();
			$row[] = $items->item_code;
			$row[] = $items->item_name;
			$row[] = $items->category_name;
			$row[] = $items->brand_name;
			$row[] = $items->stock;
			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->items->count_all(),
			"recordsFiltered" => $this->items->count_filtered(),
			"data" => $data,
		);
		//output to json format
		echo json_encode($output);
	}

	public function get_notification_counts() {
		// Capture current time to define the window upper bound
		$current_server_time = date('Y-m-d H:i:s');

		if($this->input->post('last_check_time')){
			$last_check_time = $this->input->post('last_check_time');
			$notifications = [];

			// 1. Check New Paid Subscriptions (Stripe/Online/Free)
			$this->db->select("id, subscription_name, created_date, created_time, payment_type");
			$this->db->where("payment_status", 'Paid');
			$this->db->where("CONCAT(created_date, ' ', created_time) > ", $last_check_time);
			$this->db->where("CONCAT(created_date, ' ', created_time) <= ", $current_server_time);
			$query = $this->db->get("db_subscription");
			
			foreach ($query->result() as $row) {
				$msg = 'New Subscription Payment Received: ' . $row->subscription_name;
				if(strtoupper($row->payment_type) == 'FREE'){
					$msg = 'New Free Registration: ' . $row->subscription_name;
				}

				$notifications[] = [
					'type' => 'success',
					'message' => $msg,
					'timestamp' => $row->created_date . ' ' . $row->created_time
				];
			}

			// 2. Check New Offline Requests
			$this->db->select("id, created_date, created_time");
			$this->db->where("status", 0); // Pending
			$this->db->where("CONCAT(created_date, ' ', created_time) > ", $last_check_time);
			$this->db->where("CONCAT(created_date, ' ', created_time) <= ", $current_server_time);
			$query2 = $this->db->get("db_offline_requests");
			foreach ($query2->result() as $row) {
				$notifications[] = [
					'type' => 'info',
					'message' => 'New Offline Payment Request Pending!',
					'timestamp' => $row->created_date . ' ' . $row->created_time
				];
			}

			echo json_encode(['notifications' => $notifications, 'server_time' => $current_server_time]);
		} else {
			// First load, just return server time
			echo json_encode(['notifications' => [], 'server_time' => $current_server_time]);
		}
	}

	public function update_support_status_ajax(){
		$this->form_validation->set_rules('store_id', 'Store ID', 'required');
		$this->form_validation->set_rules('status', 'Status', 'required');
		
		if ($this->form_validation->run() == TRUE) {
			$store_id = $this->input->post('store_id');
			$status = $this->input->post('status');
			$note = $this->input->post('note');
			
			$this->load->model('dashboard_model');
			$result = $this->dashboard_model->update_support_status($store_id, $status, $note);
			
			if($result){
				echo "success";
			} else {
				echo "failed";
			}
		} else {
			echo "failed";
		}
	}
	public function get_monthly_paid_comparison_ajax() {
		$year = $this->input->post('year');
		$this->load->model('dashboard_model');
		$data = $this->dashboard_model->get_monthly_paid_comparison_chart($year);
		echo json_encode($data);
	}
}
