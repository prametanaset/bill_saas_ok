<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;
use Dompdf\Options;


class Sales extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('sales_model','sales');
		$this->load->helper('sms_template_helper');
	}

	public function is_sms_enabled(){
		return is_sms_enabled();
	}

	public function index()
	{
		$this->permission_check('sales_view');
		if(get_current_store_id()==1) { sync_stores_to_customers(); }
		$data=$this->data;
		$data['page_title']=$this->lang->line('sales_list');
		$this->load->view('sales-list',$data);
	}

	//Convert to Quotation to Sales Invoice
	public function quotation($quotation_id){
		//Load model
		$this->load->model('customers_model');

		$this->belong_to('db_quotation',$quotation_id);
		$this->permission_check('sales_add');
		$data=$this->data;
		$data['page_title']=$this->lang->line('quotation_to_sales_invoice');
		$data['quotation_id']=$quotation_id;

		$data=array_merge($data);

		$this->load->view('sales',$data);
	}

	public function add()
	{	
		$this->permission_check('sales_add');
		if(get_current_store_id()==1) { sync_stores_to_customers(); }
		$data=$this->data;
		$data['page_title']=$this->lang->line('sales');
		$this->load->view('sales',$data);
	}
	

	public function sales_save_and_update(){
		$this->form_validation->set_rules('sales_date', 'Sales Date', 'trim|required');
		$this->form_validation->set_rules('customer_id', 'Customer Name', 'trim|required');
		
		if ($this->form_validation->run() == TRUE) {
	    	$result = $this->sales->verify_save_and_update();
	    	echo $result;
		} else {
			echo "โปรดกรอกข้อมูลในช่องที่มีเครื่องหมายดอกจัน (*) กำกับไว้";
		}
	}
	
	
	public function update($id){
		//Load model
		$this->load->model('customers_model');

		//Verification of invoice
		$this->belong_to('db_sales',$id);

		// Check if locked
		if ($this->db->field_exists('reference_type', 'db_sales')) {
			$sales_rec = $this->db->select('reference_type')->where('id', $id)->get('db_sales')->row();
			if(!empty($sales_rec->reference_type) && !$this->input->get('sell_again')){
				redirect('sales');
			}
		}

		//Permission check
		$this->permission_check('sales_edit');

		if(get_current_store_id()==1) { sync_stores_to_customers(); }

		$data=$this->data;

        if($this->input->get('sell_again')){
            $data=array_merge($data,array('sell_again_id'=>$id));
            $data['page_title']='ทำรายการซ้ำ (Sell Again)';
        } else {
		    $data=array_merge($data,array('sales_id'=>$id));
		    $data['page_title']=$this->lang->line('sales');
        }

		$this->load->view('sales', $data);
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
			
			$row[] = show_date($sales->sales_date);

			#-----------------------------------------------------
			$date_difference = date_difference($sales->due_date,date("Y-m-d"));
					$str='';
					//$info='';
					if($date_difference>0){
			          $str= "<br><span class='label label-danger' style='cursor:pointer'> $date_difference วัน เกินกำหนด</span>";
					}
					else{
						$str= "<br><span class='label label-info' style='cursor:pointer'> ".abs($date_difference)." วัน ครบกำหนด</span>";
					}
			$row[] = (!empty($sales->due_date) && $sales->payment_status!='Paid') ? show_date($sales->due_date).$str : '';
			#-----------------------------------------------------

			$info = (!empty($sales->quotation_id)) ? "<br><span class='label label-success' style='cursor:pointer'><i class='fa fa-fw  fa-check-circle'></i>ตามใบเสนอราคา</span>" : '';

			$info .= (!empty($sales->return_bit)) ? "<br><span class='label label-danger' style='cursor:pointer'><i class='fa fa-fw fa-undo'></i>ลดหนี้</span>" : '';
			$info .= (!empty($sales->debit_bit)) ? "<br><span class='label label-warning' style='cursor:pointer'><i class='fa fa-fw fa-plus-circle'></i>เพิ่มหนี้</span>" : '';

			if((isset($sales->status) && $sales->status == 0) || (isset($sales->referenced_count) && $sales->referenced_count > 0)){
				$info .= "<br><span class='label label-danger' style='cursor:pointer'><i class='fa fa-fw fa-times-circle'></i>".$this->lang->line('cancelled')."</span>";
			}

			if(!empty($sales->receipt_codes)){
				$info .= "<br><span class='label label-success' style='cursor:pointer'><i class='fa fa-fw fa-file-text-o'></i>".$sales->receipt_codes."</span>";
			}

			if(!empty($sales->invoice_codes)){
				$info .= "<br><span class='label label-warning' style='cursor:pointer'><i class='fa fa-fw fa-file-text-o'></i>".$sales->invoice_codes."</span>";
			}

			$row[] = $sales->sales_code.$info;
			
			$row[] = $sales->reference_no;

			$reference_type = '';
			if($sales->reference_type == 'tax_invoice'){
				$reference_type = 'ใบกำกับภาษี';
			} else if($sales->reference_type == 'tax_receipt'){
				$reference_type = 'ใบเสร็จรับเงิน/ใบกำกับภาษี';
			}
			$row[] = $reference_type;

			$row[] = $sales->customer_name;
			
			$print_url = base_url().'sales/print_invoice/'.$sales->id;

			$row[] = store_number_format($sales->grand_total); // Index 7: Amount
			$row[] = store_number_format($sales->vat_amt); // Index 8: Tax Amount
			$row[] = store_number_format($sales->paid_amount); // Index 9: Paid Payment
			$str='';
			if($sales->payment_status=='Unpaid')
				$str= "<span class='label label-danger' style='cursor:pointer'>รอจ่าย </span>";
			if($sales->payment_status=='Partial')
				$str="<span class='label label-warning' style='cursor:pointer'> จ่ายบางส่วน </span>";
			if($sales->payment_status=='Paid')
				$str="<span class='label label-success' style='cursor:pointer'> จ่ายแล้ว </span>";
			$row[] = $str; // Index 10: Status
			
			if($this->input->post('page') != 'dashboard'){
				$row[] = ($sales->created_by); // Index 11: Created By (Main List Only)
			}

					 if($sales->pos ==1):
					 	$str1='pos/edit/';
					 else:
					 	$str1='sales/update/';
					 endif;

					$str2 = '<div class="btn-group" title="View Account">
										<a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">
											Action <span class="caret"></span>
										</a>
										<ul role="menu" class="dropdown-menu dropdown-light pull-right">';
											if($this->permissions('sales_view'))
											$str2.='<li>
												<a title="View Invoice" href="'.base_url().'sales/invoice/'.$sales->id.'" >
													<i class="fa fa-fw fa-eye text-green"></i>ข้อมูลการขาย
												</a>
											</li>';

										/*	if($this->permissions('sales_edit'))
											$str2.='<li>
												<a title="Update Record ?" href="'.base_url().''.$str1.$sales->id.'">
													<i class="fa fa-fw fa-edit text-blue"></i>แก้ไข
												</a>
											</li>';  */

											if($this->permissions('sales_payment_view'))
											$str2.='<li>
												<a title="Pay" class="pointer" onclick="view_payments('.$sales->id.')" >
													<i class="fa fa-fw fa-money text-blue"></i>ดูการชำระเงิน
												</a>
											</li>';

											if($this->permissions('sales_payment_add'))
											$str2.='<li>
												<a title="Receive Payments" class="pointer" onclick="pay_now('.$sales->id.')" >
													<i class="fa fa-fw fa-hourglass-half text-green"></i>รับชำระเงิน
												</a>
											</li>';

											if($this->permissions('sales_add') || $this->permissions('sales_edit'))
											// $str2.='
											//   <li>
											// 	<a title="Take Print" target="_blank" href="sales/print_invoice/'.$sales->id.'">
											//		<i class="fa fa-fw fa-print text-blue"></i>Print
											// 	</a>
											// </li>
											
											$str2.='
									    	 <li>
												<a title="Take Print" target="_blank" onclick="printTemplate(\''.$print_url.'\')">
													<i class="fa fa-fw fa-print text-blue"></i>ใบแจ้งหนี้
												</a>
											</li> 
											<li>
												<a title="Download PDF" target="_blank" href="'.base_url().'pdf/sales/'.$sales->id.'">
													<i class="fa fa-fw fa-file-pdf-o text-blue"></i>ใบกำกับภาษี e-Tax
												</a>
											</li>
											<li>
												<a style="cursor:pointer" title="Print POS Invoice ?" onclick="print_invoice('.$sales->id.')">
													<i class="fa fa-fw fa-file-text text-blue"></i>ใบเสร็จ POS
												</a>
											</li>';

											if($this->permissions('sales_return_add'))
											$str2.='<li>
												<a title="Sales Return" href="'.base_url().'sales_return/add/'.$sales->id.'">
													<i class="fa fa-fw fa-undo text-blue"></i>ลดหนี้/คืนสินค้า
												</a>
											</li>';

											$str2.='<li>
												<a title="Sell Again" href="'.base_url().''.$str1.$sales->id.'?sell_again=true">
													<i class="fa fa-fw fa-files-o text-blue"></i>ทำการขายอีกครั้ง 
												</a>
											</li>';

										    $can_delete = $this->permissions('sales_delete');
											if ($can_delete && isset($sales->reference_type) && !empty($sales->reference_type)) {
												$can_delete = false;
											}
										    if($can_delete)
											$str2.='<li>
												<a style="cursor:pointer" title="Delete Record ?" onclick="delete_sales(\''.$sales->id.'\')">
													<i class="fa fa-fw fa-trash text-red"></i>ลบการขาย
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
		$this->permission_check('sales_edit');
		$id=$this->input->post('id');
		$status=$this->input->post('status');

		
		$result=$this->sales->update_status($id,$status);
		return $result;
	}
	public function delete_sales(){
		$this->permission_check_with_msg('sales_delete');
		$id=$this->input->post('q_id');

		// Check if locked
		if ($this->db->field_exists('reference_type', 'db_sales')) {
			$sales_rec = $this->db->select('reference_type')->where('id', $id)->get('db_sales')->row();
			if(!empty($sales_rec->reference_type)){
				echo "ระบบล๊อค! ไม่สามารถลบบิลที่ออกเอกสารใบกำกับภาษีแล้วได้";
				exit;
			}
		}

		echo $this->sales->delete_sales($id);
	}
	public function multi_delete(){
		$this->permission_check_with_msg('sales_delete');
		$ids=implode (",",$_POST['checkbox']);
		echo $this->sales->delete_sales($ids);
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
		$this->belong_to('db_sales',$id);
		if(!$this->permissions('sales_add') && !$this->permissions('sales_edit') && !$this->permissions('sales_view')){
			$this->show_access_denied_page();
		}
		
		$data=$this->data;
		$data=array_merge($data,array('sales_id'=>$id));

		// Check if locked
		if ($this->db->field_exists('reference_type', 'db_sales')) {
			$sales_rec = $this->db->select('reference_type')->where('id', $id)->get('db_sales')->row();
			if(!empty($sales_rec->reference_type)){
				$reference_name = 'ใบกำกับภาษี';
				if($sales_rec->reference_type == 'tax_receipt'){
					$reference_name = 'ใบเสร็จรับเงิน/ใบกำกับภาษี';
				}
				$data['locked_bill_msg'] = 'ระบบล๊อค! ไม่สามารถแก้ไขบิลที่ออกเอกสาร '.$reference_name.' แล้วได้';
			}
		}

		$data['page_title']=$this->lang->line('sales_invoice');
		$this->load->view('sal-invoice',$data);
	}
	
	//Print sales invoice 
	public function print_invoice($sales_id)
	{
		$this->belong_to('db_sales',$sales_id);
		if(!$this->permissions('sales_add') && !$this->permissions('sales_edit')){
			$this->show_access_denied_page();
		}
		
		$data=$this->data;
		$data=array_merge($data,array('sales_id'=>$sales_id));
		$data['page_title']=$this->lang->line('sales_invoice');

		$this->get_html_invoice($data);
	}


	

	//Print sales POS invoice 
	public function print_invoice_pos($sales_id)
	{

		$this->belong_to('db_sales',$sales_id);
		if(!$this->permissions('sales_add') && !$this->permissions('sales_edit')){
			$this->show_access_denied_page();
		}
		$data=$this->data;
		$data=array_merge($data,array('sales_id'=>$sales_id));
		$data['page_title']=$this->lang->line('sales_invoice');
		
		$this->load->view('sal-invoice-pos',$data);
		
		
	}
	public function get_html_invoice($data){
		$invoice_format_id = get_invoice_format_id();

		if($invoice_format_id==4){
			$this->load->view('print-sales-invoice-4',$data);
		}
		else{
			$this->load->view('print-sales-invoice-3',$data);
		}
        // Get output html
        return $this->output->get_output();
	}
	public function pdf($sales_id){

		if(!$this->permissions('sales_add') && !$this->permissions('sales_edit')){
			$this->show_access_denied_page();
		}
		$this->belong_to('db_sales',$sales_id);

		$data=$this->data;
		$data['page_title']=$this->lang->line('sales_invoice');
        $data=array_merge($data,array('sales_id'=>$sales_id));


        $html = $this->get_html_invoice($data);
        
		mb_internal_encoding('UTF-8');

        // Clean up NBSP characters and other problematic characters that Dompdf might struggle with
        $html = str_replace(array('&nbsp;', "\xc2\xa0", "\xc2\xad"), ' ', $html);

        $options = new Options();
		$options->set('isRemoteEnabled', true);
		$options->set('isFontSubsettingEnabled', false); // Ensure full font glyph mapping for Thai
        $dompdf = new Dompdf($options);

        // Load HTML content
        $dompdf->loadHtml($html,'UTF-8');
        
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');/*landscape or portrait*/
        
        // Render the HTML as PDF
        $dompdf->render();
        
        // Output the generated PDF (1 = download and 0 = preview)
        $dompdf->stream("Sales-invoice-$sales_id-".date('M')."_".date('d')."_".date('Y'), array("Attachment"=>0));
	}
	
	

	
	/*v1.1*/
	public function return_row_with_data($rowcount,$item_id){
		echo $this->sales->get_items_info($rowcount,$item_id);
	}
	public function return_sales_list($sales_id){
		echo $this->sales->return_sales_list($sales_id);
	}
	public function delete_payment(){
		$this->permission_check_with_msg('sales_payment_delete');
		$payment_id = $this->input->post('payment_id');
		echo $this->sales->delete_payment($payment_id);
	}
	public function show_pay_now_modal(){
		$this->permission_check_with_msg('sales_view');
		$sales_id=$this->input->post('sales_id');
		echo $this->sales->show_pay_now_modal($sales_id);
	}
	public function save_payment(){
		$this->permission_check_with_msg('sales_add');
		echo $this->sales->save_payment();
	}
	public function view_payments_modal(){
		$this->permission_check_with_msg('sales_view');
		$sales_id=$this->input->post('sales_id');
		echo $this->sales->view_payments_modal($sales_id);
	}
	public function get_customers_select_list(){
		if(get_current_store_id()==1) { sync_stores_to_customers(); }
		echo get_customers_select_list(null,$_POST['store_id']);
	}
	public function get_items_select_list(){
		$item_type = isset($_POST['item_type']) ? $_POST['item_type'] : '';
		echo get_items_select_list(null,$_POST['store_id'],$item_type);
	}
	public function get_tax_select_list(){
		echo get_tax_select_list(null,$_POST['store_id']);
	}
	/*Get warehouse select list*/
	public function get_warehouse_select_list(){
		echo get_warehouse_select_list(null,$_POST['store_id']);
	}

	public function get_brands_select_list(){
		echo get_brands_select_list(null,$_POST['store_id']);
	}
	public function get_categories_select_list(){
		echo get_categories_select_list(null,$_POST['store_id']);
	}

	public function get_payment_types_select_list(){
		echo get_payment_types_select_list(null,$_POST['store_id']);
	}

	//Print sales Payment Receipt
	public function print_show_receipt($payment_id){
		if(!$this->permissions('sales_add') && !$this->permissions('sales_edit')){
			$this->show_access_denied_page();
		}
		$data=$this->data;
		$data['page_title']=$this->lang->line('payment_receipt');
		$data=array_merge($data,array('payment_id'=>$payment_id));
		$this->load->view('print-cust-payment-receipt',$data);
	}
	
	public function get_users_select_list(){
		echo get_users_select_list($this->session->userdata("role_id"),$_POST['store_id']);
	}

	public function return_quotation_list($quotation_id){
		echo $this->sales->return_quotation_list($quotation_id);
	}
	
	// Controller: Sales.php
	public function get_sales_items_json($sales_id) {
		$store_id = get_current_store_id(); // ใช้ store ปัจจุบัน
	
		$items = $this->db
			->select("a.description, a.sales_qty, a.price_per_unit, b.tax, a.discount_amt, a.tax_type, a.tax_amt, a.item_id, i.item_name AS db_item_name")
			->from("db_salesitems a")
			->join("db_tax b", "b.id = a.tax_id", "left")
			->join("db_items i", "i.count_id = a.item_id AND i.store_id = '$store_id'", "left")
			->where("a.sales_id", $sales_id)
			->get()
			->result();
	
		echo json_encode($items);
	}
	


	public function get_sales_suggestions()
	{
		$term = $this->input->get('term');
		$this->db->select("id, sales_code, sales_date");
		$this->db->from("db_sales");
		$this->db->like("sales_code", $term);
		$this->db->order_by("sales_date", "DESC");
		$this->db->limit(20);
		$query = $this->db->get();
		
		$result = array();
		if($query->num_rows() > 0){
			foreach ($query->result() as $row) {
				$result[] = array(
					'label' => $row->sales_code,
					'value' => $row->sales_code,
					'sales_date' => show_date($row->sales_date)
				);
			}
		} else {
			$result[] = array(
				'label' => 'No Results Found',
				'value' => ''
			);
		}
		echo json_encode($result);
	}

	public function test_db() {
		$res = $this->db->query("SHOW COLUMNS FROM db_sales LIKE 'reference_type'");
		if($res->num_rows() > 0) {
			echo "Column reference_type exists in database: " . $this->db->database;
		} else {
			echo "Column reference_type DOES NOT exist in " . $this->db->database . ". Adding...";
			$sql = "ALTER TABLE db_sales ADD COLUMN reference_type VARCHAR(50) DEFAULT NULL AFTER reference_no";
			if ($this->db->query($sql)) {
				echo " Added successfully.";
			} else {
				echo " Error adding.";
			}
		}
	}
}
