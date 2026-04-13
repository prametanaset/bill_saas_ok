<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Offline_Requests extends CI_Controller {

    public function index() {
        $this->load->database();
        $this->load->dbforge();

        $fields = array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'store_id' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'package_id' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'plan_type' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
            ),
             'amount' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ),
            'payment_slip' => array(
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => TRUE,
            ),
            'status' => array(
                'type' => 'INT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '0=Pending, 1=Approved, 2=Rejected'
            ),
            'note' => array(
                'type' => 'TEXT',
                'null' => TRUE,
            ),
            'created_date' => array(
                'type' => 'DATE',
            ),
            'created_time' => array(
                'type' => 'TIME',
            ),
        );

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('id', TRUE);
        
        if ($this->dbforge->create_table('db_offline_requests', TRUE)) {
            echo "Table 'db_offline_requests' created successfully!";
        } else {
            echo "Failed to create table 'db_offline_requests'.";
        }
    }
}
