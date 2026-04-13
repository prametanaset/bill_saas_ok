<!DOCTYPE html>
<html>
   <head>
  <!-- TABLES CSS CODE -->
  <?php $this->load->view('comman/code_css.php');?>
  <!-- </copy> -->  
  </head>
   <body class="hold-transition skin-blue  sidebar-mini">
      <!-- **********************MODALS***************** -->
      <?php $this->load->view('modals/modal_brand'); ?>
      <?php $this->load->view('modals/modal_category'); ?>
      <?php $this->load->view('modals/modal_unit'); ?>
      <!-- **********************MODALS END***************** -->

      <div class="wrapper">
      <?php $this->load->view('sidebar');?>
      <?php
         if(!isset($item_name)){
         $item_name=$sku=$opening_stock=$brand_id=$category_id=$gst_percentage=$tax_type=
         $sales_price=$purchase_price=$profit_margin=$unit_id=$price=$alert_qty=$lot_number=$store_id="";
         $stock = 0;
         $sac ='';
         $expire_date ='';
         $seller_points =0;
         $custom_barcode ='';
         $description ='';
         $hsn ='';
         $discount='';

         $tax_id ="";
         $discount_type='Percentage';

         $item_code = get_init_code('item');
         $hide_in_pos = 0;

         }
         $new_opening_stock ='';
         $CI =& get_instance();
         ?>
      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
         <!-- Content Header (Page header) -->
         <section class="content-header">
            <h1>
               <?= $page_title;?>
               <small>Add/Update Services</small>
            </h1>
            <ol class="breadcrumb">
               <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i>Home</a></li>
               <li><a href="<?php echo $base_url; ?>items"><?= $this->lang->line('items_list'); ?></a></li>
               <li class="active"><?= $page_title;?></li>
            </ol>
         </section>
         <!-- Main content -->
         <section class="content">
            <div class="row">
               <!-- ********** ALERT MESSAGE START******* -->
               <?php $this->load->view('comman/code_flashdata');?>
               <!-- ********** ALERT MESSAGE END******* -->
               <!-- right column -->
               <div class="col-md-12">
                  <!-- Horizontal Form -->
                  <div class="box box-primary ">
                     
                      <?= form_open('#', array('class' => 'form', 'id' => 'items-form', 'enctype'=>'multipart/form-data', 'method'=>'POST'));?>
                        <input type="hidden" id="base_url" value="<?php echo $base_url;; ?>">
                        <div class="box-body">

                          <div class="row">
                             <!-- Store Code -->
                              <?php /*if(store_module() && is_admin()) {$this->load->view('store/store_code',array('show_store_select_box_1'=>true,'store_id'=>$store_id)); }else{*/
                                echo "<input type='hidden' name='store_id' id='store_id' value='".get_current_store_id()."'>";
                              /*}*/ ?>
                              <!-- Store Code end -->
                          </div>

                          <div class="row">
                              <div class="form-group col-md-4">
                                 <label for="item_code"><?= $this->lang->line('item_code'); ?><span class="text-danger">*</span></label>
                                 <input type="text" class="form-control" id="item_code" name="item_code" placeholder="" value="<?php print $item_code; ?>" >
                                 <span id="item_code_msg" style="display:none" class="text-danger"></span>
                              </div>
                           </div>

                           <div class="row">
                              <div class="form-group col-md-4">
                                 <label for="item_name"><?= $this->lang->line('item_name'); ?><span class="text-danger">*</span></label>
                                 <input type="text" class="form-control" id="item_name" name="item_name" placeholder="" value="<?php print $item_name; ?>" >
                                 <span id="item_name_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="brand_id"><?= $this->lang->line('brand'); ?></label>
                                 <div class="input-group">
                                    <select class="form-control select2" id="brand_id" name="brand_id"  style="width: 100%;">
                                       <option value="">-เลือก-</option>
                                       <?= get_brands_select_list($brand_id);  ?>
                                    </select>
                                    <span class="input-group-addon pointer" data-toggle="modal" data-target="#brand_modal" title="Add Brand"><i class="fa fa-plus-square-o text-primary fa-lg"></i></span>
                                 </div>
                                 <span id="brand_id_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4">                           
                                 <label for="category_id"><?= $this->lang->line('category'); ?><span class="text-danger">*</span></label>
                                 <div class="input-group">
                                    <select class="form-control select2" id="category_id" name="category_id"  style="width: 100%;">
                                       <option value="">-เลือก-</option>
                                     <?= get_categories_select_list($category_id);  ?>
                                    </select>
                                    <span class="input-group-addon pointer" data-toggle="modal" data-target="#category_modal" title="Add Category"><i class="fa fa-plus-square-o text-primary fa-lg"></i></span>
                                 </div>
                                 <span id="category_id_msg" style="display:none" class="text-danger"></span>
                              </div>
                           </div>
                           <div class="row">
                              <div class="form-group col-md-4">
                                 <label for="unit_id"><?= $this->lang->line('unit'); ?><span class="text-danger">*</span></label>
                                 <div class="input-group">
                                    <select class="form-control select2" id="unit_id" name="unit_id"  style="width: 100%;">
                                       <?= get_units_select_list($unit_id);  ?>
                                    </select>
                                    <span class="input-group-addon pointer" data-toggle="modal" data-target="#unit_modal" title="Add Unit"><i class="fa fa-plus-square-o text-primary fa-lg"></i></span>
                                 </div>
                                 <span id="unit_id_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="custom_barcode" ><?= $this->lang->line('barcode'); ?></label>
                                 <input type="text" class="form-control" id="custom_barcode" name="custom_barcode" placeholder=""  value="<?php print $custom_barcode; ?>" >
                                 <span id="custom_barcode_msg" style="display:none" class="text-danger"></span>
                              </div>

                             <!-- <div class="form-group col-md-4">
                                 <label for="sac" ><?= $this->lang->line('sac'); ?></label>
                                 <input type="text" class="form-control" id="sac" name="sac" placeholder=""  value="<?php print $sac; ?>" >
                                 <span id="sac_msg" style="display:none" class="text-danger"></span>
                              </div>-->

                        

                              <div class="form-group col-md-4">
                                 <label for="seller_points" ><?= $this->lang->line('seller_points'); ?></label>
                                 <input type="text" class="form-control only_currency" id="seller_points" name="seller_points" placeholder=""  value="<?php print $seller_points; ?>" >
                                 <span id="seller_points_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="custom_barcode" ><?= $this->lang->line('description'); ?></label>
                                 <textarea type="text" class="form-control" id="description" name="description" placeholder=""><?php print $description; ?></textarea>
                                 <span id="description_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="item_image"><?= $this->lang->line('select_image'); ?></label>
                                 <input type="file" name="item_image" id="item_image">
                                 <span id="item_image_msg" style="display:block;" class="text-danger">Max Width/Height: 1000px * 1000px & Size: 1MB </span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="hide_in_pos">ซ่อนจากหน้าขาย POS (Hide in POS)</label>
                                 <div class="checkbox">
                                    <label>
                                      <?php $hide_in_pos_checked = ($hide_in_pos == 1) ? 'checked' : ''; ?>
                                      <input type="checkbox" id="hide_in_pos" name="hide_in_pos" value="1" <?= $hide_in_pos_checked; ?>> ซ่อนสินค้านี้จากหน้า POS
                                    </label>
                                 </div>
                              </div>
                              
                           </div>
                           <hr>
                           <div class="row">
                              <div class="form-group col-md-4">
                                 <label for="discount_type"><?= $this->lang->line('discount_type'); ?></label>
                                 <select class="form-control" id="discount_type" name="discount_type"  style="width: 100%;" >
                                 <option value='Percentage'>เปอร์เซ็นต์(%)</option>
                                 <option value='Fixed'>จำนวนเงิน(<?= $CI->currency() ?>)</option>
                                 </select>
                                 <span id="discount_type_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="discount"><?= $this->lang->line('discount'); ?></label>
                                 <input type="text" class="form-control only_currency" id="discount" name="discount" value="<?php print $discount; ?>" >
                                 <span id="discount_msg" style="display:none" class="text-danger"></span>
                              </div>
                              
                           </div>
                           <hr>
                           <div class="row">
                              <div class="form-group col-md-4">
                                 <label for="price"><?= $this->lang->line('price'); ?> (ต้นทุนไม่รวมค่าแรง)<span class="text-danger">*</span></label>
                                 <input type="text" class="form-control only_currency" id="price" name="price" placeholder="ราคาไม่รวมภาษี "  value="<?php print $price; ?>" >
                                 <span id="price_msg" style="display:none" class="text-danger"></span>
                                 <span id="" style="display:show" class="text-primary">ป้อน "0", ถ้าไม่มีต้นทุน</span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="tax_id" ><?= $this->lang->line('tax'); ?><span class="text-danger">*</span></label>
                                 <select class="form-control select2" id="tax_id" name="tax_id"  style="width: 100%;" >
                                  <?= get_tax_select_list($tax_id);  ?>
                                 </select>
                                 <span id="tax_id_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4 hide">
                                 <label for="purchase_price"><?= $this->lang->line('purchase_price'); ?><span class="text-danger">*</span></label>
                                 <input type="text" class="form-control only_currency" id="purchase_price" name="purchase_price" placeholder="Total Price with Tax Amount"  value="<?php print $purchase_price; ?>" readonly='' >
                                 <span id="purchase_price_msg" style="display:none" class="text-danger"></span>
                              </div>
                           </div>
                           <!-- /row -->
                           <div class="row">
                              <div class="form-group col-md-4">
                                 <label for="tax_type"><?= $this->lang->line('sales_tax_type'); ?><span class="text-danger">*</span></label>
                                 <select class="form-control select2" id="tax_type" name="tax_type"  style="width: 100%;" >
                                  <?php 
                                    $inclusive_selected=$exclusive_selected='';
                                    if($tax_type =='Inclusive') { $inclusive_selected='selected'; }
                                    if($tax_type =='Exclusive') { $exclusive_selected='selected'; }

                                  ?>
                                    <option <?= $inclusive_selected ?> value="Inclusive">ราคารวมภาษี</option>
                                    <option <?= $exclusive_selected ?> value="Exclusive">ราคาไม่รวมภาษี</option>
                                 </select>
                                 <span id="tax_type_msg" style="display:none" class="text-danger"></span>
                                 
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="sales_price" class="control-label"><?= $this->lang->line('sales_price'); ?><span class="text-danger">*</span></label>
                                 <input type="text" class="form-control only_currency " id="sales_price" name="sales_price" placeholder="ราคาขายสินค้าบริการ"  value="<?php print $sales_price; ?>" >
                                 <span id="sales_price_msg" style="display:none" class="text-danger"></span>
                              </div>
                           </div>
                           <!-- /row -->
                           
                           
                           

                           <!-- /row -->
                           <!-- /.box-body -->
                           <div class="box-footer">
                              <div class="col-sm-8 col-sm-offset-2 text-center">
                                 <!-- <div class="col-sm-4"></div> -->
                                 <?php
                                    if($item_name!=""){
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
                              </div>
                           </div>
                           <!-- /.box-footer -->
                     <?= form_close(); ?>
                     </div>
                     <!-- /.box -->
                  </div>
                  <!--/.col (right) -->
               </div>
               
         </section>
         <!-- /.content -->
         </div>
         <!-- /.content-wrapper -->
         <?php $this->load->view('footer');?>
         <!-- Add the sidebar's background. This div must be placed
            immediately after the control sidebar -->
         <div class="control-sidebar-bg"></div>
      </div>
      <!-- ./wrapper -->
      <!-- SOUND CODE -->
      <?php $this->load->view('comman/code_js_sound');?>
      <!-- TABLES CODE -->
      <?php $this->load->view('comman/code_js.php');?>
      <script src="<?php echo $theme_link; ?>js/services/services.js"></script>
      <script src="<?php echo $theme_link; ?>js/modals.js"></script>
      <script type="text/javascript">
         $("#discount_type").val('<?=$discount_type; ?>');
        <?php if(isset($q_id)){ ?>
          $("#store_id").attr('readonly',true);
        <?php }?>
      </script>
      <!-- Make sidebar menu hughlighter/selector -->
      <script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
     
   </body>
</html>
