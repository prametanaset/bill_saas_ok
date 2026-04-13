<?php
class Debug_db extends CI_Controller {
    public function index() {
        echo "<pre>";
        echo "Current DB: " . $this->db->database . "\n\n";
        
        $query = $this->db->query("SHOW TABLES");
        if($query){
            echo "Tables in " . $this->db->database . ":\n";
            foreach($query->result_array() as $row){
                echo " - " . current($row) . "\n";
            }
        }
        echo "</pre>";
    }
}
