<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>การสมัครสมาชิกหมดอายุ</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="<?php echo $theme_link; ?>bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo $theme_link; ?>dist/css/AdminLTE.min.css">
</head>
<body class="hold-transition lockscreen" style="height:auto; min-height:100vh;">
<!-- Automatic element centering -->
<div class="container" style="margin-top:50px;">
  <div class="text-center" style="margin-bottom:30px;">
    <a href="#" style="font-size: 30px; color:#444;"><b>การสมัครสมาชิก</b>หมดอายุ</a>
    <h2 class="text-danger" style="margin-top:0;">การสมัครสมาชิกหมดอายุ</h2>
  </div>

  <div class="help-block text-center">
  	<div class="alert alert-danger alert-dismissible" style="display:inline-block; min-width:50%;">
        <h4><i class="icon fa fa-ban"></i> แจ้งเตือน!</h4>
        การสมัครสมาชิกของคุณหมดอายุแล้ว! กรุณาต่ออายุเพื่อใช้งานต่อ
    </div>
  </div>
  
  <div class="text-center" style="margin-bottom:30px;">
      <h3>เลือกแพ็กเกจเพื่อต่ออายุ</h3>
  </div>

  <div class="row">
    <?php if(!empty($packages)): ?>
        <?php foreach($packages as $pkg): ?>
            <?php 
                // Skip if plan is hidden or weird (optional check)
            ?>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="box box-widget widget-user-2" style="border: 1px solid #d2d6de; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <!-- Add the bg color to the header using any of the bg-* classes -->
                    <div class="widget-user-header bg-blue">
                        <div class="widget-user-image">
                            <!-- Optional: Icon or Image -->
                            <i class="fa fa-cube fa-3x pull-left" style="color:rgba(255,255,255,0.8)"></i>
                        </div>
                        <!-- /.widget-user-image -->
                        <h3 class="widget-user-username" style="margin-left: 0; padding-left:60px; font-weight:bold;"><?= $pkg['package_name'] ?></h3>
                        <h5 class="widget-user-desc" style="margin-left: 0; padding-left:60px; color: #ffffff; font-weight: bold; font-size: 16px;">
                            <?php if((float)$pkg['monthly_price'] > 0): ?>
                                <?= number_format($pkg['monthly_price'], 2) ?> / เดือน
                            <?php elseif((float)$pkg['annual_price'] == 0): ?>
                                ฟรี
                            <?php endif; ?>
                            
                            <?php if((float)$pkg['annual_price'] > 0): ?>
                                <?php if((float)$pkg['monthly_price'] > 0) echo "<br>"; ?>
                                <?= number_format($pkg['annual_price'], 2) ?> / ปี
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="box-footer no-padding">
                        <ul class="nav nav-stacked">
                            <li><a href="#">คลังสินค้า <span class="pull-right badge bg-blue"><b><?= ($pkg['max_warehouses'] == '∞' || $pkg['max_warehouses'] == -1) ? 'ไม่จำกัด' : number_format($pkg['max_warehouses']) ?></b></span></a></li>
                            <li><a href="#">ผู้ใช้งาน <span class="pull-right badge bg-aqua"><b><?= ($pkg['max_users'] == '∞' || $pkg['max_users'] == -1) ? 'ไม่จำกัด' : number_format($pkg['max_users']) ?></b></span></a></li>
                            <li><a href="#">จำนวนสินค้าสูงสุด <span class="pull-right badge bg-green"><b><?= ($pkg['max_items'] == '∞' || $pkg['max_items'] == -1) ? 'ไม่จำกัด' : number_format($pkg['max_items']) ?></b></span></a></li>
                            <li><a href="#">จำนวนใบเสร็จ <span class="pull-right badge bg-yellow"><b><?= ($pkg['max_invoices'] == '∞' || $pkg['max_invoices'] == -1) ? 'ไม่จำกัด' : number_format($pkg['max_invoices']) ?></b></span></a></li>
                            <li><a href="#">e-TAX Invoice <span class="pull-right badge bg-maroon"><b><?= ($pkg['max_etax_emails'] == '∞' || $pkg['max_etax_emails'] == -1) ? 'ไม่จำกัด' : number_format($pkg['max_etax_emails']) ?></b></span></a></li>
                            <li><a href="#">ระยะเวลาทดลองใช้ <span class="pull-right badge bg-purple"><?= $pkg['trial_days'] ?> วัน</span></a></li>
                            <li><a href="#">วันหมดอายุ <span class="pull-right badge bg-red">
                                <?php 
                                    $trial_days = isset($pkg['trial_days']) ? (int)$pkg['trial_days'] : 0;
                                    if((float)$pkg['monthly_price'] == 0 && (float)$pkg['annual_price'] == 0 && $trial_days > 0) {
                                        $duration = '+' . $trial_days . ' days';
                                    } else {
                                        $duration = (strpos((string)$pkg['plan_type'], 'Annually') !== false) ? '+1 year' : '+1 month';
                                    }
                                    echo date('d-m-Y', strtotime($duration)); 
                                ?>
                            </span></a></li>
                        </ul>
                    </div>
                    <div class="box-body text-center">
                         <?php 
                            $is_free_pkg = (strtoupper($pkg['package_type']) == 'FREE') || ((float)$pkg['monthly_price'] == 0 && (float)$pkg['annual_price'] == 0);
                            // Check if this specific package is free AND user has used a free package before
                            if(isset($free_package_used) && $free_package_used && $is_free_pkg): 
                         ?>
                             <button type="button" class="btn btn-default btn-block btn-flat" disabled><b>ใช้แล้ว</b></button>
                         <?php else: ?>
                             <a href="<?= base_url('subscription/renew_plan/'.$pkg['id']) ?>" class="btn btn-primary btn-block btn-flat"><b>ต่ออายุทันที</b></a>
                         <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-md-12 text-center">
            <div class="alert alert-warning">ไม่พบแพ็กเกจที่เลือกได้ โปรดติดต่อเจ้าหน้าที่ดูแลระบบ</div>
        </div>
    <?php endif; ?>
  </div>

  <div class="text-center" style="margin-top:20px; margin-bottom:50px;">
    <a href="<?= base_url('login') ?>" class="btn btn-link">กลับไปหน้าเข้าสู่ระบบ</a>
    <br><br>
    ลิขสิทธิ์ &copy; <?= date('Y') ?> <b><a href="#" class="text-black"><?= $SITE_TITLE ?></a></b><br>
    สงวนลิขสิทธิ์
  </div>
</div>
</body>
</html>
