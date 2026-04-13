<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Receipts extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('receipt_model','receipts');
		$this->permission_check('sales_view');
	}

	public function index()
	{
		$this->permission_check('sales_view');
		$data=$this->data;
		$data['page_title']=$this->lang->line('receipts_list');
		$this->load->view('receipts-list',$data);
	}

	public function ajax_list()
	{
		$list = $this->receipts->get_datatables();
		
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $res) {
			$no++;
			$row = array();
			$row[] = '<input type="checkbox" name="checkbox[]" value='.$res->id.' class="checkbox column_checkbox" >';
			$row[] = show_date($res->receipt_date);
			
			$receipt_code = $res->receipt_code;
			if($res->status == 0){
				$receipt_code .= ' <span class="label label-danger">'.$this->lang->line('cancelled').'</span>';
			}
			$row[] = $receipt_code;
			
			$row[] = $res->sales_ref;
			$row[] = $res->reference_receipt;
			$row[] = $res->customer_name;
			$row[] = store_number_format($res->grand_total);
			$row[] = $res->created_by;

			$str2 = '<div class="btn-group" title="Actions">
						<a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">
							Action <span class="caret"></span>
						</a>
						<ul role="menu" class="dropdown-menu dropdown-light pull-right">';
							$str2.='<li>
								<a title="View Receipt" target="_blank" href="'.base_url().'pdf/receipt/'.$res->sales_id.'" >
									<i class="fa fa-fw fa-file-pdf-o text-blue"></i>'.$this->lang->line('view_receipt').'
								</a>
							</li>';

							if($res->status == 1){
								$str2.='<li>
									<a title="Re-issue Receipt" style="cursor:pointer" onclick="javascript:re_issue_receipt('.$res->sales_id.',\''.$res->receipt_code.'\')" >
										<i class="fa fa-fw fa-refresh text-orange"></i>'.$this->lang->line('re_issue_receipt').'
									</a>
								</li>';
							}

							if($this->permissions('sales_delete'))
							$str2.='<li>
								<a style="cursor:pointer" title="Delete Record ?" onclick="delete_receipt(\''.$res->id.'\')">
									<i class="fa fa-fw fa-trash text-red"></i>'.$this->lang->line('delete').'
								</a>
							</li>';
							
							$str2.='</ul>
					</div>';			

			$row[] = $str2;
			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->receipts->count_all(),
						"recordsFiltered" => $this->receipts->count_filtered(),
						"data" => $data,
				);
		echo json_encode($output);
	}

	public function delete_receipt(){
		$this->permission_check_with_msg('sales_delete');
		$id=$this->input->post('q_id');
		echo $this->receipts->delete_receipt($id);
	}

	public function multi_delete(){
		$this->permission_check_with_msg('sales_delete');
		$ids=implode (",",$_POST['checkbox']);
		echo $this->receipts->delete_receipt($ids);
	}
}
