<!DOCTYPE html>
<html>

<head>
<!-- TABLES CSS CODE -->
<?php include"comman/code_css.php"; ?>
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
 
 
 <?php include"sidebar.php"; ?>
 
 <?php

    if(!isset($purchase_id)){
      $supplier_id  = $pur_date = $purchase_status = $warehouse_id =
      $reference_no  =
      $other_charges_input          = $other_charges_tax_id =
      $discount_input = $discount_type  = $purchase_note=$store_id='';
      $pur_date=show_date(date("d-m-Y"));
    }
    else{
      $q2 = $this->db->query("select * from db_purchase where id=$purchase_id");
      $supplier_id=$q2->row()->supplier_id;
      $warehouse_id=$q2->row()->warehouse_id;
      $pur_date=show_date($q2->row()->purchase_date);
      $purchase_status=$q2->row()->purchase_status;
      $reference_no=$q2->row()->reference_no;
      $discount_input=store_number_format($q2->row()->discount_to_all_input,2);
      $discount_type=$q2->row()->discount_to_all_type;
      $other_charges_input=store_number_format($q2->row()->other_charges_input,0);
      $other_charges_tax_id=$q2->row()->other_charges_tax_id;
      $purchase_note=$q2->row()->purchase_note;
      $store_id=$q2->row()->store_id;

      $items_count = $this->db->query("select count(*) as items_count from db_purchaseitems where purchase_id=$purchase_id")->row()->items_count;


    }
   
    ?>

 
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- **********************MODALS***************** -->
    <?php include"modals/modal_supplier.php"; ?>
    <?php include"modals/modal_purchase_item.php"; ?>
    <?php include"modals/modal_item.php"; ?>
    <?php include"modals/modal_item_or_service.php"; ?>
   <?php /*include"modals/modal_service.php";*/ ?>
    <!-- **********************MODALS END***************** -->
    <!-- Content Header (Page header) -->
    <section class="content-header">
         <h1>
            <?=$page_title;?>
            <small>Add/Update Purchase</small>
         </h1>
         <ol class="breadcrumb">
            <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="<?php echo $base_url; ?>purchase"><?= $this->lang->line('purchase_list'); ?></a></li>
            <li><a href="<?php echo $base_url; ?>purchase/add"><?= $this->lang->line('new_purchase'); ?></a></li>
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
                     <div class="box box-primary " id="purchase_box" >
                        <!-- style="background: #68deac;" -->
                        
                        <!-- form start -->
                         <!-- OK START -->
                        <?= form_open('#', array('class' => 'form-horizontal', 'id' => 'purchase-form', 'enctype'=>'multipart/form-data', 'method'=>'POST'));?>
                           <input type="hidden" id="base_url" value="<?php echo $base_url;; ?>">
                           <input type="hidden" value='1' id="hidden_rowcount" name="hidden_rowcount">
                           <input type="hidden" value='0' id="hidden_update_rowid" name="hidden_update_rowid">
                           <input type="hidden" value='Received' id="purchase_status" name="purchase_status">

                          
                           <div class="box-body">

