<!DOCTYPE html>
<html>
   
   <head>
  <!-- TABLES CSS CODE -->
  <?php include"comman/code_css.php"; ?>
  <!-- </copy> -->  
  </head>

   <body class="hold-transition skin-blue sidebar-mini">
      <div class="wrapper">
         <?php include"sidebar.php"; ?>
         <!-- Content Wrapper. Contains page content -->
         <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
               <h1>
                  <?= $this->lang->line('site_settings'); ?>
                  <small><?= $this->lang->line('add_or_update'); ?> <?= $this->lang->line('site_settings'); ?></small>
               </h1>
               <ol class="breadcrumb">
                  <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
                  <li class="active">ตั้งค่าระบบ</li>
               </ol>
            </section>
            
            <!-- Main content -->
  <?= form_open('#', array('class' => 'form-horizontal', 'id' => 'site-form', 'enctype'=>'multipart/form-data', 'method'=>'POST'));?>
            <section class="content">
               <div class="row">
                  <!-- ********** ALERT MESSAGE START******* -->
                <?php include"comman/code_flashdata.php"; ?>
                  <!-- ********** ALERT MESSAGE END******* -->

                  <div class="col-md-12">
                     <!-- Custom Tabs -->
                     <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                           <li class="active"><a href="#tab_1" data-toggle="tab"><?= $this->lang->line('site'); ?></a></li>
                           <li><a href="#tab_2" data-toggle="tab"><?= $this->lang->line('system'); ?></a></li>
                           
                        </ul>
                        <div class="tab-content">
                           <div class="tab-pane active" id="tab_1">
                              <div class="row">
                                 <!-- right column -->
                                 <div class="col-md-12">
                                    <!-- form start -->
                                       <input type="hidden" id="base_url" value="<?php echo $base_url;; ?>">
                                       <div class="box-body">
                                          <div class="row">
                                             <div class="col-md-5">
                                                <div class="form-group">
                                                   <label for="site_name" class="col-sm-4 control-label"><?= $this->lang->line('site_name'); ?><label class="text-danger">*</label></label>
                                                   <div class="col-sm-8">
                                                      <input type="text" class="form-control" id="site_name" name="site_name" placeholder="" onkeyup="shift_cursor(event,'mobile')" value="<?php print $site_name; ?>" >
                                                      <span id="site_name_msg" style="display:none" class="text-danger"></span>
                                                   </div>
                                                </div>
                                             </div>
                                             <div class="col-md-5">
                                                <div class="form-group">
                                                   <label for="address" class="col-sm-4 control-label"><?= $this->lang->line('site_logo'); ?></label>
                                                   <div class="col-sm-8">
                                                      <input type="file" id="logo" name="logo">
                                                      <span id="logo_msg" style="display:block;" class="text-danger">Max Width/Height: 300px * 300px & Size: 300px </span>
                                                   </div>
                                                </div>
                                                <?php 
                                                if(empty($logo)){
                                                  $logo = base_url('uploads/no_logo/nologo.png');
                                                }
                                                else{
                                                  $logo = base_url($logo);
                                                }
                                                ?>
                                                <div class="form-group">
                                                   <div class="col-sm-8 col-sm-offset-4">
                                                      <img class='img-responsive' style='border:3px solid #d2d6de;' src="<?php echo $logo;?>">
                                                   </div>
                                                </div>
                                             </div>
                                             <!-- ########### -->
                                          </div>
                                       </div>
                                       <!-- /.box-body -->
                                       <!-- /.box-footer -->
                                    
                                 </div>
                                 <!--/.col (right) -->
                              </div>
                              <!-- /.row -->
                           </div>
                           <!-- /.tab-pane -->
                           <div class="tab-pane" id="tab_2">
                              <div class="row">
                                 <div class="col-md-12">
                                    <div class="box-body">
                                       <div class="row">
                                          <div class="col-md-5">
                                             <div class="form-group">
                                                <label for="timezone" class="col-sm-4 control-label"><?= $this->lang->line('timezone'); ?><label class="text-danger">*</label> </label>
                                                <div class="col-sm-8">
                                                   <select class="form-control select2" id="timezone" name="timezone" style="width: 100%;">
                                                      <?php
                                                      if (!empty($timezone_list)) {
                                                         foreach ($timezone_list as $res1) {
                                                            $selected = ((isset($timezone) && !empty($timezone)) && trim($timezone) == trim($res1->timezone)) ? 'selected' : '';
                                                            echo "<option " . $selected . " value='" . $res1->timezone . "'>" . $res1->timezone . "</option>";
                                                         }
                                                      } else {
                                                         echo '<option value="">ไม่มีข้อมูล</option>';
                                                      }
                                                      ?>
                                                   </select>
                                                   <span id="timezone_msg" style="display:none" class="text-danger"></span>
                                                </div>
                                             </div>
                                             <div class="form-group">
                                                <label for="date_format" class="col-sm-4 control-label"><?= $this->lang->line('date_format'); ?><label class="text-danger">*</label> </label>
                                                <div class="col-sm-8">
                                                   <select class="form-control select2" id="date_format" name="date_format" style="width: 100%;">
                                                      <option value="dd-mm-yyyy" <?= ($date_format == 'dd-mm-yyyy') ? 'selected' : ''; ?>>dd-mm-yyyy</option>
                                                      <option value="mm/dd/yyyy" <?= ($date_format == 'mm/dd/yyyy') ? 'selected' : ''; ?>>mm/dd/yyyy</option>
                                                   </select>
                                                   <span id="date_format_msg" style="display:none" class="text-danger"></span>
                                                </div>
                                             </div>
                                             <div class="form-group">
                                                <label for="time_format" class="col-sm-4 control-label"><?= $this->lang->line('time_format'); ?><label class="text-danger">*</label> </label>
                                                <div class="col-sm-8">
                                                   <select class="form-control select2" id="time_format" name="time_format" style="width: 100%;">
                                                      <option value="12" <?= ($time_format == '12') ? 'selected' : ''; ?>>12 ชั่วโมง</option>
                                                      <option value="24" <?= ($time_format == '24') ? 'selected' : ''; ?>>24 ชั่วโมง</option>
                                                   </select>
                                                   <span id="time_format_msg" style="display:none" class="text-danger"></span>
                                                </div>
                                             </div>
                                             <div class="form-group">
                                                <label for="currency" class="col-sm-4 control-label"><?= $this->lang->line('currency'); ?><label class="text-danger">*</label> </label>
                                                <div class="col-sm-8">
                                                   <select class="form-control select2" id="currency_id" name="currency_id" style="width: 100%;">
                                                      <?php
                                                      if (!empty($currency_list)) {
                                                         foreach ($currency_list as $res1) {
                                                            $selected = ((isset($currency_id) && !empty($currency_id)) && $currency_id == $res1->id) ? 'selected' : '';
                                                            echo "<option " . $selected . " value='" . $res1->id . "'>" . $res1->currency_name . ' ' . $res1->currency_code . ' (' . $res1->currency . ")</option>";
                                                         }
                                                      } else {
                                                         echo '<option value="">ไม่มีข้อมูล</option>';
                                                      }
                                                      ?>
                                                   </select>
                                                   <span id="currency_msg" style="display:none" class="text-danger"></span>
                                                </div>
                                             </div>
                                             <div class="form-group">
                                                <label for="currency_placement" class="col-sm-4 control-label"><?= $this->lang->line('currency_symbol_placement'); ?><label class="text-danger">*</label> </label>
                                                <div class="col-sm-8">
                                                   <select class="form-control select2" id="currency_placement" name="currency_placement" style="width: 100%;">
                                                      <option value="Right" <?= ($currency_placement == 'Right') ? 'selected' : ''; ?>>หลังจำนวนเงิน</option>
                                                      <option value="Left" <?= ($currency_placement == 'Left') ? 'selected' : ''; ?>>ก่อนจำนวนเงิน</option>
                                                   </select>
                                                   <span id="currency_placement_msg" style="display:none" class="text-danger"></span>
                                                </div>
                                             </div>
                                             <input type="hidden" name="decimals" value="<?= $decimals; ?>">
    
                                              <div class="form-group">
                                                 <label for="qty_decimals" class="col-sm-4 control-label"><?= $this->lang->line('qty_decimals'); ?><label class="text-danger">*</label> </label>
                                                 <div class="col-sm-8">
                                                    <select class="form-control select2" id="qty_decimals" name="qty_decimals" style="width: 100%;">
                                                       <?php for($i=0;$i<=4;$i++){ 
                                                          $selected = ($qty_decimals == $i) ? 'selected' : '';
                                                          echo "<option value='$i' $selected>$i</option>";
                                                       } ?>
                                                    </select>
                                                    <span id="qty_decimals_msg" style="display:none" class="text-danger"></span>
                                                 </div>
                                              </div>
                                          </div>
                                          
                                          <div class="col-md-5">
                                             <div class="form-group">
                                                 <label for="language_id" class="col-sm-4 control-label"><?= $this->lang->line('language'); ?><label class="text-danger">*</label> </label>
                                                 <div class="col-sm-8">
                                                    <select class="form-control select2" id="language_id" name="language_id" style="width: 100%;">
                                                       <?php
                                                       if (!empty($language_list)) {
                                                          foreach ($language_list as $res1) {
                                                             $selected = ($res1->id == $language_id) ? 'selected' : '';
                                                             echo "<option " . $selected . " value='" . $res1->id . "'>" . $res1->language . "</option>";
                                                          }
                                                       } else {
                                                          echo '<option value="">ไม่มีข้อมูล</option>';
                                                       }
                                                       ?>
                                                    </select>
                                                    <span id="language_id_msg" style="display:none" class="text-danger"></span>
                                                 </div>
                                              </div>
                                              
                                              <div class="form-group">
                                                 <label for="round_off" class="col-sm-4 control-label"><?= $this->lang->line('round_off'); ?></label>
                                                 <div class="col-sm-8">
                                                    <input type="checkbox" <?= ($round_off==1) ? 'checked' : ''; ?> class="form-control" id="round_off" name="round_off" value="1">
                                                    <span id="round_off_msg" style="display:none" class="text-danger"></span>
                                                 </div>
                                              </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <!-- /.tab-content -->
                     </div>
                     <!-- nav-tabs-custom -->
                     <div>
                        <div class="col-sm-8 col-sm-offset-2 text-center">
                           <center>
                              <?php
                                 if($site_name!=""){
                                      $btn_name="อัพเดท";
                                      $btn_id="update";
                                      ?>
                              <input type="hidden" name="q_id" id="q_id" value="<?php echo $q_id;?>"/>
                              <?php
                                 }
                                 else{
                                     $btn_name="บันทึก";
                                     $btn_id="save";
                                 }
                                 
                                 ?>
                              <div class="col-md-3 col-md-offset-3">
                                 <button type="button" id="<?php echo $btn_id;?>" class=" btn btn-block btn-primary" title="Save Data"><?php echo $btn_name;?></button>
                              </div>
                              <div class="col-sm-3">
                                <a href="<?=base_url('dashboard');?>">
                                 <button type="button" class="col-sm-3 btn btn-block btn-info close_btn" title="Go Dashboard">ปิด</button>
                               </a>
                              </div>
                           </center>
                        </div>
                     </div>
                  </div>
                  <!-- /.col -->
               </div>
               <!-- /.row -->
            </section>
            <!-- /.content -->
            <?= form_close(); ?>
         </div>
         <!-- /.content-wrapper -->
         <?php include"footer.php"; ?>
         <!-- Add the sidebar's background. This div must be placed
            immediately after the control sidebar -->
         <div class="control-sidebar-bg"></div>
      </div>
      <!-- ./wrapper -->
      
      <?php include'comman/code_js_language.php'; ?>

      <!-- SOUND CODE -->
      <?php include"comman/code_js_sound.php"; ?>
      <!-- TABLES CODE -->
      <?php include"comman/code_js.php"; ?>

      <script type="text/javascript">
         $(document).submit(function(event) {
           event.preventDefault();
           if($("#update").length){
             $("#update").trigger('click');
           }
         });
      </script>
      <script src="<?php echo $theme_link; ?>js/site-settings.js"></script>
     
 
      <!-- Make sidebar menu hughlighter/selector -->
      <script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
   </body>
</html>
