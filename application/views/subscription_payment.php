<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>ต่ออายุการใช้งาน (Renew Subscription)</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.6 -->
    <link rel="stylesheet" href="<?php echo $theme_link; ?>bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo $theme_link; ?>dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="<?php echo $theme_link; ?>dist/css/skins/_all-skins.min.css">
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
    <header class="main-header">
        <nav class="navbar navbar-static-top">
            <div class="container">
                <div class="navbar-header">
                    <a href="<?php echo base_url(); ?>" class="navbar-brand"><b><?php echo $this->config->item('site_name'); ?></b></a>
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
                        <i class="fa fa-bars"></i>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <div class="content-wrapper">
        <div class="container">
            <section class="content-header">
                <h1>ต่ออายุการสมัครสมาชิก</h1>
            </section>

            <section class="content">
                <?php if ($this->session->flashdata('failed')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h4><i class="icon fa fa-ban"></i> Error!</h4>
                        <?= $this->session->flashdata('failed') ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('warning')): ?>
                    <div class="alert alert-warning alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h4><i class="icon fa fa-warning"></i> แจ้งเตือน</h4>
                        <?= $this->session->flashdata('warning') ?>
                    </div>
                <?php endif; ?>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">แพ็กเกจที่เลือก: <?= $package->package_name ?></h3>
                    </div>
                    <div class="box-body">
                        <!-- Billing Cycle Selection -->
                        <?php if($package->monthly_price > 0 || $package->annual_price > 0): ?>
                        <div class="row" id="billing-cycle-section" style="margin-bottom: 20px;">
                            <div class="col-md-6 col-md-offset-3">
                                <div class="well text-center" style="background: #fff; border: 2px solid #3c8dbc;">
                                    <h4>เลือกชุดการเรียกเก็บเงิน (Select Billing Cycle)</h4>
                                    <div class="btn-group" data-toggle="buttons">
                                        <label class="btn btn-default active" id="btn-monthly">
                                            <input type="radio" name="billing_cycle" value="Monthly" checked> รายเดือน (Monthly)
                                        </label>
                                        <label class="btn btn-default" id="btn-annually">
                                            <input type="radio" name="billing_cycle" value="Annually"> รายปี (Annually) <span class="label label-danger">ประหยัด 2 เดือน!</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 col-md-offset-3">
                                <table class="table table-bordered table-striped">
                                    <tr>
                                        <th style="width:200px">ชื่อแพ็กเกจ</th>
                                        <td><span class="text-bold text-lg text-blue"><?= $package->package_name ?></span></td>
                                    </tr>
                                    <tr>
                                        <th>ราคาจ่าย</th>
                                        <td>
                                            <span id="display_price" class="text-bold text-lg text-red" style="font-size: 24px;"></span>
                                            <span id="display_price_label"></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>จำนวนคลังสินค้า</th>
                                        <td class="text-right text-bold" id="display_warehouses"></td>
                                    </tr>
                                    <tr>
                                        <th>จำนวนผู้ใช้งาน</th>
                                        <td class="text-right text-bold" id="display_users"></td>
                                    </tr>
                                    <tr>
                                        <th>จำนวนสินค้าสูงสุด</th>
                                        <td class="text-right text-bold" id="display_items"></td>
                                    </tr>
                                    <tr>
                                        <th>จำนวนใบเสร็จ</th>
                                        <td class="text-right text-bold" id="display_invoices"></td>
                                    </tr>
                                    <tr>
                                        <th>ออก e-TAX Invoice</th>
                                        <td class="text-right text-bold text-maroon" id="display_etax"></td>
                                    </tr>
                                    <tr>
                                        <th>ระยะเวลาทดลองใช้</th>
                                        <td><span class="badge bg-purple"><?= $package->trial_days ?> วัน</span></td>
                                    </tr>
                                    <tr>
                                        <th>วันหมดอายุสมาชิก</th>
                                        <td class="text-bold text-red" id="display_expire_date"></td>
                                    </tr>
                                </table>
                                <hr>

                                <?php if($price > 0): ?>
                                <div class="nav-tabs-custom">
                                    <ul class="nav nav-tabs">
                                        <li class="active"><a href="#tab_1" data-toggle="tab"><i class="fa fa-cc-stripe"></i> ชำระด้วยบัตรเครดิต (Stripe)</a></li>
                                        <li><a href="#tab_2" data-toggle="tab"><i class="fa fa-bank"></i> โอนเงินผ่านธนาคาร (ไทย)</a></li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="tab_1">
                                            <h4>Pay with Card</h4>
                                            <?php if(!empty($stripe_publishable_key)): ?>
                                                <style>
                                                    .StripeElement { #card-element {
                                                      box-sizing: border-box;
                                                      height: 40px;
                                                      padding: 10px 12px;
                                                      border: 1px solid transparent;
                                                      border-radius: 4px;
                                                      background-color: white;
                                                      box-shadow: 0 1px 3px 0 #e6ebf1;
                                                      -webkit-transition: box-shadow 150ms ease;
                                                      transition: box-shadow 150ms ease;
                                                    }
                                                    .StripeElement--focus { box-shadow: 0 1px 3px 0 #cfd7df; }
                                                    .StripeElement--invalid { border-color: #fa755a; }
                                                    .StripeElement--webkit-autofill { background-color: #fefde5 !important; }
                                                </style>

                                                <?php echo form_open('stripe/payment', ['class' => 'form-horizontal', 'id' => 'payment-form']); ?>
                                                    <input type="hidden" name="package_id" value="<?= $package->id ?>">
                                                    <input type="hidden" name="plan_type" id="plan_type_stripe" value="Monthly">
                                                    
                                                    <div class="form-group">
                                                        <label for="card-element" class="col-sm-12">Credit or debit card</label>
                                                        <div class="col-sm-12">
                                                            <div id="card-element" class="form-control">
                                                              <!-- A Stripe Element will be inserted here. -->
                                                            </div>
                                                            <!-- Used to display form errors. -->
                                                            <div id="card-errors" role="alert" class="text-danger" style="margin-top:10px;"></div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <div class="col-sm-12 text-center">
                                                            <button type="submit" class="btn btn-primary btn-lg btn-block" id="submit-pay"><i class="fa fa-credit-card"></i> Pay Now</button>
                                                        </div>
                                                    </div>
                                                <?php echo form_close(); ?>

                                                <script src="https://js.stripe.com/v3/"></script>
                                                <script>
                                                    var stripe = Stripe('<?= $stripe_publishable_key ?>');
                                                    var elements = stripe.elements();
                                                    var style = {
                                                      base: {
                                                        color: '#32325d',
                                                        fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                                                        fontSmoothing: 'antialiased',
                                                        fontSize: '16px',
                                                        '::placeholder': { color: '#aab7c4' }
                                                      },
                                                      invalid: { color: '#fa755a', iconColor: '#fa755a' }
                                                    };
                                                    var card = elements.create('card', {style: style});
                                                    card.mount('#card-element');

                                                    card.addEventListener('change', function(event) {
                                                      var displayError = document.getElementById('card-errors');
                                                      if (event.error) { displayError.textContent = event.error.message; } else { displayError.textContent = ''; }
                                                    });

                                                    var form = document.getElementById('payment-form');
                                                    form.addEventListener('submit', function(event) {
                                                      event.preventDefault();
                                                      document.getElementById('submit-pay').disabled = true;
                                                      document.getElementById('submit-pay').innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
                                                      stripe.createToken(card).then(function(result) {
                                                        if (result.error) {
                                                          var errorElement = document.getElementById('card-errors');
                                                          errorElement.textContent = result.error.message;
                                                          document.getElementById('submit-pay').disabled = false;
                                                          document.getElementById('submit-pay').innerHTML = '<i class="fa fa-credit-card"></i> Pay Now';
                                                        } else {
                                                          stripeTokenHandler(result.token);
                                                        }
                                                      });
                                                    });

                                                    function stripeTokenHandler(token) {
                                                      var form = document.getElementById('payment-form');
                                                      var hiddenInput = document.createElement('input');
                                                      hiddenInput.setAttribute('type', 'hidden');
                                                      hiddenInput.setAttribute('name', 'stripeToken');
                                                      hiddenInput.setAttribute('value', token.id);
                                                      form.appendChild(hiddenInput);

                                                      form.submit();
                                                    }
                                                </script>
                                            <?php else: ?>
                                                <div class="alert alert-warning">
                                                    <h4><i class="icon fa fa-warning"></i> Stripe Configuration Missing!</h4>
                                                    Please contact Admin to configure Stripe API Keys in <code>application/config/stripe.php</code>.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="tab-pane" id="tab_2">
                                            <h4><?= $this->lang->line('landing_signup_bank_transfer'); ?></h4>
                                            <div id="bank_section">
                                                <?php 
                                                    $CI =& get_instance();
                                                    $bank = $CI->db->where('store_id', 1)->get('bill_xml.db_bankdetails')->row();
                                                ?>
                                                <div style="background: #f9f9f9; padding: 15px; border-left: 4px solid #3c8dbc; margin-top: 15px;">
                                                    <?php if($bank): ?>
                                                        <p><strong><?= $this->lang->line('landing_signup_bank_details') ? $this->lang->line('landing_signup_bank_details') : 'ธนาคาร:' ?></strong> <?= $bank->bank_name ?></p>
                                                        <p><strong><?= $this->lang->line('landing_signup_acc_name') ? $this->lang->line('landing_signup_acc_name') : 'ชื่อบัญชี:' ?></strong> <?= $bank->holder_name ?></p>
                                                        <p><strong><?= $this->lang->line('landing_signup_acc_no') ? $this->lang->line('landing_signup_acc_no') : 'เลขที่บัญชี:' ?></strong> <span class="text-large text-bold" style="font-size: 18px;"><?= $bank->account_number ?></span></p>
                                                        <p><strong><?= $this->lang->line('landing_signup_branch') ? $this->lang->line('landing_signup_branch') : 'สาขา:' ?></strong> <?= $bank->branch_name ?></p>
                                                    <?php else: ?>
                                                        <p><?= $this->lang->line('landing_signup_admin_bank') ? $this->lang->line('landing_signup_admin_bank') : 'กรุณาติดต่อผู้ดูแลระบบเพื่อขอรายละเอียดบัญชีธนาคาร' ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
        
                                            <?php echo form_open_multipart('subscription/process_payment', ['class' => 'form-horizontal']); ?>
                                                <input type="hidden" name="payment_method" value="bank_transfer">
                                                <input type="hidden" name="package_id" value="<?= $package->id ?>">
                                                <input type="hidden" name="plan_type" id="plan_type_bank" value="Monthly">
                                                
                                                <div class="form-group">
                                                    <label class="col-sm-12">อัพโหลด สลิปชำระเงิน <span class="text-danger">*</span></label>
                                                    <div class="col-sm-12">
                                                        <input type="file" name="payment_slip" class="form-control" required>
                                                        <p class="help-block"><small>JPG, PNG, PDF allowed.</small></p>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-sm-12">
                                                        <button type="submit" class="btn btn-success btn-block"><i class="fa fa-upload"></i> อัปโหลดและส่ง</button>
                                                    </div>
                                                </div>
                                            <?php echo form_close(); ?>
                                        </div>
                                    </div>
                                </div>
                                <?php else: ?>
                                    <!-- FREE PACKAGE RENEWAL -->
                                    <div class="text-center">
                                        <h4>ยืนยันการต่ออายุ</h4>
                                        <p>คุณกำลังจะต่ออายุการสมัครสมาชิกของ... <strong><?= $package->package_name ?></strong> แพ็กเกจ.</p>
                                        
                                        <?php echo form_open('subscription/process_payment', ['class' => 'form-horizontal']); ?>
                                            <input type="hidden" name="payment_method" value="free">
                                            <input type="hidden" name="package_id" value="<?= $package->id ?>">
                                            <input type="hidden" name="plan_type" id="plan_type_free" value="Monthly">
                                            <button type="submit" class="btn btn-success btn-lg"><i class="fa fa-check-circle"></i> ยืนยันการสมัครสมาชิก</button>
                                        <?php echo form_close(); ?>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                </div>
            </section>
        </div>
    </div>
    <footer class="main-footer">
        <div class="container">
            <strong>Copyright &copy; <?php echo date('Y'); ?>.</strong> All rights reserved.
        </div>
    </footer>
</div>

 <script src="<?php echo $theme_link; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
 <script src="<?php echo $theme_link; ?>bootstrap/js/bootstrap.min.js"></script>
 <script src="<?php echo $theme_link; ?>dist/js/app.min.js"></script>
 
 <script>
 $(document).ready(function() {
     var monthly_price = <?= $package->monthly_price ?>;
     var annual_price = (monthly_price > 0) ? (monthly_price * 10) : 0;
     
     var max_warehouses = <?= $package->max_warehouses ?>;
     var max_users = <?= $package->max_users ?>;
     var max_items = <?= $package->max_items ?>;
     var max_invoices = <?= $package->max_invoices ?>;
     var max_etax = <?= (isset($package->max_etax_emails) ? $package->max_etax_emails : 0) ?>;
     
     function updateDisplay(cycle) {
         $("#plan_type_stripe, #plan_type_bank, #plan_type_free").val(cycle);
         
         if (cycle == 'Monthly') {
             var price = monthly_price;
             $("#display_price").text(new Intl.NumberFormat().format(price));
             $("#display_price_label").text(" บาท / เดือน");
             
             $("#display_warehouses").text(max_warehouses == -1 ? 'ไม่จำกัด' : new Intl.NumberFormat().format(max_warehouses));
             $("#display_users").text(max_users == -1 ? 'ไม่จำกัด' : new Intl.NumberFormat().format(max_users));
             $("#display_items").text(max_items == -1 ? 'ไม่จำกัด' : new Intl.NumberFormat().format(max_items));
             $("#display_invoices").text(max_invoices == -1 ? 'ไม่จำกัด' : new Intl.NumberFormat().format(max_invoices));
             $("#display_etax").text(max_etax == -1 ? 'ไม่จำกัด' : new Intl.NumberFormat().format(max_etax));
             
             var d = new Date();
             d.setMonth(d.getMonth() + 1);
             $("#display_expire_date").text(d.getDate().toString().padStart(2, '0') + '-' + (d.getMonth()+1).toString().padStart(2, '0') + '-' + d.getFullYear());
         } else {
             var price = annual_price;
             $("#display_price").text(new Intl.NumberFormat().format(price));
             $("#display_price_label").html(" บาท / ปี <span class='label label-success'>ประหยัด 2 เดือน!</span>");
             
             $("#display_warehouses").text(max_warehouses == -1 ? 'ไม่จำกัด' : new Intl.NumberFormat().format(max_warehouses));
             $("#display_users").text(max_users == -1 ? 'ไม่จำกัด' : new Intl.NumberFormat().format(max_users));
             $("#display_items").text(max_items == -1 ? 'ไม่จำกัด' : new Intl.NumberFormat().format(max_items));
             
             // Boost 12x
             var boosted_invoices = max_invoices == -1 ? 'ไม่จำกัด' : new Intl.NumberFormat().format(max_invoices * 12);
             var boosted_etax = max_etax == -1 ? 'ไม่จำกัด' : new Intl.NumberFormat().format(max_etax * 12);
             
             $("#display_invoices").html(boosted_invoices + " <small class='text-green'>(12 เท่า!)</small>");
             $("#display_etax").html(boosted_etax + " <small class='text-green'>(12 เท่า!)</small>");
             
             var d = new Date();
             d.setFullYear(d.getFullYear() + 1);
             $("#display_expire_date").text(d.getDate().toString().padStart(2, '0') + '-' + (d.getMonth()+1).toString().padStart(2, '0') + '-' + d.getFullYear());
         }

         if(price == 0){
            $("#display_price").html('<span class="label label-success" style="font-size:14px;">FREE</span>');
            $("#display_price_label").text("");
         }
     }

     $('input[name="billing_cycle"]').on('change', function() {
         var cycle = $(this).val();
         // Force Monthly if price is 0
         if(monthly_price == 0 && annual_price == 0){
            cycle = 'Monthly';
         }
         updateDisplay(cycle);
         if(cycle == 'Monthly'){
            $("#btn-monthly").addClass('active').addClass('btn-primary').removeClass('btn-default');
            $("#btn-annually").removeClass('active').removeClass('btn-primary').addClass('btn-default');
         } else {
            $("#btn-annually").addClass('active').addClass('btn-primary').removeClass('btn-default');
            $("#btn-monthly").removeClass('active').removeClass('btn-primary').addClass('btn-default');
         }
     });

     // Init
     updateDisplay('Monthly');
     $("#btn-monthly").addClass('btn-primary').removeClass('btn-default');
 });
 </script>
</body>
</html>
