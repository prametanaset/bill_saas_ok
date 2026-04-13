<!DOCTYPE html>
<html>
<title><?= $page_title;?></title>
<head>
  <?php //include"comman/code_css.php"; ?>
<link rel='shortcut icon' href='<?php echo $theme_link; ?>images/favicon.ico' />

<style>
    @font-face {
        font-family: 'THSarabunNew';
        src: url('<?php echo $theme_link; ?>css/fonts-THSarabun/th-sarabun-psk/THSarabun.ttf') format('truetype');
        font-weight: normal;
        font-style: normal;
    }
    @font-face {
        font-family: 'THSarabunNew';
        src: url('<?php echo $theme_link; ?>css/fonts-THSarabun/th-sarabun-psk/THSarabun Bold.ttf') format('truetype');
        font-weight: bold;
        font-style: normal;
    }
    @font-face {
        font-family: 'THSarabunNew';
        src: url('<?php echo $theme_link; ?>css/fonts-THSarabun/th-sarabun-psk/THSarabun Italic.ttf') format('truetype');
        font-weight: normal;
        font-style: italic;
    }
    @font-face {
        font-family: 'THSarabunNew';
        src: url('<?php echo $theme_link; ?>css/fonts-THSarabun/th-sarabun-psk/THSarabun Bold Italic.ttf') format('truetype');
        font-weight: bold;
        font-style: italic;
    }

    @page {
      size: A4;
      margin: 20px 30px 30px 30px;
    }

    * {
      font-family: 'THSarabunNew', sans-serif !important;
    }

    body {
      font-family: 'THSarabunNew', sans-serif;
      word-wrap: break-word;
      font-size: 17px;
      line-height: 1.2;
      margin: 5px;
    }

    table, th, td {
      border-collapse: collapse;
      padding: 0px;
      font-family: 'THSarabunNew', sans-serif;
    }

    .border-blue {
      border-top: 2px solid #0070C0;
      border-bottom: 1px solid #0070C0;
    }
    
    .border-bottom-blue {
      border-bottom: 1px solid #0070C0;
    }

    th, td {
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

    .bg-sky {
      background-color: #65a8e7ff;
    }

    @media print {
      * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
    }
</style>
</head>
<body onload="window.print();">
<?php
    $q1=$this->db->query("select * from db_store where status=1 and id=".get_current_store_id());
    $res1=$q1->row();
    $store_name=$res1->store_name;
    $company_mobile=$res1->mobile;
    $company_phone=$res1->phone;
    $company_email=$res1->email;
    $company_country=$res1->country;
    $company_state=$res1->state;
    $company_city=$res1->city;
    $company_address=$res1->address;
    $company_postcode=$res1->postcode;
    $company_gst_no=$res1->gst_no;
    $company_vat_no=$res1->vat_no;
    $company_pan_no=$res1->pan_no;
    $store_logo=(!empty($res1->store_logo)) ? $res1->store_logo : store_demo_logo();
    $image_file=($res1->show_signature && !empty($res1->signature)) ? $res1->signature : '';

    $q3=$this->db->query("SELECT a.supplier_name,a.mobile,a.phone,a.gstin,a.tax_number,a.email,
                           a.opening_balance,a.country_id,a.state_id,a.created_by,
                           a.postcode,a.address,b.return_date,b.created_time,b.reference_no,
                           b.return_code,b.return_note,b.return_status,b.vat,b.purchase_id,
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
                           b.payment_status
                           FROM db_suppliers a,
                           db_purchasereturn b 
                           WHERE 
                           a.`id`=b.`supplier_id` AND 
                           b.`id`='$return_id' 
                           ");
    $res3=$q3->row();
    $supplier_name=$res3->supplier_name;
    $supplier_mobile=$res3->mobile;
    $supplier_phone=$res3->phone;
    $supplier_email=$res3->email;
    $supplier_gst_no=$res3->gstin;
    $supplier_tax_number=$res3->tax_number;
    $return_date=$res3->return_date;
    $reference_no=$res3->reference_no;
    $return_code=$res3->return_code;
    $return_note=$res3->return_note;
    $return_status=$res3->return_status;
    $grand_total=$res3->grand_total;
    $other_charges_amt=$res3->other_charges_amt;
    $tot_discount_to_all_amt=$res3->tot_discount_to_all_amt;
    $taxable_amount=$res3->taxable_amount;
    $db_vat_amt=$res3->vat_amt;
    $pur_vat_rate=$res3->vat;
    $purchase_id=$res3->purchase_id;

    $purchase_code = (!empty($purchase_id))?$this->db->query("select purchase_code from db_purchase where id=".$purchase_id)->row()->purchase_code:'';

    $is_bangkok = false;
    if (!empty($company_country)) {
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
    }
    if (!empty($company_city)) {
      $q = $this->db->get_where('subdistricts', ['code' => $company_city]);
      if ($q->num_rows() > 0) {
        $prefix = $is_bangkok ? ' แขวง' : ' ต.';
        $city_name = $prefix . $q->row()->name_in_thai;
      }
    }
    if (!empty($company_state)) {
      $q = $this->db->get_where('districts', ['code' => $company_state]);
      if ($q->num_rows() > 0) {
        $prefix = $is_bangkok ? ' เขต' : ' อ.';
        $state_name = $prefix . $q->row()->name_in_thai;
      }
    }
?>
<table autosize="1" style="overflow: wrap" id='mytable' align="center" width="100%" height='100%' cellpadding="0" cellspacing="0">
    <thead>
      <tr>
        <th colspan="16">
          <table width="100%" height='100%' class="style_hidden fixed_table">
            <tr>
              <td colspan="10">
                <p style="font-size: 17px; line-height: 1.1; margin-left: 10px; margin-top: 5px">
                  <br />
                  <span style="font-size: 19px;"><b><?php echo $store_name; ?></b></span> <br />
                  <?php echo $company_address; ?>
                  <?php
                  if (!empty($company_city)) { echo "  " . $city_name; }
                  if (!empty($company_state)) { echo "  " . $state_name; }
                  if (!empty($company_country)) { echo "  " . $country_name; }
                  if (!empty($company_postcode)) { echo "-" . $company_postcode; }
                  ?>
                  <br />
                  <?php echo (!empty(trim($company_gst_no))) ? $this->lang->line('tax_id') . " " . $company_gst_no . "  " : ''; ?>
                  <?php echo (!empty(trim($company_pan_no)) && pan_number()) ? $this->lang->line('branch_no') . " : " . $company_pan_no . "  " : ''; ?> <br />
                  <?= $this->lang->line('phone'); ?>: <?php echo  $company_mobile; ?>
                  <?php echo (!empty(trim($company_phone))) ? $this->lang->line('mobile') . ": " . $company_phone . "  " : ''; ?> <br />
                  <?php echo (!empty(trim($company_email))) ? $this->lang->line('email') . ": " . $company_email . "<br/>" : ''; ?>
                </p>
              </td>

              <td colspan="6" rowspan="1">
                <span>
                  <table style="width: 100%;" class="style_hidden fixed_table">
                    <tr>
                      <td colspan="6" class="text-left" style="color:blue; font-size:36px; font-weight: 700;">
                        <?php echo $this->lang->line('return_pur_invoice'); ?> 
                      </td>
                    </tr>
                    <tr><td colspan="6" style="height: 10px;"></td></tr>

                    <tr>
                      <td colspan="6">
                        เลขที่ / No. : 
                        <span style="font-size: 18px;">
                          <b><?php echo " $return_code"; ?></b>
                        </span>
                      </td>
                    </tr>

                    <tr>
                      <td colspan="6">
                        <?php echo $this->lang->line('date'); ?>:
                        <span style="font-size: 18px;">
                          <b><?php echo show_date($return_date); ?></b>
                        </span>
                      </td>
                    </tr>

                       <?php if(!empty($purchase_code)) { ?>
                    <tr>
                      <td colspan="6">
                        <?= $this->lang->line('return_against_purchase'); ?>:
                        <span style="font-size: 18px;">
                        <b>#<?php echo $purchase_code; ?></b>
                        </span>
                      </td>
                    </tr>
                    <?php } ?>
                    <?php if(!empty($reference_no)) { ?>
                    <tr>
                      <td colspan="6">
                        <?php echo $this->lang->line('reason'); ?>:
                        <span style="font-size: 18px;">
                          <b><?php echo "$reference_no"; ?></b>
                        </span>
                      </td>
                    </tr>
                    <?php } ?>
                 <!--   <tr>
                      <td colspan="6">
                        <?php echo $this->lang->line('status'); ?>:
                        <span style="font-size: 18px;">
                          <b><?php echo "$return_status"; ?></b>
                        </span>
                      </td>
                    </tr> -->
               
                  </table>
                </span>
              </td>
            </tr>
           
            <tr>
              <td colspan="16">
                <p style="font-size: 17px; line-height: 1.1; margin-left: 10px">
                  <span style="color:blue;font-size: 15px;"><?= $this->lang->line('supplier'); ?> :</span><br>
                  <span style="font-size: 19px;"><b><?php echo $supplier_name; ?></b></span> <br />
                  <?php
                    // Directly using address parts from $res3 for Return
                    echo get_full_address_thai($res3->address, $res3->country_id, $res3->state_id, null, $res3->postcode);
                  ?>
                  <br />
                  <?php echo (!empty(trim($supplier_gst_no))) ? $this->lang->line('tax_id') . " : " . $supplier_gst_no . "  " : ''; ?>
                  <?php echo (!empty(trim($supplier_tax_number))) ? $this->lang->line('branch_no') . " : " . $supplier_tax_number . "  " : ''; ?> <br />
                  <?php echo (!empty(trim($supplier_phone))) ? $this->lang->line('phone') . " : " . $supplier_phone . "  " : ''; ?>
                  <?php echo (!empty(trim($supplier_mobile))) ? $this->lang->line('mobile') . " : " . $supplier_mobile . "   " : ''; ?>
                  <?php echo (!empty(trim($supplier_email))) ? $this->lang->line('email') . ": " . $supplier_email . "<br/>" : ''; ?>
                </p>
              </td>
            </tr>
          </table>
        </th>
      </tr>

      <tr>
        <td colspan="16"> </td>
      </tr>
      <tr style="background-color: #dbe0e6 !important; color: #333333 !important; border: none !important;">
        <th colspan='1' class="text-center" style="border: none; padding: 10px 5px;"><?= $this->lang->line('sl_no'); ?></th>
        <th colspan='6' class="text-center" style="border: none; padding: 10px 5px;" ><?= $this->lang->line('description_of_goods'); ?></th>
        <th colspan='1' class="text-center" style="border: none; padding: 10px 5px;"><?= $this->lang->line('qty'); ?></th>
        <th colspan='2' class="text-center" style="border: none; padding: 10px 5px;"><?= $this->lang->line('unit_cost'); ?></th>    
        <th colspan='2' class="text-center" style="border: none; padding: 10px 5px;"><?= $this->lang->line('disc.'); ?></th>  
        <th colspan='2' class="text-center" style="border: none; padding: 10px 5px;"><?= $this->lang->line('vat_amount'); ?></th>
        <th colspan='2' class="text-center" style="border: none; padding: 10px 5px;"><?= $this->lang->line('amount'); ?></th>
      </tr>
  </thead>

  <tbody style="border-bottom: 2px solid #0070C0;">
    <?php
      $i=1;
      $tot_qty=0;
      $tot_tax_amt=0;
      $tot_discount_amt=0;
      $tot_total_cost=0;

      $this->db->select("a.description,c.item_name, a.return_qty, a.price_per_unit, b.tax, b.tax_name, a.tax_amt, a.discount_amt, a.unit_total_cost, a.total_cost, d.unit_name");
      $this->db->where("a.return_id",$return_id);
      $this->db->from("db_purchaseitemsreturn a");
      $this->db->join("db_tax b","b.id=a.tax_id","left");
      $this->db->join("db_items c","c.id=a.item_id","left");
      $this->db->join("db_units d","d.id = c.unit_id","left");
      $q2=$this->db->get();
      
      foreach ($q2->result() as $res2) {
          $discount_amt = (empty($res2->discount_amt)) ? '0' : $res2->discount_amt;
          
          echo "<tr>";  
          echo "<td colspan='1' class='text-center'>".$i++."</td>";
          echo "<td colspan='6'>";
          echo $res2->item_name;
          echo (!empty($res2->description)) ? "<br><i>[".nl2br($res2->description)."]</i>" : '';
          echo "</td>";
          echo "<td colspan='1' class='text-center'>".format_qty($res2->return_qty)."</td>";
          echo "<td colspan='2' class='text-right'>".store_number_format($res2->price_per_unit)."</td>";
          echo "<td colspan='2' class='text-right'>".store_number_format($discount_amt)."</td>";         
          echo "<td colspan='2' class='text-right'>".store_number_format($res2->tax_amt)."</td>";
          echo "<td colspan='2' class='text-right'>".store_number_format($res2->total_cost)."</td>";
          echo "</tr>";  

          $tot_qty += $res2->return_qty;
          $tot_tax_amt += $res2->tax_amt;
          $tot_discount_amt += $res2->discount_amt;
          $tot_total_cost += $res2->total_cost;
      }

      // Standardized values from DB
      $total_vatable = $taxable_amount;
      $vat_amt = $db_vat_amt;
      $vat_no = floatval($pur_vat_rate);
      $total_non_tax = round($grand_total - $total_vatable - $vat_amt, 2);
    ?>

    <tr style="background-color: #eaeff5 !important; border-bottom: 1px solid #0070C0;">
      <td colspan="7" class='text-center text-bold' style="padding: 5px;"><?= $this->lang->line('total'); ?></td>
      <td colspan="1" class='text-bold text-center' style="padding: 5px;"><?=format_qty($tot_qty); ?></td>
      <td colspan="2" class='text-right' style="padding: 5px;"></td>
      <td colspan="2" class='text-right' style="padding: 5px;"><b><?php echo store_number_format($tot_discount_amt); ?></b></td> 
      <td colspan="2" class='text-right' style="padding: 5px;"></td>
      <td colspan="2" class='text-right' style="padding: 5px;"><b><?php echo store_number_format($tot_total_cost); ?></b></td>
    </tr>

    <tr><td colspan='16' style="height:12px;"></td></tr>

    <?php if ($tot_discount_to_all_amt != 0) { ?>
      <tr>
        <td colspan="13" class='text-right'><b><?= $this->lang->line('discount_on_all'); ?></b></td>
        <td colspan="3" class='text-right'><b><?php echo store_number_format($tot_discount_to_all_amt); ?></b></td>
      </tr>
    <?php } ?>
    <tr><td colspan="16" style="height:3px;"></td></tr>
    <?php if ($total_non_tax != 0) { ?>
      <tr>
        <td colspan="13" class='text-right'><b><?= $this->lang->line('total_zero_vat'); ?></b></td>
        <td colspan="3" class='text-right'><b><?php echo store_number_format($total_non_tax); ?></b></td>
      </tr>
    <?php } ?>

    <?php if ($vat_amt != 0) { ?>
    <tr>
      <td colspan="13" class='text-right'><b><?= $this->lang->line('before_amt'); ?></b></td>
      <td colspan="3" class='text-right'><b><?php echo store_number_format($total_vatable); ?></b></td>
    </tr>
    <tr>
      <td colspan="13" class='text-right'><b><?= $this->lang->line('vat_amount') . " " . store_number_format($vat_no) . " % "; ?></b></td>
      <td colspan="3" class='text-right'><b><?php echo store_number_format($vat_amt); ?></b> </td>
    </tr>
    <?php } ?>

    <tr style="background-color: #eaeff5 !important;">
      <td colspan="10" class="text-left" style="padding: 5px;">
    <?php
      if (!function_exists('baht_text')) {
        function baht_text($number, $include_unit = true, $display_zero = true)
        {
          $BAHT_TEXT_NUMBERS = array('ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า');
          $BAHT_TEXT_UNITS = array('', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน');
          $BAHT_TEXT_ONE_IN_TENTH = 'เอ็ด';
          $BAHT_TEXT_TWENTY = 'ยี่';
          $BAHT_TEXT_INTEGER = 'ถ้วน';
          $BAHT_TEXT_BAHT = 'บาท';
          $BAHT_TEXT_SATANG = 'สตางค์';
          $BAHT_TEXT_POINT = 'จุด';

          if (!is_numeric($number)) { return null; }
          $log = floor(log($number, 10));
          if ($log > 5) {
            $millions = floor($log / 6);
            $million_value = pow(1000000, $millions);
            $normalised_million = floor($number / $million_value);
            $rest = $number - ($normalised_million * $million_value);
            $millions_text = '';
            for ($i = 0; $i < $millions; $i++) { $millions_text .= $BAHT_TEXT_UNITS[6]; }
            return baht_text($normalised_million, false) . $millions_text . baht_text($rest, true, false);
          }
          $number_str = (string)floor($number);
          $text = '';
          $unit = 0;
          if ($display_zero && $number_str == '0') {
            $text = $BAHT_TEXT_NUMBERS[0];
          } else for ($i = strlen($number_str) - 1; $i > -1; $i--) {
            $current_number = (int)$number_str[$i];
            $unit_text = '';
            if ($unit == 0 && $i > 0) {
              $previous_number = isset($number_str[$i - 1]) ? (int)$number_str[$i - 1] : 0;
              if ($current_number == 1 && $previous_number > 0) { $unit_text .= $BAHT_TEXT_ONE_IN_TENTH; }
              else if ($current_number > 0) { $unit_text .= $BAHT_TEXT_NUMBERS[$current_number]; }
            } else if ($unit == 1 && $current_number == 2) { $unit_text .= $BAHT_TEXT_TWENTY; }
            else if ($current_number > 0 && ($unit != 1 || $current_number != 1)) { $unit_text .= $BAHT_TEXT_NUMBERS[$current_number]; }
            if ($current_number > 0) { $unit_text .= $BAHT_TEXT_UNITS[$unit]; }
            $text = $unit_text . $text;
            $unit++;
          }
          if ($include_unit) {
            $text .= $BAHT_TEXT_BAHT;
            $satang = explode('.', number_format($number, 2, '.', ''))[1];
            $text .= $satang == 0 ? $BAHT_TEXT_INTEGER : baht_text($satang, false) . $BAHT_TEXT_SATANG;
          } else {
            $exploded = explode('.', $number);
            if (isset($exploded[1])) {
              $text .= $BAHT_TEXT_POINT;
              $decimal = (string)$exploded[1];
              for ($i = 0; $i < strlen($decimal); $i++) { $text .= $BAHT_TEXT_NUMBERS[$decimal[$i]]; }
            }
          }
          return $text;
        }
      }
    ?>
        <span class='amt-in-word'><b><?= $this->lang->line('amount_in_words'); ?> : </b> <i style='font-weight:bold;'><?= baht_text($grand_total); ?></i></span>
      </td>
      <td colspan="2" class='text-right' style="padding: 5px;"><b><?= $this->lang->line('grand_total'); ?></b></td>
      <td colspan="4" class='text-right' style="padding: 5px;"><b><?php echo store_number_format($grand_total); ?></b> </td>
    </tr>
    <tr class="border-blue">
      <td colspan="16">
        <table width="100%" class="style_hidden fixed_table">
          <tr>
            <td colspan="8">
              <br>
              <span><b> <?= $this->lang->line('note'); ?></b></span> :  <br>
              <span style="font-size: 13px;"><b><?php echo $return_note; ?></b></span>
              <br>
            </td>
            <td colspan="8" class="text-left">
              <br>
                <div><b> <?= $this->lang->line('authorised_signatory'); ?></b></div> <br/>
              <br>
              <?php if(!empty($image_file)) { ?>
                <center><img src="<?= base_url($image_file);?>" width='50%' height='auto'></center>
              <?php } ?>
              <br>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </tbody>
</table>
</body>
</html>
