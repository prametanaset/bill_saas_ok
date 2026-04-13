<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subscription extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load_info();
		// Load models
		$this->load->model('package_model');
		$this->load->model('store_model');
		$this->load->model('subscription_model');
		$this->load->model('super_admin_payment_model');
	}

	public function index() {
		$this->expired();
	}

	/**
	 * Check if a store has ever used a FREE package.
	 * Always queries master bill_xml.db_subscription (tenant DB has no sub data).
	 */
	private function _has_used_free_package($store_id) {
		$sql = "SELECT a.id FROM bill_xml.db_subscription a
		        LEFT JOIN bill_xml.db_package b ON b.id = a.package_id
		        WHERE a.store_id = ?
		          AND (UPPER(b.package_type) = 'FREE' OR b.monthly_price = 0 OR b.annual_price = 0)
		        LIMIT 1";
		$q = $this->db->query($sql, [$store_id]);
		return ($q && $q->num_rows() > 0);
	}

	/**
	 * Fetch the active subscription + package fields in ONE query.
	 * Uses cross-DB references so no USE-db switching is needed.
	 */
	private function _get_subscription_with_package($sub_id) {
		$sql = "SELECT
		            s.*,
		            p.package_name,
		            p.max_warehouses,
		            p.max_users,
		            p.max_items,
		            p.max_invoices,
		            p.max_etax_emails,
		            p.monthly_price,
		            p.annual_price,
		            p.package_type,
		            p.trial_days
		        FROM bill_xml.db_subscription s
		        LEFT JOIN bill_xml.db_package p ON p.id = s.package_id
		        WHERE s.id = ?
		        LIMIT 1";
		$q = $this->db->query($sql, [$sub_id]);
		$sub = ($q && $q->num_rows() > 0) ? $q->row() : null;

		if ($sub && empty($sub->subscription_name)) {
			$sub->subscription_name = $sub->package_name ?? 'N/A';
		}
		return $sub;
	}

	/**
	 * Fetch available package list from master bill_xml using direct cross-DB SQL.
	 * No USE-db switching needed. Returns packages with status=1 (active) only.
	 */
	private function _get_master_packages() {
		$sql = "SELECT
		            id, package_name, package_type, description,
		            monthly_price, annual_price, trial_days, plan_type,
		            max_warehouses, max_users, max_items, max_invoices, max_etax_emails
		        FROM bill_xml.db_package
		        WHERE store_id = 1
		          AND (status = 1 OR status IS NULL)
		          AND max_warehouses IS NOT NULL AND max_warehouses != ''
		        ORDER BY id DESC";
		$q = $this->db->query($sql);
		if (!$q || $q->num_rows() == 0) return [];

		$packages = [];
		foreach ($q->result_array() as $row) {
			// Convert -1 to '∞' to match Package_model convention used in views
			$row['max_warehouses']  = ($row['max_warehouses']  == -1) ? '∞' : $row['max_warehouses'];
			$row['max_users']       = ($row['max_users']       == -1) ? '∞' : $row['max_users'];
			$row['max_items']       = ($row['max_items']       == -1) ? '∞' : $row['max_items'];
			$row['max_invoices']    = ($row['max_invoices']    == -1) ? '∞' : $row['max_invoices'];
			$row['max_etax_emails'] = ($row['max_etax_emails'] == -1) ? '∞' : (int)$row['max_etax_emails'];
			$packages[] = $row;
		}
		return $packages;
	}

	/**
	 * Fetch a single package by ID from master bill_xml database.
	 * Uses cross-DB reference for a single, efficient query.
	 */
	private function _get_master_package_by_id($package_id) {
		$q = $this->db->where('id', $package_id)->get('bill_xml.db_package');
		return ($q && $q->num_rows() > 0) ? $q->row() : null;
	}

	/**
	 * Get store record from master DB (db_store lives in bill_xml).
	 */
	private function _get_store_record($store_id) {
		$q = $this->db->where('id', $store_id)->get('bill_xml.db_store');
		return ($q && $q->num_rows() > 0) ? $q->row() : null;
	}

	public function expired() {
		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}

		$data = $this->data;
		$data['page_title'] = 'Subscription Expired';

		$store_id  = $this->session->userdata('store_id');
		$store_rec = $this->_get_store_record($store_id);
		$sub_id    = $store_rec->current_subscriptionlist_id ?? null;

		if (!empty($sub_id)) {
			$data['subscription'] = $this->_get_subscription_with_package($sub_id);
		}

		$data['packages']          = $this->_get_master_packages();
		$data['free_package_used'] = $this->_has_used_free_package($store_id);

		$this->load->view('subscription_expired', $data);
	}

	public function status() {
		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}

		$data = $this->data;
		$data['page_title'] = $this->lang->line('package_list');

		$store_id  = $this->session->userdata('store_id');
		$store_rec = $this->_get_store_record($store_id);
		$sub_id    = $store_rec->current_subscriptionlist_id ?? null;

		if (!empty($sub_id)) {
			$data['subscription'] = $this->_get_subscription_with_package($sub_id);
		}

		$data['packages']          = $this->_get_master_packages();
		$data['free_package_used'] = $this->_has_used_free_package($store_id);

		$this->load->view('subscription-status', $data);
	}

	public function renew_plan($package_id) {
		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}

		$data = $this->data;
		$data['package'] = $this->_get_master_package_by_id($package_id);

		if (empty($data['package'])) {
			$this->session->set_flashdata('failed', 'แพ็กเกจไม่ถูกต้อง!');
			redirect('subscription/expired');
			return;
		}

		// BLOCK FREE PACKAGE IF ALREADY USED
		$is_free = (strtoupper($data['package']->package_type) == 'FREE')
		           || ($data['package']->monthly_price == 0 && $data['package']->annual_price == 0);
		if ($is_free && $this->_has_used_free_package($this->session->userdata('store_id'))) {
		    $this->session->set_flashdata('failed', 'คุณใช้สิทธิ์ทดลองใช้ฟรี (หรือแพ็กเกจฟรี) ครบกำหนดแล้ว! กรุณาเลือกสมัครแผนแบบชำระเงิน.');
		    redirect('subscription/expired');
		    return;
		}

		// Clear any stale success flashdata so it won't appear on the payment form
		// (e.g. leftover from a previous payment approval or login redirect chain)
		$this->session->set_flashdata('success', null);

		$data['price']                = $data['package']->monthly_price;
		$data['page_title']           = 'ต่ออายุการสมัครสมาชิก';
		$this->config->load('stripe');
		$data['stripe_publishable_key'] = $this->config->item('stripe_publishable_key');

		$this->load->view('subscription_payment', $data);
	}

	public function process_payment() {
		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}

		$this->form_validation->set_rules('payment_method', 'Payment Method', 'required');
		$this->form_validation->set_rules('package_id', 'Package', 'required');

		if ($this->form_validation->run() == TRUE) {
			$package_id     = $this->input->post('package_id');
			$payment_method = $this->input->post('payment_method');
			$pkg            = $this->_get_master_package_by_id($package_id);

			if (empty($pkg)) {
				$this->session->set_flashdata('failed', 'แพ็กเกจไม่ถูกต้อง!');
				redirect('subscription/expired');
				return;
			}

			// BLOCK FREE PACKAGE IF ALREADY USED (Server-side check)
			$is_free = (strtoupper($pkg->package_type) == 'FREE')
			           || ($pkg->monthly_price == 0 && $pkg->annual_price == 0);
			if ($is_free && $this->_has_used_free_package($this->session->userdata('store_id'))) {
				$this->session->set_flashdata('failed', 'คุณใช้สิทธิ์ทดลองใช้ฟรี (หรือแพ็กเกจฟรี) ครบกำหนดแล้ว! กรุณาเลือกสมัครแผนแบบชำระเงิน.');
				redirect('subscription/expired');
				return;
			}

			$result = $this->super_admin_payment_model->process_payment($this->input->post());

			if ($result['status'] === 'success') {
				if ($payment_method === 'bank_transfer') {
					// Check if store is currently EXPIRED or IN READ-ONLY MODE
					$is_expired = ($this->session->userdata('is_expired') === true || $this->session->userdata('is_read_only') === true);
					
					$msg = 'ส่งสลิปชำระเงินสำเร็จแล้ว! กำลังรอการตรวจสอบจากผู้ดูแลระบบ (<b>จะเรียบร้อยภายในไม่เกิน 1 ชั่วโมง</b>)';
					
					if ($is_expired) {
						// For expired stores: Show global warning (Orange) to explain why they are still blocked
						$this->session->set_flashdata('warning', $msg);
					} else {
						// For active stores: Show localized success (Green) ONLY on the Package List page
						$this->session->set_flashdata('localized_success', $msg);
					}
					
					redirect('subscription/status');
				} elseif ($payment_method === 'free') {
					$this->session->set_flashdata('success', 'สมัครแพ็กเกจฟรีสำเร็จ! เริ่มใช้งานได้เลย.');
					$this->session->unset_userdata('is_expired');
					redirect('dashboard');
				} else {
					// Stripe or other auto-confirmed payment
					$this->session->set_flashdata('success', 'ชำระเงินสำเร็จ! ต่ออายุการสมัครสมาชิกเรียบร้อยแล้ว.');
					$this->session->unset_userdata('is_expired');
					redirect('dashboard');
				}
			} else {
				$this->session->set_flashdata('failed', 'การส่งคำขอล้มเหลว: ' . $result['message']);
				redirect('subscription/renew_plan/' . $package_id);
			}
		} else {
			$this->session->set_flashdata('failed', validation_errors());
			redirect('subscription/expired');
		}
	}
}
