<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pdf_facturx extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
	}

	public function sales($sales_id=null){

		$params = array();

		//Validate Record Authenttication
		$this->belong_to('db_sales',$sales_id);
		if(!$this->permissions('sales_add') && !$this->permissions('sales_edit')){
			$this->show_access_denied_page();
		}

		$params['sales_id'] = $sales_id;

		//Update Reference Type
		$this->db->set('reference_type', 'tax_receipt');
		$this->db->where('id', $sales_id);
		$this->db->where('store_id', get_current_store_id());
		$this->db->update('db_sales');

		$this->load->library('tcpdf/invoice/GstInvoice',$params);
		$this->gstinvoice->show_pdf();
	}
}
