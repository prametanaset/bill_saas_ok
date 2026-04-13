<?php
/**
 * Author: Askarali
 */
#[AllowDynamicProperties]
class MY_Controller extends CI_Controller{
      public $source_version = "3.2";
      public function __construct()
      {
        parent::__construct();

        //$this->output->enable_profiler(TRUE);

        //Before Login Update check
        if($this->uri->segment(1) == 'login' || empty($this->uri->segment(1))){
          $this->update_db();
        }

        //Used after logout
        if(!empty($this->input->cookie("language"))){
         $this->session->set_userdata('language',$this->input->cookie("language"));
         
         $cookie = array(
            'name'   => 'language',
              'value'  => '',
              'expire' => '0',
              );
          $this->input->set_cookie($cookie);
        }
        //end
        
        $default_lang = ($this->session->has_userdata('language')) ? $this->session->userdata('language') : "Thai";

        $this->lang->load($default_lang, $default_lang);
        
        // Dynamic Database Connection Switching for SaaS Tenants
        if ($this->session->has_userdata('logged_in') && $this->session->has_userdata('database_name')) {
            $tenant_db = $this->session->userdata('database_name');
            // Only switch if there's a valid DB name and user is not SuperAdmin (store_id = 1)
            if (!empty($tenant_db) && $this->session->userdata('store_id') != 1) {
                // Performance Benchmark for DB Switching
                $switch_start_time = microtime(true);
                
                // Switch the active database for the current user
                $this->db->query("USE `{$tenant_db}`");

                // Switch back to master database before PHP writes the session data
                // This is required because the session is saved in `bill_xml`.`ci_sessions`
                // and CI Query Builder will escape cross-database tables incorrectly.
                $db_ref = $this->db;
                register_shutdown_function(function() use ($db_ref) {
                    if(isset($db_ref) && $db_ref->conn_id) {
                        @$db_ref->query("USE `bill_xml` ");
                    }
                });
                
                $switch_end_time = microtime(true);
                $execution_time = ($switch_end_time - $switch_start_time) * 1000; // in milliseconds
                
                // Log performance metric
                log_message('debug', "Multi-Tenant: DB Switch to {$tenant_db} took " . number_format($execution_time, 2) . " ms");
            }
        }
      }
      public function load_info(){
        /*if(strtotime(date("d-m-Y")) >= strtotime(date("05-04-2019"))){
            echo "License Expired! Contact Admin";exit();
          }*/

            //CHECK LANGUAGE IN SESSION ELSE FROM DB
      if(!$this->session->has_userdata('language') && $this->session->has_userdata('logged_in') ){
        $this->load->model('language_model');
        $this->language_model->set(get_current_store_language());
      } else if(!$this->session->has_userdata('language')) {
        $this->load->model('language_model');
        $this->language_model->set(2); // 2 is Thai
      }

      if($this->session->has_userdata('language')){
        $this->lang->load($this->session->userdata('language'), $this->session->userdata('language'));
      }
      //End
            //End

            //If currency not set retrieve from DB
            $this->load_currency_data();
            //end

            

            $query =$this->db->select('site_name,version')->where('id',1)->get('db_sitesettings');

            $this->db->select('store_name,timezone,time_format,date_format,decimals,qty_decimals');
            if($this->session->userdata('logged_in')){
              $this->db->where('id',get_current_store_id());
            }
            else{
              $this->db->where('id',1);
            }
            $this->db->from('db_store');
            $query1 = $this->db->get();

            date_default_timezone_set(trim($query1->row()->timezone));

            $time_format = ($query1->row()->time_format=='24') ? date("H:i:s") : date("h:i:s a");

            $date_view_format = trim($query1->row()->date_format);
            $this->session->set_userdata(array('view_date'  => $date_view_format));
            $this->session->set_userdata(array('view_time'  => $query1->row()->time_format));
            $this->session->set_userdata(array('decimals'  => $query1->row()->decimals));
            $this->session->set_userdata(array('qty_decimals'  => $query1->row()->qty_decimals));
            $this->session->set_userdata(array('store_name'  => $query1->row()->store_name));
            

            $this->data = array('theme_link'    => base_url().'theme/',
                                'base_url'      => base_url(),
                                'SITE_TITLE'    => $query->row()->site_name,
                                'VERSION'       => $query->row()->version,
                                'CURRENCY'      => $this->session->userdata('currency'),
                                'CURRENCY_PLACE'=> $this->session->userdata('currency_placement'),
                                'CURRENCY_CODE' => $this->session->userdata('currency_code'),
                                'CUR_DATE'      => date("Y-m-d"),
                                'VIEW_DATE'     => $date_view_format,
                                'CUR_TIME'      => $time_format,
                                'SYSTEM_IP'     => $_SERVER['REMOTE_ADDR'],
                                'SYSTEM_NAME'   => gethostbyaddr($_SERVER['REMOTE_ADDR']),
                                'CUR_USERNAME'  => $this->session->userdata('inv_username'),
                                'CUR_USERID'    => $this->session->userdata('inv_userid'),
                                'is_read_only'  => $this->session->userdata('is_read_only'),
                                    );
      }
      public function load_currency_data(){
        if($this->session->userdata('logged_in')){
          $q1=$this->db->query("SELECT a.currency_name,a.currency,a.currency_code,a.symbol,b.currency_placement FROM db_currency a,db_store b WHERE a.id=b.currency_id AND b.id=".get_current_store_id());
              $currency = $q1->row()->currency;
              $currency_placement = $q1->row()->currency_placement;
              $currency_code = $q1->row()->currency_code;
              $this->session->set_userdata(array('currency'  => $currency,'currency_placement'  => $currency_placement,'currency_code'  => $currency_code));
        }
        else{
          $this->session->set_userdata(array('currency'  => '','currency_placement'  => '','currency_code'  => '')); 
        }
      }

