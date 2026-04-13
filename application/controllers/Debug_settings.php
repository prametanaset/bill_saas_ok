<?php
class Debug_settings extends CI_Controller {
    public function index() {
        echo "DB Settings Columns (Store 1):\n";
        $fields = $this->db->list_fields('db_settings');
        print_r($fields);
        echo "\nValues (Store 1):\n";
        $row = $this->db->where('id', 1)->get('db_settings')->row();
        print_r($row);
    }
}
