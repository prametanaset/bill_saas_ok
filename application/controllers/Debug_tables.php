<?php
class Debug_tables extends CI_Controller {
    public function index() {
        echo "All Tables:\n";
        print_r($this->db->list_tables());
    }
}
