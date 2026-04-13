<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->php_verification();
		$this->load_info();
		//if(get_domain()!=get_dbdomain()){echo appinfo_domain_msg();exit();}
		is_sql_full_group_by_enabled();
	}

	public function php_verification(){
		// $phpversion = phpversion();
		// if($phpversion!=required_php_version()){
		// 	 echo 'Application required PHP Version 7.4.*, Your server loaded with PHP Version '.$phpversion;exit;
		// }
	}

	public function langauge($id){
		$this->load->model('language_model');
        $this->language_model->set($id);
        redirect($_SERVER['HTTP_REFERER']);
	}

	public function index()
	{	
		//Verify PHP version
		// if(phpversion()>required_php_version()){
		// 	$heading = "Invalid Server Configuration!";
		// 	$message = "Application need PHP Version <b>7.4</b>, Your server loaded with PHP Version <b>".phpversion()."</b>";
		// 	$message .= "<br><a href=".base_url().">Refresh</a>";

		// 	show_error($message, null, $heading);
		// 	exit;
		// }
		if($this->session->userdata('logged_in')==1){ redirect(base_url().'dashboard');	}
		$data = $this->data;
		$this->load->view('landing_login',$data);
	}
	public function verify()
	{
		$this->form_validation->set_rules('email','Email/Username','required');
		$this->form_validation->set_rules('pass','Password','required');
		if($this->form_validation->run()==FALSE){
			$this->session->set_flashdata('failed', 'กรุณากรอกอีเมล์ และรหัสผ่าน!');
			redirect('login');
		}
		else{
			$email    = $this->input->post('email');
			$password = $this->input->post('pass');

			$this->load->model('login_model');
			$result = $this->login_model->verify_credentials($email, $password);

			// verify_credentials redirects on success — if we reach here, it failed
		}
	}
	public function forgot_password(){
		if($this->session->userdata('logged_in')==1){ redirect(base_url().'dashboard');	}
		$data = $this->data;
		$this->load->view('forgot-password',$data);
	}
	public function send_otp(){		
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
		
		if($this->form_validation->run()==FALSE){
			$this->session->set_flashdata('failed', 'Invalid Email!');
			redirect(base_url().'login/forgot_password');
		}
		else{
			$email=$this->input->post('email');
			$this->load->model('login_model');//Model
			$response = $this->login_model->verify_email_send_otp($email);

			if($response==true){//Model->Method
				redirect(base_url().'login/otp');
			}
			else{
				redirect(base_url().'login/forgot_password');
			}			
		}
	}
	public function otp(){
		if($this->session->userdata('logged_in')==1){ redirect(base_url().'dashboard');	}
		$data = $this->data;
		$this->load->view('otp',$data);
	}
	public function verify_otp(){
		$this->form_validation->set_rules('otp', 'OTP', 'required|numeric');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');

		if($this->form_validation->run()==FALSE){
			$this->session->set_flashdata('failed', 'รหัส OTP ไม่ถูกต้อง!');
			redirect(base_url().'login/otp');
		}
		else{
			$otp   = $this->input->post('otp');
			$email = $this->input->post('email');

			// ── Strict comparison + OTP expiry check ────────────────────
			$s_email   = $this->session->userdata('email');
			$s_otp     = $this->session->userdata('otp');
			$s_otp_exp = $this->session->userdata('otp_expires_at');

			$otp_expired = ($s_otp_exp && time() > $s_otp_exp);

			if($otp_expired){
				// Clear expired OTP
				$this->session->unset_userdata(['email','otp','otp_expires_at']);
				$this->session->set_flashdata('failed', 'รหัส OTP หมดอายุแล้ว! กรุณาขอรหัสใหม่อีกครั้ง');
				redirect(base_url().'login/forgot_password'); return;
			}

			if($s_email === $email && (string)$s_otp === (string)$otp){
				$data          = $this->data;
				$data['email'] = $email;
				$data['otp']   = $otp;
				$this->load->view("change-login-password", $data);
			}
			else{
				$this->session->set_flashdata('failed', 'รหัส OTP ไม่ถูกต้อง!');
				redirect(base_url().'login/otp');
			}
		}
	}
	public function change_password(){
		$this->form_validation->set_rules('otp',       'OTP',             'required|numeric');
		$this->form_validation->set_rules('email',     'Email',           'required|valid_email');
		$this->form_validation->set_rules('password',  'Password',        'required|min_length[6]');
		$this->form_validation->set_rules('cpassword', 'Confirm Password','required|matches[password]');

		if($this->form_validation->run()==FALSE){
			$this->session->set_flashdata('failed', 'กรุณากรอกรหัสผ่านให้ถูกต้อง (อย่างน้อย 6 ตัวอักษร)!');
			redirect(base_url().'login/otp');
		}
		else{
			$otp       = $this->input->post('otp');
			$email     = $this->input->post('email');
			$password  = $this->input->post('password');

			$s_email   = $this->session->userdata('email');
			$s_otp     = $this->session->userdata('otp');
			$s_otp_exp = $this->session->userdata('otp_expires_at');

			// Strict OTP + email check + expiry
			$otp_expired = ($s_otp_exp && time() > $s_otp_exp);
			$valid = ($s_email === $email && (string)$s_otp === (string)$otp && !$otp_expired);

			if($valid){
				$this->load->model('login_model');
				if($this->login_model->change_password($password, $email)){
					$this->session->unset_userdata(['email','otp','otp_expires_at']);
					$this->session->set_flashdata('success', 'เปลี่ยนรหัสผ่านสำเร็จแล้ว! กรุณาเข้าสู่ระบบด้วยรหัสผ่านใหม่');
					redirect(base_url().'login');
				} else {
					$data          = $this->data;
					$data['email'] = $email;
					$data['otp']   = $otp;
					$this->session->set_flashdata('failed', 'ไม่พบบัญชีที่เชื่อมกับอีเมลนี้!');
					$this->load->view("change-login-password", $data);
				}
			} else {
				$this->session->unset_userdata(['email','otp','otp_expires_at']);
				$this->session->set_flashdata('failed', 'รหัส OTP ไม่ถูกต้องหรือหมดอายุแล้ว! กรุณาขอรหัสใหม่');
				redirect(base_url().'login/forgot_password');
			}
		}
	}
	

}