      public function verify_store_and_user_status(){
            $store_rec = get_store_details();
            
            // AUTOMATIC DEACTIVATION CHECK (If expired and beyond grace period)
            if(store_module() && !is_admin() && $this->session->userdata('inv_userid') != 1 && $store_rec && $store_rec->status){
                $sub_id = $store_rec->current_subscriptionlist_id;
                if(!empty($sub_id)){
                    $this->db->select("a.expire_date, b.package_type");
                    $this->db->from("db_subscription a");
                    $this->db->join("db_package b", "b.id = a.package_id", "left");
                    $this->db->where("a.id", $sub_id);
                    $sub_q = $this->db->get();

                    if($sub_q->num_rows() > 0){
                        $expire_date = $sub_q->row()->expire_date;
                        $package_type = strtoupper($sub_q->row()->package_type);

                        if($expire_date < date("Y-m-d")){
                            $today = new DateTime();
                            $expiry = new DateTime($expire_date);
                            $diff = $expiry->diff($today);
                            $days_passed = $diff->days;
                            
                            $grace_limit = ($package_type == 'FREE') ? 30 : 60;

                            if($days_passed > $grace_limit){
                                // Beyond grace period -> Deactivate Store and Deny Access Immediately
                                $this->db->where('id', $store_rec->id)->update('db_store', array('status' => 0));
                                $this->session->set_flashdata('failed', 'แพ็กเกจของคุณหมดอายุและเลยกำหนดผ่อนผันแล้ว! บัญชีถูกระงับการใช้งานชั่วคราว กรุณาติดต่อผู้ดูแลระบบ.');
                                redirect('logout'); exit;
                            }
                        }
                    }
                }
            }

            //STORE ACTIVE OR NOT
            if(!$store_rec || !$store_rec->status){
              log_message('error', 'Redirecting to logout because store status is inactive. Store rec: ' . print_r($store_rec, true));
              $this->session->set_flashdata('failed', 'บัญชีของท่านถูกระงับการใช้งานชั่วคราว กรุณาติดต่อผู้ดูแลระบบ!');
              redirect('logout');exit;
            }
            //USER ACTIVE OR NOT
            $user_rec = get_user_details();
            if(!$user_rec || !$user_rec->status){
              log_message('error', 'Redirecting to logout because user status is inactive. User rec: ' . print_r($user_rec, true));
              $this->session->set_flashdata('failed', 'Your account is temporarily inactive!');
              redirect('logout');exit;
            }
      }
      public function load_global($validate_subs='VALIDATE'){
            //Check login or redirect to logout
            if($this->session->userdata('logged_in')!=1){ 
                log_message('error', 'Redirecting to logout because logged_in is not 1. Session: ' . print_r($this->session->userdata(), true));
                redirect(base_url().'logout','refresh');    
            }

            $this->verify_store_and_user_status();

            // CHECK SUBSCRIPTION EXPIRY
            if($this->session->userdata('is_expired') === true){
              $allowed_controllers = array('subscription', 'logout', 'login'); 
              $current_class = $this->router->fetch_class();
              if(!in_array(strtolower($current_class), $allowed_controllers)){
                 redirect('subscription/expired');
              }
            }

            // CHECK READ-ONLY MODE (GRACE PERIOD)
            if($this->session->userdata('is_read_only') === true){
              $current_class = strtolower($this->router->fetch_class());
              $current_method = strtolower($this->router->fetch_method());
              
              // Allowed controllers even in read-only mode
              $allowed_actions = array('subscription', 'logout', 'login', 'dashboard', 'users'); 
              
              // Allowed AJAX data-fetching methods (Read-only POST requests like DataTables lists)
              $allowed_methods = array(
                'ajax_list', 
                'get_datatables', 
                'get_districts', 
                'get_subdistricts',
                'view_payment_list',
                'view_payments_modal',
                'show_pay_now_modal',
                'get_sales_items_json',
                'get_sales_suggestions',
                'get_users_select_list',
                'return_quotation_list'
              );

              if(!in_array($current_class, $allowed_actions) && !in_array($current_method, $allowed_methods)){
                // Block all POST requests in read-only mode
                if($_SERVER['REQUEST_METHOD'] == 'POST'){
                  if($this->input->is_ajax_request()){
                    echo "คุณอยู่ในโหมดอ่านอย่างเดียวเนื่องจากแพ็กเกจหมดอายุ กรุณาต่ออายุเพื่อบันทึกข้อมูล.";
                    exit;
                  }
                  $this->session->set_flashdata('warning', 'คุณอยู่ในโหมดอ่านอย่างเดียวเนื่องจากแพ็กเกจหมดอายุ กรุณาต่ออายุเพื่อบันทึกข้อมูล.');
                  redirect($_SERVER['HTTP_REFERER'] ?? base_url());
                  exit;
                }
              }
            }

            $this->load_info();
      }

