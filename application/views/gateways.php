<!DOCTYPE html>
<html>
   <head>
      <!-- TABLES CSS CODE -->
      <?php include"comman/code_css.php"; ?>
      <style type="text/css">
     
      </style>
      <!-- </copy> -->  
   </head>
   <body class="hold-transition skin-blue sidebar-mini">
      <div class="wrapper">
         <?php include"sidebar.php"; ?>
         <?php
               /*Bank Transfer details*/
               $bank                   = get_super_admin_bank_details();
               $bankCountryId            = $bank->country_id;
               $bankAccountHolderName  = $bank->holder_name;
               $bankName               = $bank->bank_name;
               $bankBranch             = $bank->branch_name;
               $bankCode               = $bank->code; //Ifsc or Bank code
               $bankAccountType        = $bank->account_type;
               $bankAccountNumber      = $bank->account_number;
               $bankOtherDetails       = $bank->other_details;//Text
               $description            = $bank->description;//Text
               $bankStatus            = $bank->status;

            ?>
         <!-- Content Wrapper. Contains page content -->
         <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
               <h1>
                  <?=$page_title;?>
                  <small>Add/Update Customer</small>
               </h1>
               <ol class="breadcrumb">
                  <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
                  <li><a data-toggle='tooltip' title='Do you want Import Customers ?' href="<?php echo $base_url; ?>import/gateways"><i class="fa fa-arrow-circle-o-down "></i> <?= $this->lang->line('import_gateways'); ?></a></li>
                  <li><a href="<?php echo $base_url; ?>gateways"><?= $this->lang->line('gateways_list'); ?></a></li>
                  <li class="active"><?=$page_title;?></li>
               </ol>
            </section>
            <!-- Main content -->
            <section class="content">
               <div class="row">
                  <!-- ********** ALERT MESSAGE START******* -->
                  <?php include"comman/code_flashdata.php"; ?>
                  <!-- ********** ALERT MESSAGE END******* -->
                  <!-- right column -->
                  <div class="col-md-12">
                     <!-- Horizontal Form -->
                     <div class="box">
                  <form class="form-horizontal" id="gateways-form" method="post">
	<?php
     $stripe_rec = $this->db->query("select * from db_stripe where id=1");
     if($stripe_rec->num_rows()>0){
       $stripe_rec = $stripe_rec->row();
       $stripe_secret_key = $stripe_rec->stripe_secret_key;
       $stripe_publishable_key = $stripe_rec->stripe_publishable_key;
     }else{
       $stripe_secret_key = '';
       $stripe_publishable_key = '';
     }
  ?>
  <div class="nav-tabs-custom">
     <ul class="nav nav-tabs">
        <li class="active"><a href="#tab_1" data-toggle="tab"><i class="fa fa-cc-stripe"></i> Stripe</a></li>
        <li><a href="#tab_3" data-toggle="tab"><i class="fa fa-bank"></i> <?= $this->lang->line('bank_details'); ?></a></li>
       
     </ul>
     <div class="tab-content">
        <div class="tab-pane active" id="tab_1">
         <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
        <input type="hidden" id="base_url" value="<?php echo $base_url;; ?>">
        <div class="box-body">
           <div class="row">
              <div class="col-md-5">
                 <!-- Store Code -->
                 <?php 
             echo "<input type='hidden' name='store_id' id='store_id' value='".get_current_store_id()."'>";
           ?>
                 <!-- Store Code end -->
              </div>
           </div>
           <div class="row">
             <div class="callout callout-info">
                 <h4>Stripe</h4>
                   Website Link: <a href='https://stripe.com' target="_blank">https://stripe.com</a><br>
                   
                
               </div>
              <div class="col-md-5">
                 
                 <div class="form-group">
                    <label for="stripe_secret_key" class="col-sm-4 control-label">Secret Key</label>
                    <div class="col-sm-8">
                     <input type="text" class="form-control" id="stripe_secret_key" name="stripe_secret_key" placeholder="" value="<?php print $stripe_secret_key; ?>" >
                     <span id="stripe_secret_key_msg" style="display:none" class="text-danger"></span>
                  </div>
                 </div>

                 <div class="form-group">
                    <label for="stripe_publishable_key" class="col-sm-4 control-label">Publishable Key</label>
                    <div class="col-sm-8">
                     <input type="text" class="form-control" id="stripe_publishable_key" name="stripe_publishable_key" placeholder="" value="<?php print $stripe_publishable_key; ?>" >
                     <span id="stripe_publishable_key_msg" style="display:none" class="text-danger"></span>
                  </div>
                 </div>

              </div>
              <!-- ########### -->
           </div>
        </div>
        </div>


                           
                           <!-- /.tab-pane -->
                           <!-- /.tab-pane -->
                           <div class="tab-pane" id="tab_3">
                           <div class="box-body">
                              
                              <div class="row">
                                
                                 <div class="col-md-5">
                                    <div class="form-group">
                                       <label for="country_id" class="col-sm-4 control-label"><?= $this->lang->line('country'); ?></label>
                                       <div class="col-sm-8">
                                          <select class="form-control select2" id="country_id" name="country_id"  style="width: 100%;"  >
                                             <?= get_country_select_list($bankCountryId,true); ?>
                                          </select>
                                          <span id="country_id_msg" style="display:none" class="text-danger"></span>
                                       </div>
                                    </div>

                                    <div class="form-group">
                                       <label for="account_holder_name" class="col-sm-4 control-label"><?= $this->lang->line('accountHolderName'); ?></label>
                                       <div class="col-sm-8">
                                        <input type="text" class="form-control" id="account_holder_name" name="account_holder_name" placeholder="" value="<?php print $bankAccountHolderName; ?>" >
                                        <span id="account_holder_name_msg" style="display:none" class="text-danger"></span>
                                     </div>
                                    </div>
                                    <div class="form-group">
                                       <label for="bank_name" class="col-sm-4 control-label"><?= $this->lang->line('bankName'); ?></label>
                                       <div class="col-sm-8">
                                        <input type="text" class="form-control" id="bank_name" name="bank_name" placeholder="" value="<?php print $bankName; ?>" >
                                        <span id="bank_name_msg" style="display:none" class="text-danger"></span>
                                     </div>
                                    </div>

                                    <div class="form-group">
                                       <label for="bank_branch" class="col-sm-4 control-label"><?= $this->lang->line('bankBranch'); ?></label>
                                       <div class="col-sm-8">
                                        <input type="text" class="form-control" id="bank_branch" name="bank_branch" placeholder="" value="<?php print $bankBranch; ?>" >
                                        <span id="bank_branch_msg" style="display:none" class="text-danger"></span>
                                     </div>
                                    </div>

                                    <div class="form-group">
                                       <label for="bank_code" class="col-sm-4 control-label"><?= $this->lang->line('bankCode'); ?></label>
                                       <div class="col-sm-8">
                                        <input type="text" class="form-control" id="bank_code" name="bank_code" placeholder="" value="<?php print $bankCode; ?>" >
                                        <span id="bank_code_msg" style="display:none" class="text-danger"></span>
                                     </div>
                                    </div>

                                    <div class="form-group">
                                       <label for="account_type" class="col-sm-4 control-label"><?= $this->lang->line('accountType'); ?></label>
                                       <div class="col-sm-8">
                                          <select class="form-control select2" id="account_type" name="account_type"  style="width: 100%;"  >
                                             <?= get_super_admin_bank_account_type_select_list($bankAccountType); ?>
                                          </select>
                                          <span id="account_type_msg" style="display:none" class="text-danger"></span>
                                       </div>
                                    </div>

                                    <div class="form-group">
                                       <label for="account_number" class="col-sm-4 control-label"><?= $this->lang->line('accountNumber'); ?></label>
                                       <div class="col-sm-8">
                                        <input type="text" class="form-control" id="account_number" name="account_number" placeholder="" value="<?php print $bankAccountNumber; ?>" >
                                        <span id="account_number_msg" style="display:none" class="text-danger"></span>
                                     </div>
                                    </div>
                                    

                                    <div class="form-group">
                                       <label for="other_details" class="col-sm-4 control-label"><?= $this->lang->line('other_details'); ?></label>
                                       <div class="col-sm-8">
                                        <textarea type="text" class="form-control" id="other_details" name="other_details" placeholder="" ><?php print $bankOtherDetails; ?></textarea>
                                        <span id="other_details_msg" style="display:none" class="text-danger"></span>
                                     </div>
                                    </div>

                                    <div class="form-group">
                                       <label for="description" class="col-sm-4 control-label"><?= $this->lang->line('description'); ?></label>
                                       <div class="col-sm-8">
                                        <textarea type="text" class="form-control" id="description" name="description" placeholder=""><?php print $description; ?></textarea>
                                        <span id="description_msg" style="display:none" class="text-danger"></span>
                                     </div>
                                    </div>

                                    <div class="form-group">
                                       <label for="bank_status" class="col-sm-4 control-label"><?= $this->lang->line('status'); ?></label>
                                       <div class="col-sm-8">
                                          <select class="form-control select2" id="bank_status" name="bank_status"  style="width: 100%;"  >
                                             <?= get_super_admin_bank_status_select_list($bankStatus); ?>
                                          </select>
                                          <span id="bank_status_msg" style="display:none" class="text-danger"></span>
                                       </div>
                                    </div>

                                 </div>
                                </div>
                           </div>
                         </div>
                           <!-- /.tab-pane -->
                        </div>
                        <!-- /.tab-content -->

                     </div>
                    <div class="col-sm-8 col-sm-offset-2 text-center">
                           <center>
                           
                            <div class="col-md-3 col-md-offset-3">
                                 <button type="button" id="update" class=" btn btn-block btn-success" title="Save Data">Update</button>
                              </div>
                              <div class="col-sm-3">
                                    <a href="<?=base_url('dashboard');?>">
                                    <button type="button" class="col-sm-3 btn btn-block btn-warning close_btn" title="Go Dashboard">Close</button>
                                    </a>
                                 </div>
                           </center>
                        </div>
                     <!-- /.box -->
                   </form>
                     </div>
                     <!-- /.box -->
                  </div>
                  <!--/.col (right) -->
                </div>

                
            </section>
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
      <script src="<?php echo $theme_link; ?>js/gateways.js"></script>
      <!-- Make sidebar menu hughlighter/selector -->
      <script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
      <script type="text/javascript">
        <?php if(isset($q_id)){ ?>
          $("#store_id").attr('readonly',true);
        <?php }?>
        $("#paypal_sandbox").val('<?= $paypal_sandbox;?>').select2();
        $("#mojo_sandbox").val('<?= $mojo_sandbox;?>').select2();
      </script>
      
   </body>
</html>
