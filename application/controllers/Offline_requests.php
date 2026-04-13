<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Offline_requests extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load_global();
		$this->load->model('super_admin_payment_model');
	}

	public function index()
	{
        if(!is_admin()){
            echo "Access Denied"; return;
        }
		$data = $this->data;
		$data['page_title'] = 'Offline Requests';
		$data['offline_auto_email'] = $this->db->select('offline_auto_email')->get('db_sitesettings')->row()->offline_auto_email;

		$this->load->view('offline_requests', $data);
	}

    public function ajax_list()
    {
        if(!is_admin()){
            echo json_encode(["data" => []]); return;
        }

        $requests = $this->db->select('bill_xml.db_offline_requests.*, bill_xml.db_package.package_name, bill_xml.db_store.store_name')
									 ->from('bill_xml.db_offline_requests')
									 ->join('bill_xml.db_package', 'bill_xml.db_package.id = bill_xml.db_offline_requests.package_id')
									 ->join('bill_xml.db_store', 'bill_xml.db_store.id = bill_xml.db_offline_requests.store_id')
									 ->order_by('bill_xml.db_offline_requests.id', 'desc')
									 ->get()->result();

        $data = array();
        foreach ($requests as $req) {
            $row = array();
            $row[] = $req->id;
            $row[] = $req->store_name;
            $row[] = $req->package_name . " (" . $req->plan_type . ")";
            $row[] = $req->created_date;
            $row[] = $req->created_time;
            $row[] = $req->amount;
            
            // Slip
            if($req->payment_slip){
                $row[] = '<a href="'.base_url('uploads/payment_slips/'.$req->payment_slip).'" target="_blank" class="btn btn-info btn-xs"><i class="fa fa-image"></i> View Slip</a>';
            } else {
                $row[] = 'No Slip';
            }

            // Status
            $status = '';
            if($req->status == 0) $status = '<span class="label label-warning">Pending</span>';
            elseif($req->status == 1) $status = '<span class="label label-success">Approved</span>';
            else $status = '<span class="label label-danger">Rejected</span>';
            $row[] = $status;

            // Deletability logic
            $can_delete = false;
            if($req->status == 2){ // Rejected
                $can_delete = true;
            }
            else if($req->status == 1){ // Approved
                $duration_str = "+1 month";
                if(stripos((string)$req->plan_type, 'Year') !== false || stripos((string)$req->plan_type, 'Annually') !== false){
                    $duration_str = "+1 year";
                }
                $created_ts = strtotime($req->created_date);
                $expire_ts = strtotime($duration_str, $created_ts);
                if(time() > $expire_ts) $can_delete = true;
            }

            // Actions
            $actions = '';
            if($req->status == 0){
                $actions .= '<button class="btn btn-success btn-xs" onclick="update_status('.$req->id.', 1)">Approve</button> ';
                $actions .= '<button class="btn btn-danger btn-xs" onclick="update_status('.$req->id.', 2)">Reject</button> ';
            }
            
            $disabled = (!$can_delete) ? 'disabled title="Plan Active or Pending"' : 'title="Delete Request"';
            $actions .= '<button class="btn btn-danger btn-xs" onclick="delete_request('.$req->id.')" '.$disabled.'><i class="fa fa-trash"></i> Delete</button>';
            
            $row[] = $actions;
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'] ?? 1,
            "recordsTotal" => count($data),
            "recordsFiltered" => count($data),
            "data" => $data,
        );
        echo json_encode($output);
    }

	public function update_status()
	{
		$this->permission_check('utilities_edit');
		$id = $this->input->post('id');
		$status = $this->input->post('status');
		
		if($status == 1){
            // Approve
            $req = $this->db->where('id', $id)->get('bill_xml.db_offline_requests')->row();
            if($req && (int)$req->status === 0){
                $result = $this->super_admin_payment_model->approve_offline_request($req);
                echo $result ? 'success' : 'failed';
            } else {
                echo 'failed';
            }
        } else {
            // Reject
             $result = $this->db->where('id', $id)->update('bill_xml.db_offline_requests', ['status' => 2]);
             echo $result ? 'success' : 'failed';
        }
	}

    public function delete_request()
    {
        $this->permission_check('utilities_delete'); 
        $id = $this->input->post('id');
        
        $req = $this->db->where('id', $id)->get('bill_xml.db_offline_requests')->row();
        if(!$req){
            echo 'failed'; return;
        }
        
        // Double Check Deletability
        $can_delete = false;
        if($req->status == 2) $can_delete = true;
        else if($req->status == 1){
            $duration_str = "+1 month";
            if(stripos((string)$req->plan_type, 'Year') !== false || stripos((string)$req->plan_type, 'Annually') !== false){
                $duration_str = "+1 year";
            }
            $created_ts = strtotime($req->created_date);
            $expire_ts = strtotime($duration_str, $created_ts);
            if(time() > $expire_ts) $can_delete = true;
        }
        
        if($can_delete){
            // Delete File
            if(!empty($req->payment_slip)){
                $path = './uploads/payment_slips/'.$req->payment_slip;
                if(file_exists($path)){
                    unlink($path);
                }
            }
            $this->db->where('id', $id)->delete('bill_xml.db_offline_requests');
            echo 'success';
        } else {
            echo 'failed';
        }
    }

    public function toggle_auto_email() {
        if(!is_admin()){
            echo "Access Denied"; return;
        }
        $status = $this->input->post('status');
        $this->db->set('offline_auto_email', $status)->update('db_sitesettings');
        echo "success";
    }
}
