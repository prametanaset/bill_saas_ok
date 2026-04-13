<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pdf extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
	}

	/**
	 * Sales invoices
	 * 3. Default Format
	 * 4. GST invoice Format
	*/
	public function sales($sales_id=null){

		$params = array();

		//Validate Record Authenttication
		$this->belong_to('db_sales',$sales_id);
		if(!$this->permissions('sales_add') && !$this->permissions('sales_edit')){
			$this->show_access_denied_page();
		}

		//Select Store Invoice Format
		$invoice_format_id = get_invoice_format_id();

		$params['sales_id'] = $sales_id;
		
		//Update Reference Type
		$this->db->set('reference_type', 'tax_invoice');
		$this->db->where('id', $sales_id);
		$this->db->where('store_id', get_current_store_id());
		$this->db->update('db_sales');

		if($invoice_format_id==4){
			//GST invoice
			$this->load->library('tcpdf/invoice/GstInvoice',$params);

			$this->gstinvoice->show_pdf();
		}
		else{
			//Default invoice
			$this->load->library('tcpdf/invoice/Sales',$params);

			$this->sales->show_pdf();
		}

	}

	public function receipt($sales_id=null){
		$params = array();

		//Validate Record Authenttication
		$this->belong_to('db_sales',$sales_id);
		if(!$this->permissions('sales_add') && !$this->permissions('sales_edit')){
			$this->show_access_denied_page();
		}

		// Create or Load Receipt
		$this->load->model('Receipt_model');
		$receipt_date = $this->input->get('receipt_date');
		$reference_no = $this->input->get('reference_no');
		$reference_receipt = $this->input->get('reference_receipt');
		$new_receipt = $this->input->get('new_receipt') ? true : false;
		$receipt = $this->Receipt_model->get_or_create_receipt($sales_id, $receipt_date, $reference_no, $reference_receipt, $new_receipt);

		$params['sales_id'] = $sales_id;
		$params['receipt'] = $receipt;
		
		$this->load->library('tcpdf/invoice/Receipt',$params);
		$this->receipt->show_pdf();
	}

	public function invoice($sales_id=null){
		$params = array();

		//Validate Record Authenttication
		$this->belong_to('db_sales',$sales_id);
		if(!$this->permissions('sales_add') && !$this->permissions('sales_edit')){
			$this->show_access_denied_page();
		}

		// Create or Load Invoice
		$this->load->model('Invoice_model');
		$invoice_date = $this->input->get('invoice_date');
		$reference_no = $this->input->get('reference_no');
		$new_invoice = $this->input->get('new_invoice') ? true : false;
		$invoice = $this->Invoice_model->get_or_create_invoice($sales_id, $invoice_date, $reference_no, $new_invoice);

		$params['sales_id'] = $sales_id;
		$params['invoice'] = $invoice;
		
		$this->load->library('tcpdf/invoice/Invoice',$params);
		$this->invoice->show_pdf();
	}

}
