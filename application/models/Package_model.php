<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Package_model extends MY_Model {

	//Datatable start
	var $table = 'bill_xml.db_package as a';
	var $column_order = array('a.id','a.package_type','a.package_name','a.monthly_price','a.annual_price','a.trial_days','a.max_warehouses','a.max_users','a.max_items','a.max_invoices','a.max_etax_emails','a.expire_date','a.status','a.store_id','a.plan_type'); //set column field database for datatable orderable
	var $column_search = array('a.id','a.package_type','a.package_name','a.monthly_price','a.annual_price','a.trial_days','a.max_warehouses','a.max_users','a.max_items','a.max_invoices','a.max_etax_emails','a.expire_date','a.status','a.store_id','a.plan_type'); //set column field database for datatable searchable 
	var $order = array('a.id' => 'desc'); // default order 

	public function __construct()
	{
		parent::__construct();
		// Auto-update schema for ETAX limits - Use explicit master DB prefix with safety check
		$query = $this->db->query("SHOW COLUMNS FROM bill_xml.db_package LIKE 'max_etax_emails'");
		if ($query && $query->num_rows() == 0) {
			$this->db->query("ALTER TABLE bill_xml.db_package ADD `max_etax_emails` INT(11) DEFAULT -1 AFTER `max_invoices` ");
		}
	}

	private function _get_datatables_query()
	{
    /*If account payble checked*/
		$this->db->select($this->column_order);
		$this->db->from($this->table);
		//if not admin
    //if(!is_admin()){
      $this->db->where("a.store_id",get_current_store_id());
    //}

    $package_type = $this->input->post('package_type');
		$plan_type = $this->input->post('plan_type');

		if(!empty($package_type)){
			$this->db->where('a.package_type',$package_type);
		}
		if(!empty($plan_type)){
			$this->db->where('a.plan_type',$plan_type);
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
    $this->db->where("store_id",get_current_store_id());
    $this->db->from($this->table);
    return $this->db->count_all_results();
  }
	//Datatable end

	//Save Customer
	public function save_and_update(){
		//Filtering XSS and html escape from user inputs 
		//extract($this->security->xss_clean(html_escape(array_merge($_POST,$_GET))));
		
        $package_type   = $this->input->post('package_type', true);
        $package_name   = $this->input->post('package_name', true);
        $description    = $this->input->post('description', true);
        $plan_type      = $this->input->post('plan_type', true);
        $monthly_price  = $this->input->post('monthly_price', true);
        $annual_price   = $this->input->post('annual_price', true);
        $trial_days     = $this->input->post('trial_days', true);
        $expire_date    = $this->input->post('expire_date', true);
        $max_warehouses = $this->input->post('max_warehouses', true);
        $max_users      = $this->input->post('max_users', true);
        $max_items      = $this->input->post('max_items', true);
        $max_invoices   = $this->input->post('max_invoices', true);
        $max_etax_emails= $this->input->post('max_etax_emails', true);
        $command        = $this->input->get('command', true); // passed via URL
        $q_id           = $this->input->post('q_id', true);

		$CUR_DATE = date("Y-m-d");
		$CUR_TIME = date("H:i:s");
		$CUR_USERNAME = $this->session->userdata('inv_username');
		$SYSTEM_IP = $_SERVER['REMOTE_ADDR'];
		$SYSTEM_NAME = gethostbyaddr($_SERVER['REMOTE_ADDR']);

        $expire_date = system_fromatted_date($expire_date);

        $store_id = get_current_store_id();
        
        // Ensure values are not null
        $monthly_price = (strtoupper($plan_type)==strtoupper("Monthly")) ? $monthly_price : 0;
        $annual_price = (strtoupper($plan_type)==strtoupper("Annually")) ? $annual_price : 0;
        
        $info = array(
                'store_id'            => $store_id, 
                'package_type'         => $package_type,
                'package_name'         => $package_name,
                'description'          => $description,
                'monthly_price'         => $monthly_price,
                'annual_price'             => $annual_price,
                'trial_days'           => $trial_days,
                'expire_date'         => $expire_date,
                'max_warehouses'        => $max_warehouses,
                'max_users'        => $max_users,
                'max_items'        => $max_items,
                'max_invoices'        => $max_invoices,
                'max_etax_emails'        => $max_etax_emails,
                'plan_type'        => ($package_type=='Free') ? null : $plan_type,
              );

		if($command=='save'){
            $info1 = array(
                        'created_date'        => $CUR_DATE,
                        'created_time'        => $CUR_TIME,
                        'created_by'        => $CUR_USERNAME,
                        'system_ip'         => $SYSTEM_IP,
                        'system_name'         => $SYSTEM_NAME,
                        'status'          => 1,
                    );

            //$this->db->query("ALTER TABLE db_package AUTO_INCREMENT = 1");
            $query1 = $this->db->insert('db_package', array_merge($info,$info1));
            if($query1){
               $this->session->set_flashdata('success', 'Success!! New Package Added Successfully!'); 
            } else {
               $this->session->set_flashdata('error', 'Failed! Database Error.'); 
            }
            
        }
        else{
            $query1 = $this->db->where('id',$q_id)->update('db_package', $info);
            $this->session->set_flashdata('success', 'Success!! Package Updated Successfully!');
        }
        
        $this->db->set("expire_date",null)->where("expire_date",'1970-01-01')->update("db_package");
        
		if(!$query1){
		  return "failed";
		}
		return "success";
		
	}

	//Get package_details
	public function get_details($id,$data){
		//Validate This package already exist or not
		$query=$this->db->query("select * from db_package where upper(id)=upper('$id')");
		if($query->num_rows()==0){
			show_404();exit;
		}
		else{
			$query=$query->row();
			$data['q_id']=$query->id;
      $data['store_id']=$query->store_id;
			$data['package_type']=$query->package_type;
      $data['package_name']=$query->package_name;
      $data['monthly_price']=$query->monthly_price;
      $data['annual_price']=$query->annual_price;
      $data['trial_days']=$query->trial_days;
      $expire_date = (date("d-m-Y",strtotime($query->expire_date))!='01-01-1970') ? show_date($query->expire_date) : '';
      $data['expire_date']=$expire_date;
      $data['description']=$query->description;
      $data['max_warehouses']=$query->max_warehouses;
      $data['max_users']=$query->max_users;
      $data['max_items']=$query->max_items;
      $data['max_invoices']=$query->max_invoices;
      $data['max_etax_emails']=$query->max_etax_emails;
      $data['plan_type']=$query->plan_type;
			
			return $data;
		}
	}

  public function update_status($id,$status){
       if (set_status_of_table($id,$status,'db_package')){
            echo "success";
        }
        else{
            echo "failed";
        }
  }
	
	public function delete_package_from_table($ids){

        // 1. Check Usage in db_subscription (Active Stores)
        $this->db->where_in('package_id', explode(',', $ids));
        // We check if ANY subscription exists (past or present) to maintain data integrity
        // Or should we only check active? The user said "package that stores are using".
        // Use count_all_results which respects previous where clause
        $used_count = $this->db->where_in('package_id', explode(',', $ids))->count_all_results('bill_xml.db_subscription');
        if($used_count > 0){
             return "Cannot Delete: Package is in use by Store(s)!";
        }
        
        // 2. Check Usage in db_offline_requests (Pending Requests)
        $req_count = $this->db->where_in('package_id', explode(',', $ids))->count_all_results('bill_xml.db_offline_requests');
        if($req_count > 0){
             return "Cannot Delete: Package has pending Offline Requests!";
        }

      $this->db->trans_begin();
          #---------------------------------
          $this->db->where("id in ($ids)");
          //if not admin
          if(!is_admin()){
            $this->db->where("store_id",get_current_store_id());
          }

          $query2=$this->db->delete("db_package");
          #---------------------------------

          if ($query2){
            $this->db->trans_commit();
              return "success";
          }
          else{
              return "failed";
          } 
	}
	public function get_package_list($pid='')
	{
		        if(!empty($pid)){
              $this->db->where("id",$pid);
            }
            $this->db->select("*");
            $this->db->from("db_package a");
            $this->db->order_by("id",'desc');
            $q3=$this->db->get();
            $package_list=array();
            $i=0;
            if($q3->num_rows() >0){
              foreach($q3->result() as $res3){
                  	++$i;
                  	$package_list['package_list'][$i]['package_type'] = $res3->package_type;
                    $package_list['package_list'][$i]['package_name'] = $res3->package_name;
                  	$package_list['package_list'][$i]['description'] = $res3->description;
                  	$package_list['package_list'][$i]['monthly_price'] = $res3->monthly_price;
                  	$package_list['package_list'][$i]['annual_price'] = $res3->annual_price;
                  	$package_list['package_list'][$i]['trial_days'] = $res3->trial_days;
                  	$package_list['package_list'][$i]['max_warehouses'] =  ($res3->max_warehouses=='-1') ? '∞' : $res3->max_warehouses;
                    $package_list['package_list'][$i]['max_users'] =  ($res3->max_users=='-1') ? '∞' : $res3->max_users;
                    $package_list['package_list'][$i]['max_items'] =  ($res3->max_items=='-1') ? '∞' : $res3->max_items;
                    $package_list['package_list'][$i]['max_invoices'] =  ($res3->max_invoices=='-1') ? '∞' : $res3->max_invoices;
                    $package_list['package_list'][$i]['max_etax_emails'] =  ($res3->max_etax_emails=='-1') ? '∞' : $res3->max_etax_emails;

                    //$package_list['package_list'][$i]['expire_date'] =  ($res3->expire_date=='-1') ? '∞' : $res3->expire_date;
                    $package_list['package_list'][$i]['expire_date'] =   $res3->expire_date;
                    
                    $package_list['package_list'][$i]['id'] = $res3->id;
                    $package_list['package_list'][$i]['plan_type'] = $res3->plan_type;
              }
            }
            $package_list['package_list']['tot_rec'] = $i;
            return $package_list;
	}

	public function ajax_package_list(){
            $plan_type = $this->input->post('plan_type');

            $CI =& get_instance();
            $this->db->select("*");
            $this->db->from("db_package a");

            $this->db->where('upper(plan_type) = upper("'.$plan_type.'") or upper(package_type) = upper("free")');
            

            $this->db->order_by("id",'asc');
           
            $q3=$this->db->get();
            $package_list=array();
            $i=0;
            if($q3->num_rows() >0){
              foreach($q3->result() as $res3){
                  	++$i;
                  	//$res3->package_type;
                   //$res3->package_name;
                  	// $res3->description;
                  	//$res3->monthly_price;
                  	//$res3->annual_price;
                  	//$package_list['package_list'][$i]['trial_days'] = $res3->trial_days;
                  	$max_warehouses =  ($res3->max_warehouses=='-1') ? '∞' : $res3->max_warehouses;
                    $max_users = ($res3->max_users=='-1') ? '∞' : $res3->max_users;
                    $max_items =  ($res3->max_items=='-1') ? '∞' : $res3->max_items;
                    $max_invoices =  ($res3->max_invoices=='-1') ? '∞' : $res3->max_invoices;
                    $max_etax_emails =  ($res3->max_etax_emails=='-1') ? '∞' : $res3->max_etax_emails;
                    //$package_list['package_list'][$i]['expire_date'] =  ($res3->expire_date=='-1') ? '∞' : $res3->expire_date;
                    $id = $res3->id;
                    //$plan_type = $res3->plan_type;



                    ?>
                    <div class="plan">
                          <div class="titleContainer">
                            <div class="title"><?= $res3->package_name ?></div>
                          </div>
                          <div class="infoContainer">

                            <?php if(strtoupper($res3->package_type) == strtoupper('free')){ ?>
                              <div class="price">
                              <p><?= $CI->currency($res3->monthly_price) ?> </p><span>/ <?= $res3->trial_days ?> <?= $this->lang->line('days'); ?></span>
                            </div>
                            <?php } elseif(strtoupper($plan_type) == strtoupper('monthly')){ ?>
                            <div class="price">
                              <p><?= $CI->currency($res3->monthly_price) ?> </p><span>/<?= $this->lang->line('month'); ?></span>
                            </div>
                          <?php } else{ ?>
                            <div class="price">
                              <p><?= $CI->currency($res3->annual_price) ?> </p><span>/<?= $this->lang->line('annual'); ?></span>
                            </div>
                          <?php } ?>

                            <div class="p desc"><em><?= $res3->description ?></em></div>
                            <ul class="features">
                              <li><strong><?= ($max_warehouses) ?></strong> <?= $this->lang->line('warehouses'); ?></li>
                              <li><strong><?= ($max_users) ?></strong> <?= $this->lang->line('users'); ?></li>
                              <li><strong><?= ($max_items) ?></strong> <?= $this->lang->line('items'); ?></li>
                              <li><strong><?= ($max_invoices) ?></strong> <?= $this->lang->line('invoices'); ?></li>
                              <li><strong><?= ($max_etax_emails) ?></strong> ETAX Emails/Month</li>
                              
                              
                            </ul>


                            <a class="selectPlan" href="<?=base_url('subscription/add/'.$id)?>">Select Plan</a>
                          </div>
                        </div>
                    <?php



              }
            }
            else{
            	?>
            	<div class="plan">
                    <div class="titleContainer">
                      <div class="title">No Packages</div>
                    </div>
                    <div class="infoContainer">
                      <div class="price">
                        
                      </div>
                      <div class="p desc"><em>Please contact Admin</em></div>
                      <a class="selectPlan">No Plan</a>
                    </div>
                  </div>
            	<?php
            }
           // $package_list['package_list']['tot_rec'] = $i;
           // return $package_list;
	}

}
