<!DOCTYPE html>
<html>
<head>
<!-- TABLES CSS CODE -->
<?php include"comman/code_css.php"; ?>
<!-- </copy> -->  
 
<script>  
    function printTemplate(invoiceUrl) {
      const templateUrl = invoiceUrl;
      const iframe = document.createElement("iframe");
      iframe.style.position = "absolute";
      iframe.style.top = "-10000px";
      iframe.style.width = "0";
      iframe.style.height = "0";
      iframe.style.border = "none";
      document.body.style.cursor = "wait"; // Add waiting cursor
      document.body.appendChild(iframe);

      iframe.src = templateUrl;

      iframe.onload = function() {
        document.body.style.cursor = "default"; // Reset cursor
        iframe.contentWindow.focus();
        iframe.contentWindow.print();

        // Cleanup the iframe after printing
        setTimeout(() => {
          document.body.removeChild(iframe);
        }, 1000);
      };
    }  
</script>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include"sidebar.php"; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <?= $this->lang->line('invoice'); ?>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo $base_url; ?>sales"><?= $this->lang->line('sales_list'); ?></a></li>
        <li><a href="<?php echo $base_url; ?>sales/add"><?= $this->lang->line('new_sales'); ?></a></li>
        <li class="active"><?= $this->lang->line('invoice'); ?></li>
      </ol>
    </section>
    <div class="row">
      <div class="col-md-12">
      <!-- ********** ALERT MESSAGE START******* -->
      <?php include"comman/code_flashdata.php"; ?>
      <?php if(isset($locked_bill_msg)){ ?>
        <div class="alert alert-danger alert-dismissable text-center">
          <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
          <strong><?=$locked_bill_msg?></strong>
        </div>
      <?php } ?>
      <!-- ********** ALERT MESSAGE END******* -->
      </div>
    </div>
    <?php
    $CI =& get_instance();
        
    $q3 = $this->db->query("SELECT 
    b.coupon_id, b.coupon_amt, b.due_date, b.quotation_id, b.store_id,
    a.customer_name, a.mobile, a.phone, a.gstin, a.tax_number, a.email, a.shippingaddress_id, a.id,
    a.opening_balance, a.country_id, a.state_id, a.city,
    a.postcode, a.address, b.sales_date, b.created_time, b.reference_no,
    b.sales_code, b.sales_status, b.sales_note, b.invoice_terms, b.vat,
    coalesce(b.grand_total,0) as grand_total,
    coalesce(b.subtotal,0) as subtotal,
    coalesce(b.paid_amount,0) as paid_amount,
    coalesce(b.other_charges_input,0) as other_charges_input,
    other_charges_tax_id,
    coalesce(b.other_charges_amt,0) as other_charges_amt,
    discount_to_all_input,
    b.discount_to_all_type,
    coalesce(b.tot_discount_to_all_amt,0) as tot_discount_to_all_amt,
    coalesce(b.round_off,0) as round_off,
    coalesce(b.taxable_amount,0) as taxable_amount,
    coalesce(b.vat_amt,0) as vat_amt,
    b.payment_status, b.pos

    FROM db_customers a, db_sales b 
    WHERE a.id = b.customer_id 
      AND b.id = '$sales_id' 
      AND b.store_id = " . get_current_store_id());
                       
    
    $res3=$q3->row();
    if($res3->store_id!=get_current_store_id()){
      $CI->show_access_denied_page();exit();
    }
    $vat_sale=$res3->vat;
    $customer_id=$res3->id;
    $customer_name=$res3->customer_name;
    $customer_mobile=$res3->mobile;
    $customer_phone=$res3->phone;
    $customer_email=$res3->email;
    $customer_country=get_country($res3->country_id);
    $customer_state=get_state($res3->state_id);
    $customer_city=$res3->city;
    $customer_address=$res3->address;
    $customer_postcode=$res3->postcode;
    $customer_gst_no=$res3->gstin;
    $customer_tax_number=$res3->tax_number;
    $customer_opening_balance=$res3->opening_balance;
    $sales_date=$res3->sales_date;

    $due_date=(!empty($res3->due_date)) ? show_date($res3->due_date) : '';
    $created_time=$res3->created_time;
    $reference_no=$res3->reference_no;
    $sales_code=$res3->sales_code;
    $sales_status=$res3->sales_status;
    $sales_note=$res3->sales_note;
    $invoice_terms=$res3->invoice_terms;
    $quotation_id=$res3->quotation_id;
    
    $sales_reference_type_raw = '';
    if ($this->db->field_exists('reference_type', 'db_sales')) {
        $ref_q = $this->db->select('reference_type')->where('id', $sales_id)->get('db_sales');
        if ($ref_q && $ref_q->num_rows() > 0) {
            $sales_reference_type_raw = $ref_q->row()->reference_type;
        }
    }
    $sales_reference_type = '';
    if ($sales_reference_type_raw == 'tax_invoice') {
        $sales_reference_type = 'ใบกำกับภาษี';
    } elseif ($sales_reference_type_raw == 'tax_receipt') {
        $sales_reference_type = 'ใบเสร็จรับเงิน/ใบกำกับภาษี';
    }

    $iso_datetime = date('Y-m-d\TH:i:s', strtotime("$sales_date $created_time"));


    $coupon_id=$res3->coupon_id;
    $coupon_amt=$res3->coupon_amt;
    $coupon_code = '';
    $coupon_type = '';
    $coupon_value=0;
    if(!empty($coupon_id)){
      $coupon_details =get_customer_coupon_details($coupon_id);
      $coupon_code =$coupon_details->code;
      $coupon_value =$coupon_details->value;
      $coupon_type =$coupon_details->type;
    }
    
    $subtotal=$res3->subtotal;
    $grand_total=$res3->grand_total;
    $other_charges_input=$res3->other_charges_input;
    $other_charges_tax_id=$res3->other_charges_tax_id;
    $other_charges_amt=$res3->other_charges_amt;
    $paid_amount=$res3->paid_amount;
    $discount_to_all_input=$res3->discount_to_all_input;
    $discount_to_all_type=$res3->discount_to_all_type;
    $discount_to_all_type = ($discount_to_all_type=='in_percentage') ? '%' : ' ฿ ';
    $tot_discount_to_all_amt=$res3->tot_discount_to_all_amt;
    $round_off=$res3->round_off;
    $db_taxable_amount=$res3->taxable_amount;
    $db_vat_amt=$res3->vat_amt;
    $payment_status=$res3->payment_status;
    $pos=$res3->pos;
    
    

    $q1=$this->db->query("select * from db_store where id=".$res3->store_id." ");
    $res1=$q1->row();
    $store_name=$res1->store_name;
    $company_mobile=$res1->mobile;
    $company_phone=$res1->phone;
    $company_email=$res1->email;
   
    $company_city=$res1->city;
    $company_address=$res1->address;
    $company_gst_no=$res1->gst_no;
    $company_vat_no=$res1->vat_no;
    $company_pan_no=$res1->pan_no;
    $company_postcode=$res1->postcode;
    $company_country=$res1->country;
    $company_state=$res1->state;

    $sales_invoice_footer_text = $res1->sales_invoice_footer_text;
    

    $shipping_country='';
    $shipping_state='';
    $shipping_city='';
    $shipping_address='';
    $shipping_postcode='';
    if(!empty($res3->shippingaddress_id)){
        $Q2 = $this->db->select("c.country,s.state,a.city,a.postcode,a.address")
                        ->where("a.id",$res3->shippingaddress_id)
                        ->from("db_shippingaddress a")
                        ->join("db_country c","c.id = a.country_id",'left')
                        ->join("db_states s","s.id = a.state_id",'left')
                        ->get();                    
        if($Q2->num_rows()>0){
          $shipping_country=$Q2->row()->country;
          $shipping_state=$Q2->row()->state;
          $shipping_city=$Q2->row()->city;
          $shipping_address=$Q2->row()->address;
          $shipping_postcode=$Q2->row()->postcode;
        }
      }

     
      //  ราตารวม vat 0% จะเก็บใน $tot_price_zero_vat
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
        if (($res2->tax ?? 0) == 0){
          $tot_price_zero_vat += ($res2->total_cost ?? 0);
        }
      }
        
    
     
      //  ราตารวม vat 0% จะเก็บใน $tot_price_zero_vat  _end
      
      
    $city_name;
    $state_name;
    $country_name;

    $country_code;
		$state_code;
		$city_code;
		

		

    // 1. Get Country (Province) Name First to check for Bangkok
    if (!empty($company_country)) {
        // Change lookup from 'id' to 'code'
        $q = $this->db->get_where('provinces', ['code' => $company_country]);
        if ($q->num_rows() > 0) {
            $country_name_raw = $q->row()->name_in_thai;
            if (strpos($country_name_raw, 'กรุงเทพ') !== false) {
                $is_bangkok = true;
                $country_name = ' ' . $country_name_raw;
            } else {
                $is_bangkok = false;
                $country_name = ' จ.' . $country_name_raw;
            }
        }
    } else {
        $is_bangkok = false;
    }

    // 2. Get Subdistrict (City)
    if (!empty($company_city)) {
        // Change lookup from 'id' to 'code'
        $q = $this->db->get_where('subdistricts', ['code' => $company_city]);
        if ($q->num_rows() > 0) {
            $prefix = $is_bangkok ? ' แขวง' : ' ต.';
            $city_name = $prefix . $q->row()->name_in_thai;
        }
    }

    // 3. Get District (State)
    if (!empty($company_state)) {
         // Change lookup from 'id' to 'code'
        $q = $this->db->get_where('districts', ['code' => $company_state]);
        if ($q->num_rows() > 0) {
            $prefix = $is_bangkok ? ' เขต' : ' อ.';
            $state_name = $prefix . $q->row()->name_in_thai;
        }
    }

    ?>



    <!-- Main content -->
    <section class="invoice">
      <!-- title row -->
      <div class="printableArea">
      <div class="row">
        <div class="col-xs-12">
          <h2 class="page-header">
            <i class="fa fa-globe"></i> <?= $this->lang->line('sales_invoice'); ?>
            <small class="pull-right">วันที่: <?php echo  show_date($sales_date)." ".$created_time; ?></small>
          </h2>
        </div>
        <!-- /.col -->
      </div>

      <!-- info row -->
      <div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
          
          <address>
            <strong><?php echo  $store_name; ?></strong><br>           
            <?php echo  $company_address; ?> 
                     
            <?php  
               if(!empty($company_city)){
               echo "  " .$city_name;
                }           
              if(!empty($company_state)){
                echo "  " .$state_name;
              }             
              if(!empty($company_country)){
                echo "  " .$country_name;
              }
              if(!empty($company_postcode)){
                echo "-" .$company_postcode;
              }
            ?>
            <br/>
            
            <?php echo (!empty(trim($company_gst_no)) && gst_number()) ? $this->lang->line('tax_id').": ".$company_gst_no."   " : '';?>
            <?php echo (!empty(trim($company_pan_no)) && pan_number()) ? $this->lang->line('branch_no')." : ".$company_pan_no."  " : '';?> <br>                 
            <?= $this->lang->line('phone'); ?>: <?php echo  $company_mobile; ?><br>
            <?php echo (!empty(trim($company_phone))) ? $this->lang->line('mobile').": ".$company_phone."<br>" : '';?>      
            <?php echo (!empty(trim($company_email))) ? $this->lang->line('email').": ".$company_email."<br>" : '';?>          
          </address>
        </div>
        <!-- /.col -->

        <div class="col-sm-4 invoice-col">
          <h4 class='text-uppercase text-primary'>
            <ins><?= $this->lang->line('customer_address'); ?></ins>
          </h4>
          <address>
            <strong><?php echo  $customer_name; ?></strong><br>
            <?php 
              echo get_full_address_thai($res3->address, $res3->country_id, $res3->state_id, $res3->city, $res3->postcode);
            ?>
            <br>
            <?php echo (!empty(trim($customer_gst_no)) && gst_number()) ? $this->lang->line('tax_id').": ".$customer_gst_no."  " : '';?>
			      <?php echo (!empty(trim($customer_tax_number))) ? $this->lang->line('branch_no')." : ".$customer_tax_number."  " : '';?> <br>
            <?php echo (!empty(trim($customer_phone))) ? $this->lang->line('phone').": ".$customer_phone."<br>" : '';?>
            <?php echo (!empty(trim($customer_mobile))) ? $this->lang->line('mobile').": ".$customer_mobile."<br>" : '';?>          
            <?php echo (!empty(trim($customer_email))) ? $this->lang->line('email').": ".$customer_email."<br>" : '';?>      
          </address>
                 
        </div>
      

        <!-- /.col -->
        <div class="col-sm-4 invoice-col pull-right">
          <br>
          <b style="font-size: 16px;"><?= $this->lang->line('invoice'); ?> NO. :  <?php echo  $sales_code; ?></b><br>

           <b style="color: blue; font-size: 14px;"><?= $this->lang->line('reference_tax_type'); ?> : <?php echo  $sales_reference_type; ?></b><br>

          <?php if(!empty($quotation_id)){ ?>
            <h4>อ้างอิงถึง :<?= $this->lang->line('quotation'); ?> #<a title='Click to View Quotation' href='<?=base_url("quotation/invoice/".$quotation_id)?>'><?= get_quotation_details($quotation_id)->quotation_code; ?></a></h4><br>
          <?php } ?>
           <?php if(!empty($due_date)){ ?>
          <b style="font-size: 14px;"><?= $this->lang->line('due_date'); ?> : <?php echo  $due_date; ?></b><br>
          <?php } ?>
           <?php if(!empty($reference_no)){ ?>
           <h4 style="color: ; font-size: 14px;"> <?= $this->lang->line('reference_bill'); ?><br/>เลขที่ : <b style="font-size: 18px"> <?php echo "$reference_no"; ?></b></h4>                                         
            
           <?php } ?>    
                           
                       <?php if(!empty($reference_no)){ ?>
                      <b><?= $this->lang->line('date_bill_no'); ?></b>
                        <span style="font-size: 18px;">:
                          <b><?php 
                            $ref_date = '';
                            $sales_reference_no_type = '';
                            $ref_no_clean = trim($reference_no);
                            $q_ref = $this->db->query("SELECT sales_date, reference_type FROM db_sales WHERE sales_code=? LIMIT 1", array($ref_no_clean));
                            if($q_ref->num_rows() > 0){
                                $ref_row = $q_ref->row();
                                $ref_date = show_date($ref_row->sales_date);
                                $ref_raw = $ref_row->reference_type;
                                if ($ref_raw == 'tax_invoice') {
                                    $sales_reference_no_type = 'ใบกำกับภาษี';
                                } elseif ($ref_raw == 'tax_receipt') {
                                    $sales_reference_no_type = 'ใบเสร็จรับเงิน/ใบกำกับภาษี';
                                }
                            } else {
                                $ref_date = "-"; // Not found in database
                            }
                            echo $ref_date;   // วันที่ ออกเอกสารในใบกำกับภาษีเดิม
                          ?> </b><br />
                           <b style="font-size: 14px;"><?= $this->lang->line('reference_type'); ?> : <?php echo  $sales_reference_no_type; ?></b>  
                        </span>
                       <?php } ?>
                          
        </div>
       
        <!-- /.col -->
      </div>
        <br> 
      <!-- /.row -->




      <!-- info row -->
      <div class="row invoice-info">
       
      </div>
      <!-- /.row -->

      <!-- Table row -->
      <div class="row">
        <div class="col-xs-12 table-responsive">
          <table class="table  records_table table-bordered">
            <thead class="bg-success">
            <tr>
              <th>#</th>
              <th class='text-center'><?= $this->lang->line('item_name'); ?></th>
              <th class='text-center'><?= $this->lang->line('unit_cost'); ?></th>
              <th class='text-center'><?= $this->lang->line('discount'); ?></th>
              <th class='text-center'><?= $this->lang->line('tax'); ?></th>
            <!--  <th class='text-center'><?= $this->lang->line('tax_amount'); ?></th>    -->      
              <th class='text-center'><?= $this->lang->line('unit_price'); ?></th>
               <th class='text-center'><?= $this->lang->line('quantity'); ?></th>
              <th class='text-center'><?= $this->lang->line('total_vatable'); ?></th>
            </tr>
            </thead>
            <tbody>


             <?php
              $i=0;
              $tot_qty=0;
              $tot_sales_price=0;
              $tot_tax_amt=0;
              $tot_discount_amt=0;
              $tot_total_cost=0;
              $tot_price_per_unit=0;
              $sum_of_tot_price=0;
          
                $this->db->select(" a.description,c.mrp,c.item_name, a.sales_qty,a.tax_type,
                                  a.price_per_unit, b.tax,b.tax_name,a.tax_amt,
                                  a.discount_input,a.discount_amt, a.unit_total_cost,
                                  a.total_cost , d.unit_name,c.sku,c.hsn
                                 ");
                    $this->db->where("a.sales_id",$sales_id);
                    $this->db->from("db_salesitems a");
                    $this->db->join("db_tax b","b.id=a.tax_id","left");
                    $this->db->join("db_items c","c.id=a.item_id","left");
                    $this->db->join("db_units d","d.id = c.unit_id","left");
                    $q2=$this->db->get();

                    
              foreach ($q2->result() as $res2) {
                  //Correction: if total_cost is 0, calculate it
                  if(empty($res2->total_cost) || $res2->total_cost==0){
                      $temp_tot = ($res2->price_per_unit * $res2->sales_qty) - $res2->discount_amt;
                      if($res2->tax_type=='Exclusive'){
                          $temp_tot += $res2->tax_amt;
                      }
                      $res2->total_cost = $temp_tot;
                  }
                  
                  $str = ($res2->tax_type=='Inclusive')? 'รวม' : 'แยก';
                  $discount = (empty($res2->discount_input)||$res2->discount_input==0)? '0': store_number_format($res2->discount_input)."%";
                  $discount_amt = (empty($res2->discount_amt)||$res2->discount_input==0)? '0':$res2->discount_amt."";

                  $price_per_unit = $res2->price_per_unit;
             
                  $tot_price = $price_per_unit * $res2->sales_qty;
                  $unit_cost = $res2->total_cost / $res2->sales_qty;

                  echo "<tr>";  
                  echo "<td>".++$i."</td>";
                  echo "<td>";
                  echo $res2->item_name;
                  echo (!empty($res2->description)) ? "<br><i>[".nl2br($res2->description)."]</i>" : '';
                  echo "</td>";
                  echo "<td class='text-right'>".store_number_format($price_per_unit)."</td>";              
                  echo "<td class='text-right'>".store_number_format($discount_amt)."</td>";  
                  echo "<td class='text-center'>" . $res2->tax_name . " ".store_number_format($vat_sale)."% " ."</td>";         
            
                  echo "<td class='text-right'>".store_number_format( $unit_cost)."</td>";
                   echo "<td class='text-right'>".format_qty($res2->sales_qty)."</td>";

                   echo "<td class='text-right'>".store_number_format($res2->total_cost)."</td>";
                  echo "</tr>";  
                  $tot_qty +=$res2->sales_qty;
                  $tot_tax_amt +=$res2->tax_amt;
                  $tot_discount_amt +=$res2->discount_amt;
                  $tot_total_cost +=$res2->total_cost;
                  $tot_price_per_unit +=$price_per_unit;
                  $sum_of_tot_price +=$tot_price;
              }

               //---------------------------------------------------------------//
                 

              // ภาษีมูลค่าเพิ่ม ในเวลาขาย — ดึงจาก db_sales.vat ที่บันทึกตอนขาย
              $vat_no = floatval($vat_sale); 
              if($vat_no == 0) {
                  // fallback to store's vat_no if not saved in the sale record
                  $vat_no = floatval($company_vat_no);
              }
     

               $total_non_tax = round($grand_total - $db_taxable_amount - $db_vat_amt, 2);     
              
         ?>
         
      
            </tbody>
            <tfoot class="text-right text-bold bg-gray">
              <tr>
                <td colspan="2" class="text-center">รวม</td>
                <td></td>
                <td><?= store_number_format($tot_discount_amt) ;?></td>
                 </td> <td>
                <td></td>
                <td class=" "><?=$tot_qty;?></td>
            
                <td><?= store_number_format($tot_total_cost) ;?></td>
              </tr>
            </tfoot>
          </table>
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    
      <div class="row">
       <div class="col-md-6">
          <div class="row">
              <div class="col-md-12">
                 <div class="form-group">
                    <label for="discount_to_all_input" class="col-sm-4 control-label" style="font-size: 17px;"><?= $this->lang->line('discount_on_all'); ?></label>    
                    <div class="col-sm-8">
                       <label class="control-label  " style="font-size: 17px;">: <?=store_number_format($discount_to_all_input); ?> (<?= $discount_to_all_type ?>)</label>
                    </div>
                 </div>
              </div>
           </div>    
          <div class="row">
              <div class="col-md-12">
                 <div class="form-group">
                    <label for="sales_note" class="col-sm-4 control-label" style="font-size: 17px;"><?= $this->lang->line('note'); ?></label>    
                    <div class="col-sm-8">
                       <label class="control-label  " style="font-size: 17px;">: <?=$sales_note;?></label>
                    </div>
                 </div>
              </div>
           </div>
           <div class="row">
              <div class="col-md-12">
                 <div class="form-group">
                    <label class="col-sm-4 control-label" style="font-size: 17px;"><?= $this->lang->line('invoiceTerms'); ?> : </label>    
                    <div class="col-sm-8">
                      <div><?=nl2br(html_entity_decode(trim($invoice_terms)));?></div>
                    </div>
                 </div>
              </div>
           </div> 
           <div class="row">
              <div class="col-md-12">
                 <div class="form-group">
                    <label for="sales_note" class="col-sm-4 control-label" style="font-size: 17px;"><?= $this->lang->line('sales_invoice_footer_text'); ?></label>    
                    <div class="col-sm-8">
                       <label  style="font-size: 14px; color:dodgerblue;">: <?= $sales_invoice_footer_text; ?></label>
                    </div>
                 </div>
              </div>
           </div>

           


           <div class="row">
              <div class="col-md-12">
                 <div class="form-group">
                    <table class="table table-hover table-bordered" style="width:100%" id=""><h4 class="box-title text-info"><?= $this->lang->line('payments_information'); ?> : </h4>
                       <thead>
                          <tr class="bg-purple " >
                             <th>#</th>
                             <th><?= $this->lang->line('date'); ?></th>
                             <th><?= $this->lang->line('payment_type'); ?></th>
                             <th><?= $this->lang->line('account'); ?></th>
                             <th><?= $this->lang->line('payment_note'); ?></th>
                             <th><?= $this->lang->line('payment'); ?></th>
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
                                  echo "<td>".$i++."</td>";
                                  echo "<td>".show_date($res3->payment_date)."</td>";
                                  echo "<td class='text-left'>";
                                    echo $res3->payment_type;
                                    if(!empty($res3->cheque_number)){
                                      echo "<br>Cheque no.:".$res3->cheque_number;
                                      echo "<br>Period:".$res3->cheque_period;
                                    }
                                  echo "</td>";
                                  echo "<td>".get_account_name($res3->account_id)."</td>";
                                  echo "<td>".$res3->payment_note."</td>";
                                  echo "<td class='text-right'>".store_number_format($res3->payment)."</td>";
                                  echo "</tr>";
                                  $total_paid +=$res3->payment;
                                }
                                echo "<tr class='text-right text-bold'><td colspan='5' >รวมเงิน</td><td>".store_number_format($total_paid)."</td></tr>";
                              }
                              else{
                                echo "<tr><td colspan='6' class='text-center text-bold'>ไม่มีการชำระ!!</td></tr>";
                              }

                            }
                            else{
                              echo "<tr><td colspan='6' class='text-center text-bold'>กำลังดำเนินการชำระ!!</td></tr>";
                            }
                          ?>
                       </tbody>
                     </table>
                  </div>
               </div>
            </div>           
         </div>

         <div class="col-md-6">
            <div class="row">
               <div class="col-md-12">
                  <div class="form-group">
                      
                 

                     <table  class="col-md-11">
                                                                                                       
                
                     <!--  <tr>
                           <th class="text-right" style="font-size: 17px;"><?= $this->lang->line('total_zero_vat_amt'); ?></th>
                           <th class="text-right" style="padding-left:10%;font-size: 11px;">
                              <h3><b id="subtotal_amt" name="subtotal_amt"><?php echo store_number_format($subtotal );?></b> </h3>
                           </th>
                        </tr>
                           -->
                  
                         <?php if(!empty($other_charges_amt !=0)) {?>
                           <tr>
                           <th class="text-right" style="font-size: 17px;"><?= $this->lang->line('other_charges') ; ?></th>
                           <th class="text-right" style="padding-left:10%;font-size: 17px;">
                              <h4><b id="other_charges_amt" name="other_charges_amt"><?=store_number_format($other_charges_amt);?></b></h4>
                           </th>
                          </tr> 
                           <?php } ?>       

                          <?php if($tot_discount_to_all_amt > 0) {?>
                          <tr>
                           <th class="text-right" style="font-size: 17px;"><?= $this->lang->line('discount_on_all') ; ?></th>
                           <th class="text-right" style="padding-left:10%;font-size: 17px;">
                              <h4><b id="discount_to_all_amt" name="discount_to_all_amt"><?=store_number_format($tot_discount_to_all_amt);?></b> </h4>
                           </th>
                          </tr>  
                          <?php } ?>  
                  
                   
                          <?php if(!empty($tot_price_zero_vat)) {?>
                        <tr>
                           <th class="text-right" style="font-size: 17px;"><?= $this->lang->line('total_zero_vat_amt'); ?></th>
                           <th class="text-right" style="padding-left:10%;font-size: 11px;">
                              <h4><b id="subtotal_amt" name="subtotal_amt"><?php echo store_number_format($total_non_tax);?></b> </h4>
                           </th>
                        </tr>
                        <?php } ?> 

                        
                          <?php if(!empty($vat_no !=0)) {?>
                        <tr class='text-primary'>
                           <th class="text-right" style="font-size: 17px;"><b><?= $this->lang->line('before_amt') ; ?></b></th>
                             <th class="text-right" style="padding-left:10%;font-size: 17px;">
                             <h4><b id="total_amt" name="total_amt"><?=store_number_format($db_taxable_amount);?></b> </h4>
                             <th>
                          </th>
                         </tr>
                          <?php } ?> 
                        
                        <?php if(!empty($vat_no !=0)) {?>
                        <tr class='text-primary'>
                           <th class="text-right" style="font-size: 17px;"><b><?= $this->lang->line('vat_amount') .( $vat_no)." % "; ?></b></th>
                             <th class="text-right" style="padding-left:10%;font-size: 17px;">
                             <h4><b id="total_amt" name="total_amt"><?=store_number_format($db_vat_amt);?></b> </h4>
                             <th>
                          </th>
                         </tr>
                       <?php } ?> 
                      
                        <tr class='text-primary'>
                           <th class="text-right" style="font-size: 18px;"><?= $this->lang->line('grand_total'); ?></th>
                           <th class="text-right" style="padding-left:10%;font-size: 18px;">
                              <h3 style="color:crimson ;"><b id="total_amt" name="total_amt"><?=store_number_format($grand_total);?></b> </h3>
                           </th>
                        </tr>
                     </table>
                                          
                   
                  </div>
               </div>
            </div>
         </div>
         <!-- /.col -->
      </div>
      <!-- /.row -->
    </div><!-- printableArea -->

      <!-- this row will not appear when printing -->
      <!-- this row will not appear when printing -->
      <div class="row no-print">
        <div class="col-md-12">
            <div class="col-sm-12 col-xs-12 text-left" style="margin-bottom: 10px;">
             <?php if($CI->permissions('sales_edit') && empty($sales_reference_type_raw)) { ?>
                    <?php $str2= ($pos==1)? 'pos/edit/':'sales/update/'; ?>
                    <a href="<?php echo $base_url; ?><?=$str2;?><?php echo  $sales_id ?>" target="_blank" class="btn btn-danger" style=" margin-right: 10px; margin-bottom:10px;">
                        <i class="fa fa-edit"></i> แก้ไข
                    </a>
                <?php } ?>
                      
                <a onclick="printTemplate('<?php echo $base_url; ?>pos/print_invoice_pos/<?php echo  $sales_id ?>')" target="_blank" class="btn btn-success" style=" margin-right: 10px; margin-bottom:10px;">
                     <i class="fa fa-file-text"></i> ใบเสร็จPOS
                 </a> 
               <a onclick="printTemplate('<?php echo $base_url; ?>sales/print_invoice/<?php echo  $sales_id ?>')" target="_blank" class="btn btn-primary" style=" margin-right: 10px; margin-bottom:10px;">
                    <i class="fa fa-file-text"></i> ใบแจ้งหนี้
                </a>  

                
             <a href="<?php echo $base_url; ?>pdf/invoice/<?php echo  $sales_id ?>" target="_blank" class="btn btn-primary" style="margin-bottom:5px; margin-right: 15px; margin-bottom:10px;">
              <i class="fa fa-file-pdf-o"></i> 
             ใบแจ้งหนี้PDF
             </a>
 
                             
                <?php if($CI->permissions('sales_return_add')) { ?>
                    <a href="<?php echo $base_url; ?>sales_return/add/<?php echo  $sales_id ?>" target="_blank" class="btn btn-danger" style=" margin-right: 10px; margin-bottom:10px;">
                        <i class="fa fa-undo"></i> ใบลดหนี้ e-Tax
                    </a>
                <?php } ?>

                 
                <?php if($CI->permissions('sales_return_add')) { ?>
                    <a href="<?php echo $base_url; ?>sales_debit/add/<?php echo  $sales_id ?>" target="_blank" class="btn btn-warning" style=" margin-right: 10px; margin-bottom:10px;">
                        <i class="fa fa-undo"></i> ใบเพิ่มหนี้ e-Tax
                    </a>
                <?php } ?>


                 <?php
                 $CI =& get_instance();
                 $receipt_exists = $CI->db->where('sales_id', $sales_id)->where('store_id', get_current_store_id())->get('db_receipts')->num_rows() > 0;
                 if ($receipt_exists): ?>
                     <a href="<?php echo $base_url; ?>pdf/receipt/<?php echo  $sales_id ?>" target="_blank" class="btn btn-info" style=" margin-right: 10px; margin-bottom:10px;">
                         <i class="fa fa-file-pdf-o"></i> ใบเสร็จรับเงิน
                     </a>
                 <?php else: ?>
                     <a href="#receiptModal" data-toggle="modal" class="btn btn-info" style=" margin-right: 10px; margin-bottom:10px;">
                         <i class="fa fa-file-pdf-o"></i> ใบเสร็จรับเงิน
                     </a>
                 <?php endif; ?>

                 <!-- Receipt Date Modal -->
                 <div class="modal fade" id="receiptModal" tabindex="-1" role="dialog" aria-labelledby="receiptModalLabel">
                   <div class="modal-dialog modal-sm" role="document">
                     <div class="modal-content text-left">
                       <form action="<?php echo $base_url; ?>pdf/receipt/<?php echo $sales_id ?>" method="GET" target="_blank" onsubmit="$('#receiptModal').modal('hide'); setTimeout(function(){location.reload();}, 1000);">
                         <div class="modal-header" style="background-color: #00a65a; color: white;">
                           <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                           <h4 class="modal-title" id="receiptModalLabel">เลือกวันที่ออกใบเสร็จรับเงิน</h4>
                         </div>
                         <div class="modal-body">
                            <div class="form-group">
                              <label for="receipt_date">วันที่ออกเอกสาร (Receipt Date):</label>
                              <div class="input-group date">
                                <div class="input-group-addon">
                                  <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right datepicker" id="receipt_date" name="receipt_date" value="<?php echo date('d-m-Y'); ?>" required>
                              </div>
                            </div>
                            <div class="form-group">
                              <label for="reference_no">อ้างอิงใบกำกับภาษี (Reference No):</label>
                              <select class="form-control" name="reference_no" id="reference_no">
                                <option value="<?php echo $sales_code; ?>" selected><?php echo $sales_code; ?></option>
                                <option value="">ไม่มีอ้างอิง (None)</option>
                              </select>
                            </div>
                         </div>
                         <div class="modal-footer">
                           <button type="button" class="btn btn-default" data-dismiss="modal">ยกเลิก</button>
                           <button type="submit" class="btn btn-success">ยืนยัน และสร้าง PDF</button>
                         </div>
                       </form>
                     </div>
                   </div>
                 </div>

                    <a href="<?php echo $base_url; ?>pdf/sales/<?php echo  $sales_id ?>" target="_blank" class="btn btn-success" style=" margin-right: 10px; margin-bottom:10px;">
                    <i class="fa fa-file-pdf-o"></i> ใบกำกับภาษี e-TAX
                </a>


                 <a href="<?php echo $base_url; ?>pdf_facturx/sales/<?php echo  $sales_id ?>" target="_blank" class="btn btn-primary" style=" margin-right: 10px; margin-bottom:10px;">
                    <i class="fa fa-file-pdf-o"></i>ใบเสร็จรับเงิน/ใบกำกับภาษี e-TAX
                </a>
               
              <!--    <a href="<?php echo $base_url; ?>pdf/sales/<?php echo  $sales_id ?>" target="_blank" class="btn btn-success" style=" margin-right: 10px; margin-bottom:10px;">
                    <i class="fa fa-file-pdf-o"></i> ใบกำกับภาษี e-TAX
                </a> -->


            </div>     
        </div>                                              
     </div>

    </section>


    <!-- /.content -->
    <div class="clearfix"></div>
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

<!-- Make sidebar menu hughlighter/selector -->
<script>$(".sales-list-active-li").addClass("active");</script>


</body>
</html>
