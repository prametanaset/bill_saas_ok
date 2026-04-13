<?php
class Migrate_toggle extends CI_Controller {
    public function index() {
        if (!$this->db->field_exists('offline_auto_email', 'db_sitesettings')) {
            $this->db->query("ALTER TABLE `db_sitesettings` ADD `offline_auto_email` TINYINT(1) DEFAULT 1");
            echo "Column 'offline_auto_email' added successfully.";
        } else {
            echo "Column 'offline_auto_email' already exists.";
        }
    }
}
