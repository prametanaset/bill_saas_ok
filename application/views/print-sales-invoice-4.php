<!DOCTYPE html>
<html>

<head>
  <?php //include"comman/code_css.php"; 
  ?>
  <link rel='shortcut icon' href='<?php echo $theme_link; ?>images/favicon.ico' />
 
 


  <style>
    @font-face {
        font-family: 'THSarabun';
        src: url('<?php echo $theme_link; ?>css/fonts-THSarabun/th-sarabun-psk/THSarabun.ttf') format('truetype');
        font-weight: normal;
        font-style: normal;
    }
    @font-face {
        font-family: 'THSarabun';
        src: url('<?php echo $theme_link; ?>css/fonts-THSarabun/th-sarabun-psk/THSarabun Bold.ttf') format('truetype');
        font-weight: bold;
        font-style: normal;
    }
    @font-face {
        font-family: 'THSarabun';
        src: url('<?php echo $theme_link; ?>css/fonts-THSarabun/th-sarabun-psk/THSarabun Italic.ttf') format('truetype');
        font-weight: normal;
        font-style: italic;
    }
    @font-face {
        font-family: 'THSarabun';
        src: url('<?php echo $theme_link; ?>css/fonts-THSarabun/th-sarabun-psk/THSarabun Bold Italic.ttf') format('truetype');
        font-weight: bold;
        font-style: italic;
    }
   

    @page {
      size: A4;
      margin: 20px 30px 30px 30px;
    }

   

    * {
      font-family: 'THSarabun', sans-serif !important;
    }

    body {
      font-family: 'THSarabun', sans-serif;
      word-wrap: break-word;
      font-size: 17px;
      line-height: 1.0;
      margin: 5px;
    }

    table,
    th,
    td {
      border-collapse: collapse;
      padding: 0px;
    }

    .border-blue {
      border-top: 1px solid #0070C0;
      border-bottom: 1px solid #0070C0;
    }

    th,
    td {
      padding: 2px; 
      text-align: left;
      vertical-align: top;
    }

    .style_hidden {
      border-style: hidden;
    }

    .fixed_table {
      table-layout: fixed;
    }

    .text-center {
      text-align: center;
    }

    .text-left {
      text-align: left;
    }

    .text-right {
      text-align: right;
    }

    .text-bold {
      font-weight: bold;
    }

    .bg-sky {
      background-color: #65a8e7ff;
    }

    #clockwise {
      rotate: 90;
    }

    #counterclockwise {
      rotate: -90;
    }

    @media print {
      * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
    }
  </style>
</head>

