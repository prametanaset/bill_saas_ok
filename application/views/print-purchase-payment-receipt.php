<!DOCTYPE html>
<html>
<head>
    <title>Payment Voucher - ใบสำคัญจ่าย</title>
    <style>
        body { font-family: 'Sarabun', sans-serif; font-size: 14px; line-height: 1.4; color: #333; }
        .container { width: 100%; max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 24px; font-weight: bold; }
        .header h3 { margin: 5px 0 0; font-size: 18px; }
        .store-info { text-align: center; margin-bottom: 20px; font-size: 12px; }
        .section-title { font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 10px; margin-top: 20px; }
        .row { display: flex; flex-wrap: wrap; margin-right: -15px; margin-left: -15px; }
        .col-6 { flex: 0 0 50%; max-width: 50%; padding-right: 15px; padding-left: 15px; box-sizing: border-box; }
        .col-12 { flex: 0 0 100%; max-width: 100%; padding-right: 15px; padding-left: 15px; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .signatures { margin-top: 50px; display: flex; justify-content: space-between; }
        .signature-box { text-align: center; width: 40%; }
        .signature-line { border-bottom: 1px solid #000; margin-bottom: 5px; margin-top: 40px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 0; }
            .container { width: 100%; max-width: none; padding: 0; }
        }
    </style>
    <!-- Google Fonts for Thai support if needed, assuming system fonts or local fonts are available -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
</head>
<body onload="window.print()">

<div class="container">
    <div class="row no-print" style="margin-bottom: 20px;">
        <div class="col-12 text-center">
            <button onclick="window.print()">Print / พิมพ์</button>
            <button onclick="window.close()">Close / ปิด</button>
        </div>
    </div>

    <div class="store-info" style="margin-bottom: 10px;">
        <strong style="font-size: 20px;"><?= $store->store_name; ?></strong><br>
    </div>

    <div class="header">
        <h1>ใบสำคัญจ่าย</h1>
        <h3>PAYMENT VOUCHER</h3>
    </div>

    <div class="store-info">
        <!-- Address and Contact Info removed as per request -->
    </div>

    <div class="row">
        <div class="col-6">
            <div class="section-title">Paid To (จ่ายเงินให้)</div>
            <strong><?= $supplier->supplier_name; ?></strong><br>
            <?= $supplier->address; ?><br>
            <!-- Tax ID removed as per request -->
            Tel: <?= $supplier->mobile; ?>
        </div>
        <div class="col-6 text-right">
            <div class="section-title" style="text-align: right;">Voucher Details (รายละเอียด)</div>
            <table style="width: 100%; border: none;">
                <tr style="border: none;">
                    <td style="border: none; text-align: right; padding: 2px;"><strong>Voucher No:</strong></td>
                    <td style="border: none; text-align: right; padding: 2px;"><?= $voucher_no; ?></td>
                </tr>
                <tr style="border: none;">
                    <td style="border: none; text-align: right; padding: 2px;"><strong>Date (วันที่):</strong></td>
                    <td style="border: none; text-align: right; padding: 2px;"><?= show_date($payment->payment_date); ?></td>
                </tr>
                <tr style="border: none;">
                    <td style="border: none; text-align: right; padding: 2px;"><strong>Reference (อ้างอิง):</strong></td>
                    <td style="border: none; text-align: right; padding: 2px;"><?= $purchase->purchase_code; ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row" style="margin-top: 20px;">
        <div class="col-12">
            <table>
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Description (รายการ)</th>
                        <th class="text-right" style="width: 150px;">Amount (จำนวนเงิน)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">1</td>
                        <td>
                            Payment for Invoice #<?= $purchase->purchase_code; ?><br>
                            <small>Reference: <?= $purchase->reference_no; ?></small><br>
                            <small>Note: <?= $payment->payment_note; ?></small>
                        </td>
                        <td class="text-right"><?= number_format($payment->payment, 2); ?></td>
                    </tr>
                    <!-- Minimum height filler -->
                    <tr style="height: 100px;">
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-right"><strong>Total Amount (รวมเป็นเงินทั้งสิ้น)</strong></td>
                        <td class="text-right"><strong><?= number_format($payment->payment, 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
            <div>
                 <strong>Payment Type (ชำระโดย):</strong> <?= $payment->payment_type; ?>
                 <?php if(!empty($payment->account_id)) { 
                    echo " (Account: " . get_account_name($payment->account_id) . ")"; 
                 } ?>
            </div>
            <?php if(!empty($payment->payment_note)): ?>
            <div style="margin-top: 10px; border: 1px solid #ddd; padding: 10px;">
                <strong>Note (หมายเหตุ):</strong><br>
                <?= nl2br($payment->payment_note); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line"></div>
            <strong>ผู้จ่ายเงิน (Payer)</strong><br>
            Date: ____/____/______
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <strong>ผู้รับเงิน (Receiver)</strong><br>
            Date: ____/____/______
        </div>
    </div>

</div>

</body>
</html>
