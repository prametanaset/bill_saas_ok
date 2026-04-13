<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Signup extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_info();
		$this->load->model('package_model');
		$this->load->model('store_model');
	}

	public function index()
	{
		if($this->session->userdata('logged_in')==1){ redirect(base_url().'dashboard','refresh');	}
		
		$data = $this->data;
		// Fetch active packages for selection
		// Note: We might want to filter only active packages or specific types
		$package_data = $this->package_model->get_package_list();
		$data['packages'] = $package_data['package_list'] ?? []; 
		
        // Load Stripe Config
        $this->config->load('stripe');
        $data['stripe_publishable_key'] = $this->config->item('stripe_publishable_key');

		$this->load->view('signup', $data);
	}

	public function register_store()
	{
		$this->form_validation->set_rules('store_name', 'Store Name', 'required|trim|min_length[3]');
		$this->form_validation->set_rules('contact_name', 'Contact Name', 'required|trim|min_length[3]');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[db_users.email]');
		$this->form_validation->set_rules('mobile', 'Mobile', 'required|numeric|min_length[10]|is_unique[db_users.mobile]');
		$this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
		$this->form_validation->set_rules('cpassword', 'Confirm Password', 'required|matches[password]');
		$this->form_validation->set_rules('package_id', 'Package', 'required');

		if ($this->form_validation->run() == TRUE) {
			
			$data = array(
				'store_name' => $this->input->post('store_name'),
				'contact_name' => $this->input->post('contact_name'),
				'email'      => $this->input->post('email'),
				'mobile'     => $this->input->post('mobile'),
				'password'   => $this->input->post('password'),
				'package_id' => $this->input->post('package_id'),
				'payment_method' => $this->input->post('payment_method'), // 'trial' or 'bank_transfer'
				
				// Optional: Set default location for now since we don't have fields in simple signup
				// Realistically these should be in the form or defaulted carefully
				'country'    => 1, 
				'state'      => 1, 
				'city'       => 'Online',
				'postcode'   => '00000',
				'address'    => '',
				'stripeToken' => $this->input->post('stripeToken'),
				'stripeEmail' => $this->input->post('stripeEmail'),
			);

			// Handle File Upload
			if(!empty($_FILES['payment_slip']['name'])){
				$config['upload_path']   = './uploads/payment_slips/';
				$config['allowed_types'] = 'gif|jpg|jpeg|png|pdf';
				$config['max_size']      = 2048; // 2MB
				$this->load->library('upload', $config);

				if ( ! $this->upload->do_upload('payment_slip')) {
					$this->session->set_flashdata('failed', $this->upload->display_errors());
					redirect('signup');
					return;
				} else {
					$data['payment_slip'] = $this->upload->data('file_name');
				}
			}

            // Using the new safe method
			$result = $this->store_model->create_store_registration($data);
			
			if($result === 'success'){
				if($data['payment_method'] == 'bank_transfer'){
					// Get Admin Contact Info
					$admin_store = get_store_details(1);
					$contact_info = "";
					if(!empty($admin_store)){
						$contact_info .= "<br><b>ช่องทางติดต่อเจ้าหน้าที่:</b>";
                        $contact_info .= "<br><i class='fa fa-phone'></i> เบอร์โทร: <b><a href='tel:" . $admin_store->mobile . "'>" . $admin_store->mobile . "</a></b>";
						if(!empty($admin_store->line_id)){ // Hypothetical, checking if column exists or just append mobile
						  // $contact_info .= " Line: " . $admin_store->line_id; 
						}
                        if(!empty($admin_store->email)){
                            $contact_info .= "<br><i class='fa fa-envelope'></i> อีเมล์: <b><a href='mailto:" . $admin_store->email . "'>" . $admin_store->email . "</a></b>";
                        }
					}

					$this->session->set_flashdata('success', 'ลงทะเบียนสำเร็จ! การชำระเงินของคุณอยู่ระหว่างดำเนินการต่อภายใน 30 นาที กรุณา login เข้าสู่ระบบ ด้วยอีเมล์และรหัสผ่าน ที่คุณได้ลงทะเบียนไว้' . $contact_info);
				} else {
					$this->session->set_flashdata('success', 'ลงทะเบียนสำเร็จ! กรุณาเข้าสู่ระบบเพื่อเริ่มใช้งานรุ่นทดลองใช้ฟรี');
				}
				
				// Clear any lingering failure messages from previous attempts
				$this->session->set_flashdata('failed', NULL);
				
				redirect('login');
			} else {
				$this->session->set_flashdata('failed', 'การลงทะเบียนล้มเหลว: ' . $result);
				redirect('signup');
			}

		} else {
			$this->session->set_flashdata('failed', validation_errors());
			redirect('signup');
		}
	}
}
