<?php
define('ENVIRONMENT', 'development');
chdir(__DIR__ . '/..'); // Go up to project root
require 'index.php'; // Boot CodeIgniter
$CI =& get_instance();

echo "Running DB Migration...\n";
if (!$CI->db->field_exists('database_name', 'db_store')) {
    $CI->db->query("ALTER TABLE `db_store` ADD `database_name` VARCHAR(100) DEFAULT NULL AFTER `store_code`");
    echo "Successfully added database_name column to db_store.\n";
} else {
    echo "Column database_name already exists.\n";
}
