<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Super_admin_payment_model extends MY_Model {
	private function get_plan_amount($package, $plan_type) {
		if ($plan_type === 'Annually') {
			if (isset($package->annual_price) && (float)$package->annual_price > 0) {
				return (float)$package->annual_price;
			}
			return (float)$package->monthly_price * 10;
		}
		return (float)$package->monthly_price;
	}

	public function __construct()
	{
		parent::__construct();
		// Auto-update schema for master tables - with safety checks
		
		// db_subscription migrations
		$q1 = $this->db->query("SHOW COLUMNS FROM bill_xml.db_subscription LIKE 'sales_id'");
		if ($q1 && $q1->num_rows() == 0) {
			$this->db->query("ALTER TABLE bill_xml.db_subscription ADD `sales_id` INT(11) AFTER `package_id` ");
		}
		
		$q2 = $this->db->query("SHOW COLUMNS FROM bill_xml.db_subscription LIKE 'plan_type'");
		if ($q2 && $q2->num_rows() == 0) {
			$this->db->query("ALTER TABLE bill_xml.db_subscription ADD `plan_type` ENUM('Monthly', 'Annually') DEFAULT 'Monthly' AFTER `subscription_date` ");
		}
		
		$q3 = $this->db->query("SHOW COLUMNS FROM bill_xml.db_subscription LIKE 'max_etax_emails'");
		if ($q3 && $q3->num_rows() == 0) {
			$this->db->query("ALTER TABLE bill_xml.db_subscription ADD `max_etax_emails` INT(11) DEFAULT -1 AFTER `max_invoices` ");
		}
		
		// db_offline_requests migrations
		$q4 = $this->db->query("SHOW COLUMNS FROM bill_xml.db_offline_requests LIKE 'plan_type'");
		if ($q4 && $q4->num_rows() == 0) {
			$this->db->query("ALTER TABLE bill_xml.db_offline_requests ADD `plan_type` ENUM('Monthly', 'Annually') DEFAULT 'Monthly' AFTER `package_id` ");
		}
	}

	public function process_payment($data) {
		$payment_method = $data['payment_method'];
		$package_id = $data['package_id'];
		$store_id = $this->session->userdata('store_id');
		
		$this->db->trans_begin();
		
		// 1. Get Package Info
		$package = $this->db->where('id', $package_id)->get('bill_xml.db_package')->row();
		if (!$package) {
			return ['status' => 'failed', 'message' => 'Invalid Package'];
		}

		$payment_status = 'Pending';
		$note = '';

		// 2. Process Payment based on method
		if ($payment_method == 'stripe') {
			// Get Stripe Keys
            $this->config->load('stripe');
            $stripe_secret_key = $this->config->item('stripe_api_key');
            $stripe_currency = $this->config->item('stripe_currency');

            if(empty($stripe_secret_key)){
                $this->db->trans_rollback();
                return ['status' => 'failed', 'message' => 'Stripe Secret Key is missing in config.'];
            }

            // Load Stripe Library
            require_once APPPATH . 'libraries/stripe/init.php';
            \Stripe\Stripe::setApiKey($stripe_secret_key);

            $token = $data['stripeToken'];
            $email = $data['stripeEmail'];
            $amount = $this->get_plan_amount($package, $data['plan_type']);
            
            // If it was already a free package, or amount is 0, let it stay 0
            if($package->monthly_price == 0 && $package->annual_price == 0) {
                $amount = 0;
            }

            $amount_cents = (int)($amount * 100); // Stripe expects cents

            try {
                $charge = \Stripe\Charge::create([
                    "amount" => $amount_cents,
                    "currency" => $stripe_currency,
                    "source" => $token,
                    "description" => "Subscription for " . $package->package_name . " (" . $store_id . ")"
                ]);

                if($charge->status == 'succeeded'){
                    $payment_status = 'Paid';
                    $note = 'Stripe Payment: ' . $charge->id;
                } else {
                    $this->db->trans_rollback();
                    return ['status' => 'failed', 'message' => 'Payment Failed: ' . $charge->failure_message];
                }

            } catch (\Stripe\Exception\CardException $e) {
                $this->db->trans_rollback();
                return ['status' => 'failed', 'message' => 'Card Error: ' . $e->getError()->message];
            } catch (Exception $e) {
                $this->db->trans_rollback();
                return ['status' => 'failed', 'message' => 'Stripe Error: ' . $e->getMessage()];
            }
		} 
		elseif ($payment_method == 'free' || $payment_method == 'manual') {
		    // Free Renewal
		    $payment_status = 'Paid';
		    $note = 'Free/Manual Renewal';
		}
		elseif ($payment_method == 'bank_transfer') {
			// Handle File Upload
			if (!empty($_FILES['payment_slip']['name'])) {
				$config['upload_path']          = './uploads/payment_slips/';
				$config['allowed_types']        = 'gif|jpg|png|jpeg|pdf';
				$config['max_size']             = 2048; // 2MB
                $config['file_name']            = time() . '_' . $_FILES['payment_slip']['name'];
				
				// Create dir if not exists
				if (!is_dir($config['upload_path'])) {
					mkdir($config['upload_path'], 0777, true);
				}

				$this->load->library('upload', $config);

				if (!$this->upload->do_upload('payment_slip')) {
					$this->db->trans_rollback();
					return ['status' => 'failed', 'message' => $this->upload->display_errors()];
				} else {
					$upload_data = $this->upload->data();
					$slip_path = $upload_data['file_name']; // Just filename
					
                    // Create Offline Request
                    $req_data = [
                        'store_id' => $store_id,
                        'package_id' => $package_id,
                        'plan_type' => $data['plan_type'], 
                        'amount' => $this->get_plan_amount($package, $data['plan_type']),
                        'payment_slip' => $slip_path,
                        'status' => 0,
                        'created_date' => date('Y-m-d'),
                        'created_time' => date('H:i:s')
                    ];
                    
                    $this->db->insert('bill_xml.db_offline_requests', $req_data);

                    // --- Send Email Notification to Superadmin ---
                    try {
                        $this->load->model('Email_model');
                        $admin_store = $this->db->select('store_name, email')->where('id', 1)->get('bill_xml.db_store')->row();
                        $store_info = $this->db->select('store_name')->where('id', $store_id)->get('bill_xml.db_store')->row();
                        
                        if ($admin_store) {
                            $to_email = $admin_store->email;
                            
                            // Fallback to SMTP user if store email is placeholder
                            if ($to_email == 'superadmin@example.com') {
                                $smtp = $this->db->select('smtp_user')->where('store_id', 1)->get('db_smtp')->row();
                                if ($smtp && !empty($smtp->smtp_user)) {
                                    $to_email = $smtp->smtp_user;
                                }
                            }

                            if (!empty($to_email)) {
                                $subject = "New Offline Payment Request from " . ($store_info->store_name ?? 'Store #'.$store_id);
                                $msg = "Dear Superadmin,<br><br>A new offline payment request has been submitted:<br><br>" .
                                       "<b>Store:</b> " . ($store_info->store_name ?? 'N/A') . "<br>" .
                                       "<b>Package:</b> " . $package->package_name . "<br>" .
                                       "<b>Amount:</b> " . $req_data['amount'] . "<br>" .
                                       "<b>Plan Type:</b> " . $req_data['plan_type'] . "<br><br>" .
                                       "Please login to Superadmin to review and approve the request.<br><br>Best regards,<br>System Notification";
                                
                                $this->Email_model->send_email_with_store_id([
                                    'to' => $to_email,
                                    'subject' => $subject,
                                    'message' => $msg
                                ], 1);
                            }
                        }
                    } catch (Exception $e) {
                        log_message('error', "Failed to send offline request notification: " . $e->getMessage());
                    }
                    // ----------------------------------------------

                    $this->db->trans_commit();
                    
					return ['status' => 'success', 'message' => 'อัพโหลดสลิป แล้ว! กรุณารอเพื่อดำเนินการต่อ'];
				}
			} else {
				$this->db->trans_rollback();
				return ['status' => 'failed', 'message' => 'กรุณาอัพโหลด สลิปชำระเงิน'];
			}
		} 
		else {
			$this->db->trans_rollback();
			return ['status' => 'failed', 'message' => 'Invalid Payment Method'];
		}
		
		// Calculate expire date
		$duration_str = "+1 month";
		if($data['plan_type'] == 'Annually'){
			$duration_str = "+1 year";
		}
		$new_expire_date = date('Y-m-d', strtotime($duration_str));
		
		$sub_data = array(
			'store_id' => $store_id,
			'package_id' => $package->id,
			'subscription_name' => $package->package_name,
			'subscription_date' => date('Y-m-d'),
			'trial_days' => 0,
			'expire_date' => $new_expire_date,
			'plan_type' => $data['plan_type'],
			'max_warehouses' => $package->max_warehouses,
			'max_users' => $package->max_users,
			'max_items' => $package->max_items,
			'max_invoices' => ($data['plan_type'] == 'Annually' && $package->monthly_price > 0) ? (($package->max_invoices == -1) ? -1 : $package->max_invoices * 12) : $package->max_invoices,
			'max_etax_emails' => ($data['plan_type'] == 'Annually' && $package->monthly_price > 0) ? (($package->max_etax_emails == -1) ? -1 : $package->max_etax_emails * 12) : $package->max_etax_emails,
			'monthly_price' => $package->monthly_price,
			'annual_price' => (isset($package->annual_price) && (float)$package->annual_price > 0) ? $package->annual_price : ($package->monthly_price * 10),
			'payment_gross' => $this->get_plan_amount($package, $data['plan_type']),
			'payment_type'  => ($payment_method == 'stripe') ? 'Stripe' : 'Cash',
			'status' => 1,
			'payment_status' => $payment_status,
			'package_status' => ($payment_status == 'Paid') ? 'Active' : 'Pending',
			'created_date' => date('Y-m-d'),
			'created_time' => date('H:i:s'),
			'created_by' => $this->session->userdata('inv_username')
		);
		
		if (!$this->db->insert('bill_xml.db_subscription', $sub_data)) {
			$this->db->trans_rollback();
			return ['status' => 'failed', 'message' => 'Database Error'];
		}
		$new_sub_id = $this->db->insert_id();

		$this->db->where('id', $store_id)->update('bill_xml.db_store', ['current_subscriptionlist_id' => $new_sub_id]);

		// Sync to Superadmin Sales List if Paid
		if($payment_status == 'Paid'){
			$sales_id = $this->sync_subscription_to_sale($new_sub_id, ($payment_method == 'stripe' ? 'Stripe' : 'Cash'));
				$this->db->where('id', $new_sub_id)->update('bill_xml.db_subscription', ['sales_id' => $sales_id]);
		}

		$this->db->trans_commit();
		return ['status' => 'success', 'message' => 'Payment Successful'];
	}

	public function approve_offline_request($req) {
        $this->db->trans_begin();

		// Allow approval only for pending request to avoid duplicate approvals.
		$pending_req = $this->db->where('id', $req->id)->where('status', 0)->get('bill_xml.db_offline_requests')->row();
		if(!$pending_req){
			$this->db->trans_rollback();
			return false;
		}
        
        // Update Request Status
        $this->db->where('id', $req->id)->where('status', 0)->update('bill_xml.db_offline_requests', ['status' => 1]);
        
        // Get Package Info
        $package = $this->db->where('id', $req->package_id)->get('bill_xml.db_package')->row();
        if (!$package) {
            $this->db->trans_rollback();
            return false;
        }

        // Calculate Expiry
        $duration_str = "+1 month";
		if($req->plan_type == 'Annually'){
			$duration_str = "+1 year";
		}
		
		$new_expire_date = date('Y-m-d', strtotime($duration_str));
		
		$sub_data = array(
			'store_id' => $req->store_id,
			'package_id' => $package->id,
			'subscription_name' => $package->package_name, 
			'subscription_date' => date('Y-m-d'),
			'trial_days' => 0,
			'expire_date' => $new_expire_date,
			'plan_type' => $req->plan_type,
			'max_warehouses' => $package->max_warehouses,
			'max_users' => $package->max_users,
			'max_items' => $package->max_items,
			'max_invoices' => ($req->plan_type == 'Annually' && $package->monthly_price > 0) ? (($package->max_invoices == -1) ? -1 : $package->max_invoices * 12) : $package->max_invoices,
			'max_etax_emails' => ($req->plan_type == 'Annually' && $package->monthly_price > 0) ? (($package->max_etax_emails == -1) ? -1 : $package->max_etax_emails * 12) : $package->max_etax_emails,
			'monthly_price' => $package->monthly_price,
			'annual_price' => (isset($package->annual_price) && (float)$package->annual_price > 0) ? $package->annual_price : ($package->monthly_price * 10),
			'payment_gross' => $req->amount,
			'payment_type' => 'Bank Transfer', 
			'status' => 1,
			'payment_status' => 'Paid',
			'package_status' => 'Active',
			'created_date' => date('Y-m-d'),
			'created_time' => date('H:i:s'),
            'created_by' => 'Superadmin' 
		);
		
		// Check for existing Unpaid subscription
        $store = $this->db->where('id', $req->store_id)->get('bill_xml.db_store')->row();
        $current_sub_id = $store->current_subscriptionlist_id;

        $existing_sub = null;
        if($current_sub_id){
             $existing_sub = $this->db->where('id', $current_sub_id)
                                     ->where('payment_status', 'Pending')
                                     ->get('bill_xml.db_subscription')->row();
        }

        if($existing_sub){
             // UPDATE Existing
             $sub_data['created_date'] = $existing_sub->created_date;
             $sub_data['created_time'] = $existing_sub->created_time;
             $sub_data['expire_date'] = $new_expire_date;
             
             if (!$this->db->where('id', $current_sub_id)->update('bill_xml.db_subscription', $sub_data)) {
                $this->db->trans_rollback();
                return false;
             }
             $new_sub_id = $current_sub_id;
        } else {
             // INSERT New
             if (!$this->db->insert('bill_xml.db_subscription', $sub_data)) {
                $this->db->trans_rollback();
                return false;
             }
             $new_sub_id = $this->db->insert_id();
        }

		// Update Store
		$this->db->where('id', $req->store_id)
				 ->update('bill_xml.db_store', ['current_subscriptionlist_id' => $new_sub_id, 'status' => 1]);

        if ($this->db->trans_status() === FALSE)
        {
                $this->db->trans_rollback();
                return false;
        }
        else
        {
				// Sync to Superadmin Sales List
				$sales_id = $this->sync_subscription_to_sale($new_sub_id, 'Bank Transfer');
				if($sales_id){
					$this->db->where('id', $new_sub_id)->update('bill_xml.db_subscription', ['sales_id' => $sales_id]);
				}

                $this->db->trans_commit();
                return true;
        }
    }

	public function sync_subscription_to_sale($subscription_id, $payment_type = 'Cash') {
		$sub = $this->db->where('id', $subscription_id)->get('bill_xml.db_subscription')->row();
		if (!$sub) return false;

		// 1. Ensure Customer exists in Store 1
		$tenant_store = $this->db->where('id', $sub->store_id)->get('bill_xml.db_store')->row();
		if (!$tenant_store) return false;

		$customer = $this->db->where('store_id', 1)
							 ->where('customer_name', $tenant_store->store_name)
							 ->get('bill_xml.db_customers')->row();
		
		if (!$customer) {
			$cust_data = [
				'customer_name' => $tenant_store->store_name,
				'email'         => $tenant_store->email,
				'mobile'        => $tenant_store->mobile,
				'address'       => $tenant_store->address,
				'city'          => $tenant_store->city,
				'postcode'      => $tenant_store->postcode,
				'country_id'    => $tenant_store->country_id,
				'state_id'      => $tenant_store->state_id,
				'tax_number'    => $tenant_store->pan_no, 
				'gstin'         => $tenant_store->gst_no, 
				'status'        => 1,
				'store_id'      => 1,
				'created_date'  => date('Y-m-d'),
				'created_time'  => date('H:i:s'),
				'created_by'    => 'System'
			];
			$this->db->insert('bill_xml.db_customers', $cust_data);
			$customer_id = $this->db->insert_id();
			$this->db->where('id', $customer_id)->update('bill_xml.db_customers', ['customer_code' => 'CU'.str_pad($customer_id, 4, '0', STR_PAD_LEFT)]);
		} else {
			$customer_id = $customer->id;
		}

		// 2. Ensure Service Item exist in Store 1
		$package = $this->db->where('id', $sub->package_id)->get('bill_xml.db_package')->row();
		$item_name = "Subscription Package: " . ($package ? $package->package_name : 'Custom');

		$item = $this->db->where('store_id', 1)->where('item_name', $item_name)->get('bill_xml.db_items')->row();
		if ($item) {
			$item_id = $item->id;
		} else {
			$unit = $this->db->where('store_id', 1)->where('upper(unit_name)', 'PC')->get('bill_xml.db_units')->row();
			if (!$unit) {
				$this->db->insert('bill_xml.db_units', ['unit_name' => 'Pc', 'description' => 'Piece', 'store_id' => 1, 'status' => 1]);
				$unit_id = $this->db->insert_id();
			} else {
				$unit_id = $unit->id;
			}

			$item_data = [
				'item_name'     => $item_name,
				'sales_price'   => $sub->payment_gross,
				'purchase_price'=> 0,
				'stock'         => 999999, 
				'unit_id'       => $unit_id,
				'status'        => 1,
				'store_id'      => 1,
				'service_bit'   => 1,
				'created_date'  => date('Y-m-d'),
				'created_time'  => date('H:i:s'),
				'created_by'    => 'System'
			];
			$this->db->insert('bill_xml.db_items', $item_data);
			$item_id = $this->db->insert_id();
			$this->db->where('id', $item_id)->update('bill_xml.db_items', ['item_code' => 'IT'.str_pad($item_id, 4, '0', STR_PAD_LEFT)]);
		}

		// 3. Calculate VAT (Inclusive 7%)
		$total = $sub->payment_gross;
		$taxable_amount = round($total / 1.07, 2);
		$vat_amt = round($total - $taxable_amount, 2);

		$tax_row = $this->db->where('store_id', 1)->where('tax', 7)->get('bill_xml.db_tax')->row();
		$tax_id = $tax_row ? $tax_row->id : 0;

		// 4. Create Sale in Store 1
		$sales_count = $this->db->where('store_id', 1)->count_all_results('bill_xml.db_sales');
		$sales_code = 'SL'.str_pad($sales_count + 1, 4, '0', STR_PAD_LEFT);

		$sale_data = [
			'sales_code'    => $sales_code,
			'sales_date'    => date('Y-m-d'),
			'customer_id'   => $customer_id,
			'subtotal'      => $taxable_amount,
			'taxable_amount'=> $taxable_amount,
			'vat_amt'       => $vat_amt,
			'total_amount'  => $total,
			'grand_total'   => $total,
			'paid_amount'   => $total,
			'sales_status'  => 'Final',
			'payment_status'=> 'Paid',
			'reference_type'=> 'tax_receipt', 
			'store_id'      => 1,
			'created_date'  => date('Y-m-d'),
			'created_time'  => date('H:i:s'),
			'created_by'    => 'System',
			'system_ip'     => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
			'system_name'   => 'System'
		];
		$this->db->insert('bill_xml.db_sales', $sale_data);
		$sales_id = $this->db->insert_id();

		// 5. Create Sale Item
		$sale_item_data = [
			'sales_id'      => $sales_id,
			'item_id'       => $item_id,
			'sales_qty'     => 1,
			'price_per_unit'=> $taxable_amount,
			'tax_type'      => 'Inclusive',
			'tax_id'        => $tax_id,
			'tax_amt'       => $vat_amt,
			'unit_total_cost'=> $total,
			'total_cost'    => $total,
			'store_id'      => 1,
			'status'        => 1
		];
		$this->db->insert('bill_xml.db_salesitems', $sale_item_data);

		// 6. Create Sale Payment
		$payment_data = [
			'sales_id'      => $sales_id,
			'payment_date'  => date('Y-m-d'),
			'payment'       => $total,
			'payment_type'  => $payment_type,
			'store_id'      => 1,
			'status'        => 1,
			'created_date'  => date('Y-m-d'),
			'created_time'  => date('H:i:s'),
			'created_by'    => 'System'
		];
		$this->db->insert('bill_xml.db_salespayments', $payment_data);

		// 7. Update Subscription with sales_id
		$this->db->where('id', $subscription_id)->update('bill_xml.db_subscription', ['sales_id' => $sales_id]);

		// Check if Auto-Email is enabled
		$auto_email = $this->db->select('offline_auto_email')->get('bill_xml.db_sitesettings')->row()->offline_auto_email;

		// 8. Generate Receipt and Email (Only if enabled)
		if($auto_email == 1){
			$this->generate_and_email_receipt($sales_id, $tenant_store->email);
		} else {
			log_message('debug', "Auto-Email is PAUSED. Receipt generated but not sent for Sales ID: $sales_id");
		}

		return $sales_id;
	}

	public function generate_and_email_receipt($sales_id, $customer_email) {
		log_message('debug', "Starting PDF generation for Sales ID: $sales_id");
		$this->load->model('Receipt_model');
		$this->load->model('Email_model');

		$receipt = $this->Receipt_model->get_or_create_receipt($sales_id);
		if (!$receipt) {
			log_message('error', "Failed to create receipt record for Sales ID: $sales_id");
			return false;
		}

		$params = [
			'sales_id' => $sales_id
		];
		
		try {
			require_once APPPATH . 'libraries/TCPDF/invoice/GstInvoice.php';
			$pdf_lib = new GstInvoice($params);
			
			$file_name = 'Invoice_' . $sales_id . '.pdf';
			$temp_dir = FCPATH . 'uploads/temp/';
			if (!is_dir($temp_dir)) {
				mkdir($temp_dir, 0777, true);
			}
			$file_path = $temp_dir . $file_name;

			$pdf_content = $pdf_lib->show_pdf('S'); 
			file_put_contents($file_path, $pdf_content);
			
			log_message('debug', "PDF generated and saved at: $file_path");

			$subject = "Receipt/Tax Invoice for your Subscription - " . $receipt->receipt_code;
			$message = "Dear Customer,<br><br>Thank you for your payment. Please find attached your Receipt/Tax Invoice for your subscription (e-Tax format).<br><br>Best regards,<br>Superadmin Team";
			
			$email_content = [
				'to' => $customer_email,
				'subject' => $subject,
				'message' => $message,
				'attachment' => $file_path
			];

			$result = $this->Email_model->send_email($email_content);
			log_message('debug', "Email sent to $customer_email, Result: " . ($result ? 'Success' : 'Failed'));
			
			return $result;
		} catch (Exception $e) {
			log_message('error', "Error in generate_and_email_receipt: " . $e->getMessage());
			return false;
		}
	}
}
