<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Smtp extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
	}
	
	//Open SMTP Form 
	public function index(){
		$this->permission_check('smtp_settings');
		$data=$this->data;
		$data['page_title']=$this->lang->line('smtp_settings');
		
		$q1 = $this->db->where("store_id",get_current_store_id())->get("db_smtp");
		if($q1->num_rows()>0){
			$smtp_rec = $q1->row();
			$data['smtp_status']=$smtp_rec->smtp_status;
			$data['smtp_host']=$smtp_rec->smtp_host;
			$data['smtp_port']=$smtp_rec->smtp_port;
			$data['smtp_user']=$smtp_rec->smtp_user;
			$data['smtp_pass']=$smtp_rec->smtp_pass;
			$data['smtp_encryption']=$smtp_rec->smtp_encryption;
			$data['from_email']=$smtp_rec->from_email;
			$data['from_name']=$smtp_rec->from_name;
		}else{
			$data['smtp_status']=0;
			$data['smtp_host']='';
			$data['smtp_port']='';
			$data['smtp_user']='';
			$data['smtp_pass']='';
			$data['smtp_encryption']='';
			$data['from_email']='';
			$data['from_name']='';
		}
		
		$this->load->view('smtp', $data);
	}


	//UPDATE SMTP SETTINGS
	public function update_smtp(){

		$this->permission_check_with_msg('smtp_settings');
		//Extract Inputs
		extract(xss_html_filter(array_merge($this->data,$_POST,$_GET)));

		$site_id = get_current_store_id();

		//Update SMTP Settings
		// Ensure we don't save to db_store as per user request
		$info = array(
			'smtp_status'     => (int)$smtp_status,
			'smtp_host'       => $smtp_host,
			'smtp_port'       => (empty($smtp_port)) ? 0 : (int)$smtp_port,
			'smtp_user'       => $smtp_user,
			'smtp_pass'       => $smtp_pass,
			'smtp_encryption' => $smtp_encryption,
			'from_email'      => $from_email,
			'from_name'       => $from_name,
			'store_id'        => $site_id
		);

		$this->db->trans_begin();

		$q1 = $this->db->where("store_id",$site_id)->get("db_smtp");
		if($q1->num_rows()>0){
			$q2 = $this->db->where("store_id",$site_id)->update("db_smtp",$info);
		}else{
			$q2 = $this->db->insert("db_smtp",$info);
		}



		if(!$q2){
			echo "failed: " . $this->db->error()['message'];
		}else{
			$this->db->trans_commit();
			echo "success";
		}
	}
	public function test_email(){

		$this->permission_check_with_msg('smtp_settings');
		$this->load->library('email');

		//Extract Inputs from Form directly (not from DB)
		extract(xss_html_filter(array_merge($this->data,$_POST,$_GET)));

		$config = Array(
			'protocol' => 'smtp',
			'smtp_host' => $smtp_host,
			'smtp_port' => $smtp_port,
			'smtp_user' => $smtp_user,
			'smtp_pass' => $smtp_pass,
			'smtp_crypto' => $smtp_encryption,
			'crlf' => "\r\n",
			'newline' => "\r\n",
			'mailtype' => 'html'
		);

		$this->email->initialize($config);
		$this->email->set_newline("\r\n");

		$to = $from_email;
		$this->email->to($to);

		$from_email_addr = (!empty($from_email)) ? $from_email : $smtp_user;
		$from_name_val = (!empty($from_name)) ? $from_name : $this->session->userdata('store_name');

		$this->email->from($from_email_addr, $from_name_val);

		$this->email->subject("SMTP Test Email - ".$this->session->userdata('store_name'));
		$this->email->message("<h1>เชื่อมต่อสำเร็จ!</h1><p>นี่คืออีเมลทดสอบจากระบบของคุณ หากคุณเห็นข้อความนี้ แสดงว่าการตั้งค่า SMTP ถูกต้องแล้ว</p>");

		if($this->email->send()){
			echo "success";
		}
		else{
			echo "ล้มเหลว: " . $this->email->print_debugger();
			exit();
		}
	}
}

