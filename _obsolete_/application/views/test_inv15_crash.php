<?php
// Mock Constants and Variables
$sales_id = 'INV-TEST-CRASH'; 
$base_url = 'http://localhost/bill_u/';

// Mock Functions
$instance = new CI_Mock();
function &get_instance() { global $instance; return $instance; }
function get_current_store_id() { return 1; }
function get_country($id) { return 'Thailand'; }
function get_state($id) { return 'Bangkok'; }
function show_date($date) { return $date; }
function get_customer_coupon_details($id) { return new stdClass(); }
function store_number_format($num) { return number_format((float)$num, 2); }
function format_qty($num) { return number_format((float)$num, 2); }
function gst_number() { return true; }
function pan_number() { return true; }
function get_quotation_details($id) { return new stdClass(); }
function get_account_name($id) { return 'Cash Account'; }
function nl2br_custom($str) { return nl2br($str ?? ''); }
function is_user() { return true; }
function is_admin() { return true; }
function special_access() { return true; }
function get_profile_picture() { return 'dist/img/avatar5.png'; }
function get_admin_name() { return 'Admin'; }
function get_role_name() { return 'Manager'; }
function get_lang() { return []; }
function get_currencies() { return []; }
function app_version() { return '1.0'; }
function get_db_select($t, $s, $w) { return new stdClass(); }
function currency_code() { return 'THB'; }
function base_url($uri = '') { return 'http://localhost/bill_u/' . $uri; }
function get_tax() { return []; }
function get_alert_count() { return 0; }
function store_demo_logo() { return 'logo.png'; }
function get_current_store_language() { return 'Thai'; }

// Mock CI Classes
class CI_Session_Mock {
    public function userdata($key) { return 'Thai'; }
}

class CI_Mock {
    public $lang;
    public $db;
    public $session;
    public $language; 
    public function __construct() {
        $this->lang = new CI_Lang_Mock();
        $this->db = new CI_DB_Mock();
        $this->session = new CI_Session_Mock();
        $this->language = 'Thai';
    }
    public function permissions($perm) { return true; }
    public function show_access_denied_page() { echo "Access Denied"; exit; }
}

class CI_Lang_Mock {
    public function line($key) { return $key; }
}

class CI_DB_Mock {
    public function query($sql) { return new CI_DB_Result_Mock($sql); }
    public function select($s) { return $this; }
    public function where($k, $v=null) { return $this; }
    public function from($t) { return $this; }
    public function join($t, $c, $type='') { return $this; }
    public function get($t=null) { return new CI_DB_Result_Mock(''); }
    public function get_where($t, $w) { return new CI_DB_Result_Mock(''); }
}

class CI_DB_Result_Mock {
    private $sql;
    public function __construct($sql) { $this->sql = $sql; }
    public function row() {
        $obj = new stdClass();
        // Basic dummy data
        $obj->id = 1;
        $obj->store_id = 1;
        $obj->sales_code = 'INV-TEST-CRASH';
        $obj->sales_date = '2026-01-26';
        $obj->created_time = '12:00:00';
        $obj->grand_total = 100;
        $obj->subtotal = 100;
        $obj->tot_discount_to_all_amt = 0;
        $obj->other_charges_amt = 0;
        
        // Item specific data for crash reproduction (0% tax)
        $obj->sales_qty = 1;
        $obj->total_cost = 100;
        $obj->tax = 0; // 0% Tax to trigger DivisionByZero
        $obj->tax_amt = 0;
        
        // ... fill other required fields to suppress warnings ...
        $obj->coupon_id = 0; $obj->due_date = ''; $obj->quotation_id = 0; $obj->customer_name = 'Cust';
        $obj->mobile = ''; $obj->phone = ''; $obj->gstin = ''; $obj->tax_number = ''; $obj->email = '';
        $obj->shippingaddress_id = 0; $obj->opening_balance = 0; $obj->country_id = 1; $obj->state_id = 1;
        $obj->city = 'City'; $obj->postcode = '123'; $obj->address = 'Addr';
        $obj->reference_no = ''; $obj->sales_status = ''; $obj->sales_note = ''; $obj->invoice_terms = ''; $obj->vat = 0;
        $obj->paid_amount = 0; $obj->other_charges_input = 0; $obj->other_charges_tax_id = 0;
        $obj->discount_to_all_input = 0; $obj->discount_to_all_type = ''; $obj->round_off = 0; $obj->payment_status = ''; $obj->pos = 0;
        
        $obj->item_name = 'Item 0% Tax'; $obj->description = ''; $obj->price_per_unit = 100; $obj->tax_name = 'VAT 0%';
        $obj->discount_input = 0; $obj->discount_amt = 0; $obj->unit_total_cost = 100; $obj->tax_type = 'Exclusive';
        $obj->sku = ''; $obj->hsn = ''; $obj->unit_name = 'pc'; $obj->mrp = 100;
        
        $obj->store_name = 'Store'; $obj->gst_no = ''; $obj->vat_no = ''; $obj->pan_no = '';
        $obj->country = 1; $obj->state = 1; $obj->sales_invoice_footer_text = '';
        $obj->name_in_thai = 'BKK'; $obj->code = 'BKK';
        $obj->company_country = 1; $obj->company_state = 1; $obj->company_city = 1;

        return $obj; 
    }
    public function result() { return [$this->row()]; }
    public function num_rows() { return 1; }
}

// Wrapper
class ViewTester extends CI_Mock {
    public function load_view() {
        $theme_link = 'dist/css/';
        $base_url = 'http://localhost/bill_u/';
        $sales_id = 'INV-TEST-CRASH';
        $page_title = 'Sales Invoice';
        $SITE_TITLE = 'Bill U';
        include 'sal_invoice_test_copy.php'; // Uses the modified view from previous step
    }
}

$tester = new ViewTester();
try {
    ob_start();
    $tester->load_view();
    $output = ob_get_clean();
    echo "SUCCESS: View Executed.\n";
} catch (Throwable $e) {
    echo "CRASH DETECTED: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n";
}
?>
