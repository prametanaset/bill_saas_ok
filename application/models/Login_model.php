<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Author: Askarali Makanadar
 * Date: 05-11-2018
 */
class Login_model extends MY_Model
{
	
	function __construct()
	{
		parent::__construct();
	}

	public function verify_credentials($email, $password)
	{
		//Filtering XSS and html escape from user inputs
		$email    = $this->security->xss_clean(html_escape($email));
		$password = $this->security->xss_clean(html_escape($password));


		// Use Query Builder with parameter binding for SQL Injection protection
		$this->db->select("a.email, a.store_id, a.id, a.username, a.role_id, b.role_name, a.status, a.last_name, a.password as hashed_password");
		$this->db->from("db_users a");
		$this->db->join("db_roles b", "b.id = a.role_id");
		$this->db->group_start()
		         ->where("a.email", $email)
		         ->or_where("a.username", $email)
		         ->group_end();
		$query = $this->db->get();

		if($query->num_rows() == 1){
			$user = $query->row();
			$is_authenticated = false;

			// Verify Password (BCRYPT is preferred, fallback to MD5 for legacy users)
			if (password_verify($password, $user->hashed_password)) {
				$is_authenticated = true;
			}
			// Legacy MD5 Support & Auto-Upgrade
			elseif (md5($password) === $user->hashed_password) {
				$is_authenticated = true;
				// Auto-Upgrade to BCRYPT
				$new_hash = password_hash($password, PASSWORD_BCRYPT);
				$this->db->where('id', $user->id)->update('db_users', ['password' => $new_hash]);
			}

			if($is_authenticated){
				// Verify is SaaS module Active ?
				if($user->id == 1){
					if(!store_module()){
						$this->session->set_flashdata('failed', 'ระบบไม่อนุญาตให้คุณใช้งาน!! ใบอนุญาต (License) หมดอายุ');
						redirect('login');exit;
					}
				}

				$store_rec = get_store_details($user->store_id);
				//STORE ACTIVE OR NOT
				if(!$store_rec->status && $user->id != 1){
					// Check if store has a pending offline payment request
					$pending = $this->db->where('store_id', $user->store_id)->where('status', 0)->get('bill_xml.db_offline_requests')->row();
					if($pending){
						$this->session->set_flashdata('pending', true);
					} else {
						$this->session->set_flashdata('failed', 'ร้านค้าของคุณถูกระงับการใช้งานชั่วคราว!');
					}
					redirect('login');exit;
				}

				//CHECK SUBSCRIPTION STATUS
				if(store_module() && !is_admin() && $user->id != 1){
					$sub_id = $store_rec->current_subscriptionlist_id;
					if(!empty($sub_id)){
						$this->db->select("a.expire_date, b.package_type");
						$this->db->from("db_subscription a");
						$this->db->join("db_package b", "b.id = a.package_id", "left");
						$this->db->where("a.id", $sub_id);
						$sub_q = $this->db->get();

						if($sub_q->num_rows() > 0){
							$expire_date  = $sub_q->row()->expire_date;
							$package_type = strtoupper($sub_q->row()->package_type);

							if($expire_date < date("Y-m-d")){
								$today       = new DateTime();
								$expiry      = new DateTime($expire_date);
								$diff        = $expiry->diff($today);
								$days_passed = $diff->days;

								$grace_limit = ($package_type == 'FREE') ? 30 : 60;

								if($days_passed <= $grace_limit){
									$days_left = $grace_limit - $days_passed;
									$this->session->set_flashdata('warning', "แพ็กเกจ {$package_type} ของคุณหมดอายุแล้วเมื่อ " . show_date($expire_date) . "! สามารถใช้งานต่อได้ (อ่านอย่างเดียว) อีก {$days_left} วัน กรุณาต่ออายุ.");

									$logdata = array(
										'inv_username'  => $user->username,
										'user_lname'    => $user->last_name,
										'inv_userid'    => $user->id,
										'logged_in'     => TRUE,
										'role_id'       => $user->role_id,
										'role_name'     => trim($user->role_name),
										'store_id'      => trim($user->store_id),
										'email'         => trim($user->email),
										'is_read_only'  => true,
										'database_name' => trim($store_rec->database_name),
									);
									$this->session->set_userdata($logdata);
									redirect(base_url().'dashboard');exit;
								} else {
									// Beyond grace period -> Deactivate Store and Deny Access
									$this->db->where('id', $user->store_id)->update('db_store', array('status' => 0));
									$this->session->set_flashdata('failed', 'แพ็กเกจของคุณหมดอายุและเลยกำหนดผ่อนผันแล้ว! บัญชีถูกระงับการใช้งานชั่วคราว กรุณาติดต่อผู้ดูแลระบบ.');
									redirect('login'); exit;
								}
							}
						}
					}
				}

				//USER ACTIVE OR NOT
				if(!$user->status && $user->id != 1){
					$this->session->set_flashdata('failed', 'บัญชีของคุณถูกปิดใช้งานชั่วคราว!');
					redirect('login');exit;
				}

				$logdata = array(
					'inv_username'  => $user->username,
					'user_lname'    => $user->last_name,
					'inv_userid'    => $user->id,
					'logged_in'     => TRUE,
					'role_id'       => $user->role_id,
					'role_name'     => trim($user->role_name),
					'store_id'      => trim($user->store_id),
					'email'         => trim($user->email),
					'database_name' => trim($store_rec->database_name),
				);
				$this->session->set_userdata($logdata);
				//$this->session->set_flashdata('success', 'Welcome ' . ucfirst($user->username) . ' !');
				redirect(base_url().'dashboard');
			}
		}

		$this->session->set_flashdata('failed', 'อีเมลหรือรหัสผ่านไม่ถูกต้อง!');
		redirect('login');
	}
	public function verify_email_send_otp($email)
	{
		//Filtering XSS and html escape from user inputs
		$to = $this->security->xss_clean(html_escape($email));

		$query = $this->db->where('email', $to)->where('status', 1)->get('db_users');

		if($query->num_rows() == 0){
			$this->session->set_flashdata('failed', 'อีเมลนี้ไม่ได้ลงทะเบียนไว้ในระบบของเรา!');
			return false;
		}

		$otp = rand(100000, 999999); // 6-digit OTP

		$subject = 'รหัส OTP สำหรับการเปลี่ยนรหัสผ่าน | Bill SaaS';
		$message = '
<html>
<body style="font-family:Arial,sans-serif; max-width:500px; margin:auto; border:1px solid #ddd; padding:20px; border-radius:8px;">
  <h3 style="color:#337ab7;">🔐 รหัส OTP สำหรับการเปลี่ยนรหัสผ่าน</h3>
  <p>สวัสดี,</p>
  <p>คุณได้ขอรีเซ็ตรหัสผ่านในระบบ <b>Bill SaaS</b><br>กรุณาใส่รหัส OTP ด้านล่างในหน้าเว็บ:</p>
  <div style="text-align:center; margin:20px 0; padding:15px; background:#f4f8ff; border-radius:6px; border:1px dashed #337ab7;">
    <h1 style="letter-spacing:12px; color:#337ab7; margin:0;">' . $otp . '</h1>
  </div>
  <p style="color:red;"><b>⚠ ห้ามแชร์รหัสนี้กับผู้อื่น!</b></p>
  <p style="color:#777; font-size:12px;">รหัสนี้จะหมดอายุทันทีที่ใช้งาน<br>หากคุณไม่ได้ทำรายการนี้ กรุณาเพิกเฉยต่ออีเมลฉบับนี้</p>
  <hr>
  <small style="color:#999;">Bill SaaS System — ส่งโดยอัตโนมัติ กรุณาอย่าตอบกลับอีเมลนี้</small>
</body>
</html>';

		// ใช้ SMTP ของ Super Admin (store_id=1) ที่มี Gmail App Password ตั้งค่าไว้
		// เพราะ user ยังไม่ได้ login จึงไม่มี session store_id
		$this->load->model('email_model');
		$response = $this->email_model->send_email_with_store_id(
			array('to' => $to, 'subject' => $subject, 'message' => $message),
			1 // store_id = 1 = Super Admin (Gmail SMTP)
		);

		if($response === true){
			$this->session->set_flashdata('success', 'รหัส OTP ได้ถูกส่งไปยังอีเมล ' . $to . ' เรียบร้อยแล้ว! (ให้เช็กที่กล่องจดหมายเข้า หรือ กล่องจดหมายขยะ)');
		$otpdata = array(
			'email'          => $to,
			'otp'            => $otp,
			'otp_expires_at' => time() + (10 * 60), // OTP valid for 10 minutes
		);
		$this->session->set_userdata($otpdata);
		return true;
		}
		else{
			// $response คือข้อความ error จาก SMTP
			$this->session->set_flashdata('failed', 'ไม่สามารถส่งอีเมลได้: ' . $response);
			return false;
		}
	}
	public function verify_otp($otp)
	{
		//Filtering XSS and html escape from user inputs 
		$otp=$this->security->xss_clean(html_escape($otp));
		$email=$this->security->xss_clean(html_escape($email));
		if($this->session->userdata('email')==$email){ redirect(base_url().'logout','refresh');	}
				
		$query=$this->db->query("select * from db_users where username='$username' and password='".md5($password)."' and status=1");
		if($query->num_rows()==1){

			$logdata = array(
							'inv_username'  => $query->row()->username,
							'user_lname'  => $query->row()->last_name,
				        	 'inv_userid'  => $query->row()->id,
				        	 'logged_in' => TRUE,
				        	 'role_id' => $query->row()->role_id,
				        	 'role_name' => trim($query->row()->role_name),
				        	 'store_id' => trim($query->row()->store_id),
				        	);
			$this->session->set_userdata($logdata);
			return true;
		}
		else{
			return false;
		}		
	}
	public function change_password($password, $email){
		$email = $this->security->xss_clean(html_escape($email));
		$query = $this->db->where('email', $email)->where('status', 1)->get('db_users');
		if($query->num_rows() == 1){
			$hashed = password_hash($password, PASSWORD_BCRYPT);
			$this->db->where('email', $email)->update('db_users', ['password' => $hashed]);
			return true;
		}
		else{
			return false;
		}
	}
}