      public function currency($value='',$with_comma=false){
        $value = trim($value);

        if($value!=='' && is_numeric($value)){
          $value= ($with_comma) ? store_number_format($value) : store_number_format($value,false);
        }

        if($this->session->userdata('currency_placement')=='Left'){
          if(!empty($value)){
            return $this->session->userdata('currency')." ".$value;
          }
          return $this->session->userdata('currency')."".$value;
          
        }
        else{
          if(!empty($value)){
            return $value." ".$this->session->userdata('currency');    
          }
         return $value."".$this->session->userdata('currency'); 
        }
      }

      

      public function store_wise_currency($store_id,$value=''){

        $q1=$this->db->query("SELECT a.currency_name,a.currency,a.currency_code,a.symbol,b.currency_placement FROM db_currency a,db_store b WHERE a.id=b.currency_id AND b.id=".$store_id);
              $currency = $q1->row()->currency;
              $currency_placement = $q1->row()->currency_placement;
              $currency_code = $q1->row()->currency_code;

        $value = trim($value);
        if(!empty($value) && is_numeric($value)){
          $value=number_format($value,2,'.','');
        }
        if($currency_placement=='Left'){
          if(!empty($value)){
            return $currency." ".$value;
          }
          return $currency."".$value;
          
        }
        else{
          if(!empty($value)){
            return $value." ".$currency;    
          }
         return $value."".$currency; 
        }
      }
      
      public function currency_code($value=''){
        if(!empty($this->session->userdata('currency_code'))){
          if($this->session->userdata('currency_placement')=='Left'){
            return $this->session->userdata('currency_code')." ".$value;
          }
          else{
           return $value." ".$this->session->userdata('currency'); 
          }
        }
        else{
          return $value;
        }
      }
      public function permissions($permissions=''){
          //If he the Admin
          if($this->session->userdata('inv_userid')==1){
            return true;
          }

          $tot=$this->db->query('SELECT count(*) as tot FROM db_permissions where permissions="'.$permissions.'" and role_id='.$this->session->userdata('role_id'))->row()->tot;
          if($tot>=1){
            return true;
          }
           return false;
        }
        
        public function permission_check($value=''){
          if(!$this->permissions($value)){
             show_error("Access Denied", 403, $heading = "You Don't Have Enough Permission!!");
          }
          return true;
        }
        public function permission_check_with_msg($value=''){
          if(!$this->permissions($value)){
             echo "You Don't Have Enough Permission for this Operation!";
            exit();
          }
          return true;
        }
        public function show_access_denied_page()
        {
          show_error("Access Denied", 403, $heading = "You Don't Have Enough Permission!!");
        }
            //end
        public function get_current_version_of_db(){
          return $this->db->select('version')->from('db_sitesettings')->get()->row()->version;
        }
        
        public function belong_to($table,$rec_id){
          if(!is_it_belong_to_store($table,$rec_id)){
            show_error("Data may not avaialable!!", 403, $heading = "Something Went Wrong!!");
          }
        }

       public function update_db()
        { 
          //Before Login purpose only
          $this->load->model('updates_model');
          $this->updates_model->index();
        }

}