<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;
use Dompdf\Options;

class Sales_return extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('sales_return_model','sales');
		$this->load->helper('sms_template_helper');
        if($this->uri->segment(2) == 'sales_list'){
             log_message('error', 'DEBUG: Sales_return constructor hit for sales_list');
        }
	}

	public function is_sms_enabled(){
		return is_sms_enabled();
	}

	public function index()
	{
		$this->permission_check('sales_return_view');
		$data=$this->data;
		$data['page_title']=$this->lang->line('sales_returns_list');
		$this->load->view('sales-return-list',$data);
	}

	public function create(){
		$this->permission_check('sales_return_add');
		$data=$this->data;
		$data['page_title']=$this->lang->line('sales_return');
		$data['oper']='create_new_return';
		$data['subtitle']=$this->lang->line('create_new_return');;
		$this->load->view('sales-return', $data);
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
		//echo "db_salesreturn <br>";
  	    $q1=$this->db->query("select id from db_salesreturn where sales_id=".$id);
		if($q1->num_rows()>0){
			$this->belong_to('db_salesreturn',$q1->row()->id);
			$this->session->set_flashdata('success','Sales Return Invoice Already Generated!');
			redirect(base_url('sales_return/edit/'.$q1->row()->id),'refresh');exit();
		}
	    
	    $data=$this->data;
	    $data['sales_id']=$id;;
	    $data['page_title']=$this->lang->line('sales_return');
	    $data['oper']='return_against_sales';
	    $data['items_count']=0;
	    $data['subtitle']=$this->lang->line('return_against_sales');;
        
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

        log_message('error', 'DEBUG: Sales_return::add() loaded view for sales_id: ' . $id);
	    $this->load->view('sales-return', $data);
	  }

	public function sales_save_and_update(){
		$this->form_validation->set_rules('return_date', 'Return Date', 'trim|required');
		$this->form_validation->set_rules('customer_id', 'Customer Name', 'trim|required');
		$this->form_validation->set_rules('return_note', 'Return Note', 'trim|required');
		
		if ($this->form_validation->run() == TRUE) {
	    	$result = $this->sales->verify_save_and_update();
	    	echo $result;
		} else {
			echo "Please Fill Compulsory(* marked) Fields.";
		}
	}
	
	
	public function edit($id){
        log_message('error', 'DEBUG: Sales_return::edit() called with id: ' . $id);
		$this->belong_to('db_salesreturn',$id);
		$this->permission_check('sales_return_edit');
		$data=$this->data;
		$data=array_merge($data,array('return_id'=>$id));
		$data['oper']='edit_existing_return';
		$q2=$this->db->query("select * from db_salesitemsreturn where return_id=".$id);
		$data['items_count']=$q2->num_rows();
		$data['subtitle']=$this->lang->line('edit_return_sales_entry');;
		$data['page_title']=$this->lang->line('sales_return');

        $sales_id = $this->db->select('sales_id')->where('id',$id)->get('db_salesreturn')->row()->sales_id;
        $sales_reference_type = '';
        if(!empty($sales_id)){
            $ref_q = $this->db->select('reference_type')->where('id', $sales_id)->get('db_sales');
            if ($ref_q && $ref_q->num_rows() > 0) {
                $ref_raw = $ref_q->row()->reference_type;
                if ($ref_raw == 'tax_invoice') {
                    $sales_reference_type = 'ใบกำกับภาษี';
                } elseif ($ref_raw == 'tax_receipt') {
                    $sales_reference_type = 'ใบเสร็จรับเงิน/ใบกำกับภาษี';
                }
            }
        }
        $data['sales_reference_type'] = $sales_reference_type;

		$this->load->view('sales-return', $data);
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
			
			$row[] = show_date($sales->return_date);
			$row[] = $sales->sales_code;
			$row[] = $sales->return_code;
			$row[] = $sales->return_note;
			$row[] = store_number_format($sales->grand_total);
			$row[] = $sales->customer_name;
			
			// มูลค่าที่ถูกต้อง = มูลค่าใบกำกับภาษีเดิม - มูลค่าที่ส่งคืน
			$sales_gt = isset($sales->sales_grand_total) ? (float)$sales->sales_grand_total : 0;
			$return_gt = (float)$sales->grand_total;
			$correct_total = $sales_gt - $return_gt;
			$row[] = store_number_format($correct_total);
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

					
					 	$str1='sales_return/edit/';
					

					$str2 = '<div class="btn-group" title="View Account">
										<a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">
											Action <span class="caret"></span>
										</a>
										<ul role="menu" class="dropdown-menu dropdown-light pull-right">';
											if($this->permissions('sales_return_view'))
											$str2.='<li>
												<a title="View Invoice" href="'.base_url().'sales_return/invoice/'.$sales->id.'" >
													<i class="fa fa-fw fa-eye text-blue"></i>รายละเอียดใบลดหนี้
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
											{
												$print_url = base_url().'sales_return/print_invoice/'.$sales->id;
												$str2.='<li>
													<a title="Take Print" class="pointer" onclick="printTemplate(\''.$print_url.'\')">
														<i class="fa fa-fw fa-print text-blue"></i>พิมพ์ใบลดหนี้
													</a>
												</li>';
											}


											if($this->permissions('sales_return_delete'))
											$str2.='<li>
												<a style="cursor:pointer" title="Delete Record ?" onclick="delete_return(\''.$sales->id.'\')">
													<i class="fa fa-fw fa-trash text-red"></i>ลบ
												</a>
											</li>
											
										</ul>
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
		$this->belong_to('db_salesreturn',$id);
		if(!$this->permissions('sales_return_add') && !$this->permissions('sales_return_edit')){
			$this->show_access_denied_page();
		}
		$data=$this->data;
		$data=array_merge($data,array('return_id'=>$id));
		$data['page_title']=$this->lang->line('sales_return_invoice');
		$this->load->view('sal-return-invoice',$data);
	}
	
	//Print sales invoice 
	public function print_invoice($return_id)
	{
		$this->belong_to('db_salesreturn',$return_id);
		if(!$this->permissions('sales_return_add') && !$this->permissions('sales_return_edit')){
			$this->show_access_denied_page();
		}
		$data=$this->data;
		$data=array_merge($data,array('return_id'=>$return_id));
		$data['page_title']=$this->lang->line('return_invoice');
		$this->load->view('print-sales-return-invoice-2',$data);
		
	}

	//Print sales POS invoice 
	public function print_invoice_pos($return_id)
	{
		$this->belong_to('db_salesreturn',$return_id);
		if(!$this->permissions('sales_return_add') && !$this->permissions('sales_return_edit')){
			$this->show_access_denied_page();
		}
		$data=$this->data;
		$data=array_merge($data,array('return_id'=>$return_id));
		$data['page_title']=$this->lang->line('sales_return_invoice');
		$this->load->view('sal-invoice-pos',$data);
	}
	public function pdf($return_id){
		$this->belong_to('db_salesreturn',$return_id);
		if(!$this->permissions('sales_return_add') && !$this->permissions('sales_return_edit')){
			$this->show_access_denied_page();
		}
		
		$data=$this->data;
		$data['page_title']=$this->lang->line('sales_invoice');
        $data=array_merge($data,array('return_id'=>$return_id));
      
		$this->load->view('print-sales-return-invoice-2',$data);
       

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
        $dompdf->stream("Sales-return-$return_id-".date('M')."_".date('d')."_".date('Y'), array("Attachment"=>0));
	}

  public function pdf_a3($return_id){
    $this->belong_to('db_salesreturn',$return_id);
    if(!$this->permissions('sales_return_add') && !$this->permissions('sales_return_edit')){
      $this->show_access_denied_page();
    }
    
    // Load the library file
    require_once(APPPATH.'libraries/TCPDF/invoice/SalesReturn.php');
    
    // Instantiate the class
    // Pass sales_id in params array as expected by the constructor
    $pdf = new SalesReturn(array('sales_id' => $return_id));
    
    // Generate PDF
    $pdf->show_pdf('I');
  }
	
	

	
	/*v1.1*/
	public function return_row_with_data($rowcount,$item_id){
		echo $this->sales->get_items_info($rowcount,$item_id);
	}
	/*Sales List from existing return entry*/
	public function return_sales_list($return_id){
		log_message('error', 'DEBUG: return_sales_list called with argument: ' . $return_id);
		echo $this->sales->return_sales_list($return_id);
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
		$return_id=$this->input->post('return_id');
		echo $this->sales->show_pay_now_modal($return_id);
	}
	public function save_payment(){
		$this->permission_check_with_msg('sales_return_add');
		echo $this->sales->save_payment();
	}
	public function view_payments_modal(){
		$this->permission_check_with_msg('sales_return_view');
		$return_id=$this->input->post('return_id');
		echo $this->sales->view_payments_modal($return_id);
	}
}
