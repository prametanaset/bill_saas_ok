<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_templates_model extends MY_Model {

	var $table = 'db_emailtemplates';
	var $column_order = array('template_name','content','status'); //set column field database for datatable orderable
	var $column_search = array('template_name','content','status'); //set column field database for datatable searchable 
	var $order = array('id' => 'asc'); // default order 
	
	public function ensure_columns_exist() {
		$fields = $this->db->list_fields($this->table);
		if (!in_array('subject', $fields)) {
			$this->db->query("ALTER TABLE $this->table ADD COLUMN subject VARCHAR(255) NULL AFTER content");
		}
		if (!in_array('cc', $fields)) {
			$this->db->query("ALTER TABLE $this->table ADD COLUMN cc VARCHAR(255) NULL AFTER subject");
		}
		if (!in_array('to_email', $fields)) {
			$this->db->query("ALTER TABLE $this->table ADD COLUMN to_email VARCHAR(255) NULL AFTER cc");
		}

		// Seed E-TAX Template if not exists for this store
		$store_id = get_current_store_id();
		$this->db->where('template_name', 'ETAX - Invoice');
		$this->db->where('store_id', $store_id);
		$count = $this->db->count_all_results($this->table);
		if($count == 0) {
			$data = [
				'template_name' => 'ETAX - Invoice',
				'content' => 'เรียนลูกค้า <br><br>โปรดตรวจสอบ ใบกำกับภาษีอิเลคทรอนิคส์ ที่แนบมาพร้อมนี้<br><br> ขอบคุณค่ะ.',
				'subject' => '[[$sales_date]][[$doc_tag]][[$sales_code]][[$ref_code]]',
				'cc' => 'csemail@uat.etda.teda.th',
				'to_email' => '{{$customer_email}}',
				'status' => 1,
				'store_id' => $store_id
			];
			$this->db->insert($this->table, $data);
		}
	}

	private function _get_datatables_query()
	{
		
		$this->db->from($this->table);
		$this->db->where("store_id",get_current_store_id());
		if(is_admin()){
			$this->db->or_where("admin_only",1);
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


	public function verify_and_save(){
		//Filtering XSS and html escape from user inputs 
		extract($this->security->xss_clean(html_escape(array_merge($this->data,$_POST))));
		
		$store_id = get_current_store_id();

		//Validate This category already exist or not
		$this->db->where("upper(template_name)", strtoupper($template_name));
		$this->db->where("store_id", $store_id);
		$query = $this->db->get("db_emailtemplates");

		if($query->num_rows()>0){
			return "This Template Name already Exist.";
		}
		else{
			$data = array(
				'template_name' => $template_name,
				'content'       => $_POST['content'], // Use raw content to allow html from editor
				'subject'       => $subject,
				'cc'            => $cc,
				'to_email'      => $to_email,
				'status'        => 1,
				'store_id'      => $store_id
			);

			if ($this->db->insert("db_emailtemplates", $data)){
					$this->session->set_flashdata('success', 'Success!! New Template Added Successfully!');
			        return "success";
			}
			else{
			        return "failed";
			}
		}
	}

	//Get category_details
	public function get_details($id,$data){
		//Validate This category already exist or not
		$store_id = get_current_store_id();
		$query=$this->db->query("select * from db_emailtemplates where upper(id)=upper('$id') and store_id=".$store_id);
		if($query->num_rows()==0){
			show_404();exit;
		}
		else{
			$query=$query->row();
			$data['q_id']=$query->id;
			$data['template_name']=$query->template_name;
			$data['content']=$query->content;
			$data['subject']=$query->subject;
			$data['cc']=$query->cc;
			$data['to_email']=$query->to_email;
			$data['undelete_bit']=$query->undelete_bit;
			$data['variables']=$query->variables;
			return $data;
		}
	}
	public function update_template(){
		//Filtering XSS and html escape from user inputs 
		extract($this->security->xss_clean(html_escape(array_merge($this->data,$_POST))));
		$store_id = get_current_store_id();
		
		//Validate This category already exist or not
		$this->db->where("upper(template_name)", strtoupper($template_name));
		$this->db->where("id <>", $q_id);
		$this->db->where("store_id", $store_id);
		$query = $this->db->get("db_emailtemplates");

		if($query->num_rows()>0){
			return "This Template Name already Exist.";
		}
		else{
			$data = array(
				'template_name' => $template_name,
				'content'       => $_POST['content'], // Use raw content to allow html from editor
				'subject'       => $subject,
				'cc'            => $cc,
				'to_email'      => $to_email,
			);

			$this->db->where('id', $q_id);
			$this->db->where('store_id', $store_id);
			if ($this->db->update("db_emailtemplates", $data)){
					$this->session->set_flashdata('success', 'Success!! Template Updated Successfully!');
			        return "success";
			}
			else{
			        return "failed";
			}
		}
	}
	public function update_status($id,$status){
       if (set_status_of_table($id,$status,'db_emailtemplates')){
            echo "success";
        }
        else{
            echo "failed";
        }
	}
	public function delete_template($id){
		//FIND THIS CATEGORY ALREADY USED IN ITEMS OR NOT
		$store_id = get_current_store_id();
		$tot_count=$this->db->query("select count(*) as tot_count from db_emailtemplates where id=$id and undelete_bit=1 and store_id=".$store_id)->row()->tot_count;
		if($tot_count>0){
			echo "Sorry! Can't Delete.\nThis Template Restricted!";exit();
		}
        $query1="delete from db_emailtemplates where id=$id and store_id=".$store_id;
        if ($this->db->simple_query($query1)){
            echo "success";
        }
        else{
            echo "failed";
        }
	}

	public function get_active_templates(){
		$this->ensure_columns_exist();
		$this->db->select('id, template_name, content, subject, cc, to_email');
		$this->db->where('store_id', get_current_store_id());
		$this->db->where('status', 1);
		$query = $this->db->get($this->table);
		return $query->result();
	}
}
