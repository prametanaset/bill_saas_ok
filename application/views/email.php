<!DOCTYPE html>
<html>
<head>
<!-- FORM CSS CODE -->
<?php include"comman/code_css.php"; ?>
<!-- </copy> -->  
<link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">  
<style>
  .email-recipient-row {
    position: relative;
    display: flex;
    align-items: center;
  }
  .email-recipient-row input {
    padding-right: 70px;
  }
  .cc-bcc-toggles {
    position: absolute;
    right: 10px;
    z-index: 5;
  }
  .cc-bcc-toggles a {
    cursor: pointer;
    margin-left: 5px;
    color: #777;
    font-size: 13px;
    text-decoration: none;
  }
  .cc-bcc-toggles a:hover {
    color: #337ab7;
    text-decoration: underline;
  }
  .cc-bcc-row {
    display: none;
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
       <?= $this->lang->line('send_email'); ?>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active"><?= $this->lang->line('send_email'); ?></li>
      </ol>
    </section>

    <!-- Main content -->
    <form role="form" id="email-form" method="post" action='<?=base_url('email/send_message')?>' onkeypress="return event.keyCode != 13;" enctype="multipart/form-data">
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
    <input type="hidden" id="base_url" value="<?php echo $base_url;; ?>">
    <section class="content">
      <div class="row">
        <!-- ********** ALERT MESSAGE START******* -->
          <?php include"comman/code_flashdata.php"; ?>
            <!-- ********** ALERT MESSAGE END******* -->
          <?php if(empty($smtp_configured)): ?>
          <div class="col-md-12">
            <div class="alert alert-warning">
              <i class="fa fa-exclamation-triangle"></i>
              <strong>ยังไม่ได้ตั้งค่า SMTP</strong> — กรุณาตั้งค่า SMTP ก่อนส่งอีเมล์
              <a href="<?= base_url('smtp') ?>" class="alert-link">
                <i class="fa fa-cog"></i> ไปที่ตั้งค่า SMTP
              </a>
            </div>
          </div>
          <?php endif; ?>
        <div class="col-md-9">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title"><?= $this->lang->line('send_email'); ?></h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <select class="form-control select2" name="document_type" id="document_type" style="width: 100%;">
                      <option value="">เลือกประเภทเอกสาร</option>
                      <option value="Sales">Sales (ขาย)</option>
                      <option value="Sales Return">Sales Credit Note (ลดหนี้/คืนสินค้า)</option>
                      <option value="Sales Debit">Sales Debit Note (เพิ่มหนี้)</option>
                      <option value="Quotation">Quotation (ใบเสนอราคา)</option>
                      <option value="Receipt">Receipt (ใบเสร็จรับเงิน)</option>
                      <option value="Invoice">Invoice (ใบแจ้งหนี้)</option>
                      <option value="Purchase">Purchase(ซื้อ)</option>
                      <option value="Purchase Return">Purchase Return (คืนสินค้าที่ซื้อ)</option>
                   <!--   <option value="Expense">Expense (ค่าใช้จ่าย)</option>-->
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <select class="form-control select2" name="document_id" id="document_id" style="width: 100%;">
                      <option value="">เลือกเอกสารเลขที่ </option>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <select class="form-control select2" name="template_id" id="template_id" style="width: 100%;">
                      <option value="">Template (เลือกเทมเพลต)</option>
                      <?php
                        if(!empty($templates_list)){
                          foreach ($templates_list as $res) {
                            echo "<option value='".$res->id."'>".$res->template_name."</option>";
                          }
                        }
                      ?>
                    </select>
                  </div>
                </div>
              </div>
              <div class="form-group email-recipient-row">
                <input class="form-control" name='email_to' id='email_to' placeholder="To:">
                <div class="cc-bcc-toggles">
                  <a id="toggle-cc">Cc</a>
                  <a id="toggle-bcc">Bcc</a>
                </div>
                <span id="email_to_msg" style="display:none" class="text-danger"></span>
              </div>
              <div class="form-group cc-bcc-row" id="cc-row">
                <input class="form-control" name='email_cc' id='email_cc' placeholder="Cc:">
              </div>
              <div class="form-group cc-bcc-row" id="bcc-row">
                <input class="form-control" name='email_bcc' id='email_bcc' placeholder="Bcc:">
              </div>
              <div class="form-group">
                <input class="form-control" name='email_subject' id='email_subject' placeholder="Subject:">
                <span id="email_subject_msg" style="display:none" class="text-danger"></span>
              </div>
              <div class="form-group">
                    <textarea id="compose-textarea" name='email_content'  class="form-control" style="height: 300px"></textarea>
              </div>
              <div class="form-group">
                <div class="btn btn-default btn-file">
                  <i class="fa fa-paperclip"></i> ไฟล์แนบ
                  <input type="file" name="attachment">
                </div>
                <p class="help-block">Max. 3.0 MB</p>
                <div id="attachment-info" style="margin-top: 10px;"></div>
              </div>
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
              <div class="pull-right">
                <button type="button" class="btn btn-primary send"><i class="fa fa-envelope-o"></i> ส่ง</button>
              </div>
              <button type="reset" class="btn btn-default"><i class="fa fa-times"></i> ยกเลิก</button>
            </div>
            <!-- /.box-footer -->
          </div>
          <!-- /. box -->
        </div>
        <!-- right column -->
       
        <!--/.col (right) -->
      </div>
      <!-- /.row -->

    </section>
  </form>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

 <?php include"footer.php"; ?>


  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->
<!-- SOUND CODE -->
<?php include"comman/code_js_sound.php"; ?>
<!-- TABLES CODE -->
<?php include"comman/code_js.php"; ?>

<script src="<?php echo $theme_link; ?>js/email.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo $theme_link; ?>plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
<!-- Make sidebar menu hughlighter/selector -->
<!-- Make sidebar menu hughlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
<script type="text/javascript">
  var csrf_token_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_token_value = '<?php echo $this->security->get_csrf_hash(); ?>';
</script>
<script>
  $(function () {
    //Add text editor
    $("#compose-textarea").wysihtml5();
    //Initialize Select2 Elements
    $(".select2").select2();
  });
</script>
</body>
</html>
