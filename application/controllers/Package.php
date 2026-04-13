<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Package extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load_global();
		$this->load->model('package_model');
	}

	public function index()
	{
		$this->permission_check('package_view');
		$data = $this->data;
		$data['page_title'] = $this->lang->line('package_list');
		$this->load->view('package-list', $data);
	}

	public function add()
	{
		$this->permission_check('package_add');
		$data = $this->data;
		$data['page_title'] = $this->lang->line('new_package');
		$this->load->view('package', $data);
	}

	public function update($id)
	{
		$this->permission_check('package_edit');
		$data = $this->data;
		$data['page_title'] = $this->lang->line('update_package');
		$data = $this->package_model->get_details($id, $data);
		$this->load->view('package', $data);
	}

	public function save()
	{
		$this->permission_check('package_add');
		echo $this->package_model->save_and_update();
	}

    public function save_update_package()
    {
        $log = date('Y-m-d H:i:s') . " save_update_package hit. GET: ".print_r($_GET, true)." POST: ".print_r($_POST, true)."\n";
        file_put_contents('c:/laragon/www/bill_pdf_u/package_save_log.txt', $log, FILE_APPEND);
        
        $this->permission_check('package_add');
		echo $this->package_model->save_and_update();
    }
    
    public function update_package()
    {
        $this->permission_check('package_edit');
        echo $this->package_model->save_and_update();
    }

	public function delete_package()
	{
		$this->permission_check('package_delete');
		$id = $this->input->post('q_id');
		echo $this->package_model->delete_package_from_table($id);
	}

	public function status_update()
	{
		$this->permission_check('package_edit');
		$id = $this->input->post('id');
		$status = $this->input->post('status');
		echo $this->package_model->update_status($id, $status);
	}

	public function ajax_list()
	{
		$list = $this->package_model->get_datatables();
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $package) {
			$no++;
			$row = array();
			$row[] = '<input type="checkbox" name="checkbox[]" value=' . $package->id . ' class="checkbox column_checkbox" >';
			$row[] = $package->package_type;
			$row[] = $package->package_name;
			$row[] = store_number_format($package->monthly_price);
			$row[] = store_number_format($package->annual_price);
			$row[] = ($package->trial_days == -1) ? 'Unlimited' : $package->trial_days;
			$row[] = ($package->max_warehouses == -1) ? 'Unlimited' : $package->max_warehouses;
			$row[] = ($package->max_users == -1) ? 'Unlimited' : $package->max_users;
			$row[] = ($package->max_items == -1) ? 'Unlimited' : $package->max_items;
			$row[] = ($package->max_invoices == -1) ? 'Unlimited' : $package->max_invoices;
			$row[] = ($package->max_etax_emails == -1) ? 'Unlimited' : $package->max_etax_emails;
			$row[] = show_date($package->expire_date);

			if ($package->status == 1) {
				$str = "<span onclick=\"update_status(" . $package->id . ",0)\" id='span_" . $package->id . "'  class='label label-success cursor-pointer'>Active </span>";
			} else {
				$str = "<span onclick=\"update_status(" . $package->id . ",1)\" id='span_" . $package->id . "'  class='label label-danger cursor-pointer'> Inactive </span>";
			}
			$row[] = $str;

			$str2 = '<div class="btn-group" title="View Account">
                        <a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">
                            Action <span class="caret"></span>
                        </a>
                        <ul role="menu" class="dropdown-menu dropdown-light pull-right">';

			if ($this->permissions('package_edit'))
				$str2 .= '<li>
                            <a title="Edit Record ?" href="' . base_url() . 'package/update/' . $package->id . '">
                                <i class="fa fa-fw fa-edit text-blue"></i>แก้ไข
                            </a>
                        </li>';

			if ($this->permissions('package_delete'))
				$str2 .= '<li>
                            <a style="cursor:pointer" title="Delete Record ?" onclick="delete_package(' . $package->id . ')">
                                <i class="fa fa-fw fa-trash text-red"></i>ลบ
                            </a>
                        </li>';

			$str2 .= '</ul></div>';
			$row[] = $str2;

			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->package_model->count_all(),
			"recordsFiltered" => $this->package_model->count_filtered(),
			"data" => $data,
		);
		echo json_encode($output);
	}
}
