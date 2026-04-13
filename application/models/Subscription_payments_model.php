<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subscription_payments_model extends MY_Model {

	//Datatable start
	var $table = 'bill_xml.db_subscription as a';
	var $column_order = array(
								'a.id',
								'b.store_name',
								'a.subscription_name',
								'a.created_date',
                                'a.created_time', // Added Time
								'a.payment_type',
								'a.payment_gross',
								'a.payment_status',
								'a.store_id'
							); //set column field database for datatable orderable
	var $column_search = array(
								'a.id',
								'b.store_name',
								'a.subscription_name',
								'a.created_date',
                                'a.created_time', // Added Time
								'a.payment_type',
								'a.payment_gross',
								'a.payment_status',
								'a.store_id'
							); //set column field database for datatable searchable 
	var $order = array('a.id' => 'desc'); // default order 
    
    public function delete_payment_from_table($ids){
			$this->db->trans_begin();
			// Delete logic
			$query1=$this->db->where_in('id',$ids)->delete('bill_xml.db_subscription');
	        if ($query1){
	        	$this->db->trans_commit();
	            return "success";
	        }
	        else{
	            $this->db->trans_rollback();
	            return "failed";
	        }	
	} 

	public function __construct()
	{
		parent::__construct();
		if ($this->db->database == 'bill_xml') {
	        // Check if created_time column exists in db_subscription
	        if (!$this->db->field_exists('created_time', 'db_subscription')) {
	            $this->db->query("ALTER TABLE `db_subscription` ADD `created_time` TIME DEFAULT NULL AFTER `created_date`");
	        }
		}
	}

	private function _get_datatables_query()
	{
		$this->db->select('a.*, b.store_name');
		$this->db->from($this->table);
		$this->db->join('bill_xml.db_store as b', 'b.id = a.store_id', 'left');
		
		// Store Filter
		if(isset($_POST['store_id']) && $_POST['store_id'] != '') {
			$this->db->where('a.store_id', $_POST['store_id']);
		}

		// Date Filter Logic
		if(isset($_POST['from_date']) && $_POST['from_date'] != '' && isset($_POST['to_date']) && $_POST['to_date'] != '') {
			$from_date = system_fromatted_date($_POST['from_date']);
			$to_date = system_fromatted_date($_POST['to_date']);
			$this->db->where('a.created_date >=', $from_date);
			$this->db->where('a.created_date <=', $to_date);
		}

		$i = 0;
	
		foreach ($this->column_search as $item) // loop column 
		{
			if($_POST['search']['value']) // if datatable send POST for search
			{
				
				if($i===0) // first loop
				{
					$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
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
		$this->db->from($this->table);
		return $this->db->count_all_results();
	}

	// Calculate Total Payment
	public function get_total_payment()
	{
		$this->db->select_sum('payment_gross');
		$this->db->from($this->table);
		// Apply same filters if searching? usually totals are for the view.
		// If specific filters are needed, we can reuse _get_datatables_query logic but without limit/order.
		// For now, let's just return grand total or filtered total?
		// User asked for "Total summary at the end of table"
		// Usually this means sum of currently filtered records or all records.
		// Let's try to match the query filters.
		
		// We can reuse the query logic partially
		$this->db->join('bill_xml.db_store as b', 'b.id = a.store_id', 'left');
		
		$i = 0;
		foreach ($this->column_search as $item) // loop column 
		{
			if($_POST['search']['value']) // if datatable send POST for search
			{
				if($i===0) // first loop
				{
					$this->db->group_start(); 
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

		$query = $this->db->get();
		
		// The above gets all rows. We need to sum them. 
		// Actually, standard way is to use get_datatables query but with select sum.
		// Let's refactor to use _get_datatables_query but replace select/order/limit.
		
		// Simpler approach for now:
		return 0; // The controller handles specific logic or we fetch all and sum in PHP if pagination allows, but for server side processing we should run a separate sum query.
	}
	
	public function get_filtered_sum()
    {
        $this->_get_datatables_query();
        $query = $this->db->get();
        $result = $query->result();
        $sum = 0;
        foreach ($result as $row) {
            $sum += $row->payment_gross;
        }
        return $sum;
    }
    
    public function get_grand_total()
    {
        $this->db->select_sum('payment_gross');
        $this->db->from($this->table);
        $query = $this->db->get();
        return $query->row()->payment_gross;
    }

}
