<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
	}
	
	//Open SMS Form 
	public function index(){
		$this->permission_check('send_email');
		$data=$this->data;
		$data['page_title']=$this->lang->line('send_email');

		$this->load->model('email_templates_model', 'templates');
		$data['templates_list'] = $this->templates->get_active_templates();

		$q_smtp = $this->db->where("store_id", get_current_store_id())->get("db_smtp");
		$data['smtp_configured'] = ($q_smtp->num_rows() > 0 && $q_smtp->row()->smtp_status == 1);

		$this->load->view('email', $data);
	}


	//Create Message
	public function send_message(){
		$this->permission_check('send_email');
		$data=$this->data;
		
		$attachment = "";
		if(!empty($_FILES['attachment']['name'])){
			$config['upload_path']          = './uploads/email/';
	        $config['allowed_types']        = 'pdf|xml|jpg|jpeg|png|gif|docx|doc|xls|xlsx|txt|zip|rar';
	        $config['max_size']             = 3072; // 3MB
	        $config['file_name']            = time()."_".$_FILES['attachment']['name'];

	        if (!file_exists($config['upload_path'])) {
			    mkdir($config['upload_path'], 0777, true);
			}

	        $this->load->library('upload', $config);

	        if ($this->upload->do_upload('attachment')){
	        	$upload_data = $this->upload->data();
	        	$attachment = $upload_data['full_path'];
	        }
	        else{
	        	$this->session->set_flashdata('error', $this->upload->display_errors());
	        	redirect('email/index','refresh');
	        }
		} else if($this->input->post('document_id') != "" && $this->input->post('document_type') != "") {
			// Generate PDF on the fly for attachment
			$doc_type = $this->input->post('document_type');
			$doc_id = $this->input->post('document_id');
			$attachment = $this->generate_document_pdf($doc_type, $doc_id);

			// generate_document_pdf() switches DB back to master (bill_xml) at the end.
			// Switch BACK to tenant DB so email_model can query db_smtp, db_email_logs, etc.
			$tenant_db = $this->session->userdata('database_name');
			if(!empty($tenant_db) && $this->session->userdata('store_id') != 1) {
				$this->db->query("USE `{$tenant_db}`");
			}
		}

		$this->load->model('email_model');

		// Server-side: ตรวจสอบว่า email_to ไม่มี placeholder ค้างอยู่
		// ป้องกันกรณีที่ JS ไม่ได้ replace {{$customer_email}} ก่อน submit
		$email_to_raw = $this->input->post('email_to');
		if(preg_match('/\{\{\$[a-zA-Z_]+\}\}|\[\[\$[a-zA-Z_]+\]\]/', $email_to_raw)) {
			$doc_id_post = $this->input->post('document_id');
			$doc_type_post = $this->input->post('document_type');
			
			if(!empty($doc_id_post)) {
				if($doc_type_post == 'Sales') {
					$doc_row = $this->db->select('b.email')->from('db_sales a')
						->join('db_customers b', 'b.id=a.customer_id')
						->where('a.id', $doc_id_post)->get()->row();
				} else if($doc_type_post == 'Invoice') {
					$doc_row = $this->db->select('c.email')->from('db_invoice a')
						->join('db_sales b', 'b.id=a.sales_id')
						->join('db_customers c', 'c.id=b.customer_id')
						->where('a.id', $doc_id_post)->get()->row();
				} else if($doc_type_post == 'Sales Return') {
					$doc_row = $this->db->select('b.email')->from('db_salesreturn a')
						->join('db_customers b', 'b.id=a.customer_id')
						->where('a.id', $doc_id_post)->get()->row();
				} else if($doc_type_post == 'Sales Debit') {
					$doc_row = $this->db->select('b.email')->from('db_salesdebit a')
						->join('db_customers b', 'b.id=a.customer_id')
						->where('a.id', $doc_id_post)->get()->row();
				} else if($doc_type_post == 'Quotation') {
					$doc_row = $this->db->select('b.email')->from('db_quotation a')
						->join('db_customers b', 'b.id=a.customer_id')
						->where('a.id', $doc_id_post)->get()->row();
				} else if($doc_type_post == 'Receipt') {
					$doc_row = $this->db->select('c.email')->from('db_receipts a')
						->join('db_sales b', 'b.id=a.sales_id')
						->join('db_customers c', 'c.id=b.customer_id')
						->where('a.id', $doc_id_post)->get()->row();
				}
				
				$email_to_raw = (isset($doc_row) && $doc_row && !empty($doc_row->email)) ? $doc_row->email : '';
			}
			
			// ถ้ายังเป็น placeholder อยู่ หรือไม่มีข้อมูล ให้ลบออกเพื่อให้ email_model ฟ้อง Required Field
			if(preg_match('/\{\{\$[a-zA-Z_]+\}\}|\[\[\$[a-zA-Z_]+\]\]/', $email_to_raw)) {
				$email_to_raw = '';
			}
		}

		$email_subject_raw = $this->input->post('email_subject');
		$email_message_raw = $this->input->post('email_content');

		// Server-side Placeholder Replacement (Safety Net)
		// ถ้ายังมีตัวแปรค้างอยู่ในหัวข้อหรือเนื้อหา ให้พยายามหาข้อมูลจริงมาแทนที่
		if(preg_match('/\[\[\$[a-zA-Z_]+\]\]/', $email_subject_raw . $email_message_raw)) {
			// ดึงข้อมูลเอกสารมาเพื่อนำมาแทนที่
			$doc_id_post = $this->input->post('document_id');
			$doc_type_post = $this->input->post('document_type');
			
			if(!empty($doc_id_post)) {
				$template_data = null;
				if($doc_type_post == 'Sales') {
					$template_data = $this->db->select('a.*, b.customer_name, b.email')->from('db_sales a')
						->join('db_customers b', 'b.id=a.customer_id')
						->where('a.id', $doc_id_post)->get()->row();
					if($template_data) {
						$template_data->sales_date_be = date("dm", strtotime($template_data->sales_date)) . (date("Y", strtotime($template_data->sales_date)) + 543);
						$template_data->actual_code = $template_data->sales_code;
						$template_data->doc_tag = "SLS";
					}
				} else if($doc_type_post == 'Invoice') {
					$template_data = $this->db->select('i.*, a.sales_date, b.customer_name, b.email, a.sales_code as ref_sales_code')->from('db_invoice i')
						->join('db_sales a', 'a.id=i.sales_id')
						->join('db_customers b', 'b.id=a.customer_id')
						->where('i.id', $doc_id_post)->get()->row();
					if($template_data) {
						$template_data->sales_date_be = date("dm", strtotime($template_data->invoice_date)) . (date("Y", strtotime($template_data->invoice_date)) + 543);
						$template_data->actual_code = $template_data->invoice_code;
						$template_data->doc_tag = "INV";
						$template_data->ref_code_final = $template_data->ref_sales_code;
					}
				} else if($doc_type_post == 'Sales Return') {
					$template_data = $this->db->select('a.*, b.customer_name, b.email, s.sales_code as original_sales_code')->from('db_salesreturn a')
						->join('db_customers b', 'b.id=a.customer_id')
						->join('db_sales s', 's.id=a.sales_id', 'left')
						->where('a.id', $doc_id_post)->get()->row();
					if($template_data) {
						$template_data->sales_date_be = date("dm", strtotime($template_data->return_date)) . (date("Y", strtotime($template_data->return_date)) + 543);
						$template_data->actual_code = $template_data->return_code;
						$template_data->doc_tag = "CRN";
						$template_data->ref_code_final = $template_data->original_sales_code;
					}
				} else if($doc_type_post == 'Sales Debit') {
					$template_data = $this->db->select('a.*, b.customer_name, b.email, s.sales_code as original_sales_code')->from('db_salesdebit a')
						->join('db_customers b', 'b.id=a.customer_id')
						->join('db_sales s', 's.id=a.sales_id', 'left')
						->where('a.id', $doc_id_post)->get()->row();
					if($template_data) {
						$template_data->sales_date_be = date("dm", strtotime($template_data->debit_date)) . (date("Y", strtotime($template_data->debit_date)) + 543);
						$template_data->actual_code = $template_data->debit_code;
						$template_data->doc_tag = "DBN";
						$template_data->ref_code_final = $template_data->original_sales_code;
					}
				} else if($doc_type_post == 'Purchase') {
					$template_data = $this->db->select('a.*, b.supplier_name as customer_name, b.email')->from('db_purchase a')
						->join('db_suppliers b', 'b.id=a.supplier_id')
						->where('a.id', $doc_id_post)->get()->row();
					if($template_data) {
						$template_data->sales_date_be = date("dm", strtotime($template_data->purchase_date)) . (date("Y", strtotime($template_data->purchase_date)) + 543);
						$template_data->actual_code = $template_data->purchase_code;
						$template_data->doc_tag = "PUR";
					}
				} else if($doc_type_post == 'Purchase Return') {
					$template_data = $this->db->select('a.*, b.supplier_name as customer_name, b.email')->from('db_purchasereturn a')
						->join('db_suppliers b', 'b.id=a.supplier_id')
						->where('a.id', $doc_id_post)->get()->row();
					if($template_data) {
						$template_data->sales_date_be = date("dm", strtotime($template_data->return_date)) . (date("Y", strtotime($template_data->return_date)) + 543);
						$template_data->actual_code = $template_data->return_code;
						$template_data->doc_tag = "PRN";
					}
				} else if($doc_type_post == 'Quotation') {
					$template_data = $this->db->select('a.*, b.customer_name, b.email')->from('db_quotation a')
						->join('db_customers b', 'b.id=a.customer_id')
						->where('a.id', $doc_id_post)->get()->row();
					if($template_data) {
						$template_data->sales_date_be = date("dm", strtotime($template_data->quotation_date)) . (date("Y", strtotime($template_data->quotation_date)) + 543);
						$template_data->actual_code = $template_data->quotation_code;
						$template_data->doc_tag = "QTN";
					}
				} else if($doc_type_post == 'Receipt') {
					$template_data = $this->db->select('a.*, b.sales_code as original_sales_code, c.customer_name, c.email')->from('db_receipts a')
						->join('db_sales b', 'b.id=a.sales_id')
						->join('db_customers c', 'c.id=b.customer_id')
						->where('a.id', $doc_id_post)->get()->row();
					if($template_data) {
						$template_data->sales_date_be = date("dm", strtotime($template_data->receipt_date)) . (date("Y", strtotime($template_data->receipt_date)) + 543);
						$template_data->actual_code = $template_data->receipt_code;
						$template_data->doc_tag = "RCP";
						$template_data->ref_code_final = $template_data->original_sales_code;
					}
				} else if($doc_type_post == 'Expense') {
					$template_data = $this->db->select('a.*')->from('db_expense a')
						->where('a.id', $doc_id_post)->get()->row();
					if($template_data) {
						$template_data->sales_date_be = date("dm", strtotime($template_data->expense_date)) . (date("Y", strtotime($template_data->expense_date)) + 543);
						$template_data->actual_code = $template_data->expense_code;
						$template_data->doc_tag = "EXP";
						$template_data->customer_name = $template_data->expense_for;
						$template_data->email = ""; // Expense usually doesn't have a direct email
					}
				}

				if($template_data) {
					$replacements = [
						'[[$sales_date]]' => "[" . $template_data->sales_date_be . "]",
						'[[$sales_code]]' => "[" . $template_data->actual_code . "]",
						'[[$doc_tag]]'    => "[" . $template_data->doc_tag . "]",
						'[[$ref_code]]'   => !empty($template_data->ref_code_final) ? "[" . $template_data->ref_code_final . "]" : (!empty($template_data->reference_no) ? "[" . $template_data->reference_no . "]" : ""),
						'{{$customer_email}}' => $template_data->email,
					];
					
					$email_subject_raw = strtr($email_subject_raw, $replacements);
					$email_message_raw = strtr($email_message_raw, $replacements);
				}
			}
		}

		$template_id = $this->input->post('template_id');
		$template_name = "";
		if(!empty($template_id)){
			$template_name = $this->db->select('template_name')->where('id',$template_id)->get('db_emailtemplates')->row()->template_name;
		}

		// Check ETAX Limit
		if(!empty($template_name) && stripos($template_name, 'ETAX') !== false){
			$this->load->model('email_model');
			$store_id = $this->session->userdata('store_id');
			$usage = $this->email_model->get_etax_usage($store_id);
			$limit = $this->email_model->get_package_limit($store_id);
			
			if($limit != -1 && $usage >= $limit){
				$this->session->set_flashdata('error', "ขออภัย! โควตาการส่ง ETAX ของคุณเต็มแล้ว ({$limit} ครั้ง/เดือน) กรุณาอัปเกรดแพ็กเกจ");
				redirect('email/index');
			}
		}

		$email_info = array(
							'to' 				=> $email_to_raw, 
							'cc' 				=> $this->input->post('email_cc'), 
							'bcc' 				=> $this->input->post('email_bcc'), 
							'subject' 			=> $email_subject_raw, 
							'message' 			=> $email_message_raw, 
							'attachment' 		=> $attachment,
						);
		$response = $this->email_model->send_email($email_info, $template_id, $template_name);
		
		//Delete attachment after sending
		if(!empty($attachment) && file_exists($attachment)){
			unlink($attachment);
		}

		if($response===true){
			$this->session->set_flashdata('success', 'Success!! Email Sent Successfully! ');
		}
		else{
			$this->session->set_flashdata('error', $response);
		}

		redirect('email/index');
	}


	public function get_documents_json() {
		$type = $this->input->get('type');
		$term = $this->input->get('term');
		$store_id = get_current_store_id();

		$this->db->select('id');
		if($type == 'Sales') {
			$this->db->select('sales_code as code');
			$this->db->from('db_sales');
		} else if($type == 'Invoice') {
			$this->db->select('invoice_code as code');
			$this->db->from('db_invoice');
		} else if($type == 'Purchase') {
			$this->db->select('purchase_code as code');
			$this->db->from('db_purchase');		
		} else if($type == 'Sales Return') {
			$this->db->select('return_code as code');
			$this->db->from('db_salesreturn');
        } else if($type == 'Sales Debit') {
			$this->db->select('debit_code as code');
			$this->db->from('db_salesdebit');		
		} else if($type == 'Purchase Return') {
			$this->db->select('return_code as code');
			$this->db->from('db_purchasereturn');		
		} else if($type == 'Quotation') {
			$this->db->select('quotation_code as code');
			$this->db->from('db_quotation');	
		} else if($type == 'Receipt') {
			$this->db->select('receipt_code as code');
			$this->db->from('db_receipts');		
		} else if($type == 'Expense') {
			$this->db->select('expense_code as code');
			$this->db->from('db_expense');	
		} else {
			echo json_encode([]);
			return;
		}

		$this->db->where('store_id', $store_id);
		if(!empty($term)) {
			$this->db->group_start();
			if($type == 'Sales') $this->db->like('sales_code', $term);
			else if($type == 'Invoice') $this->db->like('invoice_code', $term);
			else if($type == 'Purchase') $this->db->like('purchase_code', $term);
			else if($type == 'Expense') $this->db->like('expense_code', $term);
			else if($type == 'Quotation') $this->db->like('quotation_code', $term);
			else if($type == 'Receipt') $this->db->like('receipt_code', $term);
			else $this->db->like('return_code', $term);
			$this->db->group_end();
		}
		$this->db->order_by('id', 'desc');
		$this->db->limit(20);
		$query = $this->db->get();

		$result = [];
		foreach($query->result() as $row) {
			$result[] = ['id' => $row->id, 'text' => $row->code];
		}
		echo json_encode($result);
	}

	public function get_document_details() {
		$type = $this->input->get('type');
		$id = $this->input->get('id');
		$store_id = get_current_store_id();

		if(empty($type) || empty($id)) {
			echo json_encode(['status' => 'failed']);
			return;
		}

		$html = "";
		$email = "";
		$subject = "";

		if($type == 'Sales') {
			$doc = $this->db->select('a.*, b.customer_name, b.email')
							->from('db_sales a')
							->join('db_customers b', 'b.id=a.customer_id')
							->where('a.id', $id)
							->get()->row();
			if($doc) {
				$email = $doc->email;
				
				// E-TAX Placeholders
				$sales_date_be = date("dm", strtotime($doc->sales_date)) . (date("Y", strtotime($doc->sales_date)) + 543);
				$doc_tag = "INV";
				if(isset($doc->debit_bit) && !empty($doc->debit_bit)) $doc_tag = "DBN";
				if(isset($doc->return_bit) && !empty($doc->return_bit)) $doc_tag = "CRN";
				$ref_code = (isset($doc->reference_no) && !empty($doc->reference_no)) ? $doc->reference_no : "";

				$subject = $this->lang->line('sales') . " #" . $doc->sales_code;
				$items = $this->db->select('a.*, i.item_name')
								  ->from('db_salesitems a')
								  ->join('db_items i', 'i.id=a.item_id')
								  ->where('a.sales_id', $id)
								  ->get()->result();
				$html = $this->generate_summary_table($type, $doc, $items);

				$extra_data = [
					'sales_date_be' => $sales_date_be,
					'sales_code' => $doc->sales_code,
					'doc_tag' => $doc_tag,
					'ref_code' => $ref_code,
					'customer_email' => $doc->email
				];
			}
		} else if($type == 'Invoice') {
			$doc = $this->db->select('i.*, a.sales_code as ref_sales_code, b.customer_name, b.email, a.grand_total')
							->from('db_invoice i')
							->join('db_sales a', 'a.id=i.sales_id')
							->join('db_customers b', 'b.id=a.customer_id')
							->where('i.id', $id)
							->get()->row();
			if($doc) {
				$email = $doc->email;
				
				// E-TAX Placeholders
				$sales_date_be = date("dm", strtotime($doc->invoice_date)) . (date("Y", strtotime($doc->invoice_date)) + 543);
				$doc_tag = "INV";
				$ref_code = $doc->ref_sales_code;

				$subject = $this->lang->line('e_invoice') . " #" . $doc->invoice_code;
				$items = $this->db->select('a.*, i.item_name')
								  ->from('db_salesitems a')
								  ->join('db_items i', 'i.id=a.item_id')
								  ->where('a.sales_id', $doc->sales_id)
								  ->get()->result();
				$html = $this->generate_summary_table($type, $doc, $items);

				$extra_data = [
					'sales_date_be' => $sales_date_be,
					'sales_code' => $doc->invoice_code,
					'doc_tag' => $doc_tag,
					'ref_code' => $ref_code,
					'customer_email' => $doc->email
				];
			}
		} else if($type == 'Purchase') {
			$doc = $this->db->select('a.*, b.supplier_name, b.email')
							->from('db_purchase a')
							->join('db_suppliers b', 'b.id=a.supplier_id')
							->where('a.id', $id)
							->get()->row();
			if($doc) {
				$email = $doc->email;
				$subject = $this->lang->line('purchase') . " #" . $doc->purchase_code;
				$items = $this->db->select('a.*, i.item_name')
								  ->from('db_purchaseitems a')
								  ->join('db_items i', 'i.id=a.item_id')
								  ->where('a.purchase_id', $id)
								  ->get()->result();
				$html = $this->generate_summary_table($type, $doc, $items);
			}
		} else if($type == 'Sales Return') {
			$doc = $this->db->select('a.*, b.customer_name, b.email, s.sales_code as original_sales_code')
							->from('db_salesreturn a')
							->join('db_customers b', 'b.id=a.customer_id')
							->join('db_sales s', 's.id=a.sales_id', 'left')
							->where('a.id', $id)
							->get()->row();
			if($doc) {
				$email = $doc->email;
				
				// E-TAX Placeholders for CN
				$sales_date_be = date("dm", strtotime($doc->return_date)) . (date("Y", strtotime($doc->return_date)) + 543);
				$doc_tag = "CRN";
				$ref_code = (isset($doc->original_sales_code) && !empty($doc->original_sales_code)) ? $doc->original_sales_code : "";

				$subject = $this->lang->line('sales_return') . " #" . $doc->return_code;
				$items = $this->db->select('a.*, i.item_name')
								  ->from('db_salesitemsreturn a')
								  ->join('db_items i', 'i.id=a.item_id')
								  ->where('a.return_id', $id)
								  ->get()->result();
				$html = $this->generate_summary_table($type, $doc, $items);

				$extra_data = [
					'sales_date_be' => $sales_date_be,
					'sales_code' => $doc->return_code,
					'doc_tag' => $doc_tag,
					'ref_code' => $ref_code,
					'customer_email' => $doc->email
				];
			}
		} else if($type == 'Sales Debit') {
			$doc = $this->db->select('a.*, b.customer_name, b.email, s.sales_code as original_sales_code')
							->from('db_salesdebit a')
							->join('db_customers b', 'b.id=a.customer_id')
							->join('db_sales s', 's.id=a.sales_id', 'left')
							->where('a.id', $id)
							->get()->row();
			if($doc) {
				$email = $doc->email;
				
				// E-TAX Placeholders for DN
				$sales_date_be = date("dm", strtotime($doc->debit_date)) . (date("Y", strtotime($doc->debit_date)) + 543);
				$doc_tag = "DBN";
				$ref_code = (isset($doc->original_sales_code) && !empty($doc->original_sales_code)) ? $doc->original_sales_code : "";

				$subject = $this->lang->line('sales_debit') . " #" . $doc->debit_code;
				$items = $this->db->select('a.*, i.item_name')
								  ->from('db_salesitemsdebit a')
								  ->join('db_items i', 'i.id=a.item_id')
								  ->where('a.debit_id', $id)
								  ->get()->result();
				$html = $this->generate_summary_table($type, $doc, $items);

				$extra_data = [
					'sales_date_be' => $sales_date_be,
					'sales_code' => $doc->debit_code,
					'doc_tag' => $doc_tag,
					'ref_code' => $ref_code,
					'customer_email' => $doc->email
				];
			}
		} else if($type == 'Purchase Return') {
			$doc = $this->db->select('a.*, b.supplier_name, b.email')
							->from('db_purchasereturn a')
							->join('db_suppliers b', 'b.id=a.supplier_id')
							->where('a.id', $id)
							->get()->row();
			if($doc) {
				$email = $doc->email;
				$subject = $this->lang->line('purchase_return') . " #" . $doc->return_code;
				$items = $this->db->select('a.*, i.item_name')
								  ->from('db_purchaseitemsreturn a')
								  ->join('db_items i', 'i.id=a.item_id')
								  ->where('a.return_id', $id)
								  ->get()->result();
				$html = $this->generate_summary_table($type, $doc, $items);
			}
		} else if($type == 'Quotation') {
			$doc = $this->db->select('a.*, b.customer_name, b.email')
							->from('db_quotation a')
							->join('db_customers b', 'b.id=a.customer_id')
							->where('a.id', $id)
							->get()->row();
			if($doc) {
				$email = $doc->email;
				
				// E-TAX Placeholders for Quotation
				$sales_date_be = date("dm", strtotime($doc->quotation_date)) . (date("Y", strtotime($doc->quotation_date)) + 543);
				$doc_tag = "QTN";
				$ref_code = (isset($doc->reference_no) && !empty($doc->reference_no)) ? $doc->reference_no : "";

				$subject = $this->lang->line('quotation_doc') . " #" . $doc->quotation_code;
				$items = $this->db->select('a.*, i.item_name')
								  ->from('db_quotationitems a')
								  ->join('db_items i', 'i.id=a.item_id')
								  ->where('a.quotation_id', $id)
								  ->get()->result();
				$html = $this->generate_summary_table($type, $doc, $items);

				$extra_data = [
					'sales_date_be' => $sales_date_be,
					'sales_code' => $doc->quotation_code,
					'doc_tag' => $doc_tag,
					'ref_code' => $ref_code,
					'customer_email' => $doc->email
				];
			}
		} else if($type == 'Receipt') {
			$doc = $this->db->select('a.*, b.sales_code, c.customer_name, c.email, b.grand_total as sales_total')
							->from('db_receipts a')
							->join('db_sales b', 'b.id=a.sales_id')
							->join('db_customers c', 'c.id=b.customer_id')
							->where('a.id', $id)
							->get()->row();
			if($doc) {
				$email = $doc->email;
				$subject = $this->lang->line('receipt') . " #" . $doc->receipt_code;
				
				$html = "<h3>" . $this->lang->line('receipt') . " Summary</h3>";
				$html .= "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
				$html .= "<tr><th style='background-color: #f2f2f2;'>" . $this->lang->line('receipt_no') . "</th><td>" . $doc->receipt_code . "</td></tr>";
				$html .= "<tr><th style='background-color: #f2f2f2;'>" . $this->lang->line('date') . "</th><td>" . show_date($doc->receipt_date) . "</td></tr>";
				$html .= "<tr><th style='background-color: #f2f2f2;'>" . $this->lang->line('sales_code') . "</th><td>" . $doc->sales_code . "</td></tr>";
				$html .= "<tr><th style='background-color: #f2f2f2;'>" . $this->lang->line('customer_name') . "</th><td>" . $doc->customer_name . "</td></tr>";
				$html .= "<tr><th style='background-color: #f2f2f2;'>" . $this->lang->line('amount') . "</th><td>" . store_number_format($doc->sales_total) . "</td></tr>";
				$html .= "</table>";
				
				$extra_data = [
					'sales_date_be' => date("dm", strtotime($doc->receipt_date)) . (date("Y", strtotime($doc->receipt_date)) + 543),
					'sales_code' => $doc->receipt_code,
					'doc_tag' => "RCP",
					'ref_code' => $doc->sales_code,
					'customer_email' => $doc->email
				];
			}
		} else if($type == 'Expense') {
			$doc = $this->db->select('a.*')
							->from('db_expense a')
							->where('a.id', $id)
							->get()->row();
			if($doc) {
				$subject = $this->lang->line('expense') . " #" . $doc->expense_code;
				$html = "<h3>" . $this->lang->line('expense') . " Summary</h3>";
				$html .= "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
				$html .= "<tr><th style='background-color: #f2f2f2;'>" . $this->lang->line('date') . "</th><td>" . show_date($doc->expense_date) . "</td></tr>";
				$html .= "<tr><th style='background-color: #f2f2f2;'>" . $this->lang->line('expense_for') . "</th><td>" . $doc->expense_for . "</td></tr>";
				$html .= "<tr><th style='background-color: #f2f2f2;'>" . $this->lang->line('amount') . "</th><td>" . store_number_format($doc->expense_amt) . "</td></tr>";
				$html .= "<tr><th style='background-color: #f2f2f2;'>" . $this->lang->line('note') . "</th><td>" . $doc->note . "</td></tr>";
				$html .= "</table>";
			}
		}

		echo json_encode([
			'status' => 'success',
			'content' => $html,
			'email' => $email,
			'subject' => $subject,
			'extra' => isset($extra_data) ? $extra_data : null
		]);
	}

	private function generate_summary_table($type, $doc, $items) {
		$label = $this->lang->line(strtolower(str_replace(' ', '_', $type)));
		if(empty($label)) $label = $type;
		
		$html = "<h3>" . $label . " Summary</h3>";
		$html .= "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
		$html .= "<thead><tr style='background-color: #f2f2f2;'>";
		$html .= "<th>" . $this->lang->line('item_name') . "</th>";
		$html .= "<th>" . $this->lang->line('quantity') . "</th>";
		$html .= "<th>" . $this->lang->line('price') . "</th>";
		$html .= "<th>" . $this->lang->line('total') . "</th>";
		$html .= "</tr></thead>";
		$html .= "<tbody>";
		foreach($items as $item) {
			$qty = 0;
			if(isset($item->sales_qty)) $qty = $item->sales_qty;
			else if(isset($item->purchase_qty)) $qty = $item->purchase_qty;
			else if(isset($item->return_qty)) $qty = $item->return_qty;

			$html .= "<tr>";
			$html .= "<td>" . $item->item_name . "</td>";
			$html .= "<td style='text-align: center;'>" . format_qty($qty) . "</td>";
			$html .= "<td style='text-align: right;'>" . store_number_format($item->price_per_unit) . "</td>";
			$html .= "<td style='text-align: right;'>" . store_number_format($item->total_cost) . "</td>";
			$html .= "</tr>";
		}
		$html .= "</tbody>";
		$html .= "<tfoot>";
		$html .= "<tr><td colspan='3' style='text-align: right;'><b>" . $this->lang->line('grand_total') . "</b></td><td style='text-align: right;'><b>" . store_number_format($doc->grand_total) . "</b></td></tr>";
		$html .= "</tfoot></table>";
		return $html;
	}

	private function generate_document_pdf($type, $id) {
		$temp_path = './uploads/email/'.time().'_document.pdf';
		if (!file_exists('./uploads/email/')) {
		    mkdir('./uploads/email/', 0777, true);
		}

		// เพิ่ม execution time limit เพื่อป้องกัน timeout ระหว่าง TCPDF PDF/A-3 generation
		@set_time_limit(120); // 2 minutes
		@ini_set('memory_limit', '256M');

		// ให้แน่ใจว่า DB อยู่ที่ tenant DB
		$tenant_db = $this->session->userdata('database_name');
		if(!empty($tenant_db) && $this->session->userdata('store_id') != 1) {
			$this->db->query("USE `{$tenant_db}`");
		}

		$pdf_content = '';

		try {
			if($type == 'Sales') {
				$params = ['sales_id' => $id];
				$invoice_format_id = get_invoice_format_id();
				if($invoice_format_id == 4) {
					$this->load->library('tcpdf/invoice/GstInvoice', $params, 'pdf_lib');
				} else {
					$this->load->library('tcpdf/invoice/Sales', $params, 'pdf_lib');
				}
				$pdf_content = $this->pdf_lib->show_pdf('S'); 
			} else if($type == 'Invoice') {
				$invoice_row = $this->db->where('id', $id)->get('db_invoice')->row();
				$params = [
					'sales_id' => $invoice_row->sales_id,
					'invoice' => $invoice_row
				];
				$this->load->library('tcpdf/invoice/Invoice', $params, 'pdf_lib');
				$pdf_content = $this->pdf_lib->show_pdf('S'); 
			} else if($type == 'Sales Return') {
				$params = ['return_id' => $id];
				$this->load->library('tcpdf/invoice/SalesReturn', $params, 'pdf_lib');
				$pdf_content = $this->pdf_lib->show_pdf('S'); 
			} else if($type == 'Sales Debit') {
				$params = ['debit_id' => $id];
				$this->load->library('tcpdf/invoice/SalesDebit', $params, 'pdf_lib');
				$pdf_content = $this->pdf_lib->show_pdf('S'); 
			} else if($type == 'Quotation') {
				$params = ['quotation_id' => $id];
				$this->load->library('tcpdf/invoice/Quotation_pdf', $params, 'pdf_lib');
				$pdf_content = $this->pdf_lib->show_pdf('S'); 
			} else if($type == 'Receipt') {
				$params = ['receipt_id' => $id];
				$this->load->library('tcpdf/invoice/Receipt', $params, 'pdf_lib');
				$pdf_content = $this->pdf_lib->show_pdf('S'); 
			} else if($type == 'Purchase') {
				$params = ['purchase_id' => $id];
				$this->load->library('tcpdf/invoice/Purchase_pdf', $params, 'pdf_lib');
				$pdf_content = $this->pdf_lib->show_pdf('S'); 
			} else if($type == 'Purchase Return') {
				$params = ['return_id' => $id];
				$this->load->library('tcpdf/invoice/PurchaseReturn_pdf', $params, 'pdf_lib');
				$pdf_content = $this->pdf_lib->show_pdf('S'); 
			} else {
				return "";
			}
		} catch (Exception $e) {
			log_message('error', 'Email PDF generation failed: ' . $e->getMessage());
			return ""; // ส่งอีเมลโดยไม่มีไฟล์แนบ
		} catch (Error $e) {
			log_message('error', 'Email PDF generation fatal error: ' . $e->getMessage());
			return "";
		}

		// เขียน PDF ลงไฟล์
		if(!empty($pdf_content)) {
			file_put_contents($temp_path, $pdf_content);
			return realpath($temp_path);
		}

		return "";
	}
}

