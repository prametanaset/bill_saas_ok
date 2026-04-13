<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Location extends MY_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->database(); 
    }

    public function get_districts() {
        $province_code = $this->input->post('province_id'); // Receiving Code from View
        
        // Get Province ID from Code
        $province = $this->db->get_where('provinces', ['code' => $province_code])->row();
        
        if($province) {
            $query = $this->db->order_by('name_in_thai','asc')->get_where('districts', ['province_id' => $province->id]);
            
            echo '<option value="">- เลือกอำเภอ -</option>';
            foreach ($query->result() as $row) {
                echo "<option value='{$row->code}'>{$row->name_in_thai}</option>"; // Return Code
            }
        } else {
            echo '<option value="">- เลือกอำเภอ -</option>';
        }
    }

    public function get_subdistricts() {
        $district_code = $this->input->post('district_id'); // Receiving Code from View
        
        // Get District ID from Code
        $district = $this->db->get_where('districts', ['code' => $district_code])->row();
        
        $result = [];
        if($district) {
            $query = $this->db->order_by('name_in_thai','asc')->get_where('subdistricts', ['district_id' => $district->id]);
            
            foreach ($query->result() as $row) {
              $result[] = [
                'id' => $row->code, // Send Code as ID for View value
                'name_in_thai' => $row->name_in_thai,
                'zip_code' => $row->zip_code,
              ];
            }
        }
      
        echo json_encode($result);
    }
      
}
