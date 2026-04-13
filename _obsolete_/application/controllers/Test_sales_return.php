<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_sales_return extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->model('sales_return_model','sales');
	}
	public function index(){
        $sales_id_str = "INV-16"; 
        echo "Testing with string ID: $sales_id_str\n";
        // Convert to int to simulate what might be happening if logic is weird, OR pass as string
        // The model uses: where("sales_id=$sales_id")
        
        try {
            $result = $this->sales->sales_list($sales_id_str);
            echo "Result length with string: " . strlen($result) . "\n";
        } catch (Exception $e) {
            echo "Exception with string: " . $e->getMessage() . "\n";
        }
        
        $sales_id_int = 16;
        echo "Testing with int ID: $sales_id_int\n";
        $result = $this->sales->sales_list($sales_id_int);
        echo "Result length with int: " . strlen($result) . "\n";
	}
}
