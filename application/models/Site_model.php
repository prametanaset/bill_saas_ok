<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

class Site_model extends MY_Model {
    public function get_details(){
		$data=$this->data;

		//Ensure columns exist
		$this->check_and_add_columns();

		//Validate This suppliers already exist or not
		$query=$this->db->query("select * from db_sitesettings order by id asc limit 1");
		if($query->num_rows()==0){
			show_404();exit;
		}
		else{
			/* QUERY 1*/
			$query=$query->row();
			$data['q_id']=$query->id;
            $data['site_name']=$query->site_name;
            $data['logo']=$query->logo;
            
            // System Settings
            $data['timezone']=$query->timezone;
            $data['date_format']=$query->date_format;
            $data['time_format']=$query->time_format;
            $data['currency_id']=$query->currency_id;
            $data['currency_placement']=$query->currency_placement;
            $data['decimals']=$query->decimals;
            $data['qty_decimals']=$query->qty_decimals;
            $data['round_off']=$query->round_off;
            $data['language_id']=$query->language_id;

			return $data;
		}
	}

	public function check_and_add_columns(){
		if (!$this->db->field_exists('timezone', 'db_sitesettings')) {
			$this->db->query("ALTER TABLE `db_sitesettings` ADD `timezone` VARCHAR(100) NULL DEFAULT 'Asia/Bangkok';");
		}
		if (!$this->db->field_exists('date_format', 'db_sitesettings')) {
			$this->db->query("ALTER TABLE `db_sitesettings` ADD `date_format` VARCHAR(50) NULL DEFAULT 'dd-mm-yyyy';");
		}
		if (!$this->db->field_exists('time_format', 'db_sitesettings')) {
			$this->db->query("ALTER TABLE `db_sitesettings` ADD `time_format` VARCHAR(50) NULL DEFAULT '12';");
		}
		if (!$this->db->field_exists('currency_id', 'db_sitesettings')) {
			$this->db->query("ALTER TABLE `db_sitesettings` ADD `currency_id` INT(11) NULL DEFAULT 1;");
		}
		if (!$this->db->field_exists('currency_placement', 'db_sitesettings')) {
			$this->db->query("ALTER TABLE `db_sitesettings` ADD `currency_placement` VARCHAR(50) NULL DEFAULT 'Left';");
		}
		if (!$this->db->field_exists('decimals', 'db_sitesettings')) {
			$this->db->query("ALTER TABLE `db_sitesettings` ADD `decimals` INT(5) NULL DEFAULT 2;");
		}
		if (!$this->db->field_exists('qty_decimals', 'db_sitesettings')) {
			$this->db->query("ALTER TABLE `db_sitesettings` ADD `qty_decimals` INT(5) NULL DEFAULT 2;");
		}
		if (!$this->db->field_exists('round_off', 'db_sitesettings')) {
			$this->db->query("ALTER TABLE `db_sitesettings` ADD `round_off` INT(5) NULL DEFAULT 1;");
		}
		if (!$this->db->field_exists('language_id', 'db_sitesettings')) {
			$this->db->query("ALTER TABLE `db_sitesettings` ADD `language_id` INT(11) NULL DEFAULT 1;");
		}
	}
	public function update_site(){
		//Filtering XSS and html escape from user inputs 
		extract($this->security->xss_clean(html_escape(array_merge($this->data,$_POST))));
		//echo "<pre>";print_r($this->security->xss_clean(html_escape(array_merge($this->data,$_POST))));exit();
				
		
		$logo='';
		if(!empty($_FILES['logo']['name'])){
			$config['upload_path']          = './uploads/site/';
	        $config['allowed_types']        = 'gif|jpg|png';
	        $config['max_size']             = 500;
	        $config['max_width']            = 500;
	        $config['max_height']           = 500;

	        $this->load->library('upload', $config);

	        if ( ! $this->upload->do_upload('logo'))
	        {
	                $error = array('error' => $this->upload->display_errors());
	                print($error['error']);
	                exit();
	        }
	        else
	        {
	        	   $logo_name=$this->upload->data('file_name');
	        		$logo=" ,logo='/uploads/site/$logo_name' ";
	        }
		}
        
		$change_return = (isset($change_return)) ? 1 : 0;
		$round_off = (isset($round_off)) ? 1 : 0;
		
		$query1="update db_sitesettings set site_name='$site_name', timezone='$timezone', date_format='$date_format', time_format='$time_format', currency_id='$currency_id', currency_placement='$currency_placement', decimals='$decimals', qty_decimals='$qty_decimals', round_off='$round_off', language_id='$language_id' $logo where id=$q_id";
        $query1= $this->db->simple_query($query1);
      
		if ($query1){
		    return "success";
		}
		else{
		    return "failed";
		}
	}
}

/* End of file Site_model.php */
