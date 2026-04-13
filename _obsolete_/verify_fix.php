<?php
/**
 * Verification Script for Transaction Deletion Fixes
 * This script verifies that deleting payments and sales correctly removes entries from ac_transactions.
 */

// Basic setup to load CI environment
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('BASEPATH', 'tmp');
require_once('index.php');
$CI =& get_instance();
$CI->load->model('sales_model');
$CI->load->model('pos_model');
$CI->load->model('sales_debit_model');
$CI->load->helper('accounts_helper');

echo "Starting Verification...\n";

function verify_payment_deletion($CI, $payment_id, $type) {
    if ($type == 'sales') {
        $ref_field = 'ref_salespayments_id';
        $model = 'sales_model';
    } else if ($type == 'debit') {
        $ref_field = 'ref_salespaymentsdebit_id';
        $model = 'sales_debit_model';
    } else {
        return false;
    }

    echo "Verifying deletion for $type payment ID: $payment_id\n";
    
    // Check if ac_transactions exists
    $trans = $CI->db->where($ref_field, $payment_id)->get('ac_transactions')->row();
    if (!$trans) {
        echo "Warning: No ac_transactions record found for payment $payment_id before deletion. Testing might be inconclusive.\n";
    } else {
        echo "Found ac_transactions record ID: " . $trans->id . "\n";
    }

    // Perform deletion
    $result = $CI->$model->delete_payment($payment_id);
    echo "Deletion result: $result\n";

    if ($result == 'success') {
        // Verify ac_transactions is gone
        $trans_after = $CI->db->where($ref_field, $payment_id)->get('ac_transactions')->row();
        if (!$trans_after) {
            echo "SUCCESS: ac_transactions entry was correctly deleted.\n";
        } else {
            echo "FAILED: ac_transactions entry STILL EXISTS (ID: " . $trans_after->id . ")\n";
        }
    } else {
        echo "ERROR: Model returned failure during deletion.\n";
    }
}

function verify_bulk_sales_deletion($CI, $sales_id) {
    echo "Verifying bulk deletion for Sales ID: $sales_id\n";
    
    // Find associated payments
    $payments = $CI->db->where('sales_id', $sales_id)->get('db_salespayments')->result();
    $payment_ids = array_map(function($p) { return $p->id; }, $payments);
    
    if (empty($payment_ids)) {
        echo "Warning: No payments found for Sales ID $sales_id. Testing bulk deletion of payments might be inconclusive.\n";
    } else {
        echo "Found Payment IDs: " . implode(', ', $payment_ids) . "\n";
        $trans_count = $CI->db->where_in('ref_salespayments_id', $payment_ids)->count_all_results('ac_transactions');
        echo "Found $trans_count ac_transactions records before deletion.\n";
    }

    // Perform bulk deletion
    $result = $CI->sales_model->delete_sales($sales_id);
    echo "Bulk deletion result: $result\n";

    if ($result == 'success' || strpos($result, 'success') !== false) {
        // Verify ac_transactions are gone
        if (!empty($payment_ids)) {
            $trans_after = $CI->db->where_in('ref_salespayments_id', $payment_ids)->count_all_results('ac_transactions');
            if ($trans_after == 0) {
                echo "SUCCESS: All associated ac_transactions entries were correctly deleted.\n";
            } else {
                echo "FAILED: $trans_after ac_transactions entries STILL EXIST.\n";
            }
        }
    } else {
        echo "ERROR: Model returned failure during bulk deletion. Result: $result\n";
    }
}

// 1. Try to find a recent sale with a payment and accounting transaction
$recent_payment = $CI->db->select('p.id, p.sales_id')
                         ->from('db_salespayments p')
                         ->join('ac_transactions t', 't.ref_salespayments_id = p.id')
                         ->order_by('p.id', 'DESC')
                         ->limit(1)
                         ->get()
                         ->row();

if ($recent_payment) {
    verify_payment_deletion($CI, $recent_payment->id, 'sales');
    echo "-------------------\n";
} else {
    echo "No recent sales payment with accounting record found for testing.\n";
}

// 2. Try to find a recent sale for bulk deletion test
$recent_sale = $CI->db->select('s.id')
                      ->from('db_sales s')
                      ->join('db_salespayments p', 'p.sales_id = s.id')
                      ->join('ac_transactions t', 't.ref_salespayments_id = p.id')
                      ->order_by('s.id', 'DESC')
                      ->limit(1)
                      ->get()
                      ->row();

if ($recent_sale) {
    verify_bulk_sales_deletion($CI, $recent_sale->id);
    echo "-------------------\n";
} else {
    echo "No recent sale with accounting record found for bulk deletion testing.\n";
}

echo "Verification Finished.\n";
