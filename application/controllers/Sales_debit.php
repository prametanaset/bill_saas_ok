<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;
use Dompdf\Options;

class Sales_debit extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('sales_debit_model','sales');
		$this->load->helper('sms_template_helper');
        if($this->uri->segment(2) == 'sales_list'){
             log_message('error', 'DEBUG: Sales_debit constructor hit for sales_list');
        }
        if(!$this->permissions('sales_return_view') && !$this->permissions('sales_return_add') && !$this->permissions('sales_return_edit')){
        	$this->permission_check('sales_return_view');
        }
	}

	public function is_sms_enabled(){
		return is_sms_enabled();
	}

	public function index()
	{
		$this->permission_check('sales_return_view');
		$data=$this->data;
		$data['page_title']=$this->lang->line('sales_debits_list');
		$this->load->view('sales-debit-list',$data);
	}

	public function create(){
		$this->permission_check('sales_return_add');
		$data=$this->data;
		$data['page_title']=$this->lang->line('sales_debit');
		$data['oper']='create_new_return';
		$data['subtitle']=$this->lang->line('sales_debit_entry');;
		$this->load->view('sales-debit', $data);
	}

  public function add($id){
  		//echo "db_sales <br>";
  		$this->belong_to('db_sales',$id);
  		$this->permission_check('sales_return_edit');
  		$q2=$this->db->query("select sales_status from db_sales where id=".$id);
		if($q2->row()->sales_status=='Quotation'){
			$this->session->set_flashdata('warning','Sorry! Quotation could not be returned!');
			redirect($_SERVER['HTTP_REFERER']);
			exit();
		}
		//echo "db_salesdebit <br>";
  	    $q1=$this->db->query("select id from db_salesdebit where sales_id=".$id);
		if($q1->num_rows()>0){
			$this->belong_to('db_salesdebit',$q1->row()->id);
			$this->session->set_flashdata('success','Sales Debit Invoice Already Generated!');
			redirect(base_url('sales_debit/edit/'.$q1->row()->id),'refresh');exit();
		}
	    
	    $data=$this->data;
	    $data['sales_id']=$id;;
	    $data['page_title']=$this->lang->line('sales_debit');
	    $data['oper']='return_against_sales';
	    $data['items_count']=0;
	    $data['subtitle']=$this->lang->line('sales_debit_entry');;
        
        //Fetch Sales Details
        $sales_data = $this->db->select('other_charges_input,other_charges_type,other_charges_tax_id,discount_to_all_input,discount_to_all_type,reference_type')->where('id',$id)->get('db_sales')->row();
        $data['other_charges_input'] = $sales_data->other_charges_input;
        $data['other_charges_type'] = $sales_data->other_charges_type;
        $data['other_charges_tax_id'] = $sales_data->other_charges_tax_id;
        $data['discount_input'] = $sales_data->discount_to_all_input;
        $data['discount_type'] = $sales_data->discount_to_all_type;

        $sales_reference_type = '';
        if ($sales_data->reference_type == 'tax_invoice') {
            $sales_reference_type = 'ใบกำกับภาษี';
        } elseif ($sales_data->reference_type == 'tax_receipt') {
            $sales_reference_type = 'ใบเสร็จรับเงิน/ใบกำกับภาษี';
        }
        $data['sales_reference_type'] = $sales_reference_type;

        log_message('error', 'DEBUG: Sales_debit::add() loaded view for sales_id: ' . $id);
	    $this->load->view('sales-debit', $data);
	  }

	public function edit($id){
        log_message('error', 'DEBUG: Sales_debit::edit() called with id: ' . $id);
		$this->belong_to('db_salesdebit',$id);
		$this->permission_check('sales_return_edit');
		$data=$this->data;
		$data=array_merge($data,array('debit_id'=>$id));
		$data['oper']='edit_existing_return';
		
		// Fetch Debit Details
		$debit_data = $this->db->select('*')->where('id',$id)->get('db_salesdebit')->row();
		$data['sales_id'] = $debit_data->sales_id;
		$data['other_charges_input'] = $debit_data->other_charges_input;
		$data['other_charges_type'] = $debit_data->other_charges_type;
		$data['other_charges_tax_id'] = $debit_data->other_charges_tax_id;
		$data['discount_input'] = $debit_data->discount_to_all_input;
		$data['discount_type'] = $debit_data->discount_to_all_type;
		
		$q2=$this->db->query("select * from db_salesitemsdebit where debit_id=".$id);
		$data['items_count']=$q2->num_rows();

        // Fetch Reference Type from Original Sale
        $sales_reference_type = '';
        if(!empty($data['sales_id'])){
            $ref_q = $this->db->select('reference_type')->where('id', $data['sales_id'])->get('db_sales');
            if($ref_q->num_rows() > 0){
                $ref_raw = $ref_q->row()->reference_type;
                if ($ref_raw == 'tax_invoice') {
                    $sales_reference_type = 'ใบกำกับภาษี';
                } elseif ($ref_raw == 'tax_receipt') {
                    $sales_reference_type = 'ใบเสร็จรับเงิน/ใบกำกับภาษี';
                }
            }
        }
        $data['sales_reference_type'] = $sales_reference_type;

		$data['subtitle']=$this->lang->line('sales_debit_entry');;
		$data['page_title']=$this->lang->line('sales_debit');
		$this->load->view('sales-debit', $data);
	}

	public function sales_save_and_update(){
		$this->form_validation->set_rules('debit_date', 'Return Date', 'trim|required');
		$this->form_validation->set_rules('customer_id', 'Customer Name', 'trim|required');
		$this->form_validation->set_rules('debit_note', 'Debit Note', 'trim|required');
		
		if ($this->form_validation->run() == TRUE) {
	    	$result = $this->sales->verify_save_and_update();
	    	echo $result;
		} else {
			echo "กรุณากรอกข้อมูลในช่องที่จำเป็น (* ).";
		}
	}
	

	

	public function ajax_list()
	{
		$list = $this->sales->get_datatables();
		
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $sales) {
			
			$no++;
			$row = array();
			$row[] = '<input type="checkbox" name="checkbox[]" value='.$sales->id.' class="checkbox column_checkbox" >';
			
			$row[] = show_date($sales->debit_date);
			$row[] = $sales->sales_code;
			$row[] = $sales->debit_code;
			$row[] = $sales->debit_note;
			$row[] = store_number_format($sales->grand_total);
			$row[] = $sales->customer_name;
			
			$correct_val = string_to_number($sales->reference_no);
			$row[] = is_numeric($correct_val) ? store_number_format($correct_val) : $sales->reference_no;
			$row[] = store_number_format($sales->paid_amount);
					$str='';
					if($sales->payment_status=='Unpaid')
			          $str= "<span class='label label-danger' style='cursor:pointer'>รอจ่าย </span>";
			        if($sales->payment_status=='Partial')
			          $str="<span class='label label-warning' style='cursor:pointer'> จ่ายบางส่วน </span>";
			        if($sales->payment_status=='Paid')
			          $str="<span class='label label-success' style='cursor:pointer'> จ่ายแล้ว </span>";

			$row[] = $str;
			$row[] = ($sales->created_by);

					
					 	$str1='sales_debit/edit/';
					

					$str2 = '<div class="btn-group" title="View Account">
										<a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">
											Action <span class="caret"></span>
										</a>
										<ul role="menu" class="dropdown-menu dropdown-light pull-right">';
											if($this->permissions('sales_return_view'))
											$str2.='<li>
												<a title="View Invoice" href="'.base_url().'sales_debit/invoice/'.$sales->id.'" >
													<i class="fa fa-fw fa-eye text-blue"></i>รายละเอียดใบเพิ่มหนี้
												</a>
											</li>';

											if($this->permissions('sales_return_edit'))
											$str2.='<li>
												<a title="Update Record ?" href="'.base_url().''.$str1.$sales->id.'">
													<i class="fa fa-fw fa-edit text-blue"></i>แก้ไข
												</a>
											</li>';

											if($this->permissions('sales_return_payment_view'))
											$str2.='<li>
												<a title="Pay" class="pointer" onclick="view_payments('.$sales->id.')" >
													<i class="fa fa-fw fa-money text-blue"></i>มุมองการชำระเงิน
												</a>
											</li>';

											if($this->permissions('sales_return_payment_add'))
											$str2.='<li>
												<a title="Pay" class="pointer" onclick="pay_now('.$sales->id.')" >
													<i class="fa fa-fw fa-hourglass-half text-blue"></i>ชำระเงิน
												</a>
											</li>';

											if($this->permissions('sales_return_add') || $this->permissions('sales_return_edit'))
											$str2.='<li>
												<a title="Take Print" target="_blank" href="sales_debit/print_invoice/'.$sales->id.'">
													<i class="fa fa-fw fa-print text-blue"></i>พิมพ์ใบเพิ่มหนี้
												</a>
											</li>

									    	<li>
												<a title="Download PDF" target="_blank" href="'.base_url().'sales_debit/pdf/'.$sales->id.'">
													<i class="fa fa-fw fa-file-pdf-o text-blue"></i>ใบเพิ่มหนี้ e-TAX
												</a>
											</li>
											';

											if($this->permissions('sales_return_delete'))
											$str2.='<li>
												<a style="cursor:pointer" title="Delete Record ?" onclick="delete_return(\''.$sales->id.'\')">
													<i class="fa fa-fw fa-trash text-red"></i>ลบ
												</a>
											</li>';
											
											$str2.='</ul>
									</div>';			

			$row[] = $str2;

			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->sales->count_all(),
						"recordsFiltered" => $this->sales->count_filtered(),
						"data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}
	public function update_status(){
		$this->permission_check('sales_return_edit');
		$id=$this->input->post('id');
		$status=$this->input->post('status');

		
		$result=$this->sales->update_status($id,$status);
		return $result;
	}
	public function delete_return(){
		$this->permission_check_with_msg('sales_return_delete');
		$id=$this->input->post('q_id');
		echo $this->sales->delete_return($id);
	}
	public function multi_delete(){
		$this->permission_check_with_msg('sales_return_delete');
		$ids=implode (",",$_POST['checkbox']);
		echo $this->sales->delete_return($ids);
	}


	//Table ajax code
	public function search_item(){
		$q=$this->input->get('q');
		$result=$this->sales->search_item($q);
		echo $result;
	}
	public function find_item_details(){
		$id=$this->input->post('id');
		
		$result=$this->sales->find_item_details($id);
		echo $result;
	}

	//sales invoice form
	public function invoice($id)
	{	
	//	echo "id=";
	//	echo $id;
		$this->belong_to('db_salesdebit',$id);
		if(!$this->permissions('sales_return_add') && !$this->permissions('sales_return_edit') && !$this->permissions('sales_return_view')){
			$this->show_access_denied_page();
		}
		$data=$this->data;
		$data=array_merge($data,array('debit_id'=>$id));
		$data['page_title']=$this->lang->line('sales_debit_invoice');
		$this->load->view('sal-debit-invoice',$data);
	}
	
	//Print sales invoice 
	public function print_invoice($debit_id)
	{
		$this->belong_to('db_salesdebit',$debit_id);
		if(!$this->permissions('sales_return_add') && !$this->permissions('sales_return_edit') && !$this->permissions('sales_return_view')){
			$this->show_access_denied_page();
		}
		$data=$this->data;
		$data=array_merge($data,array('debit_id'=>$debit_id));
		$data['page_title']=$this->lang->line('debit_invoice');
		$this->load->view('print-sales-debit-invoice-5',$data);
		
	}

	//Print sales POS invoice 
	public function print_invoice_pos($debit_id)
	{
		$this->belong_to('db_salesdebit',$debit_id);
		if(!$this->permissions('sales_return_add') && !$this->permissions('sales_return_edit') && !$this->permissions('sales_return_view')){
			$this->show_access_denied_page();
		}
		$data=$this->data;
		$data=array_merge($data,array('debit_id'=>$debit_id));
		$data['page_title']=$this->lang->line('sales_debit_invoice');
		$this->load->view('sal-invoice-pos',$data);
	}
	public function pdf($debit_id){
		$this->belong_to('db_salesdebit',$debit_id);
		if(!$this->permissions('sales_return_add') && !$this->permissions('sales_return_edit')){
			$this->show_access_denied_page();
		}
		
		$data=$this->data;
		$data['page_title']=$this->lang->line('sales_invoice');
        $data=array_merge($data,array('debit_id'=>$debit_id));
      
		$this->load->view('print-sales-debit-invoice-5',$data);
       

        // Get output html
        $html = $this->output->get_output();

        $options = new Options();
		$options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        // Load HTML content
        $dompdf->loadHtml($html,'UTF-8');
        
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');/*landscape or portrait*/
        
        // Render the HTML as PDF
        $dompdf->render();
        
        // Output the generated PDF (1 = download and 0 = preview)
        $dompdf->stream("Sales-return-$debit_id-".date('M')."_".date('d')."_".date('Y'), array("Attachment"=>0));
	}

  public function pdf_a3($debit_id){
    $this->belong_to('db_salesdebit',$debit_id);
    if(!$this->permissions('sales_return_add') && !$this->permissions('sales_return_edit')){
      $this->show_access_denied_page();
    }
    
    // Load the library file
    require_once(APPPATH.'libraries/TCPDF/invoice/SalesDebit.php');
    
    // Instantiate the class
    // Pass sales_id in params array as expected by the constructor
    $pdf = new SalesDebit(array('sales_id' => $debit_id));
    
    // Generate PDF
    $pdf->show_pdf('I');
  }
	
	

	
	/*v1.1*/
	public function return_row_with_data($rowcount,$item_id){
		echo $this->sales->get_items_info($rowcount,$item_id);
	}
	public function return_sales_list($debit_id){
		log_message('error', 'DEBUG: Sales_debit::return_sales_list called for ID: ' . $debit_id);
		$result = $this->sales->return_sales_list($debit_id);
		log_message('error', 'DEBUG: Sales_debit::return_sales_list result length: ' . strlen($result));
		echo $result;
	}
	/*Sales List from existing sales entry*/
	public function sales_list($sales_id){
		log_message('error', 'DEBUG: sales_list called with argument: ' . $sales_id);

		if(!is_numeric($sales_id)){
			$sales_id_safe = $this->db->escape_str($sales_id);
			$q = $this->db->query("select id from db_sales where sales_code='$sales_id_safe'");
			if($q->num_rows() > 0){
				$sales_id = $q->row()->id;
				log_message('error', 'DEBUG: Resolved sales_code to ID: ' . $sales_id);
			} else {
				log_message('error', 'DEBUG: Failed to resolve sales_code: ' . $sales_id);
				
				//Try decoding if URL encoded
				$sales_id_decoded = urldecode($sales_id);
				if($sales_id_decoded != $sales_id){
					$sales_id_safe = $this->db->escape_str($sales_id_decoded);
					$q = $this->db->query("select id from db_sales where sales_code='$sales_id_safe'");
					if($q->num_rows() > 0){
						$sales_id = $q->row()->id;
						log_message('error', 'DEBUG: Resolved decoded sales_code to ID: ' . $sales_id);
					}
				}
			}
		}
		
		$result = $this->sales->sales_list($sales_id);
		log_message('error', 'DEBUG: sales_list result length: ' . strlen($result));
		echo $result;
	}
	public function delete_payment(){
		$this->permission_check_with_msg('sales_return_payment_delete');
		$payment_id = $this->input->post('payment_id');
		echo $this->sales->delete_payment($payment_id);
	}
	public function show_pay_now_modal(){
		$this->permission_check_with_msg('sales_return_view');
		$debit_id=$this->input->post('debit_id');
		echo $this->sales->show_pay_now_modal($debit_id);
	}
	public function save_payment(){
		$this->permission_check_with_msg('sales_return_add');
		echo $this->sales->save_payment();
	}
	public function view_payments_modal(){
		$this->permission_check_with_msg('sales_return_view');
		$debit_id=$this->input->post('debit_id');
		echo $this->sales->view_payments_modal($debit_id);
	}
}
