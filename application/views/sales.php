<!DOCTYPE html>
<html>

<head>
<!-- FORM CSS CODE -->
<?php include"comman/code_css.php"; ?>
<!-- </copy> -->
<!-- bootstrap wysihtml5 - text editor -->
  <link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
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
    if(isset($sales_id)){

      //Edit
      $q2 = $this->db->query("select * from db_sales where id=$sales_id");
      $customer_id=$q2->row()->customer_id;
      $sales_date=show_date($q2->row()->sales_date);
      $due_date=(!empty($q2->row()->due_date)) ? show_date($q2->row()->due_date) : '';
      $sales_status=$q2->row()->sales_status;
      $warehouse_id=$q2->row()->warehouse_id;
      $reference_no=$q2->row()->reference_no;
      $discount_input=store_number_format($q2->row()->discount_to_all_input,0);
      $discount_type=$q2->row()->discount_to_all_type;
      $other_charges_input=store_number_format($q2->row()->other_charges_input,0);
      $other_charges_type=$q2->row()->other_charges_type;
      $other_charges_tax_id=$q2->row()->other_charges_tax_id;
      $sales_note=$q2->row()->sales_note;
      $store_id=$q2->row()->store_id;
      
      $init_code=$q2->row()->init_code;
      $count_id=$q2->row()->count_id;

      $coupon_id=$q2->row()->coupon_id;
      $coupon_code = (!empty($coupon_id)) ? get_customer_coupon_details($coupon_id)->code : '';
      $invoice_terms=$q2->row()->invoice_terms;


      $items_count = $this->db->query("select count(*) as items_count from db_salesitems where sales_id=$sales_id")->row()->items_count;
      $save_operation = false;
    }
    else if(isset($sell_again_id)){
      //Sell Again - fetch old data but initialize as new
      $q2 = $this->db->query("select * from db_sales where id=$sell_again_id");
      $customer_id=$q2->row()->customer_id;
      $sales_date=show_date(date("d-m-Y"));
      $due_date='';
      $sales_status=$q2->row()->sales_status;
      $warehouse_id=$q2->row()->warehouse_id;
      $reference_no=$q2->row()->reference_no;
      $discount_input=store_number_format($q2->row()->discount_to_all_input,0);
      $discount_type=$q2->row()->discount_to_all_type;
      $other_charges_input=store_number_format($q2->row()->other_charges_input,0);
      $other_charges_type=$q2->row()->other_charges_type;
      $other_charges_tax_id=$q2->row()->other_charges_tax_id;
      $sales_note=$q2->row()->sales_note;
      $store_id=$q2->row()->store_id;
      
      $init_code=get_only_init_code('sales');
      $count_id=get_last_count_id('db_sales');

      $coupon_code='';

      $store_details = get_store_details($store_id);
      $invoice_terms =$store_details->invoice_terms;
      
      $items_count = $this->db->query("select count(*) as items_count from db_salesitems where sales_id=$sell_again_id")->row()->items_count;
      $save_operation = true;
    }
    else if(isset($quotation_id) && !empty($quotation_id)){
      //NEW
      $q2 = $this->db->query("select * from db_quotation where id=$quotation_id");
      $customer_id=$q2->row()->customer_id;
      $sales_date=show_date($q2->row()->quotation_date);
      $due_date='';
      $sales_status='';
      $warehouse_id=$q2->row()->warehouse_id;
      $reference_no=$q2->row()->reference_no;
      $discount_input=store_number_format($q2->row()->discount_to_all_input,0);
      $discount_type=$q2->row()->discount_to_all_type;
      $other_charges_input=store_number_format($q2->row()->other_charges_input,0);
      $other_charges_type=$q2->row()->other_charges_type;
      $other_charges_tax_id=$q2->row()->other_charges_tax_id;
      $sales_note=$q2->row()->quotation_note;
      $store_id=$q2->row()->store_id;

      //$sales_code = get_init_code('sales');
      $init_code=get_only_init_code('sales');
      $count_id=get_last_count_id('db_sales');

      $items_count = $this->db->query("select count(*) as items_count from db_quotationitems where quotation_id=$quotation_id")->row()->items_count;
      $coupon_code='';

      $store_details = get_store_details($store_id);
      $invoice_terms =$store_details->invoice_terms;
      $save_operation = true;

       $company_city=$res1->city;
      $company_address=$res1->address;
      $company_gst_no=$res1->gst_no;
      $company_vat_no=$res1->vat_no;
     
      $vat_amt=($grand_total /($company_vat_no+100))*$company_vat_no;
      $befor_vat=$grand_total-$vat_amt;
    }
    else{
      //NEW
      $customer_id  = $sales_date = $sales_status = $warehouse_id =$due_date=
      $reference_no  =$coupon_code=
      $other_charges_input          = $other_charges_tax_id = $store_id =
      $other_charges_type = 
      $discount_type  = $sales_note = '';
      $sales_date=show_date(date("d-m-Y"));
      $discount_input = $this->db->select("sales_discount")->get('db_store')->row()->sales_discount;
      $discount_input = ($discount_input==0) ? 0 : $discount_input;
      
      $init_code=get_only_init_code('sales');
      $count_id=get_last_count_id('db_sales');

      $store_details = get_store_details();
      $invoice_terms =$store_details->invoice_terms;
      $save_operation = true;
    }
   
   
    ?>

 

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- **********************MODALS***************** -->
    <?php include"modals/modal_customer.php"; ?>
    <?php include"modals/modal_item.php"; ?>
    <?php include"modals/modal_item_or_service.php"; ?>
   <?php /*include"modals/modal_service.php";*/ ?>
    <!-- **********************MODALS END***************** -->
    <!-- Content Header (Page header) -->
    <section class="content-header">
         <h1>
            <?=$page_title;?>
            <small>Add/Update Sales</small>
         </h1>
         <ol class="breadcrumb">
            <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="<?php echo $base_url; ?>sales"><?= $this->lang->line('sales_list'); ?></a></li>
            <li><a href="<?php echo $base_url; ?>sales/add"><?= $this->lang->line('new_sales'); ?></a></li>
            <li class="active"><?=$page_title;?></li>
         </ol>
      </section>

    <!-- Main content -->
     <section class="content">
               <div class="row">
                <!-- ********** ALERT MESSAGE START******* -->
               <?php include"comman/code_flashdata.php"; ?>
               <?php include"modals/modal_sales_item.php"; ?>
               
               <!-- ********** ALERT MESSAGE END******* -->
                  <!-- right column -->
                  <div class="col-md-12">
                     <!-- Horizontal Form -->
                     <div class="box box-primary " >
                        <!-- style="background: #68deac;" -->
                        
                        <!-- form start -->
                         <!-- OK START -->
                        <?= form_open('#', array('class' => 'form-horizontal', 'id' => 'sales-form', 'enctype'=>'multipart/form-data', 'method'=>'POST'));?>
                           <input type="hidden" id="base_url" value="<?php echo $base_url;; ?>">
                           <input type="hidden" value='1' id="hidden_rowcount" name="hidden_rowcount">
                           <input type="hidden" value='0' id="hidden_update_rowid" name="hidden_update_rowid">
                           <input type="hidden" value='Final' id="sales_status" name="sales_status">

                          <?php if(isset($quotation_id)) {?>
                           <input type="hidden" id="quotation_id" name="quotation_id" value="<?php echo $quotation_id;; ?>">
                           <?php } ?>

                           <div class="box-body">
                              <!-- Store Code -->
                              <?php 
                              /*if(store_module() && is_admin()) {$this->load->view('store/store_code',array('show_store_select_box'=>true,'store_id'=>$store_id,'div_length'=>'col-sm-3')); }else{*/
                                echo "<input type='hidden' name='store_id' id='store_id' value='".get_current_store_id()."'>";
                              /*}*/
                              ?>
                              <!-- Store Code end -->
                              <!-- Warehouse Code -->
                              
                              <!-- Warehouse Code end -->
                              <div class="form-group">
                                 <label for="warehouse_id" class="col-sm-2 control-label"><?= $this->lang->line('warehouse'); ?><label class="text-danger">*</label></label>
                                 <div class="col-sm-3">
                                    <select class="form-control select2 " id="warehouse_id" name="warehouse_id" >
                                       <?= get_warehouse_select_list($warehouse_id,get_current_store_id());?>
                                    </select>
                                    <span id="warehouse_id_msg" style="display:none" class="text-danger"></span>
                                 </div>
                                 <label for="init_code" class="col-sm-2 control-label"><?= $this->lang->line('sales_code'); ?><label class="text-danger">*</label></label>
                                 <div class="col-sm-2" style="padding-right:0;">
                                    <input type="text" value="<?= $init_code; ?>" class="form-control  no-padding" style='font-size:20px;' id="init_code" name="init_code" placeholder="" >
                                       <span id="init_code_msg" style="display:none" class="text-danger"></span>
                                 </div>
                                 <div class="col-sm-1" style="padding-left:1;">
                                    <input type="text" style='font-size:20px;' value="<?php echo  $count_id; ?>" class="form-control no_special_char" id="count_id" name="count_id" placeholder="" >
                                       <span id="count_id_msg" style="display:none" class="text-danger"></span>
                                 </div>
                         
                              </div>
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
                                  
                                  <label for="sales_date" class="col-sm-2 control-label"><?= $this->lang->line('sales_date'); ?> <label class="text-danger">*</label></label>
                                  <div class="col-sm-3">
                                    <div class="input-group date">
                                       <div class="input-group-addon">
                                          <i class="fa fa-calendar"></i>
                                       </div>
                                       <input type="text" class="form-control pull-right datepicker"  id="sales_date" name="sales_date" readonly onkeyup="shift_cursor(event,'sales_status')" value="<?= $sales_date;?>">
                                    </div>
                                    <span id="sales_date_msg" style="display:none" class="text-danger"></span>
                                   </div> 
                                </div> 
                                                                  
                              <div class="form-group">
                                <!-- <div class="col-sm-3">
                                     <span id="customer_id_msg" style="display:none" class="text-danger"></span>
                                    <lable><?= $this->lang->line('previous_due'); ?> :<label class="customer_previous_due text-red" style="font-size: 18px;">0.00</label></lable>                             
                                 </div> -->
                                    <label for="due_date" class="col-sm-2 control-label"><?= $this->lang->line('due_date'); ?></label>
                                   <div class="col-sm-3">
                                    <div class="input-group date">
                                       <div class="input-group-addon">
                                          <i class="fa fa-calendar"></i>
                                       </div>
                                       <input type="text" class="form-control pull-right datepicker"  id="due_date" name="due_date"  value="<?= $due_date;?>">
                                    </div>
                                    <span id="due_date_msg" style="display:none" class="text-danger"></span>
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
                                                 <input type="text" class="form-control " placeholder="บาร์โค้ด/ชื่อสินค้า/รหัสสินค้า" autofocus id="item_search">


                                                 <span class="input-group-addon pointer text-green show_item_service" title="Click to Add New Item or Service"><i class="fa fa-plus"></i></span>

                                                 

                                              </div>
                                        </div>
                                      </div>
                                      <div class="box-body">
                                        <div class="table-responsive" style="width: 100%">
                                        <table class="table table-hover table-bordered" style="width:100%" id="sales_table">
                                             <thead class="custom_thead">
                                                <tr class="bg-success" >
                                                   <th rowspan='2' style="width:15%"><?= $this->lang->line('item_name'); ?></th>
                                                 
                                                   <th rowspan='2' style="width:10%;min-width: 180px;"><?= $this->lang->line('quantity'); ?></th>
                                                   <th rowspan='2' style="width:10%"><?= $this->lang->line('unit_cost'); ?></th> 
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
                                             <label class="control-label total_quantity text-red" style="font-size: 15pt;">0</label>
                                          </div>
                                       </div>
                                    </div>
                                 </div>

                                   <div class="row">
                                    <div class="col-md-12">
                                       <div class="form-group">
                                          <label for="other_charges_input" class="col-sm-4 control-label"><?= $this->lang->line('other_charges'); ?></label>    
                                          <div class="col-sm-4">
                                             <input type="text" class="form-control text-right only_currency" id="other_charges_input" name="other_charges_input" onkeyup="final_total();" value="<?php echo  $other_charges_input; ?>">
                                          </div>
                                          <div class="col-sm-2">
                                             <select class="form-control" onchange="final_total();" id='other_charges_type' name="other_charges_type">
                                                <option value='Percentage'>เปอร์เซ็นต์%</option>
                                                <option value='Fixed'>จำนวนเงิน</option>
                                             </select>
                                          </div>
                                          <div class="col-sm-2">
                                             <select class="form-control " id="other_charges_tax_id" name="other_charges_tax_id" onchange="final_total();" style="width: 100%;">
                                                <?= get_tax_select_list($other_charges_tax_id,get_current_store_id());?>
                                             </select>
                                          </div>
                                          <!-- Dynamicaly select other_charges_type -->
                                          <script type="text/javascript">
                                             <?php if($other_charges_type!=''){ ?>
                                                 document.getElementById('other_charges_type').value='<?php echo  $other_charges_type; ?>';
                                             <?php }?>
                                          </script>
                                          <!-- Dynamicaly select other_charges_type end-->
                                       </div>
                                    </div>
                                 </div>
                                                                                                 
                                 <div class="row">
                                    <div class="col-md-12">
                                       <div class="form-group">
                                          <label for="discount_to_all_input" class="col-sm-4 control-label"><?= $this->lang->line('discount_on_all'); ?></label>    
                                          <div class="col-sm-4">
                                             <input type="text" class="form-control  text-right only_currency" id="discount_to_all_input" name="discount_to_all_input" onkeyup="enable_or_disable_item_discount();" value="<?= store_number_format($discount_input,0); ?>">
                                          </div>
                                          <div class="col-sm-4">
                                             <select class="form-control" onchange="final_total();" id='discount_to_all_type' name="discount_to_all_type">
                                                <option value='in_percentage'>เปอร์เซ็นต์ %</option>
                                                <option value='in_fixed'>จำนวนเงิน</option>
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
                                          <label for="sales_note" class="col-sm-4 control-label"><?= $this->lang->line('note'); ?></label>    
                                          <div class="col-sm-8">
                                             <textarea class="form-control text-left" id='sales_note' name="sales_note"><?= $sales_note; ?></textarea>
                                            <span id="sales_note_msg" style="display:none" class="text-danger"></span>
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
                                                <th class="text-right" style="font-size: 17px;"><?= $this->lang->line('other_charges'); ?></th>
                                                <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                   <h4><b id="other_charges_amt" name="other_charges_amt">0.00</b></h4>
                                                </th>
                                             </tr>
                                          
                                              <tr>
                                                <th class="text-right" style="font-size: 17px;"><?= $this->lang->line('discount_on_all'); ?></th>
                                                <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                   <h4><b id="discount_to_all_amt" name="discount_to_all_amt">0.00</b></h4>
                                                </th>
                                             </tr>
                                           
                                          
                                        
                                             <tr>
                                                <th class="text-right" style="font-size: 17px;"><?= $this->lang->line('grand_total'); ?></th>
                                                <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                   <h2 style="color:red;"><b id="total_amt" name="total_amt">0.00</b></h2>
                                                </th>
                                             </tr>
                                          </table>
                                       </div>
                                    </div>
                                 </div>
                              </div>


                              <div>
                                 <p>.</p>
                              </div>

                   <div class="form-group" style="margin-left: 30px; margin-right: 30px;">
                        <div class="col-sm-4 col-xs-6">
                            <div class="input-group">                   
                               <span class="input-group-addon"><?= $this->lang->line('reference_sales'); ?></span>
                                <input type="text" class="form-control" id="reference_no" name="reference_no" placeholder="ยกเลิกใบกำกับภาษีเลขที่" value="<?php echo isset($reference_no) ? $reference_no : ''; ?>">
                            </div>
                        </div>
                         
                       <div class="col-sm-4 col-xs-6">
                        <div class="input-group">
                            <span class="input-group-addon"><?= $this->lang->line('date'); ?></span>
                             <input type="text" class="form-control" id="ref_sales_date" name="ref_sales_date" placeholder="Sales Date" readonly value="<?php echo isset($sales_date) ? $sales_date : ''; ?>">
                        </div>
                       </div>

                       <div class="col-sm-4">
                         <textarea class="form-control" style="height: 34px;" id="sales_note" name="sales_note" placeholder="<?= $this->lang->line('reason')?>"><?php echo isset($sales_note) ? $sales_note : ''; ?></textarea>
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
                                                  if(isset($sales_id)){
                                                    $q3 = $this->db->query("select * from db_salespayments where sales_id=$sales_id");
                                                    if($q3->num_rows()>0){
                                                      $i=1;
                                                      $total_paid = 0;
                                                      foreach ($q3->result() as $res3) {
                                                        echo "<tr class='text-center text-bold' id='payment_row_".$res3->id."'>";
                                                        echo "<td>".$i."</td>";
                                                        echo "<td>".show_date($res3->payment_date)."</td>";
                                                        echo "<td>".$res3->payment_type."</td>";
                                                        echo "<td>".$res3->payment_note."</td>";
                                                        echo "<td class='text-right' id='paid_amt_$i'>".store_number_format($res3->payment)."</td>";
                                                        echo '<td><i class="fa fa-trash text-red pointer" onclick="delete_payment('.$res3->id.')"> Delete</i></td>';
                                                        echo "</tr>";
                                                        $total_paid +=$res3->payment;
                                                        $i++;
                                                      }
                                                      echo "<tr class='text-right text-bold'><td colspan='4' >รวม</td><td data-rowcount='$i' id='paid_amt_tot'>".store_number_format($total_paid)."</td><td></td></tr>";
                                                    }
                                                    else{
                                                      echo "<tr><td colspan='6' class='text-center text-bold'>ไม่พบการชำระเงิน!!</td></tr>";
                                                    }

                                                  }
                                                  else{
                                                    echo "<tr><td colspan='6' class='text-center text-bold'>การชำระเงิน!!</td></tr>";
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



                                    <div class="col-md-12">
                                       
                                       <!-- /.box-header -->
                                       <div class="box-body">
                                         

                                         <div class="box box-default collapsed-box">
                                       <div class="box-header with-border">
                                         <h3 class="box-title"><?= $this->lang->line('invoiceTermsAndConditions')?></h3>

                                         <div class="box-tools pull-right">
                                           <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus text-danger"></i>
                                           </button>
                                       </div>
                                    </div>
                                       <!-- /.box-header -->
                                       <div class="box-body">
                                        <textarea id="invoice_terms" name="invoice_terms" class="textarea" placeholder="Place some text here" style="width: 100%; height: 100px; font-size: 14px; border: 1px solid #dddddd; padding: 10px;"><?= $invoice_terms;?></textarea>
                                       </div>
                                       <!-- /.box-body -->
                                      
                                     </div>

                                       </div>
                                       <!-- /.box-body -->
                                    
                                     <!-- /.box -->
                                   </div>



                                       <div class="box-body ">

                                          <div class="col-md-12 payments_div payments_div_">
                                            <h4 class="box-title text-info"><?= $this->lang->line('payment'); ?> : </h4>

                                            <div class="row">
                                                 <div class="col-md-4">

                                                  <span for="">
                                                    <label>
                                                    <?= $this->lang->line('advance'); ?> : <label class='customer_tot_advance'></label>
                                                  </label>
                                                  </span>
                                                  
                                                  <div class="checkbox">
                                                    <label>
                                                      <input type="checkbox" id="allow_tot_advance" name="allow_tot_advance"> <?= $this->lang->line('adjust_advance_payment'); ?>
                                                    </label>
                                                  </div>
                                                 </div>
                                                  
                                              <div class="clearfix"></div>
                                          </div>
                                          <br>

                                          <div class="box box-solid bg-gray"> <span class="text-info"> ขายเครดิต ( ใส่จำนวนเงินเป็น 0 และเลือกวิธีชำระ แล้วบันทึก )</span> 
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
                                                            echo "<option value=''>ไม่มี</option>";
                                                         }
                                                        ?>
                                                    </select>
                                                    <span id="payment_type_msg" style="display:none" class="text-danger"></span>
                                                  </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="account_id"><?= $this->lang->line('account'); ?></label>
                                                    <?php
                                                      $account_id = get_store_details()->default_account_id;
                                                      if(empty($account_id)){
                                                        $q_acc = $this->db->select("id")->where("store_id",get_current_store_id())->where("account_name","รายรับ")->get("ac_accounts");
                                                        if($q_acc->num_rows()>0){
                                                          $account_id = $q_acc->row()->id;
                                                        }
                                                      }
                                                    ?>
                                                    <select class="form-control select2" id='account_id' name="account_id">
                                                      <option value="">-ไม่มี-</option>
                                                        <?= get_accounts_select_list($account_id);?>
                                                    </select>
                                                    <span id="account_id_msg" style="display:none" class="text-danger"></span>
                                                </div>
                                            <div class="clearfix"></div>
                                        </div> 

                                        <div class="row cheque_div" style="display: none;">
                                         
                                                <div class="col-md-8">
                                                  <div class="">
                                                  <label for="cheque_number"><?= $this->lang->line('cheque_number'); ?></label>
                                                    <input type="text" class="form-control" id="cheque_number" name="cheque_number" placeholder="" >
                                                      <span id="cheque_number_msg" style="display:none" class="text-danger"></span>
                                                </div>
                                               </div>
                                                
                                               <div class="col-md-4">
                                                  <div class="">
                                                  <label for="cheque_period"><?= $this->lang->line('cheque_period_days'); ?></label>
                                                    <input type="text" class="form-control only_currency" id="cheque_period" name="cheque_period" placeholder="" >
                                                      <span id="cheque_period_msg" style="display:none" class="text-danger"></span>
                                                </div>
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

                              <!-- SMS Sender while saving -->
                                <?php 
                                   //Change Return
                                    $send_sms_checkbox='disabled';
                                    if($CI->is_sms_enabled()){
                                      if(!isset($sales_id)){
                                        $send_sms_checkbox='checked';  
                                      }else{
                                        $send_sms_checkbox='';
                                      }
                                    }

                              ?> 
                             
                              <div class="col-xs-12 ">
                                 <div class="col-sm-12">
                                     <!--  <div class="box-body ">
                                          <div class="col-md-12">
                                            <div class="checkbox icheck">
                                      <label>
                                        <input type="checkbox" <?=$send_sms_checkbox;?> class="form-control" id="send_sms" name="send_sms" > <label for="sales_discount" class=" control-label"><?= $this->lang->line('send_sms_to_customer'); ?>
                                          <i class="hover-q " data-container="body" data-toggle="popover" data-placement="top" data-content="If checkbox is Disabled! You need to enable it from SMS -> SMS API <br><b>Note:<i>Walk-in Customer will not receive SMS!</i></b>" data-html="true" data-trigger="hover" data-original-title="" title="Do you wants to send SMS ?">
                                  <i class="fa fa-info-circle text-maroon text-black hover-q"></i>
                                </i>
                                        </label>
                                      </label>
                                    </div>
                                        </div>
                                       </div>-->
                                       <!-- /.box-body -->
                                    </div>
                                 <!-- /.box -->
                              </div> 
                           </div>
                           
                           <!-- /.box-body -->
                           <div class="box-footer col-sm-12">
                              <center>
                                <?php
                                if(isset($sales_id)){
                                  $btn_id='update';
                                  $btn_name="อัพเดท";
                                  echo '<input type="hidden" name="sales_id" id="sales_id" value="'.$sales_id.'"/>';
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
<script type="text/javascript">
  var walk_in_customer_name ='<?= get_walk_in_customer_name();?>'
</script>
<!-- Bootstrap WYSIHTML5 -->
<script src="<?php echo $theme_link; ?>plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>

<script src="<?php echo $theme_link; ?>plugins/jQueryUI/jquery-ui.min.js"></script>
<script src="<?php echo $theme_link; ?>js/sales.js"></script>  
<script>
    /*Reference No Autocomplete*/
    $("#reference_no").autocomplete({
        source: function(data, cb){
            $.ajax({
                autoFocus:true,
                url: '<?php echo base_url('sales/get_sales_suggestions') ?>', //Changed to sales controller
                method: 'GET',
                dataType: 'json',
                data: {
                    term: data.term
                },
                success: function(res){
                    var result;
                    result = [
                        {
                            label: 'No Results Found',
                            value: ''
                        }
                    ];

                    if (res.length) {
                        result = $.map(res, function(el){
                            return {
                                label: el.label,
                                value: el.value,
                                sales_date: el.sales_date
                            };
                        });
                    }

                    cb(result);
                }
            });
        },
        response:function(e,ui){
            if(ui.content.length==1){
                $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                $(this).autocomplete("close");
            }
        },
        select: function (e, ui) { 
            if(ui.item.value != ''){
                $("#ref_sales_date").val(ui.item.sales_date);
            }
        }
    });
</script>  
<script src="<?php echo $theme_link; ?>js/ajaxselect/customer_select_ajax.js"></script>  
      <script>

         //Customer Selection Box Search
         function getCustomerSelectionId() {
           return '#customer_id';
         }

         $(document).ready(function () {

            var customer_id = "<?= (!empty($customer_id)) ? $customer_id : '';  ?>";

            autoLoadFirstCustomer(customer_id);

         });
         //Customer Selection Box Search - END
         


         function save_operation() {
            <?php if($save_operation){ ?>
               return true;
            <?php }else{ ?>
               return false;
            <?php } ?>
         }

         $("#payment_type").on("change",function(){
          show_cheque_details();
        });
        function show_cheque_details(){
            var payment_type = $("#payment_type").val();
            if(payment_type.toUpperCase()=='<?=strtoupper(cheque_name())?>'){
               $(".cheque_div").show();
            }
            else{
               $(".cheque_div").hide();
               $("#cheque_period,#cheque_number").val('');
            }
        }
        

       
        function set_previous_due(previous_due,tot_advance){
          $(".customer_previous_due").html(previous_due);
          $(".customer_tot_advance").html(tot_advance);
        }

        var base_url=$("#base_url").val();
        $("#store_id").on("change",function(){
          var store_id=$("#store_id").val();
          $.post(base_url+"sales/get_customers_select_list",{store_id:store_id},function(result){
              $("#customer_id").html('').append(result).select2();
              $("#sales_table > tbody").empty();
              calculate_tax();
          });
          $.post(base_url+"sales/get_tax_select_list",{store_id:store_id},function(result){
              $("#other_charges_tax_id").html('').append(result).select2();
              calculate_tax();
          });
        });

        /*Warehouse*/
        $("#warehouse_id").on("change",function(){
          var warehouse_id=$(this).val();
          $("#sales_table > tbody").empty();
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
          failed.currentTime = 0; 
          failed.play();
         
        }*/
         
         /* ---------- CALCULATE TAX -------------*/
        
         // Store VAT rate for fallback (used when other charges have no tax selected)
         var store_vat_no = <?php $res_store = $this->db->query("select vat_no from db_store where id=".get_current_store_id())->row(); echo floatval($res_store->vat_no); ?>;

         function parse_float(val){
            if(!val) return 0;
            // Convert to string, remove commas, then parse
            var f = parseFloat( (val+'').replace(/,/g, '') );
            return isNaN(f) ? 0 : f;
         }

         function calculate_tax(i){ //i=Row
            set_tax_value(i);

           //Find the Tax type and Tax amount
           var tax_type = $("#tr_tax_type_"+i).val();
           var tax_amount = $("#td_data_"+i+"_11").val();
               tax_amount = parse_float(tax_amount);

           var qty=$("#td_data_"+i+"_3").val();
               qty = parse_float(qty);
           var sales_price=parse_float($("#td_data_"+i+"_10").val());
           $("#td_data_"+i+"_4").val(sales_price);
           /*Discounr*/
           var discount_amt=$("#td_data_"+i+"_8").val();
               discount_amt   = parse_float(discount_amt);

           var amt=qty * sales_price;//Taxable

           var total_amt=amt-discount_amt;
           total_amt = (tax_type=='Inclusive') ? total_amt : total_amt + tax_amount;
           
           //Set Unit cost
           $("#td_data_"+i+"_9").val('').val(to_Fixed(total_amt));
        
           final_total();
         }


        
         /* ---------- CALCULATE GST END -------------*/

         /*Calculate Coupon Discount Amount*/
         const discount_coupon_tot = function(subtotal) {
             var coupon_value=parse_float($(".coupon_value").text());
                 coupon_value = isNaN(coupon_value) ? 0 : coupon_value;

             var coupon_type=$(".coupon_type").text();

             var discount_amt =0;
             if(coupon_type!='' && coupon_value>0){
                 if(coupon_type=='Percentage'){
                     discount_amt=(subtotal*coupon_value)/100;
                 }
                 else{//Fixed
                     discount_amt=coupon_value;
                 }
             }
             return discount_amt;
         }

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
               var other_charges_type = $("#other_charges_type").val();
               var oc_base = (other_charges_type == 'Percentage') ? (subtotal_all * other_charges_input / 100) : other_charges_input;
               
               var oc_tax_id = $('option:selected', '#other_charges_tax_id').attr('data-tax');
               var oc_tax_rate = parse_float(oc_tax_id);
               
               // Tiered fallback for other charges: Prevailing Item Rate > Store VAT (Sequence A)
               if(oc_tax_rate <= 0) {
                  if(max_tax_rate > 0) { oc_tax_rate = max_tax_rate; }
                  else if(store_vat_no > 0) { oc_tax_rate = store_vat_no; }
               }
               
               if(oc_tax_rate > 0){
                  // Other charges are VAT-inclusive: de-tax them before grouping
                  var oc_base_detaxed = (oc_base * 100) / (100 + oc_tax_rate);
                  var oc_tax_amt = oc_base - oc_base_detaxed;
                  other_charges_total_amt = oc_base;
                  if(!tax_groups[oc_tax_rate]) tax_groups[oc_tax_rate] = 0;
                  tax_groups[oc_tax_rate] += oc_base_detaxed;
               } else {
                  other_charges_total_amt = oc_base;
                  total_exempt_base += oc_base;
               }
            }

            // Coupon
            var total_base_sum = total_exempt_base;
            for(var rate in tax_groups) { total_base_sum += tax_groups[rate]; }
            
            var coupon_amt = discount_coupon_tot(total_base_sum);
            
            // Discount to all — apply against GROSS Inclusive Total (Sequence A)
            var discount_input = parse_float($("#discount_to_all_input").val());
            var discount_type = $("#discount_to_all_type").val();
            
            var inclusive_total = subtotal_all + other_charges_total_amt;
            var total_discount = (discount_type == 'in_percentage') ? (inclusive_total * discount_input / 100) : discount_input;
            if(isNaN(total_discount)) total_discount = 0;
            
            // Extract VAT from the global discount (using the maximum tax rate of the cart)
            var total_global_discount = (total_discount * 100) / (100 + max_tax_rate);
            
            // Prorate and calculate final components using Sequence A
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
            $("#coupon_discount_amt").html(to_Fixed(coupon_amt));
            $("#discount_to_all_amt").html(to_Fixed(total_discount));
            $("#hidden_discount_to_all_amt").val(to_Fixed(total_discount));
            
            // Show 2-decimal grand total (Sequence A — no integer rounding)
            var round_off_val = 0;
            $("#round_off_amt").html(to_Fixed(round_off_val));
            $("#total_amt").html(to_Fixed(final_grand_total));
            $("#hidden_total_amt").val(to_Fixed(final_grand_total));
            
            if(typeof save_operation === 'function' && save_operation()){
               $("#amount").val(to_Fixed(final_grand_total));
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
         calculate_tax(k);
       }//if end
     }//for end

      //final_total();
    }

    

    //Sale Items Modal Operations Start
    function show_sales_item_modal(row_id){
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
      //get the sales price of the item
      var tax_type = $("#tr_tax_type_"+row_id).val();
      var tax = $("#tr_tax_value_"+row_id).val(); //%
      var qty=$("#td_data_"+row_id+"_3").val();
          qty = (isNaN(qty)) ? 0 :qty;
      var sales_price = parseFloat($("#td_data_"+row_id+"_10").val());
          sales_price = (isNaN(sales_price)) ? 0 :sales_price;
          sales_price = sales_price * qty;

      /*Discount*/
      var item_discount_type = $("#item_discount_type_"+row_id).val();
      var item_discount_input = parseFloat($("#item_discount_input_"+row_id).val());
          item_discount_input = (isNaN(item_discount_input)) ? 0 :item_discount_input;

      //Calculate discount      
      var discount_amt=(item_discount_type=='Percentage') ? ((sales_price) * item_discount_input)/100 : (item_discount_input*qty);
      sales_price-=parseFloat(discount_amt);

      var tax_amount = (tax_type=='Inclusive') ? calculate_inclusive(sales_price,tax) : calculate_exclusive(sales_price,tax);
      
      $("#td_data_"+row_id+"_8").val(to_Fixed(discount_amt));

      $("#td_data_"+row_id+"_11").val(to_Fixed(tax_amount));
    }
    //Sale Items Modal Operations End
      </script>


      <!-- UPDATE OPERATIONS -->
      <script type="text/javascript">
         <?php if(isset($sales_id) || isset($quotation_id)|| isset($order_id) || isset($sell_again_id)){ ?> 
             $(document).ready(function(){
                var base_url='<?= base_url();?>';
                var path='';
                var id='';
                <?php if(isset($sales_id) && !empty($sales_id)) {?>
                  var id='<?=$sales_id;?>';  
                  var path = 'return_sales_list';
                  var is_sell_again = '0';
                <?php }?>

                <?php if(isset($sell_again_id) && !empty($sell_again_id)) {?>
                  var id='<?=$sell_again_id;?>';  
                  var path = 'return_sales_list';
                  var is_sell_again = '1';
                <?php }?>

                <?php if(isset($quotation_id) && !empty($quotation_id)) {?>
                  var id='<?=$quotation_id;?>';  
                  var path = 'return_quotation_list';
                  var is_sell_again = '0';
                <?php }?>
   
                $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
                $.post(base_url+"sales/"+path+"/"+id,{is_sell_again: is_sell_again},function(result){
                //  alert(result);
                  $('#sales_table tbody').append(result);
                  $("#hidden_rowcount").val(parseInt(<?=$items_count;?>)+1);
                  try {
                      success.currentTime = 0;
                      success.play();
                  } catch(e) { console.log(e); }
                  get_coupon_details();  
                  enable_or_disable_item_discount();
                  $(".overlay").remove();
              }).fail(function() {
                  $(".overlay").remove();
                  toastr["error"]("Failed to load items. Please try again.");
                  failed.currentTime = 0;
                  failed.play();
              }); 
             });
         <?php }?>
      </script>
      <script>
        $(function () {
          //bootstrap WYSIHTML5 - text editor
          //$("#invoice_terms").wysihtml5()
        })
      </script>
      <!-- UPDATE OPERATIONS end-->

      <!-- Make sidebar menu hughlighter/selector -->
      <script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
</body>
</html>

