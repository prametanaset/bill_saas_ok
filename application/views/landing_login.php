<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php print $SITE_TITLE; ?> | Welcome</title>
  <link rel='shortcut icon' href='<?php echo $theme_link; ?>images/favicon.ico' />
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="<?php echo $theme_link; ?>bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary-gradient: linear-gradient(135deg, #508fefff 0%, #686fd7ff 100%);
      --accent-color: #3c60cbff;
      --glass-bg: rgba(255, 255, 255, 0.15);
      --glass-border: rgba(255, 255, 255, 0.3);
      --glass-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
      --text-main: #1f2937;
      --text-muted: #6b7280;
    }

    body {
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      margin: 0;
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
      background-attachment: fixed;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--text-main);
    }

    /* Animated Background Background bits */
    .bg-animation {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
      overflow: hidden;
      background: linear-gradient(135deg, #2c89c0ff 0%, #714ee6ff 100%);
    }

    .circle {
      position: absolute;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      animation: float 25s infinite linear;
    }

    @keyframes float {
      0% { transform: translateY(0) rotate(0deg); opacity: 1; border-radius: 0; }
      100% { transform: translateY(-1000px) rotate(720deg); opacity: 0; border-radius: 50%; }
    }

    .landing-container {
      width: 100%;
      max-width: 1100px;
      padding: 20px;
      z-index: 10;
    }

    .main-card {
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      box-shadow: var(--glass-shadow);
      border-radius: 32px;
      display: flex;
      overflow: hidden;
      min-height: 650px;
      transition: transform 0.3s ease;
    }

    .hero-section {
      flex: 1.2;
      padding: 60px;
      background: rgba(255, 255, 255, 0.1);
      display: flex;
      flex-direction: column;
      justify-content: center;
      color: #fff;
      position: relative;
      overflow: hidden;
    }

    .hero-section::before {
      content: '';
      position: absolute;
      top: 0; left: 0; width: 100%; height: 100%;
      background: linear-gradient(135deg, rgba(78, 97, 242, 0.8) 0%, rgba(85, 72, 203, 0.8) 100%);
      z-index: -1;
    }

    .hero-title {
      font-size: 3.8rem;
      font-weight: 800;
      margin-bottom: 24px;
      letter-spacing: -0.02em;
      line-height: 1.1;
    }

    .hero-subtitle {
      font-size: 1.8rem;
      line-height: 1.75;
      margin-bottom: 40px;
      opacity: 0.9;
      font-weight: 300;
    }

    .features-list {
      list-style: none;
      padding: 0;
      margin-bottom: 48px;
    }

    .features-list li {
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      font-size: 1.7rem;
      font-weight: 400;
    }

    .features-list li i {
      margin-right: 18px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 12px;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
    }

    .btn-outline-light {
      background: rgba(255, 255, 255, 0.1);
      border: 2px solid rgba(255, 255, 255, 0.5);
      color: #fff;
      border-radius: 16px;
      padding: 14px 32px;
      font-weight: 500;
      font-size: 1.2rem;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      text-decoration: none;
      display: inline-block;
      backdrop-filter: blur(10px);
    }

    .btn-outline-light:hover {
      background: #fff;
      color: var(--accent-color);
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .login-section {
      flex: 1;
      background: #fff;
      padding: 60px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .login-header {
      text-align: center;
      margin-bottom: 48px;
    }

    .login-header img {
      height: 80px;
      margin-bottom: 24px;
      transition: transform 0.3s ease;
    }

    .login-header h3 {
      font-size: 1.75rem;
      font-weight: 700;
      color: var(--text-main);
      margin-bottom: 8px;
    }

    .login-header p {
      color: var(--text-muted);
      font-size: 1.5rem;
    }

    .form-group label {
      font-size: 1.2rem;
      font-weight: 600;
      color: var(--text-main);
      margin-bottom: 8px;
      display: block;
    }

    .form-control {
      height: 52px;
      background: #f1f5f9;
      border: 2px solid transparent;
      border-radius: 12px;
      padding: 0 16px;
      font-size: 1.7rem;
      transition: all 0.2s ease;
      box-shadow: none !important;
    }

    .form-control:focus {
      background: #fff;
      border-color: var(--accent-color);
      box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
    }

    .btn-primary-custom {
      background: linear-gradient(135deg, #5388dfff 0%, #8d71f2ff 100%);
      color: #fff;
      border: none;
      height: 52px;
      border-radius: 12px;
      font-weight: 700;
      font-size: 1.8rem;
      width: 100%;
      margin-top: 24px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.4);
    }

    .btn-primary-custom:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.5);
      filter: brightness(1.1);
    }

    .auth-links {
      margin-top: 32px;
      text-align: center;
      font-size: 0.95rem;
      color: var(--text-muted);
    }

    .auth-links a {
      color: var(--accent-color);
      font-weight: 600;
      text-decoration: none;
    }

    .auth-links a:hover {
      text-decoration: underline;
    }

    .message-box {
      border-radius: 12px;
      padding: 16px;
      margin-bottom: 24px;
      font-size: 1.3rem;
      font-weight: 500;
      animation: slideDown 0.4s easeOutQuad;
    }

    @keyframes slideDown {
      from { transform: translateY(-10px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    .message-box.error { background: #fef2f2; color: #991b1b; border: 1px solid #fee2e2; }
    .message-box.success { background: #f0fdf4; color: #166534; border: 1px solid #dcfce7; }

    @media (max-width: 992px) {
      .main-card { flex-direction: column; max-width: 500px; margin: auto; }
      .hero-section { min-height: 400px; padding: 40px; }
      .login-section { padding: 40px; }
      .hero-title { font-size: 2.5rem; }
    }

    @media (max-width: 480px) {
      .hero-section { display: none; }
      .login-section { padding: 30px; }
    }
  </style>
</head>
<body>

  <div class="bg-animation">
    <div class="circle" style="width: 80px; height: 80px; left: 10%; bottom: 10%; animation-delay: 0s;"></div>
    <div class="circle" style="width: 120px; height: 120px; left: 20%; bottom: 20%; animation-delay: 2s;"></div>
    <div class="circle" style="width: 50px; height: 50px; left: 35%; bottom: 30%; animation-delay: 4s;"></div>
    <div class="circle" style="width: 150px; height: 150px; left: 50%; bottom: 15%; animation-delay: 6s;"></div>
    <div class="circle" style="width: 70px; height: 70px; left: 70%; bottom: 25%; animation-delay: 8s;"></div>
    <div class="circle" style="width: 100px; height: 100px; left: 85%; bottom: 5%; animation-delay: 10s;"></div>
  </div>

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

  <div class="landing-container">
    <div class="main-card">
      
      <!-- Left Side: Hero / Intro -->
      <div class="hero-section">
        <h1 class="hero-title"><?php print $SITE_TITLE; ?></h1>
        <p class="hero-subtitle"><?= $this->lang->line('landing_manage_business') ? $this->lang->line('landing_manage_business') : 'Manage your business with ease. Powerful, Simple, and Secure.' ?></p>
        
        <ul class="features-list">
          <li><i class="fa fa-check"></i> <?= $this->lang->line('landing_pos') ? $this->lang->line('landing_pos') : 'Point of Sales (POS)' ?></li>
          <li><i class="fa fa-line-chart"></i> <?= $this->lang->line('landing_inventory') ? $this->lang->line('landing_inventory') : 'Inventory Management' ?></li>
          <li><i class="fa fa-calendar-plus-o text-aqua"></i> <?= $this->lang->line('landing_quotation') ? $this->lang->line('landing_quotation') : 'Quotation & Purchase Order' ?></li>
          <li><i class="fa fa-file-text-o"></i> <?= $this->lang->line('landing_invoicing') ? $this->lang->line('landing_invoicing') : 'Invoicing & Reports' ?></li>
          <li><i class="fa fa-users"></i> <?= $this->lang->line('landing_customer') ? $this->lang->line('landing_customer') : 'Customer Management' ?></li>
        </ul>

        <?php if(store_module()){?>
        <div class="plans-container">
          <p><?= $this->lang->line('landing_no_account_yet') ? $this->lang->line('landing_no_account_yet') : "Don't have an account yet ?" ?></p>
          <a href="<?=base_url('register')?>" class="btn-outline-light" style="font-size: 1.3em;">
            <?= $this->lang->line('landing_view_plans') ? $this->lang->line('landing_view_plans') : 'View Plans & Sign Up' ?> <i class="fa fa-arrow-right"></i>
          </a>
        </div>
        <?php } ?>
      </div>

      <!-- Right Side: Login Form -->
      <div class="login-section">
        <div class="login-header">
          <img src="<?php echo base_url(get_site_logo());?>" alt="Logo">
          <h3><?= $this->lang->line('landing_welcome_back') ? $this->lang->line('landing_welcome_back') : 'Welcome Back' ?></h3>
          <p class="text-muted"><?= $this->lang->line('landing_sign_in_account') ? $this->lang->line('landing_sign_in_account') : 'Sign in to your account' ?></p>
        </div>

        <?php if($this->session->flashdata('failed')): ?>
          <div class="message-box error"><?php echo $this->session->flashdata('failed'); ?></div>
          <?php $this->session->unset_userdata('failed'); ?>
        <?php endif; ?>
        
        <?php if($this->session->flashdata('success')): ?>
          <div class="message-box success"><?php echo $this->session->flashdata('success'); ?></div>
          <?php $this->session->unset_userdata('success'); ?>
        <?php endif; ?>

        <?php if($this->session->flashdata('pending')): ?>
          <div class="message-box" style="background:#fdf6ec; border:1px solid #e0c97a; border-radius:10px; padding:14px 16px; margin-bottom:12px; color:#7a3b1e; font-size:14px; line-height:1.6;">
            <strong>ลงทะเบียนสำเร็จ!</strong> การชำระเงินของคุณอยู่ระหว่างดำเนินการ กรุณารอการอนุมัติจากเจ้าหน้าที่<br>
            <strong>ช่องทางติดต่อเจ้าหน้าที่ที่:</strong><br>
            &#128222; เบอร์โทร: <strong>0992480066</strong><br>
            &#9993; อีเมล์: <strong>phusitintech@gmail.com</strong>
          </div>
          <?php $this->session->unset_userdata('pending'); ?>
        <?php endif; ?>

        <form action="<?php echo $base_url; ?>login/verify" method="post" autocomplete="off">
          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
          
            <div class="form-group has-feedback position-relative">
            <label class="text-muted"><?= $this->lang->line('landing_email') ? $this->lang->line('landing_email') : 'Email' ?></label>
            <input type="email" class="form-control" placeholder="<?= $this->lang->line('landing_enter_email') ? $this->lang->line('landing_enter_email') : 'Enter your email' ?>" name="email" required autofocus autocomplete="off"
                   style="background-image: none !important;"> <!-- Prevent chrome autofill ugliness -->
            <span class="fa fa-envelope form-feedback-icon"></span>
          </div>

          <div class="form-group has-feedback position-relative">
            <label class="text-muted"><?= $this->lang->line('landing_password') ? $this->lang->line('landing_password') : 'Password' ?></label>
            <input type="password" class="form-control" placeholder="<?= $this->lang->line('landing_enter_password') ? $this->lang->line('landing_enter_password') : 'Enter your password' ?>" name="pass" required autocomplete="new-password">
            <span class="fa fa-lock form-feedback-icon"></span>
          </div>

          <div class="row" style="margin-bottom: 15px;">
            <div class="col-xs-6">
               <div class="checkbox icheck">
                  <!-- Optional Rememeber me -->
               </div>
            </div>
            <div class="col-xs-6 text-right">
              <a href="<?=base_url('login/forgot_password')?>" class="text-muted" style="font-size: 0.9em;"><?= $this->lang->line('landing_forgot_password') ? $this->lang->line('landing_forgot_password') : 'Forgot Password?' ?></a>
            </div>
          </div>

          <button type="submit" class="btn btn-primary-custom">
            <?= $this->lang->line('landing_sign_in') ? $this->lang->line('landing_sign_in') : 'Sign In' ?>
          </button>
        </form>
            
        <?php if(store_module()){?>
          <div class="auth-links visible-xs">
            <p style="font-size:1.5rem;"><?= $this->lang->line('landing_new_here') ? $this->lang->line('landing_new_here') : 'New here?' ?> <a href="<?=base_url('register')?>"><?= $this->lang->line('landing_create_account') ? $this->lang->line('landing_create_account') : 'Create Account' ?></a></p>
          </div>
        <?php } ?>

        <!-- Demo Info (Same as original but styled simpler) -->
        <?php if(demo_app()){ ?>
          <div class="text-center" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;">
            <p class="text-warning"><small><i class="fa fa-info-circle"></i> Demo Credentials:</small></p>
            <button class="btn btn-xs btn-default admin-fill">Admin Copy</button>
          </div>
        <?php } ?>

      </div>
    
    </div>
  </div>

<!-- jQuery and scripts -->
<script src="<?php echo $theme_link; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
<script src="<?php echo $theme_link; ?>bootstrap/js/bootstrap.min.js"></script>
<script>
  $(function() {
    // Demo helper
    $(".admin-fill").on("click", function() {
      $("input[name='email']").val("admin@example.com");
      $("input[name='pass']").val("123456");
    });
  });
</script>
<script src="<?php echo $theme_link; ?>js/language.js"></script>

</body>
</html>
