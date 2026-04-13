<!DOCTYPE html>
<html>

<head>
<!-- FORM CSS CODE -->
<?php $this->load->view('comman/code_css.php');?>
<!-- </copy> -->  
<style type="text/css">
table.table-bordered > thead > tr > th {
/* border:1px solid black;*/
text-align: center;
}
.table > tbody > tr > td, 
.table > tbody > tr > th, 
.table > tfoot > tr > td, 
.table > tfoot > tr > th, 
.table > thead > tr > td, 
.table > thead > tr > th 
{
padding-left: 2px;
padding-right: 2px;  

}
</style>
</head>


<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
 
 
 <?php $this->load->view('sidebar');?>
 
 <?php
 $CI =& get_instance();
    if(!isset($quotation_id)){
      $customer_id  = $quotation_date = $quotation_status = $warehouse_id =
      $reference_no  =
      $other_charges_input          = $other_charges_tax_id = $store_id =
      $discount_type  = $quotation_note = '';
      $quotation_date=show_date(date("d-m-Y"));
      $expire_date='';
      $discount_input = 0;
      $discount_input = ($discount_input==0) ? '' : $discount_input;
    }
    else{
      $q2 = $this->db->query("select * from db_quotation where id=$quotation_id");
      $customer_id=$q2->row()->customer_id;
      $quotation_date=show_date($q2->row()->quotation_date);
      $expire_date=(!empty($q2->row()->expire_date)) ? show_date($q2->row()->expire_date) : '';
      $quotation_status=$q2->row()->quotation_status;
      $warehouse_id=$q2->row()->warehouse_id;
      $reference_no=$q2->row()->reference_no;
      $discount_input=$q2->row()->discount_to_all_input;
      $discount_type=$q2->row()->discount_to_all_type;
      $other_charges_input=$q2->row()->other_charges_input;
      $other_charges_tax_id=$q2->row()->other_charges_tax_id;
      $quotation_note=$q2->row()->quotation_note;
      $store_id=$q2->row()->store_id;

      $items_count = $this->db->query("select count(*) as items_count from db_quotationitems where quotation_id=$quotation_id")->row()->items_count;
    }
    
    ?>

 

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- **********************MODALS***************** -->
    
    <?php $this->load->view('modals/modal_customer');?>
    <?php $this->load->view('modals/modal_item');?>
    <?php $this->load->view('modals/modal_item_or_service');?>
    <?php /*$this->load->view('modals/modal_service');*/ ?>

    <!-- **********************MODALS END***************** -->
    <!-- Content Header (Page header) -->
    <section class="content-header">
         <h1>
            <?=$page_title;?>
            <small>Add/Update Quotation</small>
         </h1>
         <ol class="breadcrumb">
            <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="<?php echo $base_url; ?>quotation"><?= $this->lang->line('quotation_list'); ?></a></li>
            <li><a href="<?php echo $base_url; ?>quotation/add">เสนอราคาใหม่</a></li>
            <li class="active"><?=$page_title;?></li>
         </ol>
      </section>

    <!-- Main content -->
     <section class="content">
               <div class="row">
                 <!-- ********** ALERT MESSAGE START******* -->
               <?php $this->load->view('comman/code_flashdata');?>
               <?php $this->load->view('modals/modal_sales_item');?>
               <!-- ********** ALERT MESSAGE END******* -->
                  <!-- right column -->
                  <div class="col-md-12">
                     <!-- Horizontal Form -->
                     <div class="box box-primary " id="quotation-box">
                        <!-- style="background: #68deac;" -->
                        
                        <!-- form start -->
                         <!-- OK START -->
                        <?= form_open('#', array('class' => 'form-horizontal', 'id' => 'quotation-form', 'enctype'=>'multipart/form-data', 'method'=>'POST'));?>
                           <input type="hidden" id="base_url" value="<?php echo $base_url;; ?>">
                           <input type="hidden" value='1' id="hidden_rowcount" name="hidden_rowcount">
                           <input type="hidden" value='0' id="hidden_update_rowid" name="hidden_update_rowid">
                           <input type="hidden" id="tot_subtotal_amt" name="tot_subtotal_amt" value="0.00">
                           <input type="hidden" id="tot_discount_to_all_amt" name="tot_discount_to_all_amt" value="0.00">
                           <input type="hidden" id="tot_round_off_amt" name="tot_round_off_amt" value="0.00">
                           <input type="hidden" id="tot_total_amt" name="tot_total_amt" value="0.00">
                           <input type="hidden" id="other_charges_amt" name="other_charges_amt" value="0.00">
                           <input type="hidden" id="taxable_amount" name="taxable_amount" value="0.00">
                           <input type="hidden" id="vat_amt" name="vat_amt" value="0.00">
                           <input type="hidden" id="vat" name="vat" value="0.00">

                          
                           <div class="box-body">
                              <!-- Store Code -->
                              <?php 
                              /*if(store_module() && is_admin()) {$this->load->view('store/store_code',array('show_store_select_box'=>true,'store_id'=>$store_id,'div_length'=>'col-sm-3')); }else{*/
                                echo "<input type='hidden' name='store_id' id='store_id' value='".get_current_store_id()."'>";
                              /*}*/
                              ?>
                              <!-- Store Code end -->
                              <!-- Warehouse Code -->
                              <?php 
                               if(warehouse_module() && warehouse_count()>1) {$this->load->view('warehouse/warehouse_code',array('show_warehouse_select_box'=>true,'warehouse_id'=>$warehouse_id,'div_length'=>'col-sm-3','show_select_option'=>false)); }else{
                                echo "<input type='hidden' name='warehouse_id' id='warehouse_id' value='".get_store_warehouse_id()."'>";
                               }
                              ?>
                              <!-- Warehouse Code end -->
                              
                              <div class="form-group">
                                 <label for="customer_id" class="col-sm-2 control-label"><?= $this->lang->line('customer_name'); ?><label class="text-danger">*</label></label>
                                 <div class="col-sm-3">
                                    <div class="input-group">
                                       <select class="form-control select2" id="customer_id" name="customer_id"  style="width: 100%;">
                                       </select>
                                       <span class="input-group-addon pointer" data-toggle="modal" data-target="#customer-modal" title="New Customer?"><i class="fa fa-user-plus text-primary fa-lg"></i></span>
                                    </div>
                                    <span id="customer_id_msg" style="display:none" class="text-danger"></span>
                                    <lable><?= $this->lang->line('previous_due'); ?> :<label class="customer_previous_due text-red" style="font-size: 18px;">0.00</label></lable>
                                 </div>
                                 <label for="quotation_date" class="col-sm-2 control-label"><?= $this->lang->line('quotation_date'); ?> <label class="text-danger">*</label></label>
                                 <div class="col-sm-3">
                                    <div class="input-group date">
                                       <div class="input-group-addon">
                                          <i class="fa fa-calendar"></i>
                                       </div>
                                       <input type="text" class="form-control pull-right datepicker"  id="quotation_date" name="quotation_date" readonly onkeyup="shift_cursor(event,'quotation_status')" value="<?= $quotation_date;?>">
                                    </div>
                                    <span id="quotation_date_msg" style="display:none" class="text-danger"></span>
                                 </div>
                              </div>
                              <div class="form-group hide">
                                 <label for="quotation_status" class="col-sm-2 control-label"><?= $this->lang->line('status'); ?> <label class="text-danger">*</label></label>
                                 <div class="col-sm-3">
                                       <select class="form-control select2" id="quotation_status" name="quotation_status"  style="width: 100%;" onkeyup="shift_cursor(event,'mobile')">
                                          <?php 
                                               $pending_select = ($quotation_status=='Quotation') ? 'selected' : '';
                                          ?>
                                            <option <?= $pending_select; ?> value="Quotation">ใบเสนอราคา</option>
                                       </select>
                                    <span id="quotation_status_msg" style="display:none" class="text-danger"></span>
                                 </div>
                              </div>
                              <div class="form-group ">
                                <label for="expire_date" class="col-sm-2 control-label"><?= $this->lang->line('expire_date'); ?></label>
                                 <div class="col-sm-3">
                                    <div class="input-group date">
                                       <div class="input-group-addon">
                                          <i class="fa fa-calendar"></i>
                                       </div>
                                       <input type="text" class="form-control pull-right datepicker"  id="expire_date" name="expire_date"  onkeyup="shift_cursor(event,'expire_status')" value="<?= $expire_date;?>">
                                    </div>
                                    <span id="expire_date_msg" style="display:none" class="text-danger"></span>
                                 </div>
                                 <label for="reference_no" class="col-sm-2 control-label"><?= $this->lang->line('quotation_signature'); ?> </label>
                                 <div class="col-sm-3">
                                    <input type="text" value="<?php echo  $reference_no; ?>" class="form-control " id="reference_no" name="reference_no" placeholder="" >
                  <span id="reference_no_msg" style="display:none" class="text-danger"></span>
                                 </div>
                                
                              </div>
                              
                           </div>
                           <!-- /.box-body -->
                           
                           <div class="row">
                              <div class="col-md-12">
                                <div class="col-md-12">
                                  <div class="box">
                                    <div class="box-info">
                                      <div class="box-header">
                                        <div class="col-md-8 col-md-offset-2 d-flex justify-content" >
                                          <div class="input-group">
                                                <span class="input-group-addon" title="Select Items"><i class="fa fa-barcode"></i></span>
                                                 <input type="text" class="form-control " placeholder="ชื่อสินค้า/สแกนบาร์โค้ด/รหัสสินค้า" autofocus id="item_search">
                                                 <span class="input-group-addon pointer text-green show_item_service" title="Click to Add New Item or Service"><i class="fa fa-plus"></i></span>
                                              </div>
                                        </div>
                                      </div>
                                      <div class="box-body">
                                        <div class="table-responsive" style="width: 100%">
                                        <table class="table table-hover table-bordered" style="width:100%" id="quotation_table">
                                             <thead class="custom_thead">
                                                <tr class="bg-success" >
                                                   <th rowspan='2' style="width:15%"><?= $this->lang->line('item_name'); ?></th>
                                                 
                                                   <th rowspan='2' style="width:10%;min-width: 180px;"><?= $this->lang->line('quantity'); ?></th>
                                                   <th rowspan='2' style="width:10%"><?= $this->lang->line('unit_price'); ?></th> 
                                                   <th rowspan='2' style="width:10%"><?= $this->lang->line('discount'); ?>(<?= $CI->currency() ?>)</th>
                                                   <th rowspan='2' style="width:10%"><?= $this->lang->line('tax_amount'); ?></th>
                                                   <th rowspan='2' style="width:5%"><?= $this->lang->line('tax'); ?></th>
                                                   <th rowspan='2' style="width:7.5%"><?= $this->lang->line('total_amount'); ?></th>
                                                   <th rowspan='2' style="width:7.5%"><?= $this->lang->line('action'); ?></th>
                                                </tr>
                                             </thead>
                                             <tbody>
                                               
                                             </tbody>
                                          </table>
                                      </div>
                                      </div>
                                    </div>
                                  </div>
                                  

                                </div>
                              </div>
                              
                              <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-12">
                                       <div class="form-group">
                                          <label for="" class="col-sm-4 control-label"><?= $this->lang->line('quantity'); ?></label>    
                                          <div class="col-sm-4">
                                             <label class="control-label total_quantity text-success" style="font-size: 15pt;">0</label>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-md-12">
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-md-12">
                                       <div class="form-group">
                                          <label for="discount_to_all_input" class="col-sm-4 control-label"><?= $this->lang->line('discount_on_all'); ?></label>    
                                          <div class="col-sm-4">
                                             <input type="text" class="form-control  text-right only_currency" id="discount_to_all_input" name="discount_to_all_input" onkeyup="enable_or_disable_item_discount();" value="<?php echo  $discount_input; ?>">
                                          </div>
                                          <div class="col-sm-4">
                                             <select class="form-control" onchange="final_total();" id='discount_to_all_type' name="discount_to_all_type">
                                                <option value='in_percentage'>เปอร์เซ็นต์ %</option>
                                                <option value='in_fixed'>บาท</option>
                                             </select>
                                          </div>
                                          <!-- Dynamicaly select Supplier name -->
                                          <script type="text/javascript">
                                             <?php if($discount_type!=''){ ?>
                                                 document.getElementById('discount_to_all_type').value='<?php echo  $discount_type; ?>';
                                             <?php }?>
                                          </script>
                                          <!-- Dynamicaly select Supplier name end-->
                                       </div>
                                    </div>
                                 </div>
                                <div class="row">
                                    <div class="col-md-12">
                                       <div class="form-group">
                                          <label for="quotation_note" class="col-sm-4 control-label"><?= $this->lang->line('note'); ?></label>    
                                          <div class="col-sm-8">
                                             <textarea class="form-control text-left" id='quotation_note' name="quotation_note"><?= $quotation_note; ?></textarea>
                                            <span id="quotation_note_msg" style="display:none" class="text-danger"></span>
                                          </div>
                                       </div>
                                    </div>
                                 </div>

                                 
                              </div>
                              

                              <div class="col-md-6">
                                 <div class="row">
                                    <div class="col-md-12">
                                       <div class="form-group">
                                           
                                          <table  class="col-md-9">
                                              <tr>
                                                 <th class="text-right" style="font-size: 17px;"><?= $this->lang->line('subtotal'); ?></th>
                                                 <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                    <h4><b id="subtotal_amt" name="subtotal_amt">0.00</b></h4>
                                                 </th>
                                              </tr>
                                    
                                              <tr>
                                                 <th class="text-right" style="font-size: 17px;"><?= $this->lang->line('discount_on_all'); ?></th>
                                                 <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                    <h4><b id="discount_to_all_amt" name="discount_to_all_amt">0.00</b></h4>
                                                 </th>
                                              </tr>

                                              <tr>
                                                 <th class="text-right" style="font-size: 17px;">มูลค่าที่ไม่มี/ยกเว้นภาษี</th>
                                                 <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                    <h4><b id="exempt_amt" name="exempt_amt">0.00</b></h4>
                                                 </th>
                                              </tr>

                                              <tr>
                                                 <th class="text-right" style="font-size: 17px;">ยอดฐานภาษี</th>
                                                 <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                    <h4><b id="taxable_amt" name="taxable_amt">0.00</b></h4>
                                                 </th>
                                              </tr>

                                              <tr>
                                                 <th class="text-right" style="font-size: 17px;">ภาษีมูลค่าเพิ่ม</th>
                                                 <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                    <h4><b id="tax_amt" name="tax_amt">0.00</b></h4>
                                                 </th>
                                              </tr>
                                        
                                              <tr class='text-primary'>
                                                 <th class="text-right" style="font-size: 17px;"><?= $this->lang->line('grand_total'); ?></th>
                                                 <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                    <h4><b id="total_amt" name="total_amt">0.00</b></h4>
                                                 </th>
                                              </tr>
                                           </table>
                                       </div>
                                    </div>
                                 </div>
                              </div>

                              

                              <!-- SMS Sender while saving -->
                                <?php 
                                   //Change Return
                                    $send_sms_checkbox='disabled';
                                    if($CI->is_sms_enabled()){
                                      if(!isset($quotation_id)){
                                        $send_sms_checkbox='checked';  
                                      }else{
                                        $send_sms_checkbox='';
                                      }
                                    }

                              ?>
                             
                              <div class="col-xs-12 ">
                                 <div class="col-sm-12">
                                       <div class="box-body ">
                                          <div class="col-md-12">
                                            <div class="checkbox icheck">
                                  <!--     <label>
                                        <input type="checkbox" <?=$send_sms_checkbox;?> class="form-control" id="send_sms" name="send_sms" > <label for="quotation_discount" class=" control-label"><?= $this->lang->line('send_sms_to_customer'); ?>
                                          <i class="hover-q " data-container="body" data-toggle="popover" data-placement="top" data-content="If checkbox is Disabled! You need to enable it from SMS -> SMS API <br><b>Note:<i>Walk-in Customer will not receive SMS!</i></b>" data-html="true" data-trigger="hover" data-original-title="" title="Do you wants to send SMS ?">
                                  <i class="fa fa-info-circle text-maroon text-black hover-q"></i>
                                </i>
                                        </label>-->
                                      </label>
                                    </div>
                                        </div><!-- col-md-12 -->
                                       </div>
                                       <!-- /.box-body -->
                                    </div>
                                 <!-- /.box -->
                              </div> 
                           </div>
                           
                           <!-- /.box-body -->
                           <div class="box-footer col-sm-12">
                              <center>
                                <?php
                                if(isset($quotation_id)){
                                  $btn_id='update';
                                  $btn_name="อัพเดท";
                                  echo '<input type="hidden" name="quotation_id" id="quotation_id" value="'.$quotation_id.'"/>';
                                }
                                else{
                                  $btn_id='save';
                                  $btn_name="บันทึก";
                                }

                                ?>
                                 <div class="col-md-3 col-md-offset-3">
                                    <button type="button" id="<?php echo $btn_id;?>" class="btn btn-block btn-primary payments_modal" title="Save Data"><?php echo $btn_name;?></button>
                                 </div>
                                 <div class="col-sm-3"><a href="<?= base_url()?>dashboard">
                                    <button type="button" class="btn btn-block btn-info" title="Go Dashboard">ปิด</button>
                                  </a>
                                </div>
                              </center>
                           </div>
                           

                           <?= form_close(); ?>
                           <!-- OK END -->
                     </div>
                  </div>
                  <!-- /.box-footer -->
                 
               </div>
               <!-- /.box -->
             </section>
            <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  
 <?php $this->load->view('footer');?>