...


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
                                 <label for="supplier_id" class="col-sm-2 control-label"><?= $this->lang->line('supplier_name'); ?><label class="text-danger">*</label></label>
                                 <div class="col-sm-3">
                                    <div class="input-group">
                                       <select class="form-control select2" id="supplier_id" name="supplier_id"  style="width: 100%;" >
                                       </select>
                                       <span class="input-group-addon pointer" data-toggle="modal" data-target="#supplier-modal" title="New Supplier?"><i class="fa fa-user-plus text-primary fa-lg"></i></span>
                                    </div>
                                    <span id="supplier_id_msg" style="display:none" class="text-danger"></span>
                                 </div>
                               <label for="pur_date" class="col-sm-2 control-label"><?= $this->lang->line('purchase_date'); ?> <label class="text-danger">*</label></label>
                                  <div class="col-sm-3">
                                    <div class="input-group date">
                                       <div class="input-group-addon">
                                          <i class="fa fa-calendar"></i>
                                       </div>
                                       <input type="text" class="form-control pull-right datepicker"  id="pur_date" name="pur_date" readonly onkeyup="shift_cursor(event,'purchase_status')" value="<?= $pur_date;?>">
                                    </div>
                                    <span id="pur_date_msg" style="display:none" class="text-danger"></span>
                                 </div>
                             </div>
                              <div class="form-group">
                              <label for="purchase_status" class="col-sm-2 control-label"><?= $this->lang->line('status'); ?> <label class="text-danger">*</label></label>
                                 <div class="col-sm-3" style="font-size:large;">
                                       <select class="form-control select2" id="purchase_status" name="purchase_status"  style="width: 100%;" onkeyup="shift_cursor(event,'mobile')">
                                         	<!-- <option value="">-Select-</option> -->
                                          <?php 
                                               $received_select = ($purchase_status=='Received') ? 'selected' : ''; 
                                               $pending_select = ($purchase_status=='Pending') ? 'selected' : ''; 
                                               $ordered_select = ($purchase_status=='Ordered') ? 'selected' : ''; 
                                          ?>
              							                <option <?= $received_select; ?> value="Received">รับสินค้าแล้ว</option>
              							                <option <?= $pending_select; ?> value="Pending">รอรับสินค้า</option>
              							                <option <?= $ordered_select; ?> value="Ordered">สั่งซื้อ</option>
                                       </select>
                                    <span id="purchase_status_msg" style="display:none" class="text-danger"></span>
                                 </div>
                              
                                                           
                                 <label for="reference_no" class="col-sm-2 control-label"><?= $this->lang->line('approver'); ?> </label>
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
                                        <table class="table table-hover table-bordered" style="width:100%" id="purchase_table">
                                             <thead class="custom_thead">
                                                <tr class="bg-success" >
                                                   <th rowspan='2' style="width:15%"><?= $this->lang->line('item_name'); ?></th>
                                                   <th rowspan='2' style="width:15%;min-width: 180px;"><?= $this->lang->line('quantity'); ?></th>
                                                   <th rowspan='2' style="width:10%"><?= $this->lang->line('unit_cost'); ?>(<?=$CURRENCY;?>)</th>
                                           <!--      <th rowspan='2' style="width:7.5%"><?= $this->lang->line('tax'); ?> %</th> -->
                                                   <th rowspan='2' style="width:10%"><?= $this->lang->line('discount'); ?>(<?=$CURRENCY;?>)</th>
                                                  <th rowspan='2' style="width:7.5%"><?= $this->lang->line('vat_amount'); ?></th> 
                                                   <th rowspan='2' style="width:7.5%"><?= $this->lang->line('purchase_price'); ?></th>
                                                   <th rowspan='2' style="width:7.5%"><?= $this->lang->line('total_amount'); ?></th>
                                                   <!-- <th rowspan='2' style="width:7.5%"><?= $this->lang->line('profit_margin'); ?>(%)</th>
                                                   <th rowspan='2' style="width:7.5%"><?= $this->lang->line('unit_sales_price'); ?>(<?=$CURRENCY;?>)</th> -->
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
                                          <label for="" class="col-sm-4 control-label"><?= $this->lang->line('total_quantities'); ?></label>    
                                          <div class="col-sm-4">
                                             <label class="control-label total_quantity text-success" style="font-size: 15pt;">0</label>
                                          </div>
                                       </div>
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
                                                <option value='in_percentage'>เปอร์เซ็นต์</option>
                                                <option value='in_fixed'>บาท</option>
                                             </select>
                                          </div>
                                         
                                          <script type="text/javascript">
                                             <?php if($discount_type!=''){ ?>
                                                 document.getElementById('discount_to_all_type').value='<?php echo  $discount_type; ?>';
                                             <?php }?>
                                          </script>
                                         
                                       </div>
                                    </div>
                                 </div> 
                         
                                <div class="row">
                                    <div class="col-md-12">
                                       <div class="form-group">
                                          <label for="purchase_note" class="col-sm-4 control-label"><?= $this->lang->line('invoiceTerms'); ?></label>    
                                          <div class="col-sm-8">
                                             <textarea class="form-control text-left" id='purchase_note' name="purchase_note"><?=$purchase_note;?></textarea>
                                            <span id="purchase_note_msg" style="display:none" class="text-danger"></span>
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
                                                   <h3>
                                                    <?= $CI->currency('<b id="subtotal_amt" name="subtotal_amt">0.00</b>'); ?>
                                                   </h3>
                                                </th>
                                             </tr>
                                              <tr>
                                                <th class="text-right" style="font-size: 17px;"><?= $this->lang->line('discount_on_all'); ?></th>
                                                <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                   <h3>
                                                    <?= $CI->currency('<b id="discount_to_all_amt" name="discount_to_all_amt">0.00</b>'); ?></h3>
                                                </th>
                                             </tr>
                                            
                                    

                                             <tr  class='text-primary'>
                                                <th class="text-right" style="font-size: 20px;"><?= $this->lang->line('grand_total'); ?></th>
                                                <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                   <h3>
                                                    <?= $CI->currency('<b id="total_amt" name="total_amt">0.00</b>'); ?>
                                                   </h3>
                                                </th>
                                             </tr>
                                             
                                                                  
                                          </table>
   
                                       </div>
                                    </div>
                                 </div>
                              </div>

                              <div class="col-xs-12 ">
                                 <div class="col-sm-12">
                                       <div class="box-body ">
                                        <div class="col-md-12">
                                          <table class="table table-hover table-bordered" style="width:100%" id="payments_table"><h4 class="box-title text-info"><?= $this->lang->line('previous_payments_information'); ?> : </h4>
                                             <thead>
                                                <tr class="bg-gray " >
                                                   <th>#</th>
                                                   <th><?= $this->lang->line('date'); ?></th>
                                                   <th><?= $this->lang->line('payment_type'); ?></th>
                                                   <th><?= $this->lang->line('payment_note'); ?></th>
                                                   <th><?= $this->lang->line('payment'); ?></th>
                                                   <th><?= $this->lang->line('action'); ?></th>
                                                </tr>
                                             </thead>
                                             <tbody>
                                                <?php 
                                                  if(isset($purchase_id)){
                                                    $q3 = $this->db->query("select * from db_purchasepayments where purchase_id=$purchase_id");
                                                    if($q3->num_rows()>0){
                                                      $i=1;
                                                      $total_paid = 0;
                                                      foreach ($q3->result() as $res3) {
                                                        echo "<tr class='text-center text-bold' id='payment_row_".$res3->id."'>";
                                                        echo "<td>".$i."</td>";
                                                        echo "<td>".show_date($res3->payment_date)."</td>";
                                                        echo "<td>".$res3->payment_type."</td>";
                                                        echo "<td>".$res3->payment_note."</td>";
                                                        echo "<td class='text-right' id='paid_amt_$i'>".
                                                         $CI->currency($res3->payment)."</td>";
                                                        echo '<td><i class="fa fa-trash text-red pointer" onclick="delete_payment('.$res3->id.')"> Delete</i></td>';
                                                        echo "</tr>";
                                                        $total_paid +=$res3->payment;
                                                        $i++;
                                                      }
                                                      echo "<tr class='text-right text-bold'><td colspan='4' >รวม</td><td data-rowcount='$i' id='paid_amt_tot'>".
                                                      $CI->currency(number_format($total_paid,2,'.',''))."</td><td></td></tr>";
                                                    }
                                                    else{
                                                      echo "<tr><td colspan='6' class='text-center text-bold'>ไม่มีการชำระเงิน!!</td></tr>";
                                                    }

                                                  }
                                                  else{
                                                    echo "<tr><td colspan='6' class='text-center text-bold'>กำลังเตรียมชำระเงิน!!</td></tr>";
                                                  }
                                                ?>
                                             </tbody>
                                          </table>
                                        </div>
                                       </div>
                                       <!-- /.box-body -->
                                    </div>
                                 <!-- /.box -->
                              </div>

                              <div class="col-xs-12 ">
                                 <div class="col-sm-12">
                                       <div class="box-body ">

                                          <div class="col-md-12 payments_div payments_div_">
                                            <h4 class="box-title text-info"><?= $this->lang->line('make_payment'); ?> : </h4>
                                          <div class="box box-solid bg-gray">
                                            <div class="box-body">
                                              <div class="row">
                                         
                                                <div class="col-md-4">
                                                  <div class="">
                                                  <label for="amount"><?= $this->lang->line('amount'); ?></label>
                                                    <input type="text" class="form-control text-right paid_amt only_currency" id="amount" name="amount" placeholder="" >
                                                      <span id="amount_msg" style="display:none" class="text-danger"></span>
                                                </div>
                                               </div>
                                                <div class="col-md-4">
                                                  <div class="">
                                                    <label for="payment_type"><?= $this->lang->line('payment_type'); ?></label>
                                                    <select class="form-control select2" id='payment_type' name="payment_type">
                                                      <?php
                                                        $q1=$this->db->query("select * from db_paymenttypes where status=1 and store_id=".get_current_store_id());
                                                         if($q1->num_rows()>0){
                                                            echo "<option value=''>-เลือก-</option>";
                                                             foreach($q1->result() as $res1){
                                                                
                                                                $selected = (strtoupper($res1->payment_type)==strtoupper('เงินสด')) ? 'selected' : '';
                                                                echo "<option $selected value='".$res1->payment_type."'>".$res1->payment_type ."</option>";
                                                           }
                                                         }
                                                         else{
                                                            echo "<option>None</option>";
                                                         }
                                                        ?>
                                                    </select>
                                                    <span id="payment_type_msg" style="display:none" class="text-danger"></span>
                                                  </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="account_id"><?= $this->lang->line('account'); ?></label>
                                                    <select class="form-control select2" id='account_id' name="account_id">
                                                      <?php
                                                        echo '<option value="">-เลือก-</option>'; 
      
                          $account_id = get_store_details()->default_account_id;
                          if(empty($account_id)){
                            $q_acc = $this->db->select("id")->where("store_id",get_current_store_id())->where("account_name","รายจ่ายซื้อสินค้า")->get("ac_accounts");
                            if($q_acc->num_rows()>0){
                              $account_id = $q_acc->row()->id;
                            }
                          }
                          echo get_accounts_select_list($account_id);
                        ?>
                              
                                                              
                                                    </select>
                                                    <span id="account_id_msg" style="display:none" class="text-danger"></span>
                                                </div>
                                            <div class="clearfix"></div>
                                        </div>  
                                        <div class="row">
                                               <div class="col-md-12">
                                                  <div class="">
                                                    <label for="payment_note"><?= $this->lang->line('payment_note'); ?></label>
                                                    <textarea type="text" class="form-control" id="payment_note" name="payment_note" placeholder="" ></textarea>
                                                    <span id="payment_note_msg" style="display:none" class="text-danger"></span>
                                                  </div>
                                               </div>
                                                
                                            <div class="clearfix"></div>
                                        </div>   
                                        </div>
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
                                if(isset($purchase_id)){
                                  $btn_id='update';
                                  $btn_name="อัพเดท";
                                  echo '<input type="hidden" name="purchase_id" id="purchase_id" value="'.$purchase_id.'"/>';
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
  
 <?php include"footer.php"; ?>
<!-- SOUND CODE -->
<?php include"comman/code_js_sound.php"; ?>
<!-- GENERAL CODE -->
<?php include"comman/code_js.php"; ?>

 <script src="<?php echo $theme_link; ?>js/modals.js"></script>
 <script src="<?php echo $theme_link; ?>js/modals/modal_item.js"></script>
  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<script src="<?php echo $theme_link; ?>js/purchase.js"></script>  
<script src="<?php echo $theme_link; ?>js/ajaxselect/supplier_select_ajax.js"></script>  

<script>

         //supplier Selection Box Search
         function getsupplierSelectionId() {
           return '#supplier_id';
         }

         $(document).ready(function () {

            var supplier_id = "<?= (!empty($supplier_id)) ? $supplier_id : '';  ?>";

            if(supplier_id!=''){
               autoLoadFirstsupplier(supplier_id);
            }

         });
         //supplier Selection Box Search - END


        var base_url=$("#base_url").val();
        $("#store_id").on("change",function(){
          var store_id=$(this).val();
          $.post(base_url+"purchase/get_suppliers_select_list",{store_id:store_id},function(result){
              $("#supplier_id").html('').append(result).select2();
              $("#purchase_table > tbody").empty();
              final_total();
          });
          $.post(base_url+"sales/get_tax_select_list",{store_id:store_id},function(result){
              $("#other_charges_tax_id").html('').append(result).select2();
              final_total();
          });
          
        });

        /*Warehouse*/
        $("#warehouse_id").on("change",function(){
          var warehouse_id=$(this).val();
          $("#purchase_table > tbody").empty();
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
          
       


         
         /* ---------- CALCULATE TAX -------------*/
         function calculate_tax(i){ //i=Row
           set_tax_value(i);

           //Find the Tax type and Tax amount
           var tax_type = $("#tr_tax_type_"+i).val();
           var tax_amount = get_float_type_data("#td_data_"+i+"_5");
           var qty=get_float_type_data("#td_data_"+i+"_3")
           var purchase_price=get_float_type_data("#td_data_"+i+"_4");
           var discount =get_float_type_data("#td_data_"+i+"_8");
           var tax=get_float_type_data("#tr_tax_value_"+i);
           
           var amt=qty * purchase_price;//Taxable
         
           var total_amt=amt-discount;

          

           total_amt = (tax_type=='Inclusive') ? total_amt : total_amt + tax_amount;
           
           //CAlculate Item wise price and tax and discount
           var tax_each = (tax_type=='Inclusive') ? 0 : calculate_exclusive(purchase_price-discount,tax);
           
           $("#td_data_"+i+"_10").val('').val(to_Fixed(total_amt/qty));
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
                       var tax_amt = parse_float($("#td_data_"+i+"_5").val());
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
            
            // Other charges (Sequence A - treats input as VAT-inclusive)
            var other_charges_input = parse_float($("#other_charges_input").val());
            var other_charges_total_amt = 0;
            if(other_charges_input > 0){
               var other_charges_type = $("#other_charges_type").val();
               var oc_base = (other_charges_type == 'Percentage' || other_charges_type == 'in_percentage') ? (subtotal_all * other_charges_input / 100) : other_charges_input;
               
               var oc_tax_id = $('option:selected', '#other_charges_tax_id').attr('data-tax');
               var oc_tax_rate = parse_float(oc_tax_id);
               
               // Tiered fallback for other charges
               if(oc_tax_rate <= 0) {
                  if(max_tax_rate > 0) { oc_tax_rate = max_tax_rate; }
                  else if(typeof store_vat_no !== 'undefined' && store_vat_no > 0) { oc_tax_rate = store_vat_no; }
               }
               
               if(oc_tax_rate > 0){
                  var oc_base_detaxed = (oc_base * 100) / (100 + oc_tax_rate);
                  other_charges_total_amt = oc_base;
                  if(!tax_groups[oc_tax_rate]) tax_groups[oc_tax_rate] = 0;
                  tax_groups[oc_tax_rate] += oc_base_detaxed;
               } else {
                  other_charges_total_amt = oc_base;
                  total_exempt_base += oc_base;
               }
            }

            // Discount to all
            var total_base_sum = total_exempt_base;
            for(var rate in tax_groups) { total_base_sum += tax_groups[rate]; }
            
            var discount_input = parse_float($("#discount_to_all_input").val());
            var discount_type = $("#discount_to_all_type").val();
            var inclusive_total = subtotal_all + other_charges_total_amt;
            var total_discount = (discount_type == 'in_percentage' || discount_type == 'Percentage') ? (inclusive_total * discount_input / 100) : discount_input;
            if(isNaN(total_discount)) total_discount = 0;
            
            // Extract VAT from the global discount using Sequence A method
            var total_global_discount = (total_discount * 100) / (100 + max_tax_rate);
            
            // Prorate and calculate final components
            var final_grand_total = 0;
            var ratio = (total_base_sum > 0) ? (total_global_discount / total_base_sum) : 0;
            
            // Exempt portion
            var net_exempt = total_exempt_base - (total_exempt_base * ratio);
            final_grand_total += Math.round(net_exempt * 100) / 100;
            
            // Taxable portions
            for(var rate in tax_groups){
               var base = tax_groups[rate];
               var net_base = base - (base * ratio);
               var rounded_net_base = Math.round(net_base * 100) / 100;
               var vat_amt = Math.round((rounded_net_base * parse_float(rate) / 100) * 100) / 100;
               final_grand_total += rounded_net_base + vat_amt;
            }

            final_grand_total = Math.round(final_grand_total * 100) / 100;

            // Update UI
            $(".total_quantity").html(format_qty(total_quantity));
            $("#subtotal_amt").html(to_Fixed(subtotal_all));
            $("#other_charges_amt").html(to_Fixed(other_charges_total_amt));
            $("#discount_to_all_amt").html(to_Fixed(total_discount));
            $("#hidden_discount_to_all_amt").val(to_Fixed(total_discount));
            
            var total_rounded = Math.round(final_grand_total);
            var round_off_val = total_rounded - final_grand_total;
            
            $("#round_off_amt").html(to_Fixed(round_off_val));
            $("#total_amt").html(to_Fixed(total_rounded));
            $("#hidden_total_amt").val(to_Fixed(total_rounded));
            
            if(typeof save_operation === 'function' && save_operation()){
               $("#amount").val(to_Fixed(total_rounded));
            }
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
         console.log("Hello="+k);
         calculate_tax(k);
       }//if end
     }//for end

      //final_total();
    }

    //Purchase Items Modal Operations Start


    function show_purchase_item_modal(row_id){

      $('#purchase_item').modal('toggle');
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
      $("#td_data_"+row_id+"_15").html(tax_name);
      
      calculate_tax(row_id);
      $('#purchase_item').modal('toggle');
    }

    
    function set_tax_value(row_id){
      //get the purchase price of the item
      var tax_type = $("#tr_tax_type_"+row_id).val();
      var tax = $("#tr_tax_value_"+row_id).val(); //%
      var qty=$("#td_data_"+row_id+"_3").val();
          qty = (isNaN(qty)) ? 0 :qty;
      var purchase_price = parseFloat($("#td_data_"+row_id+"_4").val());
          purchase_price = (isNaN(purchase_price)) ? 0 :purchase_price;
          purchase_price = purchase_price * qty;

      /*Discount*/
      var item_discount_type = $("#item_discount_type_"+row_id).val();
      var item_discount_input = parseFloat($("#item_discount_input_"+row_id).val());
          item_discount_input = (isNaN(item_discount_input)) ? 0 :item_discount_input;

      //Calculate discount      
      var discount_amt=(item_discount_type=='Percentage') ? ((purchase_price) * item_discount_input)/100 : (item_discount_input*qty);
      purchase_price-=parseFloat(discount_amt);

      var tax_amount = (tax_type=='Inclusive') ? calculate_inclusive(purchase_price,tax) : calculate_exclusive(purchase_price,tax);
      
      $("#td_data_"+row_id+"_8").val(to_Fixed(discount_amt));

      $("#td_data_"+row_id+"_5").val(to_Fixed(tax_amount));
    }
    //Purchase Items Modal Operations End
    
</script>
      <!-- UPDATE OPERATIONS -->
      <script type="text/javascript">
         <?php if(isset($purchase_id)){ ?> 
             $(document).ready(function(){
                /*$("#warehouse_id").attr('readonly',true);*/
                $("#store_id").attr('readonly',true);
                var base_url='<?= base_url();?>';
                var purchase_id='<?= $purchase_id;?>';
                $("#purchase_box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
                $.post(base_url+"purchase/return_purchase_list/"+purchase_id,{},function(result){
                  //alert(result);
                  $('#purchase_table tbody').append(result);
                  $("#hidden_rowcount").val(parseInt(<?=$items_count;?>)+1);
                  try {
                    success.currentTime = 0;
                    success.play();
                  } catch(e) { console.error("Sound play error", e); }
                  
                  try {
                    enable_or_disable_item_discount();
                  } catch(e) { console.error("enable_or_disable_item_discount error", e); }
                  
              }).fail(function() {
                  toastr["error"]("Failed to load items. Please try again.");
                  try {
                    failed.currentTime = 0;
                    failed.play();
                  } catch(e) {}
              }).always(function() {
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

