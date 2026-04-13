<!DOCTYPE html>
<html>
<head>
<!-- FORM CSS CODE -->
<?php include"comman/code_css.php"; ?>
<style>
  .widget-user-2 .widget-user-header {
    padding: 20px;
    border-top-right-radius: 3px;
    border-top-left-radius: 3px;
  }
  .widget-user-2 .widget-user-username {
    margin-top: 0;
    margin-bottom: 5px;
    font-size: 25px;
    font-weight: 300;
  }
  .widget-user-2 .widget-user-desc {
    margin-top: 0;
  }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  
  <?php include"sidebar.php"; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <?=$page_title;?>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active"><?=$page_title;?></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <?php if ($this->session->flashdata('success') && $this->session->flashdata('success') !== null): ?>
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h4><i class="icon fa fa-check"></i> สำเร็จ!</h4>
          <?= $this->session->flashdata('success') ?>
        </div>
      <?php endif; ?>
      <?php if ($this->session->flashdata('localized_success')): ?>
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h4><i class="icon fa fa-check"></i> สำเร็จ!</h4>
          <?= $this->session->flashdata('localized_success') ?>
        </div>
      <?php endif; ?>
      <?php if ($this->session->flashdata('warning')): ?>
        <div class="alert alert-warning alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h4><i class="icon fa fa-clock-o"></i> รอดำเนินการ</h4>
          <?= $this->session->flashdata('warning') ?>
        </div>
      <?php endif; ?>
      <?php if ($this->session->flashdata('failed')): ?>
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h4><i class="icon fa fa-ban"></i> เกิดข้อผิดพลาด!</h4>
          <?= $this->session->flashdata('failed') ?>
        </div>
      <?php endif; ?>
      <div class="row">
        <!-- Current Subscription Info -->
        <div class="col-md-12">
           <div class="box box-primary">
              <div class="box-header with-border">
                <h3 class="box-title">สถานะแพ็กเกจปัจจุบัน (Current Package Status)</h3>
              </div>
              <div class="box-body">
                <div class="row">
                  <div class="col-md-6">
                    <table class="table table-bordered">
                      <tr>
                        <th style="width: 200px;">แพ็กเกจปัจจุบัน:</th>
                        <td><span class="label label-success" style="font-size: 14px;"><?= isset($subscription) ? ($subscription->subscription_name ?? $subscription->package_name ?? 'N/A') : 'N/A' ?></span></td>
                      </tr>
                      <tr>
                        <th>วันเริ่มต้น:</th>
                        <td><?= (isset($subscription) && isset($subscription->subscription_date)) ? show_date($subscription->subscription_date) : '-' ?></td>
                      </tr>
                      <tr>
                        <th>วันหมดอายุ:</th>
                        <td><span class="text-danger"><b><?= (isset($subscription) && isset($subscription->expire_date)) ? show_date($subscription->expire_date) : '-' ?></b></span></td>
                      </tr>
                    </table>
                  </div>
                  <div class="col-md-6">
                     <table class="table table-bordered">
                        <tr>
                          <th>จำนวนคลังสินค้า:</th>
                          <td><?= (!isset($subscription)) ? '-' : (($subscription->max_warehouses == -1) ? 'ไม่จำกัด' : $subscription->max_warehouses) ?></td>
                        </tr>
                        <tr>
                          <th>จำนวนผู้ใช้สูงสุด:</th>
                          <td><?= (!isset($subscription)) ? '-' : (($subscription->max_users == -1) ? 'ไม่จำกัด' : $subscription->max_users) ?></td>
                        </tr>
                        <tr>
                          <th>จำนวนสินค้าสูงสุด:</th>
                          <td><?= (!isset($subscription)) ? '-' : (($subscription->max_items == -1) ? 'ไม่จำกัด' : $subscription->max_items) ?></td>
                        </tr>
                        <tr>
                          <th>จำนวนใบเสร็จ:</th>
                          <td><?= (!isset($subscription)) ? '-' : (($subscription->max_invoices == -1) ? 'ไม่จำกัด' : $subscription->max_invoices) ?></td>
                        </tr>
                        <tr>
                          <th>ออก e-TAX Invoice :</th>
                          <td><?= (!isset($subscription)) ? '-' : ((isset($subscription->max_etax_emails) && $subscription->max_etax_emails == -1) ? 'ไม่จำกัด' : ($subscription->max_etax_emails ?? '-')) ?></td>
                        </tr>
                     </table>
                  </div>
                </div>
              </div>
           </div>
        </div>

        <!-- Available Packages -->
        <div class="col-md-12">
            <h3 class="page-header">เลือกแพ็กเกจเพื่อต่ออายุหรือ อัปเกรด (Select a Plan to Renew or Upgrade)</h3>
            <div class="row">
                <?php if(!empty($packages)): ?>
                    <?php foreach($packages as $pkg): ?>
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <div class="box box-widget widget-user-2" style="border: 1px solid #d2d6de; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                <!-- Add the bg color to the header using any of the bg-* classes -->
                                <div class="widget-user-header bg-blue">
                                    <div class="widget-user-image">
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
                                        <li><a href="#">จำนวนใบเสร็จ<span class="pull-right badge bg-yellow"><b><?= ($pkg['max_invoices'] == '∞' || $pkg['max_invoices'] == -1) ? 'ไม่จำกัด' : number_format($pkg['max_invoices']) ?></b></span></a></li>
                                        <li><a href="#">ออก e-TAX Invoice / เดือน <span class="pull-right badge bg-maroon"><b><?php $etax = $pkg['max_etax_emails'] ?? 0; echo ($etax === '∞' || $etax === -1 || $etax == -1) ? 'ไม่จำกัด' : number_format($etax); ?></b></span></a></li>
                                        <li><a href="#">ระยะเวลาทดลองใช้ <span class="pull-right badge bg-purple"><?= $pkg['trial_days'] ?> วัน</span></a></li>
                                    </ul>
                                </div>
                                <div class="box-body text-center">
                                     <?php 
                                        $is_free_pkg = (strtoupper($pkg['package_type'] ?? '') == 'FREE') || ((float)$pkg['monthly_price'] == 0 && (float)$pkg['annual_price'] == 0);
                                        // Check if this specific package is free AND user has used a free package before
                                        if(isset($free_package_used) && $free_package_used && $is_free_pkg): 
                                     ?>
                                         <button type="button" class="btn btn-default btn-block btn-flat" disabled><b>ใช้แล้ว</b></button>
                                     <?php else: ?>
                                         <a href="<?= base_url('subscription/renew_plan/'.$pkg['id']) ?>" class="btn btn-primary btn-block btn-flat"><b>อัพเกรด ทันที</b></a>
                                     <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-md-12 text-center">
                        <div class="alert alert-warning">ไม่พบข้อมูลแพ็กเกจ กรุณาติดต่อผู้ดูแลระบบ.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
      </div>
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <?php $this->load->view('footer'); ?>
  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>

</div>
<!-- ./wrapper -->

<!-- TABLES CODE -->
<?php include"comman/code_js.php"; ?>

</body>
</html>
