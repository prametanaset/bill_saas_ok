<?php
/**
 * Run with:
 *   php application/repair_tenant_invoice_schema.php
 *
 * This script iterates all tenants in master db_store and ensures:
 * - db_invoice table exists with required columns.
 * - db_store.invoice_init column exists and has default values.
 */
define('ENVIRONMENT', 'development');
chdir(__DIR__ . '/..'); // project root
require 'index.php';    // boot CodeIgniter

$CI =& get_instance();
$masterDb = $CI->db->database;

function out($message) {
    echo $message . PHP_EOL;
}

function safe_db_name($dbName) {
    return preg_match('/^[A-Za-z0-9_]+$/', $dbName) === 1;
}

function ensure_invoice_table($db) {
    $db->query("CREATE TABLE IF NOT EXISTS `db_invoice` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `count_id` int(11) DEFAULT NULL,
      `store_id` int(11) DEFAULT NULL,
      `sales_id` int(11) DEFAULT NULL,
      `invoice_code` varchar(100) DEFAULT NULL,
      `invoice_date` date DEFAULT NULL,
      `reference_no` varchar(100) DEFAULT NULL,
      `created_date` date DEFAULT NULL,
      `created_time` varchar(20) DEFAULT NULL,
      `created_by` varchar(100) DEFAULT NULL,
      `system_ip` varchar(100) DEFAULT NULL,
      `system_name` varchar(100) DEFAULT NULL,
      `status` int(1) DEFAULT '1',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    if (!$db->field_exists('reference_no', 'db_invoice')) {
        $db->query("ALTER TABLE `db_invoice` ADD `reference_no` varchar(100) DEFAULT NULL AFTER `invoice_date`");
        return 'created_or_altered';
    }
    return 'verified';
}

function ensure_invoice_init_column($db) {
    if (!$db->field_exists('invoice_init', 'db_store')) {
        $db->query("ALTER TABLE `db_store` ADD `invoice_init` VARCHAR(100) DEFAULT 'IN'");
        $db->query("UPDATE `db_store` SET `invoice_init`='IN' WHERE `invoice_init` IS NULL OR `invoice_init`=''");
        return 'created';
    }
    $db->query("UPDATE `db_store` SET `invoice_init`='IN' WHERE `invoice_init` IS NULL OR `invoice_init`=''");
    return 'verified';
}

out("=== Tenant Invoice Schema Repair ===");
out("Master DB: {$masterDb}");
out("Started at: " . date('Y-m-d H:i:s'));
out("");

// Always read tenant list from master.
$CI->db->query("USE `{$masterDb}`");
$stores = $CI->db->select('id, store_code, database_name')
                 ->from('db_store')
                 ->where('status', 1)
                 ->get()
                 ->result();

$stats = array(
    'total' => 0,
    'processed' => 0,
    'skipped' => 0,
    'failed' => 0,
);

$details = array();
$stats['total'] = count($stores);

foreach ($stores as $store) {
    $storeId = (int)$store->id;
    $storeCode = !empty($store->store_code) ? $store->store_code : ('store_' . $storeId);
    $tenantDb = trim((string)$store->database_name);

    if (empty($tenantDb)) {
        $stats['skipped']++;
        $details[] = "[SKIP] {$storeCode} (id={$storeId}) : empty database_name";
        continue;
    }

    if (!safe_db_name($tenantDb)) {
        $stats['skipped']++;
        $details[] = "[SKIP] {$storeCode} (id={$storeId}) : invalid database_name={$tenantDb}";
        continue;
    }

    try {
        $CI->db->query("USE `{$tenantDb}`");

        $invoiceResult = ensure_invoice_table($CI->db);
        $initResult = ensure_invoice_init_column($CI->db);

        $stats['processed']++;
        $details[] = "[OK] {$storeCode} (id={$storeId}, db={$tenantDb}) : db_invoice={$invoiceResult}, db_store.invoice_init={$initResult}";
    } catch (Throwable $e) {
        $stats['failed']++;
        $details[] = "[FAIL] {$storeCode} (id={$storeId}, db={$tenantDb}) : " . $e->getMessage();
    } finally {
        // Move back to master to keep session/meta operations safe.
        $CI->db->query("USE `{$masterDb}`");
    }
}

out("Results:");
foreach ($details as $line) {
    out($line);
}

out("");
out("Summary:");
out("- Total stores    : " . $stats['total']);
out("- Processed       : " . $stats['processed']);
out("- Skipped         : " . $stats['skipped']);
out("- Failed          : " . $stats['failed']);
out("Finished at: " . date('Y-m-d H:i:s'));
out("=== Done ===");

