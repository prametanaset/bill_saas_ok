<?php

class Dashboard_model extends MY_Model
{

	//Datatable start
		var $table = 'db_items a';
		var $column_order = array(
									'a.item_code',
									'a.item_name',
									'b.category_name',
									'c.brand_name',
									'a.stock'
									); //set column field database for datatable orderable
		var $column_search = array( 
									'a.item_code',
									'a.item_name',
									'b.category_name',
									'c.brand_name',
									'a.stock'
									); //set column field database for datatable searchable 
		var $order = array('a.id' => 'desc'); // default order 

		public function __construct(){
			parent::__construct();
			// Auto-add support columns if not exist
			if (!$this->db->field_exists('support_status', 'db_store')) {
				$this->db->query("ALTER TABLE `db_store` ADD `support_status` VARCHAR(50) DEFAULT 'Pending' AFTER `status`");
			}
			if (!$this->db->field_exists('support_note', 'db_store')) {
				$this->db->query("ALTER TABLE `db_store` ADD `support_note` TEXT AFTER `support_status`");
			}
		}

		public function get_store_support_list(){
			$this->db->select("a.*, b.subscription_name, b.payment_status, b.expire_date, b.trial_days, b.sales_id");
			// Add expire_status_weight: 0=Expired, 1=Expiring soon, 2=Active, 3=None
			$this->db->select("CASE 
                                WHEN b.expire_date IS NULL THEN 3
                                WHEN b.expire_date < CURDATE() THEN 0
                                WHEN b.expire_date <= DATE_ADD(CURDATE(), INTERVAL 10 DAY) THEN 1
                                ELSE 2
                            END as expire_status_weight", FALSE);
			$this->db->from("db_store a");
			$this->db->join("db_subscription b", "b.id = a.current_subscriptionlist_id", "left");
			$this->db->where("a.id !=", 1); // Exclude Super Admin Store
			$this->db->where("a.is_deleted", 0);
			// Primary sort by weight (problems first), then by ID (newest first)
			$this->db->order_by("expire_status_weight", "asc");
			$this->db->order_by("a.id", "desc");
			return $this->db->get()->result();
		}

		public function get_all_stores_list(){
			$this->db->select("a.id, a.store_code, a.store_name, a.mobile, a.address, a.created_date, a.created_by, a.status, c.package_name, b.expire_date");
			$this->db->from("db_store a");
			$this->db->join("db_subscription b", "b.id = a.current_subscriptionlist_id", "left");
			$this->db->join("db_package c", "c.id = b.package_id", "left");
			$this->db->where("a.id !=", 1); // Exclude Super Admin Store
			$this->db->where("a.is_deleted", 0);
			$this->db->order_by("a.id", "desc");
			return $this->db->get()->result();
		}

		public function update_support_status($store_id, $status, $note){
			$data = array(
				'support_status' => $status,
				'support_note' => $note
			);
			$this->db->where('id', $store_id);
			return $this->db->update('db_store', $data);
		} 

	
		
		private function _get_datatables_query()
		{	
			$this->db->select($this->column_order);
			$this->db->from($this->table);
			$this->db->where('a.store_id',get_current_store_id());
	        $this->db->where('(a.stock<=a.alert_qty or a.stock is null)');
	        $this->db->where('a.service_bit',0);
	        
	        $this->db->where('a.status=1');
	        $this->db->join('db_category b','b.id=a.category_id','left');
	        $this->db->join('db_brands c','c.id=a.brand_id','left');

	     

			//	echo $this->db->get_compiled_select();exit();
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
			$this->db->where("store_id",get_current_store_id());
			$this->db->from($this->table);
			return $this->db->count_all_results();
		}
		//Datatable end

	public function get_subscription_chart()
	{
		$sub_chart = array();
		for ($i=6; $i >= 0; $i--) {
			//Date
            $sub_chart['date'][$i] = date("Y-m-d",strtotime("-".$i." months"));
            $sub_chart['sub_year'][$i] = date("Y",strtotime($sub_chart['date'][$i]));
            $sub_chart['sub_month'][$i] = date("M",strtotime($sub_chart['date'][$i]));

            $this->db->select("count(*) as tot_subscribes");
            $this->db->from("db_subscription");
            $this->db->where("month(subscription_date)",date("m",strtotime($sub_chart['date'][$i])));
            $q3=$this->db->get();
            $sub_chart['tot_subscribes'][$i] = $q3->row()->tot_subscribes;
        }
        return $sub_chart;
	}

	public function get_pie_chart($value='')
	{
		
            $this->db->where("c.store_id",get_current_store_id());
            /*if(!is_admin() && !is_store_admin()){
              $this->db->where("c.created_by",$this->session->userdata('inv_username'));  
            }*/
            $this->db->select("COALESCE(SUM(b.sales_qty),0) AS sales_qty, a.item_name");
            $this->db->from("db_items AS a, db_salesitems AS b ,db_sales AS c");
            $this->db->where("a.id=b.`item_id` AND b.sales_id=c.`id` AND c.`sales_status`='Final'");
            $this->db->group_by("a.id");
            $this->db->limit("10");
            $this->db->order_by("sales_qty","asc");

            $q3=$this->db->get();
            $pie_chart=array();
            $i=0;
            if($q3->num_rows() >0){
              foreach($q3->result() as $res3){
                  if($res3->sales_qty>0){
                  	++$i;
                  	$pie_chart['tranding_item'][$i]['name'] = $res3->item_name;
                  	$pie_chart['tranding_item'][$i]['sales_qty'] = $res3->sales_qty;
                  }
              }
            }
            $pie_chart['tranding_item']['tot_rec'] = $i;
            return $pie_chart;
	}
	public function get_bar_chart(){
		$bar_chart=array();
          for ($i=11; $i >= 0; $i--) { 

              //Date
              $bar_chart['date'][$i] = date("Y-m-d",strtotime("-".$i." months"));
              $bar_chart['month'][$i] = date("M",strtotime($bar_chart['date'][$i])).",".date("Y",strtotime($bar_chart['date'][$i]));

              //Find purchase total
              $this->db->where("store_id",get_current_store_id());
              if(!is_admin() && !is_store_admin()){
                $this->db->where("created_by",$this->session->userdata('inv_username'));  
              }
              $this->db->select("COALESCE(SUM(grand_total),0) AS pur_total");
              $this->db->from("db_purchase");
              $this->db->where("purchase_status='Received'");
              $this->db->where("month(purchase_date)",date("m",strtotime($bar_chart['date'][$i])));
              $this->db->where("year(purchase_date)",date("Y",strtotime($bar_chart['date'][$i])));
              $q1=$this->db->get()->row();
              $this->db->get_compiled_select();
              $bar_chart['purchase'][$i]=$q1->pur_total;
              
              //Find sales total
              $this->db->where("store_id",get_current_store_id());
              if(!is_admin() && !is_store_admin()){
                $this->db->where("created_by",$this->session->userdata('inv_username'));  
              }
              $this->db->select("COALESCE(SUM(grand_total),0) AS sal_total");
              $this->db->from("db_sales");
              $this->db->where("sales_status='Final'");
              $this->db->where("month(sales_date)",date("m",strtotime($bar_chart['date'][$i])));
              $this->db->where("year(sales_date)",date("Y",strtotime($bar_chart['date'][$i])));
              $q1=$this->db->get()->row();
              $bar_chart['sales'][$i]=$q1->sal_total;

              //Find expense total
              $this->db->where("store_id",get_current_store_id());
              if(!is_admin() && !is_store_admin()){
                $this->db->where("created_by",$this->session->userdata('inv_username'));  
              }
              $this->db->select("COALESCE(SUM(expense_amt),0) AS expense_amt");
              $this->db->from("db_expense");
              $this->db->where("month(expense_date)",date("m",strtotime($bar_chart['date'][$i])));
              $this->db->where("year(expense_date)",date("Y",strtotime($bar_chart['date'][$i])));
              $q1=$this->db->get()->row();
              $bar_chart['expense'][$i]=$q1->expense_amt;
          }
          return $bar_chart;
	}
	public function get_by_date($table_date)
	{
		$dates = $this->input->post('dates');
		if($dates=='Today'){
      		//$this->db->where("$table_date > DATE_SUB(NOW(), INTERVAL 1 DAY)");
      		$this->db->where("$table_date",date("Y-m-d"));
      	}
      	if($dates=='Weekly'){
      		$this->db->where("$table_date > DATE_SUB(NOW(), INTERVAL 1 WEEK)");
      	}
      	if($dates=='Monthly'){
      		$this->db->where("$table_date > DATE_SUB(NOW(), INTERVAL 1 MONTH)");
      	}
      	if($dates=='Yearly'){
      		$this->db->where("$table_date > DATE_SUB(NOW(), INTERVAL 1 YEAR)");
      	}
	}
	public function breadboard_values()
	{	
		$dates = $this->input->post('dates');
		//$store_id=$this->input->post('store_id');
		$CI =& get_instance();
		$info=array();

		///Find total suppliers
		/*if(store_module() && is_admin()){if(!empty($store_id)){ 
					$this->db->where("store_id",$store_id);}
				}else{ */
					$this->db->where("store_id",get_current_store_id());	
			/*}*/
		$this->db->select("coalesce(count(*),0) as tot_sup");
		$this->db->from("db_suppliers");
		$this->db->where("status=1");
		
		$tot_sup=$this->db->get()->row()->tot_sup;	
		$info['tot_sup']=$tot_sup;

		///Find total Products
		/*if(store_module() && is_admin()){if(!empty($store_id)){ 
					$this->db->where("store_id",$store_id);}
				}else{ */
					$this->db->where("store_id",get_current_store_id());	
			/*}*/
		$this->db->select("coalesce(count(*),0) as tot_pro");
		$this->db->from("db_items");
		$this->db->where("status=1");
		$tot_pro=$this->db->get()->row()->tot_pro;	
		$info['tot_pro']=$tot_pro;

		//Total Customers
		/*if(store_module() && is_admin()){if(!empty($store_id)){ 
					$this->db->where("store_id",$store_id);}
				}else{ */
					$this->db->where("store_id",get_current_store_id());	
			/*}*/
		$this->db->select("coalesce(count(*),0) as tot_cust");
		$this->db->from("db_customers");
		$this->db->where("status=1");
		$tot_cust=$this->db->get()->row()->tot_cust;	
		$info['tot_cust']=$tot_cust;

  		//Total Purchases Active
  		/*if(store_module() && is_admin()){if(!empty($store_id)){ 
					$this->db->where("store_id",$store_id);}
				}else{ */
					$this->db->where("store_id",get_current_store_id());	
			/*}*/
		if(!is_admin() && !is_store_admin()){
			$this->db->where("created_by",$this->session->userdata('inv_username'));	
		}
      	$this->get_by_date('purchase_date');//DATES FUNCTION
		$this->db->select("coalesce(count(*),0) as tot_pur");
		$this->db->from("db_purchase");
		$this->db->where("purchase_status='Received'");
		//echo $this->db->get_compiled_select();exit();
		$tot_pur=$this->db->get()->row()->tot_pur;	
		$info['tot_pur']=$tot_pur;

		//Total Purchases Grand Total
		$this->db->where("store_id",get_current_store_id());	
		if(!is_admin() && !is_store_admin()){
			$this->db->where("created_by",$this->session->userdata('inv_username'));	
		}
		$this->get_by_date('purchase_date');//DATES FUNCTION
		$this->db->select("COALESCE(sum(grand_total),0) AS tot_pur_grand_total");
		$this->db->from("db_purchase");
		$this->db->where("purchase_status='Received'");
		$tot_pur_grand_total=$this->db->get()->row()->tot_pur_grand_total;	
		$info['tot_pur_grand_total']=$CI->currency($tot_pur_grand_total);

  		//Total SAles Active
  		/*if(store_module() && is_admin()){if(!empty($store_id)){ 
					$this->db->where("store_id",$store_id);}
				}else{ */
					$this->db->where("store_id",get_current_store_id());	
			/*}*/
		if(!is_admin() && !is_store_admin()){
			$this->db->where("created_by",$this->session->userdata('inv_username'));	
		}
		$this->get_by_date('sales_date');//DATES FUNCTION
		$this->db->select("coalesce(count(*),0) as tot_sal");
		$this->db->from("db_sales");
		$this->db->where("`sales_status`= 'Final'");
		$tot_sal=$this->db->get()->row()->tot_sal;
		$info['tot_sal']=$tot_sal;


		//Total SAles return amount
		/*if(store_module() && is_admin()){if(!empty($store_id)){ 
					$this->db->where("store_id",$store_id);}
				}else{ */
					$this->db->where("store_id",get_current_store_id());	
			/*}*/
		if(!is_admin() && !is_store_admin()){
			$this->db->where("created_by",$this->session->userdata('inv_username'));	
		}
		$this->get_by_date('return_date');//DATES FUNCTION
		$this->db->select("COALESCE(sum(grand_total),0) AS tot_sal_ret_grand_total");
		$this->db->from("db_salesreturn");
		$tot_sal_ret_grand_total=$this->db->get()->row()->tot_sal_ret_grand_total;
		$info['tot_sal_ret_grand_total']=$CI->currency($tot_sal_ret_grand_total);

		//Total SAles amount
		/*if(store_module() && is_admin()){if(!empty($store_id)){ 
					$this->db->where("store_id",$store_id);}
				}else{ */
					$this->db->where("store_id",get_current_store_id());	
			/*}*/
		if(!is_admin() && !is_store_admin()){
			$this->db->where("created_by",$this->session->userdata('inv_username'));	
		}
		$this->get_by_date('sales_date');//DATES FUNCTION
		$this->db->select("COALESCE(sum(grand_total),0) AS tot_sal_grand_total");
		$this->db->from("db_sales");
		$this->db->where("`sales_status`= 'Final'");
		$tot_sal_grand_total=$this->db->get()->row()->tot_sal_grand_total;
	//	$info['tot_sal_grand_total']=$CI->currency($tot_sal_grand_total-$tot_sal_ret_grand_total);
        $info['tot_sal_grand_total']=$CI->currency($tot_sal_grand_total);
		


		//Total expense amount
		/*if(store_module() && is_admin()){if(!empty($store_id)){ 
					$this->db->where("store_id",$store_id);}
				}else{ */
					$this->db->where("store_id",get_current_store_id());	
			/*}*/
		if(!is_admin() && !is_store_admin()){
			$this->db->where("created_by",$this->session->userdata('inv_username'));	
		}
		$this->get_by_date('expense_date');//DATES FUNCTION
		$this->db->select("COALESCE(sum(expense_amt),0) AS tot_exp");
		$this->db->from("db_expense");
		$tot_exp=$this->db->get()->row()->tot_exp;
		$info['tot_exp']=$CI->currency($tot_exp,2);

		//Total SAles Due
		/*if(store_module() && is_admin()){if(!empty($store_id)){ 
					$this->db->where("store_id",$store_id);}
				}else{ */
					$this->db->where("store_id",get_current_store_id());	
			/*}*/
		if(!is_admin() && !is_store_admin()){
			$this->db->where("created_by",$this->session->userdata('inv_username'));	
		}
		$this->get_by_date('sales_date');//DATES FUNCTION
		$this->db->select("(COALESCE(sum(grand_total),0)-COALESCE(sum(paid_amount),0)) as sales_due");
		$this->db->from("db_sales");
		$this->db->where("`sales_status`= 'Final'");
		$sales_due=$this->db->get()->row()->sales_due;
		$info['sales_due']=$CI->currency($sales_due);

		//Total Purchase  Due
		/*if(store_module() && is_admin()){if(!empty($store_id)){ 
					$this->db->where("store_id",$store_id);}
				}else{ */
					$this->db->where("store_id",get_current_store_id());	
			/*}*/
		if(!is_admin() && !is_store_admin()){
			$this->db->where("created_by",$this->session->userdata('inv_username'));	
		}
		$this->get_by_date('purchase_date');//DATES FUNCTION
		$this->db->select("(COALESCE(sum(grand_total),0)-COALESCE(sum(paid_amount),0)) as purchase_due");
		$this->db->from("db_purchase");
		$this->db->where("`purchase_status`= 'Received'");
		$purchase_due=$this->db->get()->row()->purchase_due;
		$info['purchase_due']=$CI->currency($purchase_due);


	
		$info['sales_pay']=$CI->currency(($tot_sal_grand_total-$tot_sal_ret_grand_total) - $sales_due );

		// ETAX Usage
		$this->load->model('email_model');
		$info['etax_usage'] = $this->email_model->get_etax_usage(get_current_store_id());
		$info['etax_limit'] = $this->email_model->get_package_limit(get_current_store_id());

		return $info;
	}

	public function get_monthly_paid_comparison_chart($year = '') {
		if(empty($year)){ $year = date('Y'); }
		$chart_data = array();
		$store_id = get_current_store_id();
		for ($i=1; $i <= 12; $i++) { 
			$month = sprintf("%02d", $i);
			$month_year = date("M", mktime(0, 0, 0, $i, 1, $year)) . ", " . $year;

			$chart_data['labels'][] = $month_year;

			// Sales Paid
			$this->db->select("COALESCE(SUM(paid_amount),0) AS total");
			$this->db->from("db_sales");
			$this->db->where("store_id", $store_id);
			$this->db->where("sales_status", 'Final');
			$this->db->where("MONTH(sales_date)", $month);
			$this->db->where("YEAR(sales_date)", $year);
			$chart_data['sales_paid'][] = $this->db->get()->row()->total;

			// Sales Debit Paid
			$this->db->select("COALESCE(SUM(paid_amount),0) AS total");
			$this->db->from("db_salesdebit");
			$this->db->where("store_id", $store_id);
			$this->db->where("MONTH(debit_date)", $month);
			$this->db->where("YEAR(debit_date)", $year);
			$chart_data['sales_debit_paid'][] = $this->db->get()->row()->total;

			// Purchase Paid
			$this->db->select("COALESCE(SUM(paid_amount),0) AS total");
			$this->db->from("db_purchase");
			$this->db->where("store_id", $store_id);
			$this->db->where("purchase_status", 'Received');
			$this->db->where("MONTH(purchase_date)", $month);
			$this->db->where("YEAR(purchase_date)", $year);
			$chart_data['purchase_paid'][] = $this->db->get()->row()->total;

			// Purchase Return Paid
			$this->db->select("COALESCE(SUM(paid_amount),0) AS total");
			$this->db->from("db_purchasereturn");
			$this->db->where("store_id", $store_id);
			$this->db->where("MONTH(return_date)", $month);
			$this->db->where("YEAR(return_date)", $year);
			$chart_data['purchase_return_paid'][] = $this->db->get()->row()->total;

			// Expense (Admin)
			$this->db->select("COALESCE(SUM(expense_amt),0) AS total");
			$this->db->from("db_expense");
			$this->db->where("store_id", $store_id);
			$this->db->where("MONTH(expense_date)", $month);
			$this->db->where("YEAR(expense_date)", $year);
			$chart_data['expense'][] = $this->db->get()->row()->total;
		}
		return $chart_data;
	}
}
