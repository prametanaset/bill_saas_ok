<!DOCTYPE html>
<html>

<head>
<!-- FORM CSS CODE -->
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
    $sales_code=$customer_name='';
    if($oper=='return_against_sales'){

          $return_id='';
          $q2 = $this->db->query("select * from db_sales where id=$sales_id");
          $customer_id=$q2->row()->customer_id;
          $return_date=show_date(date("d-m-Y"));
          $sales_code=$q2->row()->sales_code;
          $return_status=$q2->row()->sales_status;
          $warehouse_id=$q2->row()->warehouse_id;
          $reference_no='';
          $discount_input=store_number_format($q2->row()->discount_to_all_input,0);
          $discount_type=$q2->row()->discount_to_all_type;
          $other_charges_input=store_number_format($q2->row()->other_charges_input,0);
          $other_charges_tax_id=$q2->row()->other_charges_tax_id;
          $store_id=$q2->row()->store_id;
          $return_note='';

          $coupon_id=$q2->row()->coupon_id;
          $coupon_code = (!empty($coupon_id)) ? get_customer_coupon_details($coupon_id)->code : '';

          $items_count = $this->db->query("select count(*) as items_count from db_salesitems where sales_id=$sales_id")->row()->items_count;
          
          $other_charges_type = $q2->row()->other_charges_type;
          $grand_total = $q2->row()->grand_total;
          $previous_grand_total = $q2->row()->total_amount; // Use VAT-excluded taxable base
          $subtotal = $q2->row()->subtotal;
          $sale_vat_no = $q2->row()->vat; // Original sale's VAT rate
          $correct_total_amt = $previous_grand_total - $grand_total;
          $reference_no = $correct_total_amt;
    }
    if($oper=='edit_existing_return'){
          $q2 = $this->db->query("select * from db_salesreturn where id=$return_id");
          $sales_id=$q2->row()->sales_id;
          $customer_id=$q2->row()->customer_id;
          $return_date=show_date(date("d-m-Y"));
          $return_status=$q2->row()->return_status;
          $return_code=$q2->row()->return_code;
          $warehouse_id=$q2->row()->warehouse_id;
          $reference_no=$q2->row()->reference_no;
          $discount_input=store_number_format($q2->row()->discount_to_all_input,0);
          $discount_type=$q2->row()->discount_to_all_type;
          $other_charges_input=store_number_format($q2->row()->other_charges_input,0);
          $other_charges_tax_id=$q2->row()->other_charges_tax_id;
          $other_charges_type=$q2->row()->other_charges_type;
          $return_note=$q2->row()->return_note;
          $store_id=$q2->row()->store_id;

          $items_count = $this->db->query("select count(*) as items_count from db_salesitemsreturn where return_id=$return_id")->row()->items_count;
          $previous_grand_total = 0;
          $sales_code = '';
          if(!empty($sales_id)){
             $sales_q = $this->db->query("select * from db_sales where id=$sales_id");
             $sales_code = $sales_q->row()->sales_code;
             $previous_grand_total = $sales_q->row()->total_amount; // Use VAT-excluded taxable base
           }
           $grand_total = $q2->row()->grand_total;
           $sale_vat_no = (!empty($sales_id)) ? $this->db->query("select vat from db_sales where id=$sales_id")->row()->vat : 0;
           $correct_total_amt = $previous_grand_total - $grand_total;
           $reference_no = $correct_total_amt;

          $coupon_id=$q2->row()->coupon_id;
          $coupon_code = (!empty($coupon_id)) ? get_customer_coupon_details($coupon_id)->code : '';
    }
    if($oper=='create_new_return'){
          $customer_id  = $return_date = $return_status = $warehouse_id =
          $reference_no  =
          $customer_id  = $return_date = $return_status = $warehouse_id =
          $reference_no  =
          $other_charges_input          = $other_charges_tax_id = $other_charges_type =
          $discount_input = $discount_type  = $return_note= $store_id='';
          $return_date=show_date(date("d-m-Y"));
          $coupon_id='';
          $coupon_code='';
          $sale_vat_no=0;
    }

    if(!empty($customer_id)){
      $customer_name=$this->db->select('customer_name')->where('id',$customer_id)->get('db_customers')->row()->customer_name;
    }

     //  ราตารวม vat 0% จะเก็บใน $tot_price_zero_vat
      
     /* 
     $vat_type =($company_vat_no + 100);
     $vat_total =($grand_total / $vat_type) *$company_vat_no ;

     $tot_price_zero_vat  = 0.0;
     $this->db->select(" a.description,c.item_name, a.sales_qty,a.tax_type,
                         a.price_per_unit, b.tax,b.tax_name,a.tax_amt,
                         a.discount_input,a.discount_amt, a.unit_total_cost,
                         a.total_cost , d.unit_name,c.sku,c.hsn
                     ");
     $this->db->where("a.sales_id",$sales_id);
     $this->db->from("db_salesitems a");
     $this->db->join("db_tax b","b.id=a.tax_id","left");
     $this->db->join("db_items c","c.id=a.item_id","left");
     $this->db->join("db_units d","d.id = c.unit_id","left");
     $q4=$this->db->get();

     foreach ($q4->result() as $res2) {
       if ($res2->tax == 0){
         $tot_price_zero_vat += $res2->total_cost;
       }
     }
     
     $total_price = $subtotal-$tot_price_zero_vat  ;
     $vat_total =($total_price / $vat_type) *$company_vat_no ;
     $befor_vat= $total_price - $vat_total ;
     */
    
     //  ราตารวม vat 0% จะเก็บใน $tot_price_zero_vat  _end
      $company_vat_no = $this->db->select('vat_no')->where('id', get_current_store_id())->get('db_store')->row()->vat_no;


    
    ?>

 

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- **********************MODALS***************** -->
    <?php include"modals/modal_customer.php"; ?>
    <?php include"modals/modal_sales_item.php"; ?>
    <?php include"modals/modal_item.php"; ?>
    <?php include"modals/modal_item_or_service.php"; ?>
   <?php /*include"modals/modal_service.php";*/ ?>
    <!-- **********************MODALS END***************** -->
    <!-- Content Header (Page header) -->
    <section class="content-header">
         <h1>
            <?=$page_title;?>
            <small><?=$subtitle;?></small>
         </h1>
         <ol class="breadcrumb">
            <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="<?php echo $base_url; ?>sales_return"><?= $this->lang->line('sales_return_list'); ?></a></li>
            <li><a href="<?php echo $base_url; ?>sales_return/create"><?= $this->lang->line('new_sales'); ?></a></li>
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
                     <div class="box box-primary " >
                        <!-- style="background: #68deac;" -->
                        
                        <!-- form start -->
                         <!-- OK START -->
                        <?= form_open('#', array('class' => 'form-horizontal', 'id' => 'sales-form', 'enctype'=>'multipart/form-data', 'method'=>'POST'));?>
                           <input type="hidden" id="base_url" value="<?php echo site_url(); ?>/">
                           <input type="hidden" value='1' id="hidden_rowcount" name="hidden_rowcount">

                           <input type="hidden" value='0' id="hidden_update_rowid" name="hidden_update_rowid">
                           <input type="hidden" value='<?=$coupon_code;?>' id="coupon_code" name="coupon_code">
                           <input type="hidden" id="hidden_ref_total_amt" value="<?= $previous_grand_total; ?>">

                          
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
                                <?php if(!empty($sales_code)) { ?>
                                 <label for="" class="col-sm-3 control-label"><?= $this->lang->line('against_sales_invoices'); ?><label class="text-danger"></label> </label>
                                  <label class="col-sm-3 control-label" style="text-align: left;"> : <?= $sales_code;?></label>
                                <?php } ?> 
                                
                                <?php if(!empty($customer_name)) { ?>
                                 <label for="" class="col-sm-2 control-label"><?= $this->lang->line('customer_name'); ?><label class="text-danger"></label> </label>
                                  <label class="col-sm-3 control-label" style="text-align: left;"><?= $customer_name;?></label>
                                  <input type="hidden" name="customer_id" id='customer_id' value="<?=$customer_id;?>">                     
                                 <?php } ?>
								         
                              </div>

                             <div class="form-group">                           
                                    
                                   <label for="" class="col-sm-3 control-label"><?= $this->lang->line('reference_type'); ?><label class="text-danger"></label> </label>
                                  <label class="col-sm-3 control-label" style="text-align: left;"> : <b> <?php echo  $sales_reference_type; ?></b></label> 
                                 
                                    
                                <label for="return_date" class="col-sm-2 control-label"><?= $this->lang->line('date'); ?> <label class="text-danger">*</label></label>
                                 <div class="col-sm-3">
                                    <div class="input-group date">
                                       <div class="input-group-addon">
                                          <i class="fa fa-calendar"></i>
                                       </div>
                                       <input type="text" class="form-control pull-right datepicker"  id="return_date" name="return_date" readonly onkeyup="shift_cursor(event,'return_status')" value="<?= $return_date;?>">
                                    </div>
                                    <span id="return_date_msg" style="display:none" class="text-danger"></span>
                                 </div>
                          

                              <!--   <label for="" class="col-sm-2 control-label"><?= $this->lang->line('total_against_sales_invoices'); ?><label class="text-danger"></label> </label>
                                  <label class="col-sm-3 control-label" style="text-align: left;"> : <b> <?php echo store_number_format( $previous_grand_total); ?></b></label>   -->
                            </div>

                              <div class="form-group">
                                <?php if(!empty($return_code)) { ?>
                                 <label for="" class="col-sm-3 control-label"><?= $this->lang->line('return_invoice_no'); ?><label class="text-danger"></label> </label>
                                  <label class="col-sm-3 control-label" style="text-align: left;"> :  <?= $return_code;?></label>
                                <?php } ?>

                                <?php if(empty($customer_id)) {?>
                                 <label for="customer_id" class="col-sm-2 control-label"><?= $this->lang->line('customer_name'); ?><label class="text-danger"></label></label>
                                 <div class="col-sm-3">
                                    <div class="input-group">
                                       <select class="form-control select2" id="customer_id" name="customer_id"  style="width: 100%;">
                                       </select>
                                      <span class="input-group-addon pointer" data-toggle="modal" data-target="#customer-modal" title="New customer?"><i class="fa fa-user-plus text-primary fa-lg"></i></span>
                                    </div>
                                    <span id="customer_id_msg" style="display:none" class="text-danger"></span>
                                 </div>
                               <?php } ?>

                              
                                <label for="" class="col-sm-2 control-label"><?= $this->lang->line('total_against_sales_invoices'); ?><label class="text-danger"></label> </label>
                                  <label class="col-sm-3 control-label" style="text-align: left;"> : <b> <?php echo store_number_format( $previous_grand_total); ?></b></label> 
                                
                              <!--  <label for="return_date" class="col-sm-2 control-label"><?= $this->lang->line('date'); ?> <label class="text-danger">*</label></label>
                                 <div class="col-sm-3">
                                    <div class="input-group date">
                                       <div class="input-group-addon">
                                          <i class="fa fa-calendar"></i>
                                       </div>
                                       <input type="text" class="form-control pull-right datepicker"  id="return_date" name="return_date" readonly onkeyup="shift_cursor(event,'return_status')" value="<?= $return_date;?>">
                                    </div>
                                    <span id="return_date_msg" style="display:none" class="text-danger"></span>
                                 </div> -->
                              </div> 

                 
                              <div class="form-group">
                                 <label for="return_status" class="col-sm-3 control-label"><?= $this->lang->line('status'); ?> <label class="text-danger">*</label></label>
                                 <div class="col-sm-3">
                                       <select class="form-control select2" id="return_status" name="return_status"  style="width: 100%;" onkeyup="shift_cursor(event,'mobile')">
                                          <!-- <option value="">-Select-</option> -->
                                          <?php 
                                               $return_select = ($return_status=='Credit Note') ? 'selected' : ''; 
                                               $cancel_select = ($return_status=='Cancel') ? 'selected' : ''; 
                                          ?>
                                            <option <?= $return_select; ?> value="Credit Note">ลดหนี้/Credit Note</option>
                                            <option <?= $cancel_select; ?> value="Cancel">ยกเลิก</option>
                                       </select>
                                    <span id="return_status_msg" style="display:none" class="text-danger"></span>
                                 </div>

                                 <label for="reference_no" class="col-sm-2 control-label"><?= $this->lang->line('total_correct_amt'); ?> </label>         
                                 <div class="col-sm-3">
                                    <input type="text" value="<?php echo  $reference_no; ?>" class="form-control " id="reference_no" name="reference_no" placeholder="จำนวนเงินลดหนี้  " >
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
                                                 <input type="text" class="form-control " placeholder="สแกนบาร์โค้ด/ชื่อสินค้า/รหัสสินค้า" autofocus id="item_search">
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
                                                   <th rowspan='2' style="width:18.5%;min-width: 180px;"><?= $this->lang->line('quantity'); ?></th>
                                                   <th rowspan='2' style="width:10%"><?= $this->lang->line('unit_price'); ?></th> 
                                                   <th rowspan='2' style="width:10%"><?= $this->lang->line('discount'); ?>(<?=$CI->currency()?>)</th>
                                                   <th rowspan='2' style="width:5%"><?= $this->lang->line('tax'); ?></th>
                                                   <th rowspan='2' style="width:10%"><?= $this->lang->line('tax_amount'); ?></th>
                                                   <th rowspan='2' style="width:7.5%"><?= $this->lang->line('total_amount'); ?></th>
                                                   <th rowspan='2' style="width:3%"><?= $this->lang->line('action'); ?></th>
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
                                    <div class="col-md-12 ">
                                       <div class="form-group">
                                          <label for="other_charges_input" class="col-sm-4 control-label"><?= $this->lang->line('other_charges'); ?></label>    
                                          <div class="col-sm-3">
                                             <input type="text" class="form-control text-right only_currency" id="other_charges_input" name="other_charges_input" onkeyup="final_total();" value="<?php echo  $other_charges_input; ?>">
                                          </div>
                                          <div class="col-sm-3">
                                             <select class="form-control" onchange="final_total();" id='other_charges_type' name="other_charges_type">
                                                <option value='Percentage'>เปอร์เซ็นต์ %</option>
                                                <option value='Fixed'>จำนวนเงิน</option>
                                             </select>
                                          </div>
                                          <div class="col-sm-2">
                                             <select class="form-control " id="other_charges_tax_id" name="other_charges_tax_id" onchange="final_total();" style="width: 100%;">
                                                 <?= get_tax_select_list($other_charges_tax_id,get_current_store_id());?>
                                             </select>
                                          </div>
                                           <script type="text/javascript">
                                              <?php if(isset($other_charges_type) && $other_charges_type!=''){ ?>
                                                  document.getElementById('other_charges_type').value='<?php echo  $other_charges_type; ?>';
                                              <?php }?>
                                           </script>
                                       </div>
                                    </div>
                                 </div>


                                <div class="row">
                                    <div class="col-md-12">
                                       <div class="form-group">
                                          <label for="discount_to_all_input" class="col-sm-4 control-label"><?= $this->lang->line('discount'); ?></label>    
                                          <div class="col-sm-4">
                                             <input type="text" class="form-control  text-right only_currency" id="discount_to_all_input" name="discount_to_all_input" onkeyup="enable_or_disable_item_discount();" value="<?php echo  $discount_input; ?>">
                                          </div>
                                          <div class="col-sm-4">
                                             <select class="form-control" onchange="final_total();" id='discount_to_all_type' name="discount_to_all_type">
                                                <option value='in_percentage'>เปอร์เซ็นต์ %</option>
                                                <option value='in_fixed'>จำนวนเงิน</option>
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
                                            
                                          <label for="return_note" class="col-sm-4 control-label" style="font-size:16px; color:red;"><?= $this->lang->line('return_note'); ?><label class="text-danger">*</label></label>    
                                          <div class="col-sm-8">
                                             <textarea class="form-control text-left" id='return_note' name="return_note" placeholder="พิมพ์สาเหตุออกใบลดหนี้ "><?= $return_note; ?>  </textarea>
                                            <span id="return_note_msg" style="display:none" class="text-danger"></span>
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
                                            <!-- <tr>
                                                <th class="text-right" style="font-size: 17px;"><?= $this->lang->line('subtotal'); ?></th>
                                                <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                   <h4><b id="subtotal_amt" name="subtotal_amt">0.00</b></h4>
                                                </th>
                                             </tr> -->

                                               <tr>
                                                <th class="text-right" style="font-size: 17px;"><?= $this->lang->line('other_charges'); ?></th>
                                                <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                   <h4><b id="other_charges_amt" name="other_charges_amt">0.00</b></h4>
                                                </th>
                                             </tr>

                                               <tr>
                                                <th class="text-right" style="font-size: 17px;"><?= $this->lang->line('discount'); ?></th>
                                                <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                   <h4><b id="discount_to_all_amt" name="discount_to_all_amt">0.00</b></h4>
                                                </th>
                                              </tr>
                                             
                                               <tr>
                                                    <th class="text-right" style="font-size: 17px; " ><?= $this->lang->line('total_zero_vat_diff'); ?></th>
                                                    <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                    <h4><b id="exempt_diff_amt" name="exempt_diff_amt">0.00</b></h4>
                                                    </th>
                                                 </tr>
                                              <tr>
                                                    <th class="text-right" style="font-size: 17px; "><?= $this->lang->line('total_taxable_diff'); ?></th>
                                                    <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                    <h4><b id="taxable_diff_amt" name="taxable_diff_amt">0.00</b></h4>
                                                    </th>
                                                 </tr>
                                                 
                                                 <tr>
                                                    <th class="text-right" style="font-size: 17px; "><?= $this->lang->line('tax_amount'); ?></th>
                                                    <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                    <h4><b id="vat_diff_amt" name="vat_diff_amt">0.00</b></h4>
                                                    </th>
                                                 </tr>
                                                <tr>
                                                   <th class="text-right" style="font-size: 17px; "><?= $this->lang->line('grand_total'); ?></th>
                                                   <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                   <h3><b id="total_amt" name="total_amt"><?= store_number_format($grand_total); ?></b></h3>
                                                   </th>
                                                </tr>
                                                                        
                                                <tr class='text-primary'>
                                                   <th class="text-right" style="font-size: 17px;"><b><?= $this->lang->line('total_against_sales_invoices') ; ?></b></th>
                                                      <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                      <h4><b id="ref_total_amt" name="ref_total_amt"><?=store_number_format($previous_grand_total);?></b></h4>
                                                      <input type="hidden" id="hidden_ref_total_amt" value="<?= $previous_grand_total; ?>">
                                                      <th>
                                                   </th>
                                                   </tr> 
                                                <tr class='text-primary'>
                                                   <th class="text-right" style="font-size: 17px;"><b>มูลค่าที่ถูกต้อง (ไม่รวมภาษีVAT)</b></th>
                                                      <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                      <h4><b id="correct_total_amt" name="correct_total_amt"><?=store_number_format( $previous_grand_total - $grand_total);?></b></h4>
                                                      <th>
                                                   </th>
                                                   </tr> 

                                         <!--  <tr class='text-primary'>
                                                <th class="text-right" style="font-size: 17px;"><?= $this->lang->line('grand_total'); ?></th>
                                                <th class="text-right" style="padding-left:10%;font-size: 17px;">
                                                   <h2><b id="total_amt" name="total_amt">0.00</b></h2>
                                                </th>
                                             </tr> -->
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
                                                  if(!empty($return_id)){
                                                    $q3 = $this->db->query("select * from db_salespaymentsreturn where return_id=$return_id");
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
                                                      echo "<tr><td colspan='6' class='text-center text-bold'>ไม่มีการชำระ!!</td></tr>";
                                                    }

                                                  }
                                                  else{
                                                    echo "<tr><td colspan='6' class='text-center text-bold'>กำลังชำระ!!</td></tr>";
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
                                            <h4 class="box-title text-info"><?= $this->lang->line('subtotal'); ?> : </h4>
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
                                                      <option value="">-เลือก-</option>
                                                      <?php
                                                          $account_id = get_store_details()->default_account_id;
                                                          if(empty($account_id)){
                                                            $q_acc = $this->db->select("id")->where("store_id",get_current_store_id())->where("account_name","รายรับ")->get("ac_accounts");
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
                                       <div class="box-body ">
                                          <div class="col-md-12">
                                            <div class="checkbox icheck">
                                  <!--    <label>
                                        <input type="checkbox" <?=$send_sms_checkbox;?> class="form-control" id="send_sms" name="send_sms" > <label for="sales_discount" class=" control-label"><?= $this->lang->line('send_sms_to_customer'); ?>
                                          <i class="hover-q " data-container="body" data-toggle="popover" data-placement="top" data-content="If checkbox is Disabled! You need to enable it from SMS -> SMS API <br><b>Note:<i>Walk-in Customer will not receive SMS!</i></b>" data-html="true" data-trigger="hover" data-original-title="" title="Do you wants to send SMS ?">
                                  <i class="fa fa-info-circle text-maroon text-black hover-q"></i>
                                </i>
                                        </label> -->
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

                                if($oper=='return_against_sales'){
                                  $btn_id='save';
                                  $btn_name="บันทึก";
                                  echo '<input type="hidden" name="sales_id" id="sales_id" value="'.$sales_id.'"/>';
                                }
                                if($oper=='edit_existing_return'){
                                  $btn_id='update';
                                  $btn_name="อัพเดท";
                                  echo '<input type="hidden" name="return_id" id="return_id" value="'.$return_id.'"/>';
                                  echo '<input type="hidden" name="sales_id" id="sales_id" value="'.$sales_id.'"/>';
                                }
                                if($oper=='create_new_return'){
                                  $btn_id='create';
                                  $btn_name="สร้าง";
                                }

                                /*if(isset($sales_id)){
                                  $btn_id='update';
                                  $btn_name="Update";
                                  echo '<input type="hidden" name="sales_id" id="sales_id" value="'.$sales_id.'"/>';
                                }
                                else{
                                  $btn_id='save';
                                  $btn_name="Save";
                                }*/

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

      <script src="<?php echo $theme_link; ?>js/sales-return.js"></script>  
      <script src="<?php echo $theme_link; ?>js/language.js"></script>  
      <script src="<?php echo $theme_link; ?>js/ajaxselect/customer_select_ajax.js"></script>  
      
      <script>
            // Helper: Format with commas and fixed decimals
            function to_Units(res=0){
                return to_Fixed(res).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }
            function to_Fixed(res=0){
              return parseFloat(res).toFixed(2);
            }
            function parse_float(val) {
                if (typeof val === 'number') return val;
                if (!val) return 0;
                var clean_val = val.toString().replace(/,/g, '');
                var parsed = parseFloat(clean_val);
                return isNaN(parsed) ? 0 : parsed;
            }
         //Customer Selection Box Search
         function load_customer_select2(){
            var customer_id = "<?= (!empty($customer_id)) ? $customer_id : '';  ?>";
            
            if(customer_id != ""){
               //If has customer id in customer_id variable
               //Then don't load the customer select2
               return false;
            }
            //Load customer select2
            return true;
         }
         function getCustomerSelectionId() {
           return '#customer_id';
         }

         $(document).ready(function () {

            var customer_id = "<?= (!empty($customer_id)) ? $customer_id : '';  ?>";

            autoLoadFirstCustomer(customer_id);

         });
         //Customer Selection Box Search - END

         function save_operation() {
            <?php if($oper=='edit_existing_return'){ ?>
               return false;
            <?php }else{ ?>
               return true;
            <?php } ?>
         }
         
        var base_url=$("#base_url").val();
        var store_vat_no = <?= (!empty($company_vat_no)) ? $company_vat_no : 0; ?>;
        var sale_vat_no = <?= (!empty($sale_vat_no)) ? $sale_vat_no : 0; ?>;

        $("#store_id").on("change",function(){
          var store_id=$(this).val();
          $.post(base_url+"sales/get_customers_select_list",{store_id:store_id},function(result){
              $("#customer_id").html('').append(result).select2();
              $("#sales_table > tbody").empty();
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
          
        if(false){
          $("#item_search").attr({
            disabled: true,
          });
          toastr["warning"]("Please Select Warehouse!!");
         }


          function calculate_tax(i, skip_final_total = false){ //i=Rowid
            try {
               set_tax_value(i);

               var qty=parse_float($("#td_data_"+i+"_3").val());
               var sales_price=parse_float($("#td_data_"+i+"_10").val());
               $("#td_data_"+i+"_4").val(sales_price);
               
               /*Discount*/
               var discount_amt=parse_float($("#td_data_"+i+"_8").val());

               var amt=qty * sales_price;//Taxable

               var total_amt=amt-discount_amt;
               var tax_type = $("#tr_tax_type_"+i).val();
               var tax_amount = parse_float($("#td_data_"+i+"_11").val());
               total_amt = (tax_type=='Inclusive') ? total_amt : parseFloat(total_amt) + parseFloat(tax_amount);
               
               //Set Unit cost
               $("#td_data_"+i+"_9").val('').val(to_Fixed(total_amt));
             
                if(!skip_final_total){
                   final_total();
                }
            } catch (err) {
               console.error("Error in calculate_tax at row " + i + ":", err);
            }
          }

          function set_tax_value(row_id){
              try {
                //get the sales price of the item
                var tax_type = $("#tr_tax_type_"+row_id).val();
                var tax = parse_float($("#tr_tax_value_"+row_id).val()); //%
                var qty=parse_float($("#td_data_"+row_id+"_3").val());
                var sales_price = parse_float($("#td_data_"+row_id+"_10").val());
                var total_price = sales_price * qty;

                /*Discount*/
                var item_discount_type = $("#item_discount_type_"+row_id).val();
                var item_discount_input = parse_float($("#item_discount_input_"+row_id).val());

                //Calculate discount      
                var discount_amt=(item_discount_type=='Percentage') ? (total_price * item_discount_input)/100 : (item_discount_input*qty);
                var net_price = total_price - discount_amt;

                var tax_amount = (tax_type=='Inclusive') ? calculate_inclusive(net_price,tax) : calculate_exclusive(net_price,tax);
                
                $("#td_data_"+row_id+"_8").val(to_Fixed(discount_amt));
                $("#td_data_"+row_id+"_11").val(to_Fixed(tax_amount));
              } catch (err) {
                console.error("Error in set_tax_value at row " + row_id + ":", err);
              }
          }
         /* ---------- CALCULATE GST END -------------*/


         /*Calculate Coupon Discount Amount - DISABLED*/
         const discount_coupon_tot = function(subtotal) {
             return 0;
         }

        
                   /* ---------- Final Description of amount ------------*/
          function final_total(){
            try {
               var total_quantity=0;
               var subtotal_all = 0;
               
               var tax_groups = {};
               var total_exempt_base = 0;
               var max_tax_rate = 0;

               // Iterate through items
               $("#sales_table tbody tr").each(function() {
                  var i = $(this).attr('data-row');
                  if(!i) i = $(this).attr('id') ? $(this).attr('id').replace('row_', '') : null;
                  
                  if(i && document.getElementById("td_data_"+i+"_3")){
                    var qty = parse_float($("#td_data_"+i+"_3").val());
                    if(qty > 0){
                      total_quantity += qty;
                      var row_total = parse_float($("#td_data_"+i+"_9").val());
                      subtotal_all += row_total;
                      
                      var item_tax_rate = parse_float($("#tr_tax_value_"+i).val());
                      var item_tax_type = $("#tr_tax_type_"+i).val();
                      var item_tax_amt = parse_float($("#td_data_"+i+"_11").val());
                      
                      if(item_tax_rate > max_tax_rate) max_tax_rate = item_tax_rate;
                      
                      if(item_tax_rate > 0) {
                         if(item_tax_type == 'Inclusive'){
                           var de_taxed = (row_total * 100) / (100 + item_tax_rate);
                           tax_groups[item_tax_rate] = (tax_groups[item_tax_rate] || 0) + de_taxed;
                         } else {
                           var base = row_total - item_tax_amt;
                           tax_groups[item_tax_rate] = (tax_groups[item_tax_rate] || 0) + base;
                         }
                      } else {
                         total_exempt_base += row_total;
                      }
                    }
                  }
               });

               // Handle Other Charges
               var other_charges_input = parse_float($("#other_charges_input").val());
               var other_charges_type = $("#other_charges_type").val();
               var other_charges_total_amt = (other_charges_type == 'Percentage' || other_charges_type == 'in_percentage') ? (subtotal_all * other_charges_input / 100) : other_charges_input;
               
               if(other_charges_total_amt > 0){
                   var oc_tax_id = $("#other_charges_tax_id").val();
                   var oc_tax_rate = 0;
                   if(oc_tax_id){
                       var selected_tax = $("#other_charges_tax_id option:selected");
                       if(selected_tax.length > 0) oc_tax_rate = parse_float(selected_tax.attr('data-tax'));
                   }
                   
                   // Fallback to highest tax rate or default VAT
                   if(oc_tax_rate <= 0){
                       if(max_tax_rate > 0) oc_tax_rate = max_tax_rate;
                       else oc_tax_rate = parse_float(window.sale_vat_no || (typeof store_vat_no !== 'undefined' ? store_vat_no : 7));
                   }
                   
                   if(oc_tax_rate > 0){
                       var oc_base_detaxed = (other_charges_total_amt * 100) / (100 + oc_tax_rate);
                       tax_groups[oc_tax_rate] = (tax_groups[oc_tax_rate] || 0) + oc_base_detaxed;
                   } else {
                       total_exempt_base += other_charges_total_amt;
                   }
               }
               
               // Calculate Total Base for Prorating
               var total_base_sum = total_exempt_base;
               for(var rate in tax_groups) total_base_sum += tax_groups[rate];

               // Prorate Discount
               var discount_input = parse_float($("#discount_to_all_input").val());
               var discount_type = $("#discount_to_all_type").val();
               var inclusive_total = subtotal_all + other_charges_total_amt;
               
               var total_discount = (discount_type == 'in_percentage' || discount_type == 'Percentage') ? (inclusive_total * discount_input / 100) : discount_input;
               if(isNaN(total_discount)) total_discount = 0;
               
               // Extract VAT from the global discount (Sync with POS logic)
               var total_global_discount = (total_discount * 100) / (100 + max_tax_rate);
               var ratio = (total_base_sum > 0) ? (total_global_discount / total_base_sum) : 0;
               var net_exempt = total_exempt_base - (total_exempt_base * ratio);
               
               var total_net_taxable = 0;
               var total_vat_amount = 0;

               for(var rate in tax_groups){
                  var base = tax_groups[rate];
                  var net_base = base - (base * ratio);
                  var rounded_net_base = Math.round(net_base * 100) / 100;
                  var vat_amt = Math.round((rounded_net_base * parse_float(rate) / 100) * 100) / 100;
                  
                  total_net_taxable += rounded_net_base;
                  total_vat_amount += vat_amt;
               }
               
               var final_grand_total = total_net_taxable + total_vat_amount + Math.round(net_exempt * 100) / 100;
               final_grand_total = Math.round(final_grand_total * 100) / 100;
               
               // Update UI
               if(typeof format_qty === 'function') $(".total_quantity").html(format_qty(total_quantity));
               $("#subtotal_amt").html(to_Fixed(subtotal_all));
               $("#other_charges_amt").html(to_Fixed(other_charges_total_amt));
               $("#discount_to_all_amt").html(to_Fixed(total_discount));
               $("#hidden_discount_to_all_amt").val(to_Fixed(total_discount));
               
               $("#total_amt").html(to_Fixed(final_grand_total));
               
               if(typeof save_operation === 'function' && save_operation()){
                  $("#amount").val(to_Fixed(final_grand_total));
               }

               // Taxable Summary Logic
               $("#taxable_diff_amt").html(to_Fixed(total_net_taxable));
               $("#exempt_diff_amt").html(to_Fixed(net_exempt));
               $("#vat_diff_amt").html(to_Fixed(total_vat_amount));

               var ref_total_val = $("#hidden_ref_total_amt").val();
               var ref_total_amt = parse_float(ref_total_val);
               $("#ref_total_amt").html(to_Fixed(ref_total_amt));
               
               // Correct Amount for Sales Return = Original - Differences
               var correct_total_amt = ref_total_amt - total_net_taxable - net_exempt;
               $("#correct_total_amt").html(to_Fixed(correct_total_amt));
               $("#reference_no").val(to_Fixed(correct_total_amt));
            } catch (err) {
               console.error("Error in final_total:", err);
               if(typeof toastr !== 'undefined') toastr["error"]("Calculation Error: " + err.message);
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
      var rowcount=$("#hidden_rowcount").val();
      for(k=1;k<=rowcount;k++){
       if(document.getElementById("tr_item_id_"+k)){
         calculate_tax(k, true);
       }//if end
     }//for end

      final_total();
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


      <!-- Return against sales Entry -->
      <script type="text/javascript">
        // alert("DEBUG CHECK: Page Loaded. oper value is: |<?= $oper; ?>|");
        // console.log("DEBUG CHECK: Page Loaded. oper value is: |<?= $oper; ?>|");

        <?php if($oper=='return_against_sales') { ?>
          
          try {
              // alert("Check 0.5: Script Block Started");
              
              // Define audio globals safely
              try {
                  window.success = new Audio("<?=base_url();?>theme/audio/success.mp3");
                  window.failed = new Audio("<?=base_url();?>theme/audio/fail.mp3");
              } catch(audioErr) {
                  console.error("Audio init failed:", audioErr);
                  window.success = null;
                  window.failed = null;
              }

              // Define the init function globally or locally
              var salesReturnInitDone = false;
              
              function initSalesReturn() {
                  if(salesReturnInitDone) return;
                  salesReturnInitDone = true;
                  
                  // alert("Check 1c: initSalesReturn EXECUTING!");
                  
                  try {
                    console.log("DEBUG: initSalesReturn running");
                    
                    // Use json_encode for SAFE variable passing
                    var base_url = <?php echo json_encode(site_url()); ?>;
                    var sales_id = <?php echo json_encode($sales_id); ?>;
                    var items_count = <?php echo json_encode(isset($items_count) ? (int)$items_count : 0); ?>;
                    var csrf_token = <?php echo json_encode($this->security->get_csrf_hash()); ?>;
                    var csrf_name = <?php echo json_encode($this->security->get_csrf_token_name()); ?>;

                    if(base_url.slice(-1) != '/') base_url += '/';
                    
                    if(!base_url || !sales_id){
                        console.error("Critical vars missing: URL=" + base_url + ", ID=" + sales_id);
                        return;
                    }

                    // Use jQuery for the DOM manipulation and AJAX if available, otherwise vanilla?
                    // Given the rest of the app uses jQuery, we assume it's there. 
                    // But we check just in case.
                    if (typeof jQuery === 'undefined') {
                        console.error("CRITICAL: jQuery not loaded at init time!");
                        return;
                    }
                    var $ = jQuery;

                    $(".box-primary").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
                    
                    var post_data = {};
                    post_data[csrf_name] = csrf_token;
                    
                    var target_url = base_url+"sales_return/sales_list/"+sales_id;

                    $.post(target_url, post_data, function(result){
                      try {
                          console.log("Sales ID:", sales_id);
                          
                          if(!result){
                             console.warn("Result is empty for sales_id: " + sales_id);
                          }

                          $('#sales_table tbody').append(result);
                          $("#hidden_rowcount").val($('#sales_table tbody tr').length+1);
                          
                          if(window.success) window.success.currentTime = 0;
                          if(window.success) window.success.play();

                          // if(typeof get_coupon_details === 'function') get_coupon_details(); // Coupon disabled
                          if(typeof enable_or_disable_item_discount === 'function') enable_or_disable_item_discount();

                      } catch (e) {
                          console.error("JS Error in sales_list success:", e);
                          toastr["error"]("Error processing data: " + e.message);
                      }
                      $(".overlay").remove();
                  })
                  .fail(function(xhr, status, error) {
                      console.error("AJAX Error:", status, error);
                      toastr["error"]("โหลดข้อมูลสินค้าไม่สำเร็จ Status: " + status);
                      $(".overlay").remove();
                      if(window.failed) window.failed.currentTime = 0; 
                      if(window.failed) window.failed.play();
                  }); 

                  } catch(err) {
                      console.error("Error in initSalesReturn:", err);
                  }
              }

              // Try multiple ways to trigger init
              if (document.readyState === 'complete' || document.readyState === 'interactive') {
                  setTimeout(initSalesReturn, 1); // Use timeout to allow stack to clear
              } else {
                  document.addEventListener('DOMContentLoaded', initSalesReturn);
                  window.addEventListener('load', initSalesReturn);
              }

          } catch(err) {
              console.error("Critical Script Error:", err);
          }
          
         <?php }?> 

      </script>
      <!-- EDIT OPERATIONS -->
      <script type="text/javascript">
         <?php if($oper=='edit_existing_return') { ?> 
             $(document).ready(function(){
                $("#store_id").attr('readonly',true);
                var base_url='<?= site_url();?>';
                if(base_url.slice(-1) != '/') base_url += '/';
                var return_id='<?= $return_id;?>';
                $(".box-primary").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
                var csrf_token = '<?=$this->security->get_csrf_hash();?>';
                var csrf_name = '<?=$this->security->get_csrf_token_name();?>';
                var post_data = {};
                post_data[csrf_name] = csrf_token;
                $.post(base_url+"sales_return/return_sales_list/"+return_id,post_data,function(result){
                  try {
                      //alert(result);
                      $('#sales_table tbody').append(result);
                      $("#hidden_rowcount").val($('#sales_table tbody tr').length+1);
                      if(window.success) window.success.currentTime = 0;
                      if(window.success) window.success.play();
                      // get_coupon_details(); // Coupon disabled
                      enable_or_disable_item_discount();
                  } catch (e) {
                      console.error("JS Error in return_sales_list success:", e);
                      toastr["error"]("Error processing data: " + e.message);
                  }
                  $(".overlay").remove();
              })
              .fail(function() {
                  toastr["error"]("Failed to load return items! Please try again.");
                  $(".overlay").remove();
                  if(window.failed) window.failed.currentTime = 0; 
                  if(window.failed) window.failed.play();
              }); 
             });
         <?php }?>
      </script>
      <!-- UPDATE OPERATIONS end-->

      <!-- Make sidebar menu highlighter/selector -->
      <script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
</body>
</html>

