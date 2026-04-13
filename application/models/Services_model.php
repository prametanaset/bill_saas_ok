<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Services_model extends MY_Model {

	//Save Cutomers
	public function verify_and_save(){
		//Filtering XSS and html escape from user inputs 
		//extract($this->security->xss_clean(html_escape(array_merge($this->data,$_POST))));

		$item_code 		= $this->input->post('item_code') ? $this->input->post('item_code') : get_init_code('item');
		$item_name 		= $this->input->post('item_name');
		$category_id 	= $this->input->post('category_id');
		$brand_id 		= $this->input->post('brand_id');
		$price 			= $this->input->post('price') ? $this->input->post('price') : 0;
		$tax_id 		= $this->input->post('tax_id');
		$purchase_price = $this->input->post('purchase_price') ? $this->input->post('purchase_price') : 0;
		$tax_type 		= $this->input->post('tax_type');
		$sales_price 	= $this->input->post('sales_price') ? $this->input->post('sales_price') : 0;
		$seller_points 	= $this->input->post('seller_points') ? $this->input->post('seller_points') : 0;
		$custom_barcode = $this->input->post('custom_barcode');
		$description 	= $this->input->post('description');
		$hsn 			= $this->input->post('hsn');
		$sac 			= $this->input->post('sac');
		$discount_type 	= $this->input->post('discount_type');
		$discount 		= $this->input->post('discount') ? $this->input->post('discount') : 0;
		$store_id 		= $this->input->post('store_id');
		$hide_in_pos 	= $this->input->post('hide_in_pos') ? $this->input->post('hide_in_pos') : 0;

		// Defaults for missing service fields
		$sku 			= '';
		$unit_id 		= $this->input->post('unit_id');
		$alert_qty 		= 0;
		$profit_margin 	= 0;
		$item_group 	= 'Single';

		$this->db->trans_begin();
		$this->db->trans_strict(TRUE);

		$file_name='';
		if(!empty($_FILES['item_image']['name'])){

			$new_name = time();
			$config['file_name'] = $new_name;
			$config['upload_path']          = './uploads/items/';
	        $config['allowed_types']        = 'jpg|png|jpeg';
	        $config['max_size']             = 1024;
	        $config['max_width']            = 1500;
	        $config['max_height']           = 1500;
	       
	        $this->load->library('upload', $config);

	        if ( ! $this->upload->do_upload('item_image'))
	        {	
	                $error = array('error' => $this->upload->display_errors());
	                print($error['error']);
	                exit();
	        }
	        else
	        {		
	        	$file_name=$this->upload->data('file_name');
	        	/*Create Thumbnail*/
	        	$config['image_library'] = 'gd2';
				$config['source_image'] = 'uploads/items/'.$file_name;
				$config['create_thumb'] = TRUE;
				$config['maintain_ratio'] = TRUE;
				$config['width']         = 75;
				$config['height']       = 50;
				$this->load->library('image_lib', $config);
				$this->image_lib->resize();
				//end
	        }
		}
		
		//Validate This items already exist or not
		//$store_id=(store_module() && is_admin()) ? ($store_id ?? get_current_store_id()) : get_current_store_id();
		$store_id = (store_module() && is_admin()) ? ($store_id ? $store_id : get_current_store_id()) : get_current_store_id();
		
		//Create items unique Number
		$this->db->query("ALTER TABLE db_items AUTO_INCREMENT = 1");
		//end
		
		#------------------------------------
		$info = array(
							'count_id' 					=> get_count_id('db_items'), 
		    				'item_code' 				=> $item_code, 
		    				'item_name' 				=> $item_name,
		    				'category_id' 				=> $category_id,
		    				'brand_id' 					=> $brand_id,
		    				'price' 					=> $price,
		    				'tax_id' 					=> $tax_id,
		    				'purchase_price' 			=> $purchase_price,
		    				'tax_type' 					=> $tax_type,
		    				'sales_price' 				=> $sales_price,
		    				/*System Info*/
		    				'created_date' 				=> date("Y-m-d"),
		    				'created_time' 				=> date("H:i:s"),
		    				'created_by' 				=> $this->session->userdata('inv_username'),
		    				'system_ip' 				=> $_SERVER['REMOTE_ADDR'],
		    				'system_name' 				=> gethostbyaddr($_SERVER['REMOTE_ADDR']),
		    				'status' 					=> 1,
		    				'service_bit' 				=> 1,
		    				'seller_points'				=> $seller_points,
		    				'custom_barcode'			=> $custom_barcode,
		    				'description'				=> $description,
		    				'hsn'						=> $hsn,
		    				'sac'						=> $sac,
		    				'discount_type'				=> $discount_type,
		    				'discount'					=> $discount,
		    				'hide_in_pos'				=> $hide_in_pos,
		    				/* Missing fields in services but present in items */
		    				'sku'						=> $sku,
		    				'unit_id'					=> $unit_id,
		    				'alert_qty'					=> $alert_qty,
		    				'profit_margin'				=> $profit_margin,
		    				'item_group'				=> $item_group,
		    				'expire_date'				=> null,

		    			);
		if(!empty($file_name)){
			$info['item_image'] = 'uploads/items/'.$file_name;
		}

		$info['store_id'] = $store_id;

		$query1 = $this->db->insert('db_items', $info);
		#------------------------------------
		if(!$query1){
			$error = $this->db->error();
			return "Failed to save service. Error: " . $error['message'];
		}
		
		$item_id = $this->db->insert_id();
		

		if ($query1){
				$this->db->trans_commit();
				$this->session->set_flashdata('success', 'Success!! New Service Added Successfully!');
		        return "success";
		}
		else{
				$this->db->trans_rollback();
		        return "failed";
		}
	}

	
	public function update_services(){
		//Filtering XSS and html escape from user inputs 
		//extract($this->security->xss_clean(html_escape(array_merge($this->data,$_POST))));
		
		$item_code 		= $this->input->post('item_code');
		$item_name 		= $this->input->post('item_name');
		$category_id 	= $this->input->post('category_id');
		$brand_id 		= $this->input->post('brand_id');
		$price 			= $this->input->post('price') ? $this->input->post('price') : 0;
		$tax_id 		= $this->input->post('tax_id');
		$purchase_price = $this->input->post('purchase_price') ? $this->input->post('purchase_price') : 0;
		$tax_type 		= $this->input->post('tax_type');
		$sales_price 	= $this->input->post('sales_price') ? $this->input->post('sales_price') : 0;
		$seller_points 	= $this->input->post('seller_points') ? $this->input->post('seller_points') : 0;
		$custom_barcode = $this->input->post('custom_barcode');
		$description 	= $this->input->post('description');
		$hsn 			= $this->input->post('hsn');
		$sac 			= $this->input->post('sac');
		$discount_type 	= $this->input->post('discount_type');
		$discount 		= $this->input->post('discount') ? $this->input->post('discount') : 0;
		$store_id 		= $this->input->post('store_id');
		$q_id 			= $this->input->post('q_id');
		$hide_in_pos 	= $this->input->post('hide_in_pos') ? $this->input->post('hide_in_pos') : 0;

		
		$sku 			= '';
		$unit_id 		= $this->input->post('unit_id');
		$alert_qty 		= 0;
		$profit_margin 	= 0;
		$item_group 	= 'Single';


		//Validate This items already exist or not
		//$store_id=(store_module() && is_admin()) ? ($store_id ?? get_current_store_id()) : get_current_store_id();
		$store_id = (store_module() && is_admin()) ? ($store_id ? $store_id : get_current_store_id()) : get_current_store_id();

		$this->db->trans_begin();

		$file_name=$item_image='';
		if(!empty($_FILES['item_image']['name'])){

			$new_name = time();
			$config['file_name'] = $new_name;
			$config['upload_path']          = './uploads/items/';
	        $config['allowed_types']        = 'jpg|png';
	        $config['max_size']             = 1024;
	        $config['max_width']            = 1500;
	        $config['max_height']           = 1500;
	       
	        $this->load->library('upload', $config);

			if ( ! $this->upload->do_upload('item_image'))
			{
					$error = array('error' => $this->upload->display_errors());
					print($error['error']);
					exit();
			}
			else
			{		
				$file_name=$this->upload->data('file_name');
				
				/*Create Thumbnail*/
				$config['image_library'] = 'gd2';
				$config['source_image'] = 'uploads/items/'.$file_name;
				$config['create_thumb'] = TRUE;
				$config['maintain_ratio'] = TRUE;
				$config['width']         = 75;
				$config['height']       = 50;
				$this->load->library('image_lib', $config);
				$this->image_lib->resize();
				//end
			}
		}

		$info = array(
	    				'item_name' 				=> $item_name,
	    				'item_code' 				=> $item_code,
	    				'category_id' 				=> $category_id,
	    				'brand_id' 					=> $brand_id,		    				
	    				'price' 					=> $price,
	    				'tax_id' 					=> $tax_id,
	    				'purchase_price' 			=> $purchase_price,
	    				'tax_type' 					=> $tax_type,
	    				'sales_price' 				=> $sales_price,
	    				'seller_points'				=> $seller_points,
	    				'custom_barcode'			=> $custom_barcode,
	    				'description'				=> $description,
	    				'hsn'						=> $hsn,
	    				'sac'						=> $sac,
	    				'discount_type'				=> $discount_type,
	    				'discount'					=> $discount,
	    				'hide_in_pos'				=> $hide_in_pos,
	    				'sku'						=> $sku,
	    				'unit_id'					=> $unit_id,
	    				'alert_qty'					=> $alert_qty,
	    				'profit_margin'				=> $profit_margin,
	    				'item_group'				=> $item_group,
	    				'expire_date'				=> null,
	    			);
		//Image Path	
		if(!empty($file_name)){
			$info['item_image'] = 'uploads/items/'.$file_name;
		}

		//Store ID
		//$info['store_id']=(store_module() && is_admin()) ? ($store_id ?? get_current_store_id()) : get_current_store_id();	
		$info['store_id'] = $store_id;

		$query1 = $this->db->where('id',$q_id)->update('db_items', $info);

		if(!$query1){
			$error = $this->db->error();
			return "Failed to update service. Error: " . $error['message'];
		}

		if ($query1){
			   $this->db->trans_commit();
			   $this->session->set_flashdata('success', 'Success!! Service Item Updated Successfully!');
		        return "success";
		}
		else{
				$this->db->trans_rollback();
		        return "failed";
		}
	}
}
