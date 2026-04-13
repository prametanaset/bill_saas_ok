<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php print $SITE_TITLE; ?> | Sign Up</title>
  <link rel='shortcut icon' href='<?php echo $theme_link; ?>images/favicon.ico' />
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="<?php echo $theme_link; ?>bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo $theme_link; ?>dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/iCheck/square/blue.css">
  <style>
      .register-box { width: 450px; margin: 3% auto; }
      .register-box-body { box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 5px; background: #fff; padding: 30px; }
      .form-section-header { border-bottom: 2px solid #f4f4f4; margin-bottom: 20px; padding-bottom: 10px; font-weight: bold; color: #555; }
      .pkg-card { border: 1px solid #ddd; padding: 15px; border-radius: 4px; margin-bottom: 10px; cursor: pointer; transition: all 0.2s; }
      .pkg-card:hover { border-color: #3c8dbc; background: #f0f9ff; }
      .pkg-card.active { border-color: #3c8dbc; background: #e6f7ff; box-shadow: 0 0 5px rgba(60,141,188,0.5); }
      .payment-options { display: none; margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px; }
      .bank-details-box { background: #f9f9f9; padding: 15px; border-left: 4px solid #3c8dbc; margin-top: 15px; display: none; }
  </style>
</head>
<body class="hold-transition login-page" style="height:auto; min-height:100vh; background: url('<?= base_url('uploads/bg/bg-02.jpg') ?>') no-repeat center center fixed; background-size: cover;">

  <!-- Language Switcher Custom -->
  <input type="hidden" id="base_url" value="<?=base_url()?>">
  <div style="position: absolute; top: 20px; right: 20px; z-index: 9999; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 5px 10px;">
    <?php 
      $lang_query = $this->db->query('select * from db_languages where status=1 order by language asc');
      $my_language = (!empty($this->session->userdata('language'))) ? $this->session->userdata('language') : "Thai";
    ?>
    <select class="language_id" style="border: none; background: transparent; font-size: 14px; font-family: 'Poppins', sans-serif; cursor: pointer; outline: none; color: #333;">
      <?php foreach ($lang_query->result() as $res): ?>
        <option value="<?= $res->id ?>" <?= ($my_language == $res->language) ? "selected" : "" ?> style="color: #333;">
          <?= $res->language ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <!-- Language Switcher Custom End -->

<div class="register-box">
  <div class="register-logo">
    <a href="#"><b><img src="<?php echo base_url(get_site_logo());?>" style="height:70px"></b></a>
  </div>

  <div class="register-box-body">
    <h3 class="login-box-msg text-bold" style="padding:0 0 20px 0;"><?= $this->lang->line('landing_signup_create_account') ? $this->lang->line('landing_signup_create_account') : 'Create Account' ?></h3>

    <?php if($this->session->flashdata('failed')): ?>
        <div class="alert alert-danger"><?php echo $this->session->flashdata('failed'); ?></div>
    <?php endif; ?>
    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
    <?php endif; ?>

    <form action="<?php echo base_url(); ?>signup/register_store" method="post" enctype="multipart/form-data" autocomplete="off">
      <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
      
      <div class="form-section-header"><i class="fa fa-user"></i> <?= $this->lang->line('landing_signup_account_details') ? $this->lang->line('landing_signup_account_details') : 'Account Details' ?></div>
      
      <div class="form-group has-feedback">
        <input type="text" class="form-control" placeholder="<?= $this->lang->line('landing_signup_store_name') ? $this->lang->line('landing_signup_store_name') : 'Store/Company Name' ?>" name="store_name" required value="<?= set_value('store_name'); ?>" autocomplete="off">
        <span class="glyphicon glyphicon-home form-control-feedback"></span>
      </div>

      <div class="form-group has-feedback">
        <input type="text" class="form-control" placeholder="<?= $this->lang->line('landing_signup_contact_name') ? $this->lang->line('landing_signup_contact_name') : 'Contact Person Name' ?>" name="contact_name" required value="<?= set_value('contact_name'); ?>" autocomplete="off">
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>

      <div class="form-group has-feedback">
        <input type="email" class="form-control" placeholder="<?= $this->lang->line('landing_signup_email') ? $this->lang->line('landing_signup_email') : 'Email Address' ?>" name="email" required value="<?= set_value('email'); ?>" autocomplete="off">
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>

      <div class="form-group has-feedback">
        <input type="text" class="form-control" placeholder="<?= $this->lang->line('landing_signup_mobile') ? $this->lang->line('landing_signup_mobile') : 'Mobile Number' ?>" name="mobile" required value="<?= set_value('mobile'); ?>" autocomplete="off">
        <span class="glyphicon glyphicon-phone form-control-feedback"></span>
      </div>

      <div class="row">
        <div class="col-md-6">
            <div class="form-group has-feedback">
                <input type="password" class="form-control" placeholder="<?= $this->lang->line('landing_signup_password') ? $this->lang->line('landing_signup_password') : 'Password' ?>" name="password" required autocomplete="new-password">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group has-feedback">
                <input type="password" class="form-control" placeholder="<?= $this->lang->line('landing_signup_cpassword') ? $this->lang->line('landing_signup_cpassword') : 'Confirm Password' ?>" name="cpassword" required autocomplete="new-password">
                <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
            </div>
        </div>
      </div>

      <div class="form-section-header" style="margin-top:20px;"><i class="fa fa-cube"></i> <?= $this->lang->line('landing_signup_select_package') ? $this->lang->line('landing_signup_select_package') : 'Select Package' ?></div>
      
      <div class="form-group">
          <?php if(!empty($packages)): ?>
            <select class="form-control" name="package_id" id="package_id" required onchange="checkPackage()">
                <option value=""><?= $this->lang->line('landing_signup_choose_plan') ? $this->lang->line('landing_signup_choose_plan') : '-- Choose a Plan --' ?></option>
                <?php foreach($packages as $pkg): ?>
                  <?php if(isset($pkg['id'])): ?>
                    <?php 
                        $price_text = $this->lang->line('landing_signup_free') ? $this->lang->line('landing_signup_free') : 'Free';
                        // Check logic: If monthly > 0, show / Month. Else if annual > 0, show / Year. Else Free.
                        // Or rely on plan_type if available? Let's check prices first as they are safer.
                        if($pkg['monthly_price'] > 0){
                            $price_text = number_format($pkg['monthly_price'], 2) . ' / ' . ($this->lang->line('landing_signup_month') ? $this->lang->line('landing_signup_month') : 'Month');
                        } else if($pkg['annual_price'] > 0){
                            $price_text = number_format($pkg['annual_price'], 2) . ' / ' . ($this->lang->line('landing_signup_year') ? $this->lang->line('landing_signup_year') : 'Year');
                        }
                    ?>
                    <option value="<?= $pkg['id'] ?>" data-price="<?= $pkg['monthly_price'] ?>"><?= $pkg['package_name'] ?> (<?= $price_text ?>)</option>
                  <?php endif; ?>
                <?php endforeach; ?>
            </select>
          <?php endif; ?>
      </div>

      <!-- Payment Method Toggle -->
      <div class="form-group text-center" id="trial_toggle_div" style="display:none;">
          <div class="btn-group" data-toggle="buttons">
              <!-- Free Trial Option (Hidden for Paid Packages) -->
              <label class="btn btn-default active" id="btn_trial" onclick="togglePayment('trial')">
                <input type="radio" name="payment_method" value="trial" checked autocomplete="off"> 
                <i class="fa fa-clock-o text-green"></i> <?= $this->lang->line('landing_signup_free_trial') ? $this->lang->line('landing_signup_free_trial') : 'Start Free Trial' ?>
              </label>

              <!-- Bank Transfer Option -->
              <label class="btn btn-default" id="btn_bank" onclick="togglePayment('bank_transfer')">
                <input type="radio" name="payment_method" value="bank_transfer" autocomplete="off"> 
                <i class="fa fa-money text-blue"></i> <?= $this->lang->line('landing_signup_bank_transfer') ? $this->lang->line('landing_signup_bank_transfer') : 'Bank Transfer' ?>
              </label>

              <!-- Stripe Option -->
               <?php if(!empty($stripe_publishable_key)): ?>
              <label class="btn btn-default" id="btn_stripe" onclick="togglePayment('stripe')">
                <input type="radio" name="payment_method" value="stripe" autocomplete="off"> 
                <i class="fa fa-cc-stripe text-blue"></i> <?= $this->lang->line('landing_signup_stripe') ? $this->lang->line('landing_signup_stripe') : 'Stripe / Card' ?>
              </label>
              <?php endif; ?>
          </div>
      </div>

      <!-- Payment Details Section -->
      <div class="payment-options" id="payment_section">
            
            <!-- Bank Transfer Details -->
            <div id="bank_section" style="display:none;">
                <h4 class="text-blue"><i class="fa fa-bank"></i> <?= $this->lang->line('landing_signup_bank_transfer') ? $this->lang->line('landing_signup_bank_transfer') : 'Bank Transfer' ?></h4>
                
                <?php 
                    $CI =& get_instance();
                    $bank = $CI->db->where('store_id', 1)->get('db_bankdetails')->row();
                ?>
                <div class="bank-details-box" id="bank_details_box" style="display:block;">
                    <?php if($bank): ?>
                        <p><strong><?= $this->lang->line('landing_signup_bank_details') ? $this->lang->line('landing_signup_bank_details') : 'Bank:' ?></strong> <?= $bank->bank_name ?></p>
                        <p><strong><?= $this->lang->line('landing_signup_acc_name') ? $this->lang->line('landing_signup_acc_name') : 'Acc Name:' ?></strong> <?= $bank->holder_name ?></p>
                        <p><strong><?= $this->lang->line('landing_signup_acc_no') ? $this->lang->line('landing_signup_acc_no') : 'Acc No:' ?></strong> <span class="text-large text-bold"><?= $bank->account_number ?></span></p>
                        <p><strong><?= $this->lang->line('landing_signup_branch') ? $this->lang->line('landing_signup_branch') : 'Branch:' ?></strong> <?= $bank->branch_name ?></p>
                    <?php else: ?>
                        <p><?= $this->lang->line('landing_signup_admin_bank') ? $this->lang->line('landing_signup_admin_bank') : 'Contact Admin for Bank Details.' ?></p>
                    <?php endif; ?>
                    
                    <hr>
                    <div class="form-group">
                        <label><?= $this->lang->line('landing_signup_upload_slip') ? $this->lang->line('landing_signup_upload_slip') : 'Upload Payment Slip' ?> <span class="text-danger">*</span></label>
                        <input type="file" name="payment_slip" class="form-control" id="payment_slip">
                        <p class="help-block"><small><?= $this->lang->line('landing_signup_supports') ? $this->lang->line('landing_signup_supports') : 'Supports: JPG, PNG, PDF (Max 2MB)' ?></small></p>
                    </div>
                </div>
            </div>

            <!-- Stripe Details -->
            <div id="stripe_section" style="display:none;">
                <?php if(!empty($stripe_publishable_key)): ?>
                <h4 class="text-blue"><i class="fa fa-credit-card"></i> <?= $this->lang->line('landing_signup_pay_card') ? $this->lang->line('landing_signup_pay_card') : 'Pay with Card' ?></h4>
                <div class="form-group">
                    <label for="card-element"><?= $this->lang->line('landing_signup_credit_card') ? $this->lang->line('landing_signup_credit_card') : 'Credit or debit card' ?></label>
                    <div id="card-element" class="form-control" style="padding-top:10px;">
                        <!-- A Stripe Element will be inserted here. -->
                    </div>
                    <!-- Used to display form errors. -->
                    <div id="card-errors" role="alert" class="text-danger" style="margin-top:10px;"></div>
                </div>
                <?php endif; ?>
            </div>

      </div>

      <div class="row" style="margin-top:20px;">
        <div class="col-xs-8">
          <div class="checkbox icheck">
            <label>
              <input type="checkbox" required> <?= $this->lang->line('landing_signup_i_agree') ? $this->lang->line('landing_signup_i_agree') : 'I agree to the' ?> <a href="#"><?= $this->lang->line('landing_signup_terms') ? $this->lang->line('landing_signup_terms') : 'terms' ?></a>
            </label>
          </div>
        </div>
        <div class="col-xs-4">
          <button type="submit" class="btn btn-primary btn-block btn-flat" id="submit_btn"><?= $this->lang->line('landing_signup_register') ? $this->lang->line('landing_signup_register') : 'Register' ?></button>
        </div>
      </div>
    </form>

    <div class="social-auth-links text-center">
        <a href="<?= base_url('login') ?>" class="text-center"><?= $this->lang->line('landing_signup_already_member') ? $this->lang->line('landing_signup_already_member') : 'I already have a membership' ?></a>
    </div>
  </div>
</div>

<script src="<?php echo $theme_link; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
<script src="<?php echo $theme_link; ?>bootstrap/js/bootstrap.min.js"></script>
<script src="<?php echo $theme_link; ?>plugins/iCheck/icheck.min.js"></script>
<?php if(!empty($stripe_publishable_key)): ?>
<script src="https://js.stripe.com/v3/"></script>
<?php endif; ?>

<script>
  var stripeKey = '<?= $stripe_publishable_key ?? '' ?>';
  var stripe, elements, card;

  $(function () {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%' 
    });

    // Initialize Stripe
    if(stripeKey){
        stripe = Stripe(stripeKey);
        elements = stripe.elements();
        
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
        
        card = elements.create('card', {style: style});
        card.mount('#card-element'); // We will mount it but hidden initially
        
        card.addEventListener('change', function(event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) { displayError.textContent = event.error.message; } 
            else { displayError.textContent = ''; }
        });
    }

    // Intercept Form Submit for Stripe
    $('form').on('submit', function(e) {
        var paymentMethod = $('input[name="payment_method"]:checked').val();
        
        if (paymentMethod === 'stripe' && stripeKey) {
            e.preventDefault();
            
            stripe.createToken(card).then(function(result) {
                if (result.error) {
                    // Inform the user if there was an error.
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                } else {
                    // Send the token to your server.
                    stripeTokenHandler(result.token);
                }
            });
        }
    });

    function stripeTokenHandler(token) {
        // Insert the token ID into the form so it gets submitted to the server
        var form = $('form')[0];
        $(form).append('<input type="hidden" name="stripeToken" value="' + token.id + '">');
         // We also need email just in case, but form already has 'email' field
        $(form).append('<input type="hidden" name="stripeEmail" value="' + $('input[name="email"]').val() + '">');
        
        // Submit the form
        form.submit();
    }

  });

  function checkPackage(){
      var price = $('#package_id').find(':selected').data('price');
      
      if(price > 0){
          // PAID PACKAGE
          $('#trial_toggle_div').fadeIn();
          
          // Hide Trial Option
          $('#btn_trial').hide();
          
          // Show Paid Options
          $('#btn_bank').show();
          $('#btn_stripe').show();
          
          // Auto select first available paid option (Bank or Stripe)
          // Default to Bank for now, or Stripe if preferred. 
          // Let's default to Bank Transfer as it requires less setup/input
          // Or if Stripe is available, user usually prefers card?
          // Let's default to Bank Transfer to be safe.
          $('#btn_bank').trigger('click'); 
          
      } else {
          // FREE PACKAGE
          // If price is 0, it's Free.
          // Hide Paid options
           $('#trial_toggle_div').fadeIn();
           $('#btn_bank').hide();
           $('#btn_stripe').hide();
           
           // Show Trial/Free option
           $('#btn_trial').show();
           $('#btn_trial').trigger('click');
      }
      
      // If no package selected
      if(!$('#package_id').val()){
          $('#trial_toggle_div').hide();
          togglePayment('trial');
      }
  }

  function togglePayment(method){
      // Update Radio Button (since we use buttons, we need to ensure radio is checked)
      $('input[name="payment_method"][value="'+method+'"]').prop('checked', true);
      
      // Update Button Styles (Bootstrap btn-group handles active class, but we trigger click)
      // Actually, if we use onclick on label, bootstrap might toggle 'active' class on the clicked one automatically.
      // But we forced trigger('click') in checkPackage, so that's handled.
      
      $('#payment_section').show(); // Container
      
      if(method == 'trial'){
          $('#payment_section').hide(); // Nothing to show for trial
          $('#bank_section').hide();
          $('#stripe_section').hide();
          
          $('#payment_slip').prop('required', false);
      } 
      else if(method == 'bank_transfer'){
          $('#bank_section').show();
          $('#stripe_section').hide();
          
          $('#payment_slip').prop('required', true);
      }
      else if(method == 'stripe'){
           $('#bank_section').hide();
           $('#stripe_section').show();
           
           $('#payment_slip').prop('required', false);
      }
  }
</script>
<script src="<?php echo $theme_link; ?>js/language.js"></script>
</body>
</html>
