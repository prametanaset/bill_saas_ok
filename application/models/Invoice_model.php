<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoice_model extends CI_Model {
	//Datatable start
	var $table = 'db_invoice as a';
	var $column_order = array( 
							'a.id',
							'a.invoice_date',
							'a.invoice_code',
							'b.sales_code',
							'c.customer_name',
							'b.grand_total',
							'a.created_by',
							); 
	var $column_search = array( 
							'a.id',
							'a.invoice_date',
							'a.invoice_code',
							'b.sales_code',
							'c.customer_name',
							'b.grand_total',
							'a.created_by',
							);
	var $order = array('a.id' => 'desc'); 

	public function __construct()
	{
		parent::__construct();
		$CI =& get_instance();
		
		// Create db_invoice table if not exists
		$this->db->query("CREATE TABLE IF NOT EXISTS `db_invoice` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `count_id` int(11) DEFAULT NULL,
		  `store_id` int(11) DEFAULT NULL,
		  `sales_id` int(11) DEFAULT NULL,
		  `invoice_code` varchar(100) DEFAULT NULL,
		  `invoice_date` date DEFAULT NULL,
		  `reference_no` varchar(100) DEFAULT NULL,
		  `created_date` date DEFAULT NULL,
		  `created_time` varchar(20) DEFAULT NULL,
		  `created_by` varchar(100) DEFAULT NULL,
		  `system_ip` varchar(100) DEFAULT NULL,
		  `system_name` varchar(100) DEFAULT NULL,
		  `status` int(1) DEFAULT '1',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // Ensure invoice_init exists in db_store
        if (!$this->db->field_exists('invoice_init', 'db_store')) {
            $this->db->query("ALTER TABLE `db_store` ADD `invoice_init` VARCHAR(100) DEFAULT 'IN'");
            $this->db->query("UPDATE `db_store` SET `invoice_init`='IN' WHERE `invoice_init` IS NULL OR `invoice_init`=''");
        }
	}

	public function get_or_create_invoice($sales_id, $invoice_date = null, $reference_no = null, $force_new = false) {
        $store_id = get_current_store_id();
        
        // If not forcing new, check if invoice already exists
        if(!$force_new){
            $this->db->where('sales_id', $sales_id);
            $this->db->where('store_id', $store_id);
            $this->db->where('status', 1);
            $query = $this->db->get('db_invoice');
            
            if($query->num_rows() > 0) {
                return $query->row();
            }
        } else {
            // Cancel old invoice if forcing new
            $this->db->where('sales_id', $sales_id);
            $this->db->where('store_id', $store_id);
            $this->db->where('status', 1);
            $this->db->update('db_invoice', array('status' => 0));
        }

        if ($invoice_date == null) {
            $invoice_date = date('Y-m-d');
        } else {
            $invoice_date = date('Y-m-d', strtotime($invoice_date));
        }

        // Generate IN-XXXX
        $invoice_code = get_init_code('invoice');
        
        $data = array(
            'store_id' => $store_id,
            'count_id' => get_count_id('db_invoice'),
            'sales_id' => $sales_id,
            'invoice_code' => $invoice_code,
            'invoice_date' => $invoice_date,
            'reference_no' => $reference_no,
            'created_date' => date('Y-m-d'),
            'created_time' => date('H:i:s'),
            'created_by' => $this->session->userdata('inv_username'),
            'system_ip' => $this->input->ip_address(),
            'system_name' => @gethostbyaddr($this->input->ip_address()),
            'status' => 1,
        );
        
        if($this->db->insert('db_invoice', $data)) {
            $invoice_id = $this->db->insert_id();
            return $this->db->where('id', $invoice_id)->get('db_invoice')->row();
        } else {
            return false;
        }
    }

	private function _get_datatables_query()
	{
		$this->db->select("a.*, b.sales_code as sales_ref, b.grand_total, c.customer_name");
		$this->db->from($this->table);
		$this->db->join('db_sales as b','b.id=a.sales_id','left');
		$this->db->join('db_customers as c','c.id=b.customer_id','left');
		
		$this->db->where("a.store_id",get_current_store_id());
		
	     $from_date = $this->input->post('from_date');
	     $from_date = system_fromatted_date($from_date);
	     $to_date = $this->input->post('to_date');
	     $to_date = system_fromatted_date($to_date);

	     if($from_date!='1970-01-01'){
	     	$this->db->where("a.invoice_date>=",$from_date);
	     }
	     if($to_date!='1970-01-01'){
	     	$this->db->where("a.invoice_date<=",$to_date);
	     }

		$i = 0;
	
		foreach ($this->column_search as $item) // loop column 
		{
			if($_POST['search']['value']) // if datatable send POST for search
			{
				if($i===0) // first loop
				{
					$this->db->group_start(); // open bracket
					$this->db->like($item, $_POST['search']['value']);
				}
				else
				{
					$this->db->or_like($item, $_POST['search']['value']);
				}

				if(count($this->column_search) - 1 == $i) //last loop
					$this->db->group_end(); //close bracket
			}
			$i++;
		}
		
		if(isset($_POST['order'])) // here order processing
		{
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} 
		else if(isset($this->order))
		{
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	function get_datatables()
	{
		$this->_get_datatables_query();
		if($_POST['length'] != -1)
		$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	function count_filtered()
	{
		$this->_get_datatables_query();
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function count_all()
	{
		$this->db->where("store_id",get_current_store_id());
		$this->db->from($this->table);
		return $this->db->count_all_results();
	}

	public function delete_invoice($ids){
		$this->db->trans_begin();
		$this->db->where("id in ($ids)");
		$this->db->where("store_id",get_current_store_id());
		$q1=$this->db->delete("db_invoice");

		if($q1){
			$this->db->trans_commit();
			return "success";
		}
		else{
			$this->db->trans_rollback();
			return "failed";
		}
	}
}
