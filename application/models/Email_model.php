<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_model extends MY_Model {
	public function __construct(){
		parent::__construct();
		// Auto-update schema for ETAX tracking
		if (!$this->db->field_exists('template_id', 'db_email_logs')) {
			$this->db->query("ALTER TABLE `db_email_logs` ADD `template_id` INT(11) NULL AFTER `attachment` ");
		}
		if (!$this->db->field_exists('template_name', 'db_email_logs')) {
			$this->db->query("ALTER TABLE `db_email_logs` ADD `template_name` VARCHAR(100) NULL AFTER `template_id` ");
		}
	}

	public function xss_html_filter($input){
		return $this->security->xss_clean(html_escape($input));
	}
	//UPDATE SMS API
	public function api_update(){
		extract($this->xss_html_filter(array_merge($this->data,$_POST,$_GET)));
		//print_r($this->xss_html_filter(array_merge($this->data,$_POST,$_GET)));exit();
		$store_id = get_current_store_id();
		$this->db->trans_begin();
		if($hidden_rowcount>0){
		$this->db->query("delete from db_emailapi where store_id=".$store_id);
		$this->db->query("ALTER TABLE db_emailapi AUTO_INCREMENT = 1");
			for($i=1; $i<=$hidden_rowcount; $i++){
				if(isset($_POST['info_'.$i])){
					$info 	 	= $_POST['info_'.$i];
					$key 	 	= $_POST['key_'.$i];
					$key_value 	= $_POST['key_val_'.$i];
					
					$q1=$this->db->query("insert into db_emailapi(
								info,`key`,key_value,store_id)
								values(
								'$info',
								'$key',
								'$key_value',
								$store_id
							)");
					if(!$q1){
						return "failed";
					}

				}//if end()
			}//for end()	
		}

		$q2=$this->db->query("update db_store set email_status=$email_status where id=".$store_id);
		if(!$q2){
			return "failed";
		}

		//save Twilio SMS API
		$twilio = array('account_sid' => $account_sid,'auth_token'=>$auth_token,'twilio_phone'=>$twilio_phone );
		$q1=$this->db->select("*")->where("store_id",$store_id)->get("db_twilio");
        if($q1->num_rows()>0){
          $q2 = $this->db->where("store_id",$store_id)->update("db_twilio",$twilio);
        }
        else{
        	$twilio = array_merge($twilio,array('store_id' => $store_id));
        	$q2=$this->db->insert("db_twilio",$twilio);
        }

        if(!$q2){
        	return "failed";
        }

			$this->session->set_flashdata('success', 'Record Successfully Saved!!');
			$this->db->trans_commit();
		    return "success";
	}

	//Send email
	public function send_email(array $content, $template_id=null, $template_name=null){
		//Extract to individual variables and filter
		$to = (isset($content['to'])) ? $this->xss_html_filter($content['to']) : '';
		$cc = (isset($content['cc'])) ? $this->xss_html_filter($content['cc']) : '';
		$bcc = (isset($content['bcc'])) ? $this->xss_html_filter($content['bcc']) : '';
		$subject = (isset($content['subject'])) ? $this->xss_html_filter($content['subject']) : '';
		$message = (isset($content['message'])) ? $this->security->xss_clean($content['message']) : ''; // xss_clean only for message content
		$attachment = (isset($content['attachment'])) ? $content['attachment'] : ''; // Don't filter attachment path
		
		if(empty($message) && !empty($attachment)){
			$message = "Please find the attachment.";
		}

		//Validate
		if(empty($to)){
			return "You forgot to add Receipient Email Address";
		}
		if(empty($subject)){
			return "Email Subject cannot be empty";
		}

		//is SaaS module enabled ?
		$store_id = get_current_store_id();

		$q1=$this->db->select("*")->where("store_id",$store_id)->get("db_smtp");
		if($q1->num_rows()>0){
			$smtp_rec = $q1->row();
			$smtp_status = $smtp_rec->smtp_status;
		}else{
			$smtp_status = 0;
		}


		$from_email = get_store_details()->email;
		$from_name = get_store_details()->store_name;

		//If SMTP active
		if($smtp_status){
					$config = Array(
				  'protocol' => 'smtp',
				  'smtp_host' => $smtp_rec->smtp_host,
				  'smtp_port' => $smtp_rec->smtp_port,
				  'smtp_user' => $smtp_rec->smtp_user,
				  'smtp_pass' => $smtp_rec->smtp_pass,
				  'smtp_crypto' => $smtp_rec->smtp_encryption,
				  'crlf' => "\r\n",
				  'newline' => "\r\n",
				  'mailtype' => 'html'
				);

		    //Load email library
		    $this->load->library('email',$config);
		    $this->email->set_newline("\r\n");
		    
		    if($smtp_status){
		    	$from_email = (!empty($smtp_rec->from_email)) ? $smtp_rec->from_email : $smtp_rec->smtp_user;
		    	$from_name = (!empty($smtp_rec->from_name)) ? $smtp_rec->from_name : $this->session->userdata('store_name');
		    }

		    $this->email->from($from_email,$from_name);
		    $this->email->to($to);
		    
		    if(!empty($cc)){
		    	$this->email->cc($cc);
		    }
		    if(!empty($bcc)){
		    	$this->email->bcc($bcc);
		    }
		    

		    //Email content
		    $this->email->subject($subject);
		    $this->email->message($message);

		    //Attachment
		    if(!empty($attachment)){
		    	$this->email->attach($attachment);
		    }

		    //Send email
		    if($this->email->send()){
		        $this->log_email($content, 'Sent', '', $template_id, $template_name);
		        return true;
		    }
		    else{
		        $error = "Failed to send Email! Error: ".$this->email->print_debugger();
		        $this->log_email($content, 'Failed', $error, $template_id, $template_name);
		        return $error;
		    }
		}//If SMTP enabled
		else{
			//Send trough regular email method
			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			$headers .= 'From: <'.$from_email.'>' . "\r\n";

			if(!empty($cc)){
				$headers .= 'Cc: '.$cc . "\r\n";
			}
			if(!empty($bcc)){
				$headers .= 'Bcc: '.$bcc . "\r\n";
			}

			if(mail($to, $subject, $message, $headers)){
				$this->log_email($content, 'Sent', '', $template_id, $template_name);
				return true;
			}
			else{
				$error = "Failed to send Email!";
				$this->log_email($content, 'Failed', $error, $template_id, $template_name);
				return $error;
			}


		}

	}

	/**
	 * Send email using a specific store's SMTP settings directly (no session needed).
	 * Used for OTP / forgot password when user is NOT yet logged in.
	 *
	 * @param array  $content   Email content: to, subject, message
	 * @param int    $store_id  SMTP settings to use (default = 1 = Super Admin Gmail)
	 * @return true|string      true on success, error string on failure
	 */
	public function send_email_with_store_id(array $content, $store_id = 1, $template_id=null, $template_name=null){
		$to      = isset($content['to'])      ? $this->xss_html_filter($content['to'])      : '';
		$subject = isset($content['subject']) ? $this->xss_html_filter($content['subject']) : '';
		$message = isset($content['message']) ? $this->security->xss_clean($content['message']) : '';

		if(empty($to))      return 'ไม่ได้ระบุอีเมลผู้รับ';
		if(empty($subject)) return 'ไม่ได้ระบุหัวข้ออีเมล';

		// Query SMTP settings by store_id directly (not from session)
		$q1 = $this->db->select('*')->where('store_id', $store_id)->get('db_smtp');

		if($q1->num_rows() > 0){
			$smtp_rec    = $q1->row();
			$smtp_status = $smtp_rec->smtp_status;
		} else {
			$smtp_status = 0;
		}

		// Get from name/email from store record
		$store_rec  = get_store_details($store_id);
		$from_email = $store_rec ? $store_rec->email      : 'noreply@billsaas.local';
		$from_name  = $store_rec ? $store_rec->store_name : 'Bill SaaS';

		if($smtp_status){
			$config = array(
				'protocol'    => 'smtp',
				'smtp_host'   => $smtp_rec->smtp_host,
				'smtp_port'   => $smtp_rec->smtp_port,
				'smtp_user'   => $smtp_rec->smtp_user,
				'smtp_pass'   => $smtp_rec->smtp_pass,
				'smtp_crypto' => $smtp_rec->smtp_encryption,
				'crlf'        => "\r\n",
				'newline'     => "\r\n",
				'mailtype'    => 'html',
			);

			// Reset any previously loaded email library instance
			$this->load->library('email', $config);
			$this->email->initialize($config);
			$this->email->set_newline("\r\n");

			// Override from with SMTP-specific from settings if available
			if(!empty($smtp_rec->from_email)) $from_email = $smtp_rec->from_email;
			if(!empty($smtp_rec->from_name))  $from_name  = $smtp_rec->from_name;

			$this->email->from($from_email, $from_name);
			$this->email->to($to);
			$this->email->subject($subject);
			$this->email->message($message);

			if($this->email->send()){
				$this->log_email($content, 'Sent', '', $template_id, $template_name, $store_id);
				return true;
			} else {
				$error_msg = 'ส่งอีเมลล้มเหลว: ' . $this->email->print_debugger();
				$this->log_email($content, 'Failed', $error_msg, $template_id, $template_name, $store_id);
				return $error_msg;
			}
		} else {
			// Fallback: PHP mail()
			$headers  = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8\r\n";
			$headers .= 'From: ' . $from_name . ' <' . $from_email . '>' . "\r\n";

			if(mail($to, $subject, $message, $headers)){
				$this->log_email($content, 'Sent', '', $template_id, $template_name, $store_id);
				return true;
			} else {
				$error_msg = 'ไม่สามารถส่งอีเมลได้ (SMTP ไม่ได้เปิดใช้งาน กรุณาตั้งค่า SMTP ใน Email Settings)';
				$this->log_email($content, 'Failed', $error_msg, $template_id, $template_name, $store_id);
				return $error_msg;
			}
		}
	}


	public function log_email($content, $status, $error_msg='', $template_id=null, $template_name=null, $store_id=null){
		if(empty($store_id)){
			$store_id = get_current_store_id();
		}
		$data = array(
			'store_id' 		=> $store_id,
			'email_to' 		=> (isset($content['to'])) ? $content['to'] : '',
			'email_cc' 		=> (isset($content['cc'])) ? $content['cc'] : '',
			'email_bcc' 	=> (isset($content['bcc'])) ? $content['bcc'] : '',
			'subject' 		=> (isset($content['subject'])) ? $content['subject'] : '',
			'message' 		=> (isset($content['message'])) ? $content['message'] : '',
			'attachment' 	=> (!empty($content['attachment'])) ? basename($content['attachment']) : '',
			'template_id'   => $template_id,
			'template_name' => $template_name,
			'status' 		=> $status,
			'error_msg' 	=> $status == 'Failed' ? $error_msg : '',
			'created_date' 	=> date("Y-m-d"),
			'created_time' 	=> date("H:i:s"),
			'created_by' 	=> $this->session->userdata('inv_username'),
		);
		$table_name = ($store_id == 1) ? 'bill_xml.db_email_logs' : 'db_email_logs';
		return $this->db->insert($table_name, $data);
	}

	public function get_etax_usage($store_id) {
		try {
			// Get active subscription info from master DB
			$sql = "SELECT plan_type, subscription_date, expire_date
			        FROM bill_xml.db_subscription
			        WHERE store_id = ?
			          AND status = 'Active'
			        ORDER BY id DESC LIMIT 1";
			$q1 = $this->db->query($sql, [$store_id]);
			
			if($q1 && $q1->num_rows() > 0) {
				$sub = $q1->row();
				if(!empty($sub->plan_type) && $sub->plan_type == 'Annually'){
					$this->db->where('created_date >=', $sub->subscription_date);
					$this->db->where('created_date <=', $sub->expire_date);
				} else {
					$this->db->where('created_date >=', date("Y-m-01"));
				}
			} else {
				$this->db->where('created_date >=', date("Y-m-01"));
			}

			$this->db->where('store_id', $store_id);
			$this->db->where('status', 'Sent');
			$this->db->like('template_name', 'ETAX', 'both');
			return $this->db->count_all_results('db_email_logs');
		} catch (Exception $e) {
			log_message('error', 'get_etax_usage error: ' . $e->getMessage());
			return 0;
		}
	}

	public function get_package_limit($store_id) {
		try {
			// Query max_etax_emails from master db_package via db_subscription join
			$sql = "SELECT p.max_etax_emails
			        FROM bill_xml.db_subscription s
			        LEFT JOIN bill_xml.db_package p ON p.id = s.package_id
			        WHERE s.store_id = ?
			          AND s.status = 'Active'
			        ORDER BY s.id DESC LIMIT 1";
			$q = $this->db->query($sql, [$store_id]);
			if($q && $q->num_rows() > 0) {
				$val = $q->row()->max_etax_emails;
				if ($val !== null) return (int)$val;
			}
			return -1; // Unlimited if no active subscription found
		} catch (Exception $e) {
			log_message('error', 'get_package_limit error: ' . $e->getMessage());
			return -1;
		}
	}

}