<body onload="window.print();"><!-- window.print() -->
  <?php
  $q1 = $this->db->query("select * from db_store where status=1 and id=" . get_current_store_id());
  $res1 = $q1->row();
  $store_name = $res1->store_name;
  $company_mobile = $res1->mobile;
  $company_phone = $res1->phone;
  $company_email = $res1->email;
  $previous_balance_bit = $res1->previous_balance_bit;
  $previous_balance_bit = $res1->previous_balance_bit;
  $t_and_c_status = $res1->t_and_c_status;
  $company_pan_no = $res1->pan_no;

  $company_address = $res1->address;
  $company_gst_no = $res1->gst_no;
  $company_vat_no = $res1->vat_no;
  $store_logo = (!empty($res1->store_logo)) ? $res1->store_logo : store_demo_logo();
  $store_website = $res1->store_website;
  $bank_details = $res1->bank_details;
  $terms_and_conditions = ""; //$res1->sales_terms_and_conditions;
  $image_file = ($res1->show_signature && !empty($res1->signature)) ? $res1->signature : '';

  $sales_invoice_footer_text = $res1->sales_invoice_footer_text;

  $company_postcode = $res1->postcode;
  $company_country = $res1->country;
  $company_state = $res1->state;
  $company_city = $res1->city;


  $q3 = $this->db->query("SELECT b.coupon_id,b.coupon_amt,b.due_date,b.customer_previous_due,b.customer_total_due,a.customer_name,a.mobile,a.phone,a.gstin,a.tax_number,a.email,a.shippingaddress_id,
                           a.opening_balance,a.country_id,a.state_id,a.city,a.created_by,
                           a.postcode,a.address,b.sales_date,b.created_time,b.reference_no,
                           b.sales_code,b.sales_note,b.sales_status,b.invoice_terms,b.vat,
                           b.taxable_amount, b.vat_amt,
                           coalesce(b.grand_total,0) as grand_total,
                           coalesce(b.subtotal,0) as subtotal,a.sales_due,
                           coalesce(b.paid_amount,0) as paid_amount,
                           coalesce(b.other_charges_input,0) as other_charges_input,
                           other_charges_tax_id,
                           coalesce(b.other_charges_amt,0) as other_charges_amt,
                           discount_to_all_input,
                           b.discount_to_all_type,
                           coalesce(b.tot_discount_to_all_amt,0) as tot_discount_to_all_amt,
                           coalesce(b.round_off,0) as round_off,
                           b.payment_status

                           FROM db_customers a,
                           db_sales b 
                           WHERE 
                           a.`id`=b.`customer_id` AND 
                           b.`id`='$sales_id' 
                           ");


  $res3 = $q3->row();
  $vat_sale=$res3->vat;
  $customer_name = $res3->customer_name;
  $customer_mobile = $res3->mobile;
  $customer_phone = $res3->phone;
  $customer_email = $res3->email;
  $customer_country = get_country($res3->country_id);
  $customer_state = get_state($res3->state_id);
  $customer_address = $res3->address;
  $customer_postcode = $res3->postcode;
  $customer_gst_no = $res3->gstin;
  $customer_tax_number = $res3->tax_number;
  $customer_opening_balance = $res3->opening_balance;
  $sales_date = $res3->sales_date;
  $due_date = $res3->due_date;
  $created_time = $res3->created_time;
  $reference_no = $res3->reference_no;
  $sales_code = $res3->sales_code;
  $sales_note = $res3->sales_note;
  $sales_status = $res3->sales_status;
  $created_by = $res3->created_by;
  //$previous_due=$res3->customer_previous_due;
  //$total_due=$res3->customer_total_due;
  $invoice_terms = $res3->invoice_terms;

  $previous_due = $res3->sales_due - ($res3->grand_total - $res3->paid_amount); //$res3->customer_previous_due;
  $previous_due = ($previous_due > 0) ? $previous_due : 0;
  $total_due = $res3->sales_due; //$res3->customer_total_due;

  $coupon_id = $res3->coupon_id;
  $coupon_amt = $res3->coupon_amt;

  $coupon_code = '';
  $coupon_type = '';
  $coupon_value = 0;
  if (!empty($coupon_id)) {
    $coupon_details = get_customer_coupon_details($coupon_id);
    $coupon_code = $coupon_details->code;
    $coupon_value = $coupon_details->value;
    $coupon_type = $coupon_details->type;
  }

  $subtotal = $res3->subtotal;
  $grand_total = $res3->grand_total;
  $other_charges_input = $res3->other_charges_input;
  $other_charges_tax_id = $res3->other_charges_tax_id;
  $other_charges_amt = $res3->other_charges_amt;
  $paid_amount = $res3->paid_amount;
  $discount_to_all_input = $res3->discount_to_all_input;
  $discount_to_all_type = $res3->discount_to_all_type;
  $discount_to_all_type = ($discount_to_all_type == 'in_percentage') ? '%' : ' ฿';
  $tot_discount_to_all_amt = $res3->tot_discount_to_all_amt;
 
  $round_off = $res3->round_off;
  $payment_status = $res3->payment_status;
  $db_taxable_amount = $res3->taxable_amount;
  $db_vat_amt = $res3->vat_amt;


  $shipping_country = '';
  $shipping_state = '';
  $shipping_city = '';
  $shipping_address = '';
  $shipping_postcode = '';
  if (!empty($res3->shippingaddress_id)) {
    $Q2 = $this->db->select("c.country,s.state,a.city,a.postcode,a.address")
      ->where("a.id", $res3->shippingaddress_id)
      ->from("db_shippingaddress a")
      ->join("db_country c", "c.id = a.country_id", 'left')
      ->join("db_states s", "s.id = a.state_id", 'left')
      ->get();
    if ($Q2->num_rows() > 0) {
      $shipping_country = $Q2->row()->country;
      $shipping_state = $Q2->row()->state;
      $shipping_city = $Q2->row()->city;
      $shipping_address = $Q2->row()->address;
      $shipping_postcode = $Q2->row()->postcode;
    }
  }

  // ราตารวม vat 0% จะเก็บใน $tot_price_zero_vat
  $tot_price_zero_vat  = 0.0;
  $this->db->select(" a.description,c.item_name, a.sales_qty,a.tax_type,
                          a.price_per_unit, b.tax,b.tax_name,a.tax_amt,
                          a.discount_input,a.discount_amt, a.unit_total_cost,
                          a.total_cost , d.unit_name,c.sku,c.hsn
                      ");
  $this->db->where("a.sales_id", $sales_id);
  $this->db->from("db_salesitems a");
  $this->db->join("db_tax b", "b.id=a.tax_id", "left");
  $this->db->join("db_items c", "c.id=a.item_id", "left");
  $this->db->join("db_units d", "d.id = c.unit_id", "left");
  $q4 = $this->db->get();

  foreach ($q4->result() as $res2) {
    if ($res2->tax == 0) {
      $tot_price_zero_vat += $res2->total_cost;
    }
  }

  
  // $befor_vat= $total_price - $vat_total ;
  // ราตารวม vat 0% จะเก็บใน $tot_price_zero_vat _end

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

  
  <table autosize="1" style="overflow: wrap" id='mytable' align="center" width="100%" height='100%' cellpadding="0" cellspacing="0">
    <!-- <table align="center" width="100%" height='100%'   > -->

    <thead>

      <tr>
        <th colspan="16">
          <table width="100%" height='100%' class="style_hidden fixed_table">

            <tr>
              <td colspan="10">
                <p style="font-size: 17px; line-height: 1.1; margin-left: 10px">
                  <span style="font-size: 19px;"><b><?php echo $store_name; ?></b></span> <br />
                  <?php echo $company_address; ?>
                  <?php
                  if (!empty($company_city)) {
                    echo "  " . $city_name;
                  }
                  if (!empty($company_state)) {
                    echo "  " . $state_name;
                  }
                  if (!empty($company_country)) {
                    echo "  " . $country_name;
                  }
                  if (!empty($company_postcode)) {
                    echo "-" . $company_postcode;
                  }
                  ?>
                  <br />
                  <?php echo (!empty(trim($company_gst_no))) ? $this->lang->line('tax_id') . " " . $company_gst_no . "  " : ''; ?>
                  <?php echo (!empty(trim($company_pan_no)) && pan_number()) ? $this->lang->line('branch_no') . " : " . $company_pan_no . "  " : ''; ?> <br />
                  <?= $this->lang->line('phone'); ?>: <?php echo  $company_mobile; ?>
                  <?php echo (!empty(trim($company_phone))) ? $this->lang->line('mobile') . ": " . $company_phone . "  " : ''; ?> <br />
                  <?php echo (!empty(trim($company_email))) ? $this->lang->line('email') . ": " . $company_email . "<br/>" : ''; ?>
                </p>
              </td>


              <!-- Second Half -->

              <td colspan="6" rowspan="1">
                <br />
                <span>
                  <table style="width: 100%;" class="style_hidden fixed_table">

                    <tr>
                      <td colspan="6" class="text-left" style="color:blue; font-size:36px; font-weight: 700;">
                        <?php echo $this->lang->line('e_invoice'); ?> 
                      </td>
                    </tr>

                    <tr>
                     <td colspan="6" class="text-left" style="color:blue; font-size:14px; font-weight: 600;">
                      <!--  <?= $this->lang->line('original'); ?> -->
                        
                      </td>
                    </tr><br />
                    
                     <tr>
                      <td colspan="6" style="margin-top: 10px; ">
                        <?= $this->lang->line('invoice_no'); ?> : 
                        <span style="font-size: 18px;">
                          <b><?php echo "$sales_code"; ?></b>
                        </span>
                      </td>
                    </tr>

                    <tr>
                      <td colspan="6">
                        <?= $this->lang->line('invoice_date'); ?>
                        <span style="font-size: 18px;">
                          <b><?php echo show_date($sales_date); ?></b>
                        </span>
                      </td>
                    </tr>
                     <tr>
                      <td colspan="6">
                        <?= $this->lang->line('reference_no'); ?>
                        <span style="font-size: 18px;">
                          <b> : <?php echo "$sales_code"; ?></b>
                        </span>
                      </td>
                    </tr>

                  </table>
                </span>
              </td>
            </tr>
           
            <tr>
              <!-- Bottom Half -->
              <td colspan="10">
                <p style="font-size: 17px; line-height: 1.1; margin-left: 10px">
                  <span style="color:blue;font-size: 17px;"><?= $this->lang->line('customer'); ?> :</span>
                  <span style="font-size: 19px;"><b><?php echo $customer_name; ?></b></span> <br />
                  <?php
                    echo get_full_address_thai($res3->address, $res3->country_id, $res3->state_id, $res3->city, $res3->postcode);
                  ?>
                  <br />
                  <?php echo (!empty(trim($customer_gst_no))) ? $this->lang->line('tax_id') . " : " . $customer_gst_no . "  " : ''; ?>
                  <?php echo (!empty(trim($customer_tax_number))) ? $this->lang->line('branch_no') . " : " . $customer_tax_number . "  " : ''; ?> <br />
                  <?php echo (!empty(trim($customer_phone))) ? $this->lang->line('phone') . " : " . $customer_phone . "  " : ''; ?>
                  <?php echo (!empty(trim($customer_mobile))) ? $this->lang->line('mobile') . " : " . $customer_mobile . "   " : ''; ?>
                  <?php echo (!empty(trim($customer_email))) ? $this->lang->line('email') . ": " . $customer_email . "<br/>" : ''; ?>
            </p>
              </td>
              <td colspan="6" rowspan="1">
                <span>
                  <table style="width: 100%;" class="style_hidden fixed_table">             
                    
                  <tr>
                      <td colspan="6">                     
                        <?php if(!empty($due_date)){ ?>
                        <?= $this->lang->line('due_date'); ?> :
                        <span style="font-size: 18px;">
                          <b><?= (!empty($due_date)) ? show_date($due_date) : ''; ?></b>
                        </span>
                        <?php } ?>
                      </td>
                     </tr> 

                  <!--   <tr>
                      <td colspan="6">
                        <?php if(!empty($reference_no)){ ?>
                        <?= $this->lang->line('reference_bill'); ?>
                        <span style="font-size: 18px;">:
                          <b><?php echo "$reference_no"; ?> </b>
                        </span>
                        <?php } ?>
                      </td>
                     </tr>

                     <tr>
                      <td colspan="6">
                        <?php if(!empty($reference_no)){ ?>
                        <?= $this->lang->line('date_bill'); ?>
                        <span style="font-size: 18px;">:
                          <b><?php 
                            $ref_date = '';
                            $ref_no_clean = trim($reference_no);
                            $q_ref = $this->db->query("SELECT sales_date FROM db_sales WHERE sales_code=? LIMIT 1", array($ref_no_clean));
                            if($q_ref->num_rows() > 0){
                                $ref_date = show_date($q_ref->row()->sales_date);
                            } else {
                                $ref_date = "-"; // Not found in database
                            }
                            echo $ref_date; 
                          ?> </b>
                        </span>
                        <?php } ?>
                      </td>
                     </tr>

                     <tr>
                      <td colspan="6">
                         <?php if(!empty($reference_no)){ ?>
                        <?= $this->lang->line('reason_bill'); ?> 
                        <span style="font-size: 18px;"> <br/>
                           <b>: <?php echo "$sales_note"; ?> </b>                       
                        </span>
                         <?php } ?>
                      </td>
                    </tr>   
                         -->
                 
                  </table>
                </span>
              </td>

            </tr>

          </table>
        </th>
      </tr>
      <!--   <tr>
        <td colspan="16">&nbsp; </td>
      </tr>-->
      <tr style="background-color: #dbe0e6 !important; color: #333333 !important; border: none !important;"><!-- Colspan 10 -->
        <th colspan='1' class="text-center" style="border: none; padding: 10px 5px;"><?= $this->lang->line('sl_no'); ?></th>
        <th colspan='6' class="text-center" style="border: none; padding: 10px 5px;"><?= $this->lang->line('description_of_goods'); ?></th>
        <th colspan='1' class="text-center" style="border: none; padding: 10px 5px;"><?= $this->lang->line('qty'); ?></th>
        <th colspan='1' class="text-center" style="border: none; padding: 10px 5px;"><?= $this->lang->line('unit_cost'); ?></th>
        <th colspan='1' class="text-center" style="border: none; padding: 10px 5px;"><?= $this->lang->line('disc.'); ?></th>
        <th colspan='2' class="text-center" style="border: none; padding: 10px 5px;"><?= $this->lang->line('tax'); ?></th>     
        <th colspan='4' class="text-center" style="border: none; padding: 10px 5px;"><?= $this->lang->line('total_vatable'); ?></th>
      </tr>
    </thead>

    <tbody style="border-bottom: 2px solid #0070C0;">

      <!-- <tr>
    <td colspan='16' class='test'> -->
      <?php
      $i = 1;
      $tot_qty = 0;
      $tot_sales_price = 0;
      $tot_tax_amt = 0;
      $tot_discount_amt = 0;
      $tot_unit_total_cost = 0;
      $tot_total_cost = 0;
      $tot_before_tax = 0;

      $tot_price_per_unit = 0;
      $sum_of_tot_price = 0;

      $this->db->select(" a.description,c.item_name, a.sales_qty,a.tax_type,
                                  a.price_per_unit, b.tax,b.tax_name,a.tax_amt,
                                  a.discount_input,a.discount_amt, a.unit_total_cost,
                                  a.total_cost , d.unit_name,c.sku,c.hsn
                              ");
      $this->db->where("a.sales_id", $sales_id);
      $this->db->from("db_salesitems a");
      $this->db->join("db_tax b", "b.id=a.tax_id", "left");
      $this->db->join("db_items c", "c.id=a.item_id", "left");
      $this->db->join("db_units d", "d.id = c.unit_id", "left");
      $q2 = $this->db->get();

      foreach ($q2->result() as $res2) {
        $str = ($res2->tax_type=='Inclusive')? 'รวม' : 'แยก';
        $discount = (empty($res2->discount_input) || $res2->discount_input == 0) ? '0' : $res2->discount_input . "%";
        $discount_amt = (empty($res2->discount_amt) || $res2->discount_input == 0) ? '0' : $res2->discount_amt . "";
        $before_tax = $res2->unit_total_cost; // * $res2->sales_qty;
        $tot_cost_before_tax = $before_tax * $res2->sales_qty;
        $res2->unit_total_cost;
        $price_per_unit = $res2->price_per_unit;
        //    if($res2->tax_type=='Inclusive'){
        //      $price_per_unit -= ($res2->tax_amt/$res2->sales_qty);
        //    }

        $discount_price = $discount_amt /  $res2->sales_qty;
        $tot_price = $price_per_unit * $res2->sales_qty;
        $other_charges_tax = $other_charges_amt / (100 +$vat_sale)* $vat_sale;

        echo "<tr>";
        echo "<td colspan='1' class='text-center'>" . $i++ . "</td>";
        echo "<td colspan='6'>";
        echo $res2->item_name;
        echo (!empty($res2->description)) ? "<br/><i>[" . nl2br($res2->description) . "]</i>" : '';
        echo "</td>";
        echo "<td colspan='1' class='text-center'>" . format_qty($res2->sales_qty) . "</td>";
        echo "<td colspan='1' class='text-center'>" . store_number_format($res2->price_per_unit) . "</td>";      
        echo "<td colspan='1' class='text-center'>" . store_number_format($discount_amt) . "</td>";
        echo "<td colspan='2' class='text-center'>". $res2->tax_name."</td>";  
      //  echo "<td class='text-center'>" . $res2->tax_name . " ".store_number_format($res2->tax)."% " ."</td>";                    
        echo "<td colspan='4' class='text-right'>" . store_number_format($res2->total_cost) . "</td>";
        echo "</tr>";
        $tot_qty += $res2->sales_qty;
        //$tot_sales_price +=$res2->price_per_unit;
        $tot_tax_amt += $res2->tax_amt;
        $tot_discount_amt += $res2->discount_amt;
        $tot_unit_total_cost += $res2->unit_total_cost;
        $tot_before_tax += $before_tax;
        $tot_total_cost += $res2->total_cost;
        $tot_price_per_unit += $price_per_unit;
        $sum_of_tot_price += $tot_price;

        $tot_total_cost_tax = $tot_total_cost - $tot_tax_amt;

         //------------------------------------------------------------------------------------//
                                 
            $total_non_tax = $tot_price_zero_vat; //ยกเว้น vat  = ราคายกเว้นVAT   ----- (ก่อนส่วนลด)
            $total_in_tax =  $tot_total_cost - $tot_price_zero_vat; //สินค้ารวมvat = ราคาสินค้ารวมภาษี    ------(ก่อนหักส่วนลด)

            // $db_taxable_amount=$res3->taxable_amount;
            // $db_vat_amt=$res3->vat_amt;
                          
                // ภาษีมูลค่าเพิ่ม ในเวลาขาย
                $vat_no = floatval($vat_sale); 
                if($vat_no == 0) {
                    //  หากค่าใน DB เป็น 0 ให้กลับไปใช้การคำนวณแบบเดิมเพื่อความปลอดภัย
                  //  $tax_no = ($total_in_tax - $tot_tax_amt) / 100; 
                    $vat_no = ($tax_no != 0) ? $tot_tax_amt / $tax_no : 0;
                }

                 //-----------------------------------//

                   

              $item_vat_inc = round(($total_in_tax * 100) / (100 + $vat_no), 2);    // สินค้ารวม VAT
        $service_fee = round(($other_charges_amt * 100) / (100 + $vat_no), 2);     // ค่าบริการ (สมมติว่าเป็นยอดที่ต้องคิด VAT เหมือนสินค้ากลุ่มแรก)
        $item_exempt =  round($total_non_tax, 2);     // สินค้ายกเว้น VAT
        $discount_total = round($tot_discount_to_all_amt, 2) ;  // ส่วนลดรวมทั้งหมด
     

        // 2. รวมยอดก่อนหักส่วนลด (เพื่อหาค่า อัตราส่วน)
        $subtotal_before_discount = round($item_vat_inc + $item_exempt + $service_fee, 2);

        // 3. คำนวณสัดส่วนส่วนลด (Ratio)
        $discount_ratio = ($subtotal_before_discount != 0) ? ($discount_total / $subtotal_before_discount) : 0;

        // 4. กระจายส่วนลดไปแต่ละรายการ (Prorate Discount)
        $discount_for_vat_group = round(($item_vat_inc + $service_fee) * $discount_ratio, 2); // ส่วนลดของกลุ่มมี VAT
        $discount_for_exempt_group = round($item_exempt * $discount_ratio, 2);               // ส่วนลดของกลุ่มยกเว้น VAT

        // 5. หายอดหลังหักส่วนลด (Net Amount)
        $net_vat_group = round(($item_vat_inc + $service_fee) - $discount_for_vat_group, 2);  //  มูลค่า คำนวนvat
        $net_exempt_group = round($item_exempt - $discount_for_exempt_group, 2);     //  มูลค่ายกเว้น vat
        $total_non_tax = $net_exempt_group;
                     
              
      }
      

      ?>
      <!--     </td>
  </tr> -->
    </tbody>


    <tfoot>

      <tr>
        <td colspan="16" style="height:3px;"></td>
      </tr>

    <!--  <tr>
              <td colspan="12" style="text-align: right;"><b><?= $this->lang->line('subtotal'); ?></b></td>
              <td colspan="4" style="text-align: right;" ><b><?php echo store_number_format(   $subtotal); ?></b></td>
         </tr>
     
      
         <?php if (!empty($tot_price_zero_vat != 0)) { ?>
        <tr>
          <td colspan="12" class='text-right'><b><?= $this->lang->line('total_zero_vat'); ?></b></td>
          <td colspan="4" class='text-right'><b><?php echo store_number_format($group_non_vat); ?></b></td>
        </tr>
         <?php } ?>


       <tr>
        <td colspan="12" class='text-right'><b><?= $this->lang->line('total_item_vat'); ?></b></td>
        <td colspan="4" class='text-right'><b><?php echo store_number_format( $total_in_tax); ?></b></td>
      </tr> 
     -->

       <?php if (!empty($other_charges_amt != 0)) { ?>
       <tr>
    <td colspan="12" style="text-align: right;"><b><?= $this->lang->line('other_charges'); ?></b></td>
    <td colspan="4" style="text-align: right;" ><b><?php echo store_number_format($other_charges_amt); ?></b></td>
    </tr>
    <?php } ?>

      <?php if (!empty($tot_discount_to_all_amt != 0)) { ?>
        <tr>
          <td colspan="12" class='text-right'><b><?= $this->lang->line('discount_on_all'); ?></b>      
          </td>
          <td colspan="4" class='text-right'><b><?php echo store_number_format($tot_discount_to_all_amt); ?></b></td>
        </tr>
      <?php } ?>

      <tr>
        <td colspan="16" style="height:3px;"></td>
      </tr>

       <?php if(!empty($vat_no !=0)) {?>
     <?php if(!empty($total_non_tax !=0)) {?>
        <tr>
          <td colspan="12" class='text-right'><b><?= $this->lang->line('total_zero_vat_amt'); ?></b></td>
          <td colspan="4" class='text-right'><b><?php echo store_number_format($total_non_tax); ?></b></td>
        </tr>
      <?php } ?>
        <?php } ?>

       <?php if(!empty($vat_no !=0)) {?>
      <tr>
        <td colspan="12" class='text-right'><b><?= $this->lang->line('before_amt'); ?></b></td>
        <td colspan="4" class='text-right'><b><?php echo store_number_format($db_taxable_amount); ?></b></td>
      </tr>
       <?php } ?>

       <?php if(!empty($vat_no !=0)) {?>
      <tr>
        <td colspan="12" class='text-right'><b><?= $this->lang->line('vat_amount') . ($vat_no) . " % "; ?></b></td>
        <td colspan="4" class='text-right'><b><?php echo store_number_format($db_vat_amt); ?></b> </td>
      </tr>
      <?php } ?>


      <tr style="background-color: #eaeff5 !important;">
        <td colspan="8" class="text-left" style="padding: 5px;">
          <?php
          const BAHT_TEXT_NUMBERS = array('ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า');
          const BAHT_TEXT_UNITS = array('', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน');
          const BAHT_TEXT_ONE_IN_TENTH = 'เอ็ด';
          const BAHT_TEXT_TWENTY = 'ยี่';
          const BAHT_TEXT_INTEGER = 'ถ้วน';
          const BAHT_TEXT_BAHT = 'บาท';
          const BAHT_TEXT_SATANG = 'สตางค์';
          const BAHT_TEXT_POINT = 'จุด';

          function baht_text($number, $include_unit = true, $display_zero = true)
          {
            if (!is_numeric($number)) {
              return null;
            }

            $log = floor(log($number, 10));
            if ($log > 5) {
              $millions = floor($log / 6);
              $million_value = pow(1000000, $millions);
              $normalised_million = floor($number / $million_value);
              $rest = $number - ($normalised_million * $million_value);
              $millions_text = '';
              for ($i = 0; $i < $millions; $i++) {
                $millions_text .= BAHT_TEXT_UNITS[6];
              }
              return baht_text($normalised_million, false) . $millions_text . baht_text($rest, true, false);
            }

            $number_str = (string)floor($number);
            $text = '';
            $unit = 0;

            if ($display_zero && $number_str == '0') {
              $text = BAHT_TEXT_NUMBERS[0];
            } else for ($i = strlen($number_str) - 1; $i > -1; $i--) {
              $current_number = (int)$number_str[$i];

              $unit_text = '';
              if ($unit == 0 && $i > 0) {
                $previous_number = isset($number_str[$i - 1]) ? (int)$number_str[$i - 1] : 0;
                if ($current_number == 1 && $previous_number > 0) {
                  $unit_text .= BAHT_TEXT_ONE_IN_TENTH;
                } else if ($current_number > 0) {
                  $unit_text .= BAHT_TEXT_NUMBERS[$current_number];
                }
              } else if ($unit == 1 && $current_number == 2) {
                $unit_text .= BAHT_TEXT_TWENTY;
              } else if ($current_number > 0 && ($unit != 1 || $current_number != 1)) {
                $unit_text .= BAHT_TEXT_NUMBERS[$current_number];
              }

              if ($current_number > 0) {
                $unit_text .= BAHT_TEXT_UNITS[$unit];
              }

              $text = $unit_text . $text;
              $unit++;
            }

            if ($include_unit) {
              $text .= BAHT_TEXT_BAHT;

              $satang = explode('.', number_format($number, 2, '.', ''))[1];
              $text .= $satang == 0
                ? BAHT_TEXT_INTEGER
                : baht_text($satang, false) . BAHT_TEXT_SATANG;
            } else {
              $exploded = explode('.', $number);
              if (isset($exploded[1])) {
                $text .= BAHT_TEXT_POINT;
                $decimal = (string)$exploded[1];
                for ($i = 0; $i < strlen($decimal); $i++) {
                  $text .= BAHT_TEXT_NUMBERS[$decimal[$i]];
                }
              }
            }

            return $text;
          }
          ?>
          <span class='amt-in-word'><b><?= $this->lang->line('amount_in_words'); ?> : </b> <i style='font-weight:bold;'><?= baht_text($grand_total); ?></i></span>
        </td>
        <td colspan="4" class='text-right' style="padding: 5px;"><b><?= $this->lang->line('grand_total'); ?></b></td>
        <td colspan="4" class='text-right' style="padding: 5px;"><b><?php echo store_number_format($grand_total); ?></b> </td>
      </tr>

<br/>

    


      <tr>
        <td colspan="16">&nbsp; </td>
      </tr>

 <tr class="border-blue">
        <td colspan="16">
          <table width="100%" class="style_hidden fixed_table">          
              <tr>
             <td colspan="16">
                  <span>
                    <table style="width: 100%;" class="style_hidden fixed_table">
                    
                        <!-- T&C & Bank Details --> 
                       <tr>
                          <td colspan='8' style="height:60px;">

                          <span class='amt-in-word'>
                         <b><?= $this->lang->line('invoiceTerms') ." :</b> <br/> " . nl2br(html_entity_decode($invoice_terms))."";?>
                         </span>
      	                                  
                          </td>
                         
                          <td colspan='8'>
                           <div><b> <?= $this->lang->line('authorised_signatory'); ?></b></div> <br/>
                           <?php if(!empty($image_file)) {?>
                           <img src="<?= base_url($image_file);?>" width='50%' height='auto'> 
                          <?php } ?>
                          </td>
                      </tr>
                     
                    </table>
                  </span>
                </td>
              </tr>          
          </table>
      </td>
      </tr>

     <!-- <?php if (!empty($sales_invoice_footer_text)) { ?>
        <tr>
          <td colspan="16" style="text-align: center;font-size:small;">
            <?= $sales_invoice_footer_text; ?>
          </td>
        </tr>
      <?php } ?> -->
    </tfoot>

  </table>
  <!-- <caption>
      <center>
        <span style="font-size: 11px;text-transform: uppercase;">
          This is Computer Generated Invoice
        </span>
      </center>
</caption> -->
</body>

</html>