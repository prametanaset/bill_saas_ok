<?php
define('BASEPATH', 'TRUE');
include_once('index.php'); // Assuming index.php is in the same dir and bootstraps CI
// Actually simpler to just use CI instance if possible, but let's just use a controller

class Debug_Sub extends CI_Controller {
    public function index($store_id) {
        if(!is_admin()){ echo "Deny"; return; }
        
        $store = $this->db->where('id', $store_id)->get('bill_xml.db_store')->row();
        echo "Store ID: " . $store_id . "<br>";
        echo "Current Sub ID in db_store: " . $store->current_subscriptionlist_id . "<br>";
        
        $sub = $this->db->where('id', $store->current_subscriptionlist_id)->get('bill_xml.db_subscription')->row();
        echo "Sub Details:<pre>";
        print_r($sub);
        echo "</pre>";
        
        $all_subs = $this->db->where('store_id', $store_id)->order_by('id','desc')->get('bill_xml.db_subscription')->result();
        echo "All Subs for this store:<pre>";
        print_r($all_subs);
        echo "</pre>";
    }
}
