<?php
class Debug_site_settings extends CI_Controller {
    public function index() {
        echo "DB Site Settings Columns:\n";
        print_r($this->db->list_fields('db_sitesettings'));
        echo "\nValues:\n";
        print_r($this->db->get('db_sitesettings')->row());
    }
}
