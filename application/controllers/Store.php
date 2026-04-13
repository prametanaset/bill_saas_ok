<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('store_model','store');
	}

	public function add(){
		$this->permission_check('store_add');
		$data=array_merge($this->data,$this->store->store_making_codes());
		$data['page_title']=$this->lang->line('store');
		$this->load->view('store', $data);
	}
	public function newstore(){
		$result=$this->store->verify_and_save();
		echo $result;	
	}
	
	
	
	public function view(){
		$this->permission_check('store_view');
		$data=array_merge($this->data);
		$data['page_title']=$this->lang->line('store_list');
		$data['sumcount']=$this->store->get_store_sum_count();
		$this->load->view('store/store_list', $data);
	}

	public function ajax_list()
	{
		$list = $this->store->get_datatables();
		//echo "<pre>";print_r($list);exit;
		
		$data = array();
		$no = $_POST['start'];
        $is_deleted = $this->input->post('is_deleted');

		foreach ($list as $store) {
			$no++;
			$row = array();
			$disable = ($store->id==1) ? 'disabled' : '';

			$row[] = ($store->id==1) ? '<span data-toggle="tooltip" title="Resticted" class="text-danger fa fa-fw fa-ban"></span>' : '<input type="checkbox" name="checkbox[]" '.$disable.' value='.$store->id.' class="checkbox column_checkbox" >';

			$row[] = $store->store_code;
			$row[] = $store->store_name;
			$row[] = $store->mobile;
			$row[] = $store->address;

			$row[] = $store->created_date;
			$row[] = $store->created_by;
			$row[] = $store->package_name;

					$str='';
					if(!empty($store->expire_date)){
						$days_remaining = (strtotime($store->expire_date) - strtotime(date("Y-m-d"))) / (60 * 60 * 24);
						$days_remaining = floor($days_remaining); // Ensure integer

						if($days_remaining < 0){
							$str = "<br><span class='label label-danger' style='cursor:pointer'>Expired</span>";
						}
						else if($days_remaining <= 10){
							$str = "<br><span class='label label-warning' style='cursor:pointer'>อีก " . $days_remaining . " วัน หมดอายุ</span>";
						}
					}
			$row[] = (!empty($store->expire_date)) ? show_date($store->expire_date).$str : '-';

					$is_expired = (!empty($store->expire_date) && strtotime($store->expire_date) < strtotime(date("Y-m-d")));

					if($store->id==1){ 
						$str= "  <span  class='label label-default' disabled='disabled' style='cursor:disabled'>Restricted</span>"; }
					else if($is_expired){
                        $str= "<span onclick='update_status(".$store->id.",0)' id='span_".$store->id."'  class='label label-warning' style='cursor:pointer'>Pending</span>";
                    }
			 		else if($store->status==1){ 
			 			$str= "<span onclick='update_status(".$store->id.",0)' id='span_".$store->id."'  class='label label-success' style='cursor:pointer'>Active </span>";}
					else{ 
						$str = "<span onclick='update_status(".$store->id.",1)' id='span_".$store->id."'  class='label label-danger' style='cursor:pointer'> Inactive </span>";
					}
			$row[] = $str;			
					
            $str2 = '<div class="btn-group" title="View Account">
                            <a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">
                                Action <span class="caret"></span>
                            </a>
                            <ul role="menu" class="dropdown-menu dropdown-light pull-right">';

            if($is_deleted == 1) {
                // Restoration Action
                 if($this->permissions('store_delete'))
                $str2.='<li>
                    <a style="cursor:pointer" data-toggle="tooltip" title="Restore Record ?" onclick="restore_store('.$store->id.')">
                        <i class="fa fa-fw fa-refresh text-green"></i>Restore
                    </a>
                </li>';
            } else {
                // Normal Actions
                if(store_module() && is_admin())
                $str2.='<li>
                    <a title="View Subscription List" data-toggle="tooltip" href="'.base_url('subscribers/list/'.$store->id).'">
                        <i class="fa fa-fw fa-edit text-blue"></i>Subscription List
                    </a>
                </li>';

                if($this->permissions('store_edit'))
                $str2.='<li>
                    <a title="Edit Record ?" data-toggle="tooltip" href="'.base_url('store_profile/update/'.$store->id).'">
                        <i class="fa fa-fw fa-edit text-blue"></i>Edit
                    </a>
                </li>';

                if($this->permissions('store_delete') && $store->id!=1)
                $str2.='<li>
                    <a style="cursor:pointer" data-toggle="tooltip" title="Delete Record ?" onclick="delete_store_with_reason('.$store->id.')">
                        <i class="fa fa-fw fa-trash text-red"></i>Delete
                    </a>
                </li>';
            }
                                
            $str2.='</ul></div>';			

			$row[] = $str2;
			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->store->count_all(),
						"recordsFiltered" => $this->store->count_filtered(),
						"data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

	public function update_status(){
		$this->permission_check_with_msg('store_edit');
		$id=$this->input->post('id');
		$status=$this->input->post('status');

		
		$result=$this->store->update_status($id,$status);
		return $result;
	}
	
	public function delete_store(){
        $this->permission_check_with_msg('store_delete');
        $id = $this->input->post('q_id');
        $reason = $this->input->post('reason');
        
        // Log Activity
        $this->load->model('activity_log_model');
        $store = $this->db->select('store_name')->where('id', $id)->get('db_store')->row();
        $store_name = ($store) ? $store->store_name : 'Unknown';
        $description = "Deleted Store: {$store_name}. Reason: {$reason}";
        $this->activity_log_model->log_action('DELETE_STORE', $id, $description);

        // Soft Delete
		echo $this->store->delete_store_soft($id);
	}

    public function recycle_bin(){
        $this->permission_check('store_view'); // Admin permission check
		$data=array_merge($this->data);
		$data['page_title']='Store Recycle Bin';
		$this->load->view('store/store_deleted_list', $data);
    }

    public function restore_store(){
        $this->permission_check_with_msg('store_delete');
        $id = $this->input->post('id');
        
        // Log Activity
        $this->load->model('activity_log_model');
        $store = $this->db->select('store_name')->where('id', $id)->get('db_store')->row();
        $store_name = ($store) ? $store->store_name : 'Unknown';
        $description = "Restored Store: {$store_name}";
        $this->activity_log_model->log_action('RESTORE_STORE', $id, $description);

        echo $this->store->restore_store($id);
    }

    public function empty_recycle_bin(){
        $this->permission_check_with_msg('store_delete');
        
        // Log Activity
        $this->load->model('activity_log_model');
        $description = "Emptied Recycle Bin (Permanent Delete All)";
        $this->activity_log_model->log_action('EMPTY_RECYCLE_BIN', 0, $description);

        echo $this->store->empty_recycle_bin();
    }

	public function multi_delete(){
		$this->permission_check_with_msg('store_delete');
		$ids=$_POST['checkbox'];
		echo $this->store->delete_store_from_table($ids);
	}

}

