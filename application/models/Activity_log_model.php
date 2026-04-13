<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Activity_log_model extends CI_Model {

    var $table = 'db_activity_log';

    public function __construct()
    {
        parent::__construct();
        // Auto-create table if not exists
        if (!$this->db->table_exists($this->table)) {
            $this->create_activity_log_table();
        }
    }

    private function create_activity_log_table()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `db_activity_log` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `action` varchar(255) NOT NULL,
          `item_id` int(11) DEFAULT NULL,
          `description` text,
          `created_date` date NOT NULL,
          `created_time` varchar(50) NOT NULL,
          `ip_address` varchar(50) NOT NULL,
          `status` int(1) DEFAULT 1,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->db->query($sql);
    }

    public function log_action($action, $item_id = null, $description = '')
    {
        $data = array(
            'user_id'       => $this->session->userdata('inv_userid') ?? 0,
            'action'        => $action,
            'item_id'       => $item_id,
            'description'   => $description,
            'created_date'  => date("Y-m-d"),
            'created_time'  => date("H:i:s"),
            'ip_address'    => $this->input->ip_address(),
            'status'        => 1
        );
        $this->db->insert($this->table, $data);

        // Auto-cleanup logging (Probabilistic cleanup to avoid overhead on every insert)
        // 1 in 10 chance to run cleanup
        if (rand(1, 10) == 1) {
            $this->cleanup_logs();
        }
    }

    public function cleanup_logs()
    {
        // Delete logs older than 90 days
        $date_threshold = date('Y-m-d', strtotime('-90 days'));
        $this->db->where('created_date <', $date_threshold);
        $this->db->delete($this->table);
    }
    
    // For DataTable
    public function get_datatables()
    {
        $this->_get_datatables_query();
        if($_POST['length'] != -1)
        $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }
    
    private function _get_datatables_query()
    {
        $this->db->from($this->table);
        $column_order = array('id', 'created_date', 'created_time', 'user_id', 'action', 'description', 'ip_address');
        $column_search = array('action', 'description', 'created_date');
        $order = array('id' => 'desc');
        
        $i = 0;
        foreach ($column_search as $item) 
        {
            if($_POST['search']['value']) 
            {
                if($i===0) 
                {
                    $this->db->group_start(); 
                    $this->db->like($item, $_POST['search']['value']);
                }
                else
                {
                    $this->db->or_like($item, $_POST['search']['value']);
                }

                if(count($column_search) - 1 == $i) 
                    $this->db->group_end(); 
            }
            $i++;
        }
        
        if(isset($_POST['order'])) 
        {
            $this->db->order_by($column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } 
        else if(isset($order))
        {
            $order = $order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    public function count_filtered()
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
}