<!-- SOUND CODE -->
<?php $this->load->view('comman/code_js_sound');?>
<!-- GENERAL CODE -->
<?php $this->load->view('comman/code_js');?>

<script src="<?php echo $theme_link; ?>js/modals.js"></script>
<script src="<?php echo $theme_link; ?>js/modals/modal_item.js"></script>
  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->
<script type="text/javascript">
  var walk_in_customer_name ='<?= get_walk_in_customer_name();?>'
</script>
<script src="<?php echo $theme_link; ?>js/quotation/quotation.js"></script>  
<script src="<?php echo $theme_link; ?>js/ajaxselect/customer_select_ajax.js"></script>  
      <script>
         //Customer Selection Box Search
         function getCustomerSelectionId() {
           return '#customer_id';
         }

         $(document).ready(function () {

            var customer_id = "<?= (!empty($customer_id)) ? $customer_id : '';  ?>";
            if(customer_id!=''){
               autoLoadFirstCustomer(customer_id);
            }
         });
         //Customer Selection Box Search - END

        function set_previous_due(previous_due,tot_advance){
          $(".customer_previous_due").html(previous_due);
        }

        var base_url=$("#base_url").val();
        $("#store_id").on("change",function(){
          var store_id=$("#store_id").val();
          $.post(base_url+"quotation/get_customers_select_list",{store_id:store_id},function(result){
              $("#customer_id").html('').append(result).select2();
              $("#quotation_table > tbody").empty();
              calculate_tax();
          });
          $.post(base_url+"quotation/get_tax_select_list",{store_id:store_id},function(result){
              $("#other_charges_tax_id").html('').append(result).select2();
              calculate_tax();
          });
        });

        /*Warehouse*/
        $("#warehouse_id").on("change",function(){
          var warehouse_id=$(this).val();
          $("#quotation_table > tbody").empty();
          final_total();
        });
        /*Warehouse end*/

         $(".close_btn").on("click",function(){
           if(confirm('Are you sure you want to navigate away from this page?')){
               window.location='<?php echo $base_url; ?>dashboard';
             }
         });
         //Initialize Select2 Elements
             $(".select2").select2();
         //Date picker
             $('.datepicker').datepicker({
               autoclose: true,
            format: 'dd-mm-yyyy',
              todayHighlight: true
             });
          
        /*if($("#warehouse_id").val()==''){
          $("#item_search").attr({
            disabled: true,
          });
          toastr["warning"]("Please Select Warehouse!!");
          failed_sound.currentTime = 0; 
          failed_sound.play();
         
        }*/
         
         /* ---------- CALCULATE TAX -------------*/
        

         function calculate_tax(i){ //i=Row
            set_tax_value(i);

           //Find the Tax type and Tax amount
           var tax_type = $("#tr_tax_type_"+i).val();
           var tax_amount = $("#td_data_"+i+"_11").val();

           var qty=$("#td_data_"+i+"_3").val();
           var quotation_price=parseFloat($("#td_data_"+i+"_10").val());
           $("#td_data_"+i+"_4").val(quotation_price);
           /*Discounr*/
           var discount_amt=$("#td_data_"+i+"_8").val();
               discount_amt   =(isNaN(parseFloat(discount_amt)))    ? 0 : parseFloat(discount_amt);

           var amt=parseFloat(qty) * quotation_price;//Taxable

           var total_amt=amt-discount_amt;
           total_amt = (tax_type=='Inclusive') ? total_amt : parseFloat(total_amt) + parseFloat(tax_amount);
           
           //Set Unit cost
           $("#td_data_"+i+"_9").val('').val(to_Fixed(total_amt));
        
           final_total();
         }

        
         /* ---------- CALCULATE GST END -------------*/

        
         /* ---------- Final Description of amount ------------*/
         function final_total(){
            
            var rowcount=$("#hidden_rowcount").val();
            var total_quantity=0;
            var subtotal_all = 0;
            
            // Grouping for high-precision calculation
            var tax_groups = {}; 
            var total_exempt_base = 0;
            var max_tax_rate = 0;
            
            for(var i=1; i<=rowcount; i++){
              if(document.getElementById("td_data_"+i+"_3")){
                var qty = parse_float($("#td_data_"+i+"_3").val());
                if(qty > 0){
                  total_quantity += qty;
                  var row_total = parse_float($("#td_data_"+i+"_9").val()); 
                  subtotal_all += row_total;
                  
                  var tax_type = $("#tr_tax_type_"+i).val();
                  var tax_rate = parse_float($("#tr_tax_value_"+i).val());
                  if(tax_rate > max_tax_rate) max_tax_rate = tax_rate;
                  
                  if(tax_rate > 0){
                    if(tax_type == 'Inclusive'){
                       // High precision de-taxing
                       var de_taxed = (row_total * 100) / (100 + tax_rate);
                       if(!tax_groups[tax_rate]) tax_groups[tax_rate] = 0;
                       tax_groups[tax_rate] += de_taxed;
                    } else {
                       // Exclusive
                       var tax_amt = parse_float($("#td_data_"+i+"_11").val());
                       var base = row_total - tax_amt;
                       if(!tax_groups[tax_rate]) tax_groups[tax_rate] = 0;
                       tax_groups[tax_rate] += base;
                    }
                  } else {
                    total_exempt_base += row_total;
                  }
                }
              }
            }
            
            // Other charges 
            var other_charges_input = parse_float($("#other_charges_input").val());
            var other_charges_total_amt = 0;
            if(other_charges_input > 0){
               var oc_tax_id = $('option:selected', '#other_charges_tax_id').attr('data-tax');
               var oc_tax_rate = parse_float(oc_tax_id);
               
               if(oc_tax_rate > 0){
                  var oc_tax_amt = (other_charges_input * oc_tax_rate / 100);
                  other_charges_total_amt = other_charges_input + oc_tax_amt;
                  if(!tax_groups[oc_tax_rate]) tax_groups[oc_tax_rate] = 0;
                  tax_groups[oc_tax_rate] += other_charges_input;
               } else {
                  other_charges_total_amt = other_charges_input;
                  total_exempt_base += other_charges_input;
               }
            }

            // Discount to all
            var discount_input = parse_float($("#discount_to_all_input").val());
            var discount_type = $("#discount_to_all_type").val();
            var inclusive_total = subtotal_all + other_charges_total_amt;
            var total_discount = (discount_type == 'in_percentage' || discount_type == 'Percentage') ? (inclusive_total * discount_input / 100) : discount_input;
            if(isNaN(total_discount)) total_discount = 0;
            
            var total_global_discount = total_discount;
            
            // Extract VAT from the global discount
            var total_global_discount_base = (total_global_discount * 100) / (100 + max_tax_rate);
            
            // Prorate and calculate final components
            var final_grand_total = 0;
            var total_net_exempt = 0;
            var total_net_taxable = 0;
            var total_vat_amount = 0;
            
            var total_base_sum = total_exempt_base;
            for(var rate in tax_groups) { total_base_sum += tax_groups[rate]; }
            var ratio = (total_base_sum > 0) ? (total_global_discount_base / total_base_sum) : 0;
            
            // Exempt portion
            var net_exempt = total_exempt_base * (1 - ratio);
            total_net_exempt = Math.round(net_exempt * 100) / 100;
            
            // Taxable portions
            for(var rate in tax_groups){
               var base = tax_groups[rate];
               var net_base = base * (1 - ratio);
               var rounded_net_base = Math.round(net_base * 100) / 100;
               var v_amt = Math.round((rounded_net_base * parse_float(rate) / 100) * 100) / 100;
               
               total_net_taxable += rounded_net_base;
               total_vat_amount += v_amt;
            }
            
            final_grand_total = total_net_exempt + total_net_taxable + total_vat_amount;
            
            // Update UI
            $(".total_quantity").html(format_qty(total_quantity));
            $("#subtotal_amt").html(to_Fixed(subtotal_all));
            $("#other_charges_amt").html(to_Fixed(other_charges_total_amt));
            $("#discount_to_all_amt").html(to_Fixed(total_discount));
            $("#hidden_discount_to_all_amt").val(to_Fixed(total_discount));
            $("#tot_discount_to_all_amt").val(to_Fixed(total_discount));
            $("#tot_subtotal_amt").val(to_Fixed(subtotal_all));
            
            $("#exempt_amt").html(to_Fixed(total_net_exempt));
            $("#taxable_amt").html(to_Fixed(total_net_taxable));
            $("#tax_amt").html(to_Fixed(total_vat_amount));
            
            $("#taxable_amount").val(to_Fixed(total_net_taxable));
            $("#vat_amt").val(to_Fixed(total_vat_amount));
            $("#vat").val(to_Fixed(max_tax_rate));
            
            $("#total_amt").html(to_Fixed(final_grand_total));
            $("#hidden_total_amt").val(to_Fixed(final_grand_total));
            $("#tot_total_amt").val(to_Fixed(final_grand_total));
            $("#tot_round_off_amt").val("0.00"); 
          }
         /* ---------- Final Description of amount end ------------*/
          
         function removerow(id){//id=Rowid
           
         $("#row_"+id).remove();
         final_total();
         failed.currentTime = 0;
        failed.play();
         }
               
     

    function enable_or_disable_item_discount(){
      /*var discount_input=parseFloat($("#discount_to_all_input").val());
      discount_input = isNaN(discount_input) ? 0 : discount_input;
      if(discount_input>0){
        $(".item_discount").attr({
          'readonly': true,
          'style': 'border-color:red;cursor:no-drop',
        });
      }
      else{
        $(".item_discount").attr({
          'readonly': false,
          'style': '',
        });
      }*/

      var rowcount=$("#hidden_rowcount").val();
      for(k=1;k<=rowcount;k++){
       if(document.getElementById("tr_item_id_"+k)){
         calculate_tax(k);
       }//if end
     }//for end

      //final_total();
    }

    

    //Sale Items Modal Operations Start
    function show_quotation_item_modal(row_id){

      $('#sales_item').modal('toggle');
      $("#popup_tax_id").select2();

      //Find the item details
      var item_name = $("#td_data_"+row_id+"_1").html();
      var tax_type = $("#tr_tax_type_"+row_id).val();
      var tax_id = $("#tr_tax_id_"+row_id).val();
      var description = $("#description_"+row_id).val();

      /*Discount*/
      var item_discount_input = $("#item_discount_input_"+row_id).val();
      var item_discount_type = $("#item_discount_type_"+row_id).val();

      //Set to Popup
      $("#item_discount_input").val(item_discount_input);
      $("#item_discount_type").val(item_discount_type).select2();

      $("#popup_item_name").html(item_name);
      $("#popup_tax_type").val(tax_type).select2();
      $("#popup_tax_id").val(tax_id).select2();
      $("#popup_description").val(description);
      $("#popup_row_id").val(row_id);
    }

    function set_info(){
      var row_id = $("#popup_row_id").val();
      var tax_type = $("#popup_tax_type").val();
      var tax_id = $("#popup_tax_id").val();
      var description = $("#popup_description").val();
      var tax_name = ($('option:selected', "#popup_tax_id").attr('data-tax-value'));
      var tax = parseFloat($('option:selected', "#popup_tax_id").attr('data-tax'));

      /*Discounr*/
      var item_discount_input = $("#item_discount_input").val();
      var item_discount_type = $("#item_discount_type").val();

      //Set it into row 
      $("#item_discount_input_"+row_id).val(item_discount_input);
      $("#item_discount_type_"+row_id).val(item_discount_type);

      $("#tr_tax_type_"+row_id).val(tax_type);
      $("#tr_tax_id_"+row_id).val(tax_id);
      $("#tr_tax_value_"+row_id).val(tax);//%
      $("#description_"+row_id).val(description);
      $("#td_data_"+row_id+"_12").html(tax_name);
      
      calculate_tax(row_id);
      $('#sales_item').modal('toggle');
    }
    function set_tax_value(row_id){
      //get the quotation price of the item
      var tax_type = $("#tr_tax_type_"+row_id).val();
      var tax = $("#tr_tax_value_"+row_id).val(); //%
      var qty=$("#td_data_"+row_id+"_3").val();
          qty = (isNaN(qty)) ? 0 :qty;
      var quotation_price = parseFloat($("#td_data_"+row_id+"_10").val());
          quotation_price = (isNaN(quotation_price)) ? 0 :quotation_price;
          quotation_price = quotation_price * qty;

      /*Discount*/
      var item_discount_type = $("#item_discount_type_"+row_id).val();
      var item_discount_input = parseFloat($("#item_discount_input_"+row_id).val());
          item_discount_input = (isNaN(item_discount_input)) ? 0 :item_discount_input;

      //Calculate discount      
      var discount_amt=(item_discount_type=='Percentage') ? ((quotation_price) * item_discount_input)/100 : (item_discount_input*qty);
      quotation_price-=parseFloat(discount_amt);

      var tax_amount = (tax_type=='Inclusive') ? calculate_inclusive(quotation_price,tax) : calculate_exclusive(quotation_price,tax);
      
      $("#td_data_"+row_id+"_8").val(to_Fixed(discount_amt));

      $("#td_data_"+row_id+"_11").val(to_Fixed(tax_amount));
    }
    //Sale Items Modal Operations End
      </script>


      <!-- UPDATE OPERATIONS -->
      <script type="text/javascript">
         <?php if(isset($quotation_id)){ ?> 
             $("#store_id").attr('readonly',true);
             $(document).ready(function(){
                var base_url='<?= base_url();?>';
                var quotation_id='<?= $quotation_id;?>';
                $("#quotation-box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
                $.post(base_url+"quotation/return_quotation_list/"+quotation_id,{},function(result){
                  //alert(result);
                  $('#quotation_table tbody').append(result);
                  $("#hidden_rowcount").val(parseInt(<?=$items_count;?>)+1);
                  success_sound.currentTime = 0;
                  success_sound.play();
                  enable_or_disable_item_discount();
                  $(".overlay").remove();
              }); 
             });
         <?php }?>
      </script>
      <!-- UPDATE OPERATIONS end-->

      <!-- Make sidebar menu hughlighter/selector -->
      <script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
</body>
</html>
