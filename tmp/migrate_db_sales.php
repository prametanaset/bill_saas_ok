<?php
define('BASEPATH', 'TRUE');
require_once('index.php'); // Assuming this provides database access in CI
$CI =& get_instance();
$CI->load->database();

$columns = array(
    'vat_amt' => 'DECIMAL(10,2) DEFAULT NULL',
    'taxable_amount' => 'DECIMAL(10,2) DEFAULT NULL',
    'total_amount' => 'DECIMAL(10,2) DEFAULT NULL'
);

foreach ($columns as $column => $definition) {
    if (!$CI->db->field_exists($column, 'db_sales')) {
        $sql = "ALTER TABLE db_sales ADD COLUMN $column $definition";
        if ($CI->db->query($sql)) {
            echo "Added column $column to db_sales\n";
        } else {
            echo "Failed to add column $column to db_sales\n";
        }
    } else {
        echo "Column $column already exists in db_sales\n";
    }
}
?>
