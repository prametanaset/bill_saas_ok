<?php
class Debug_users extends CI_Controller {
    public function index() {
        echo "Admin Users:\n";
        $users = $this->db->where('role_id', 1)->get('db_users')->result();
        print_r($users);
    }
}
