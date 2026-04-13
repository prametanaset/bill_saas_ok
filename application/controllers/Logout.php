<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Logout extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_info();
	}
	public function index()
	{
		log_message('info', 'Logout controller called for User ID: ' . $this->session->userdata('inv_userid') . ' | Store ID: ' . $this->session->userdata('store_id'));
		$this->session->userdata('language');

		$cookie= array(
           'name'   => 'language',
           'value'  => $this->session->userdata('language'),
           'expire' => '3600',
       	);
        $this->input->set_cookie($cookie);


		$data = $this->data;
		//CLEAR ALL SESSION FROM VIRTUAL VARIABLES
		$this->session->sess_destroy();
		//LOGOUT
		redirect(base_url('login'));
	}
}
