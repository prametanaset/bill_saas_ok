<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gateways extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load_global();
		$this->load->model('gateways_model');
		$this->load->helper('gateway_helper');
	}

	public function index()
	{
		$this->permission_check('gateway_view');
		$data = $this->data;
		$data['page_title'] = $this->lang->line('gateways_list');
		$this->load->view('gateways', $data);
	}

	public function update_gateways()
	{
		$this->permission_check('gateway_edit');
		$data = $this->data;
		$result = $this->gateways_model->update_gateways();
		if ($result == "success") {
			$this->session->set_flashdata('success', 'Gateways Updated Successfully!');
		} else {
			$this->session->set_flashdata('failed', 'Update Failed!');
		}
		redirect('gateways');
	}
}
