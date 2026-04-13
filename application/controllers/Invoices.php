<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoices extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('invoice_model','invoices');
		$this->permission_check('sales_view');
	}

	public function index()
	{
		$this->permission_check('sales_view');
		$data=$this->data;
		$data['page_title']=$this->lang->line('invoice_list');
		$this->load->view('invoice-list',$data);
	}

	public function ajax_list()
	{
		$list = $this->invoices->get_datatables();
		
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $res) {
			$no++;
			$row = array();
			$row[] = '<input type="checkbox" name="checkbox[]" value='.$res->id.' class="checkbox column_checkbox" >';
			$row[] = show_date($res->invoice_date);
			
			$invoice_code = $res->invoice_code;
			if($res->status == 0){
				$invoice_code .= ' <span class="label label-danger">'.$this->lang->line('cancelled').'</span>';
			}
			$row[] = $invoice_code;
			
			$row[] = $res->sales_ref;
			$row[] = $res->customer_name;
			$row[] = store_number_format($res->grand_total);
			$row[] = $res->created_by;

			$str2 = '<div class="btn-group" title="Actions">
						<a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">
							Action <span class="caret"></span>
						</a>
						<ul role="menu" class="dropdown-menu dropdown-light pull-right">';
							$str2.='<li>
								<a title="View Invoice" target="_blank" href="'.base_url().'pdf/invoice/'.$res->sales_id.'" >
									<i class="fa fa-fw fa-file-pdf-o text-blue"></i>'.$this->lang->line('invoice') . ' PDF' . '
								</a>
							</li>';

							if($this->permissions('sales_delete'))
							$str2.='<li>
								<a style="cursor:pointer" title="Delete Record ?" onclick="invoice_delete_record(\''.$res->id.'\')">
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
						"recordsTotal" => $this->invoices->count_all(),
						"recordsFiltered" => $this->invoices->count_filtered(),
						"data" => $data,
				);
		echo json_encode($output);
	}

	public function delete_invoice(){
		$this->permission_check_with_msg('sales_delete');
		$id=$this->input->post('q_id');
		if(empty($id)) {
			echo "No ID provided";
			return;
		}
		echo $this->invoices->delete_invoice($id);
	}

	public function multi_delete(){
		$this->permission_check_with_msg('sales_delete');
		$checkbox = $this->input->post('checkbox');
		if(empty($checkbox)) {
			echo "Please select at least one record";
			return;
		}
		$ids=implode (",",$checkbox);
		echo $this->invoices->delete_invoice($ids);
	}
}
