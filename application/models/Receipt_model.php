<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Receipt_model extends CI_Model {
	//Datatable start
	var $table = 'db_receipts as a';
	var $column_order = array( 
							'a.id',
							'a.receipt_date',
							'a.receipt_code',
							'b.sales_code',
							'a.reference_receipt',
							'c.customer_name',
							'b.grand_total',
							'a.created_by',
							); //set column field database for datatable orderable
	var $column_search = array( 
							'a.id',
							'a.receipt_date',
							'a.receipt_code',
							'b.sales_code',
							'a.reference_receipt',
							'c.customer_name',
							'b.grand_total',
							'a.created_by',
							);//set column field database for datatable searchable 
	var $order = array('a.id' => 'desc'); // default order  

	public function __construct()
	{
		parent::__construct();
		$CI =& get_instance();
		
		// Create db_receipts table if not exists
		// We use IF NOT EXISTS directly to bypass potential table_exists() metadata lag
		$this->db->query("CREATE TABLE IF NOT EXISTS `db_receipts` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `count_id` int(11) DEFAULT NULL,
		  `store_id` int(11) DEFAULT NULL,
		  `sales_id` int(11) DEFAULT NULL,
		  `receipt_code` varchar(100) DEFAULT NULL,
		  `receipt_date` date DEFAULT NULL,
		  `reference_no` varchar(100) DEFAULT NULL,
		  `reference_receipt` varchar(255) DEFAULT '',
		  `created_date` date DEFAULT NULL,
		  `created_time` varchar(20) DEFAULT NULL,
		  `created_by` varchar(100) DEFAULT NULL,
		  `system_ip` varchar(100) DEFAULT NULL,
		  `system_name` varchar(100) DEFAULT NULL,
		  `status` int(1) DEFAULT '1',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
		
		// Auto-add reference_receipt column if already exists but missing column
		if (!$this->db->field_exists('reference_receipt', 'db_receipts')) {
			$this->db->query("ALTER TABLE `db_receipts` ADD `reference_receipt` VARCHAR(255) DEFAULT '' AFTER `reference_no`");
		}

		// Ensure receipt_init exists in db_store
		if (!$this->db->field_exists('receipt_init', 'db_store')) {
			$this->db->query("ALTER TABLE `db_store` ADD `receipt_init` VARCHAR(100) DEFAULT 'RC'");
		}
		// Always ensure it's RC if it was previously R or empty
		$this->db->query("UPDATE `db_store` SET `receipt_init`='RC' WHERE `receipt_init` IS NULL OR `receipt_init`='' OR `receipt_init`='R'");
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
	     	$this->db->where("a.receipt_date>=",$from_date);
	     }
	     if($to_date!='1970-01-01'){
	     	$this->db->where("a.receipt_date<=",$to_date);
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
	//Datatable end

	public function delete_receipt($ids){
		$this->db->trans_begin();
		$this->db->where("id in ($ids)");
		$this->db->where("store_id",get_current_store_id());
		$q1=$this->db->delete("db_receipts");

		if($q1){
			$this->db->trans_commit();
			return "success";
		}
		else{
			$this->db->trans_rollback();
			return "failed";
		}
	}


    public function get_or_create_receipt($sales_id, $receipt_date = null, $reference_no = null, $reference_receipt = null, $force_new = false) {
        $store_id = get_current_store_id();
        
        // If not forcing new, check if receipt already exists
        if(!$force_new){
            $this->db->where('sales_id', $sales_id);
            $this->db->where('store_id', $store_id);
            $this->db->where('status', 1); // Only get active one
            $query = $this->db->get('db_receipts');
            
            if($query->num_rows() > 0) {
                // Already exists, return it
                return $query->row();
            }
        } else {
            // Forcing new: Find the latest active receipt to cancel it
            $this->db->where('sales_id', $sales_id);
            $this->db->where('store_id', $store_id);
            $this->db->where('status', 1);
            $old_q = $this->db->get('db_receipts');
            if($old_q->num_rows() > 0){
                $old_rec = $old_q->row();
                // Set old receipt as reference if not provided
                if(empty($reference_receipt)){
                    $reference_receipt = $old_rec->receipt_code;
                }
                // Cancel old receipt
                $this->db->where('id', $old_rec->id)->update('db_receipts', array('status' => 0));
            }
        }

        if ($receipt_date == null) {
            $receipt_date = date('Y-m-d');
        } else {
            $receipt_date = date('Y-m-d', strtotime($receipt_date));
        }

        // Generate RC-XXXX using existing standard function
        $receipt_code = get_init_code('receipt');
        
        $data = array(
            'store_id' => $store_id,
            'count_id' => get_count_id('db_receipts'),
            'sales_id' => $sales_id,
            'receipt_code' => $receipt_code,
            'receipt_date' => $receipt_date,
            'reference_no' => $reference_no,
            'reference_receipt' => $reference_receipt,
            /* System defaults fields */
            'created_date' => date('Y-m-d'),
            'created_time' => date('H:i:s'),
            'created_by' => $this->session->userdata('inv_username'),
            'system_ip' => $this->input->ip_address(),
            'system_name' => gethostbyaddr($this->input->ip_address()),
            'status' => 1,
        );
        
        if($this->db->insert('db_receipts', $data)) {
            $receipt_id = $this->db->insert_id();
            return $this->db->where('id', $receipt_id)->get('db_receipts')->row();
        } else {
            return false;
        }
    }
}
