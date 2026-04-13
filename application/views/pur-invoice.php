<!DOCTYPE html>
<html>

<head>
  <!-- TABLES CSS CODE -->
  <?php include "comman/code_css.php"; ?>
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

    <?php include "sidebar.php"; ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          <?= $this->lang->line('invoice'); ?>
          <small>Add/Update Invoice</small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="<?php echo $base_url; ?>purchase"><?= $this->lang->line('purchase_list'); ?></a></li>
          <li><a href="<?php echo $base_url; ?>purchase/add"><?= $this->lang->line('new_purchase'); ?></a></li>
          <li class="active"><?= $this->lang->line('invoice'); ?></li>
        </ol>
      </section>
      <div class="row">
        <div class="col-md-12">
          <!-- ********** ALERT MESSAGE START******* -->
          <?php include "comman/code_flashdata.php"; ?>
          <!-- ********** ALERT MESSAGE END******* -->
        </div>
      </div>
      <?php
      $CI = &get_instance();
      $q3 = $this->db->query("SELECT b.store_id,a.supplier_name,a.mobile,a.phone,a.gstin,a.tax_number,a.email,
                           a.opening_balance,a.country_id,a.state_id,a.city,
                           a.postcode,a.address,b.purchase_date,b.reference_no,
                           b.purchase_code,b.purchase_status,b.purchase_note,b.vat,
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
                           db_purchase b 
                           WHERE 
                           a.`id`=b.`supplier_id` AND 
                           b.`id`='$purchase_id' AND b.store_id=" . get_current_store_id());


      $res3 = $q3->row();
      if ($res3->store_id != get_current_store_id()) {
        $CI->show_access_denied_page();
        exit();
      }
      
      $supplier_name = $res3->supplier_name;
      $supplier_mobile = $res3->mobile;
      $supplier_phone = $res3->phone;
      $supplier_email = $res3->email;
      $supplier_state = $res3->state_id;
      $supplier_city = $res3->city;
      $supplier_address = $res3->address;
      $supplier_postcode = $res3->postcode;
      $supplier_gst_no = $res3->gstin;
      $supplier_tax_number = $res3->tax_number;
      $supplier_opening_balance = $res3->opening_balance;
      $purchase_date = $res3->purchase_date;
      $reference_no = $res3->reference_no;
      $purchase_code = $res3->purchase_code;
      $purchase_status = $res3->purchase_status;
      $purchase_note = $res3->purchase_note;
      $supplier_country = get_country($res3->country_id);
      $supplier_state = get_state($res3->state_id);

      $subtotal = $res3->subtotal;
      $grand_total = $res3->grand_total;
      $other_charges_input = $res3->other_charges_input;
      $other_charges_tax_id = $res3->other_charges_tax_id;
      $other_charges_amt = $res3->other_charges_amt;
      $paid_amount = $res3->paid_amount;
      $discount_to_all_input = $res3->discount_to_all_input;
      $discount_to_all_type = $res3->discount_to_all_type;
      $discount_to_all_type = ($discount_to_all_type == 'in_percentage') ? '%' : 'Fixed';
      $tot_discount_to_all_amt = $res3->tot_discount_to_all_amt;
      $round_off = $res3->round_off;
      $payment_status = $res3->payment_status;
      $db_taxable_amount = $res3->taxable_amount;
      $db_vat_amt = $res3->vat_amt;
      $pur_vat_rate = $res3->vat;


      $q1 = $this->db->query("select * from db_store where id=" . $res3->store_id . " ");
      $res1 = $q1->row();
      $store_name = $res1->store_name;
      $company_mobile = $res1->mobile;
      $company_phone = $res1->phone;
      $company_email = $res1->email;

      $company_city = $res1->city;
      $company_address = $res1->address;
      $company_gst_no = $res1->gst_no;
      $company_vat_no = $res1->vat_no;
      $company_pan_no = $res1->pan_no;
      $company_postcode = $res1->postcode;
      $company_country = $res1->country;
      $company_state = $res1->state;



      //  ราตารวม vat 0% จะเก็บใน $tot_price_zero_vat
      $tot_price_zero_vat  = 0.0;
      $this->db->select(" a.description,c.item_name, a.purchase_qty,a.tax_type,
                          a.price_per_unit, b.tax,b.tax_name,a.tax_amt,
                          a.discount_input,a.discount_amt, a.unit_total_cost,
                          a.total_cost , d.unit_name,c.sku,c.hsn
                      ");
      $this->db->where("a.purchase_id", $purchase_id);
      $this->db->from("db_purchaseitems a");
      $this->db->join("db_tax b", "b.id=a.tax_id", "left");
      $this->db->join("db_items c", "c.id=a.item_id", "left");
      $this->db->join("db_units d", "d.id = c.unit_id", "left");
      $q4 = $this->db->get();


      


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
                <i class="fa fa-globe"></i> <?= $this->lang->line('purchase_invoice'); ?>
                <small class="pull-right">วันที่ : <?= show_date($purchase_date); ?></small>
              </h2>
            </div>
            <!-- /.col -->
          </div>
          <!-- info row -->
          <div class="row invoice-info">
            <div class="col-sm-4 invoice-col">
              <i><?= $this->lang->line('from'); ?></i>
              <address>
                <strong><?php echo  $store_name; ?></strong> <br />
                <?php echo  $company_address; ?>
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

                <?php echo (!empty(trim($company_gst_no)) && gst_number()) ? $this->lang->line('tax_id') . ": " . $company_gst_no . " , " : ''; ?>
                <?php echo (!empty(trim($company_pan_no)) && vat_number()) ? $this->lang->line('') . " ( " . $company_pan_no . " )" : ''; ?><br />
                <?php echo (!empty(trim($company_mobile))) ? $this->lang->line('phone') . ": " . $company_mobile . " ," : ''; ?>
                <?php echo (!empty(trim($company_phone))) ? $this->lang->line('mobile') . ": " . $company_phone . "<br>" : ''; ?>
                <?php echo (!empty(trim($company_email))) ? $this->lang->line('email') . ": " . $company_email . "<br>" : ''; ?>

              </address>
            </div>

            <!-- /.col -->
            <div class="col-sm-4 invoice-col">
              <i><?= $this->lang->line('supplier_details'); ?><br></i>
              <address>
                <strong><?php echo  $supplier_name; ?></strong><br>
                <?php
                if (!empty($supplier_address)) {
                  echo $supplier_address;
                }

                if (!empty($supplier_postcode)) {
                  echo "-" . $supplier_postcode;
                }
                ?>
                <br>
                <?php echo (!empty(trim($supplier_mobile))) ? $this->lang->line('mobile') . ": " . $supplier_mobile . " ," : ''; ?>
                <?php echo (!empty(trim($supplier_phone))) ? $this->lang->line('phone') . ": " . $supplier_phone . "<br>" : ''; ?>
                <?php echo (!empty(trim($supplier_email))) ? $this->lang->line('email') . ": " . $supplier_email . "<br>" : ''; ?>
                <?php echo (!empty(trim($supplier_gst_no)) && gst_number()) ? $this->lang->line('tax_id') . ": " . $supplier_gst_no . "<br>" : ''; ?>
                <!--  <?php echo (!empty(trim($supplier_tax_number))) ? $this->lang->line('tax_number') . ": " . $supplier_tax_number . "<br>" : ''; ?> -->

              </address>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 invoice-col" style="font-size:large; color:blueviolet";>
              <b><?= $this->lang->line('invoice'); ?>  # <?php echo  $purchase_code; ?></b><br>
              <b><?= $this->lang->line('purchase_status'); ?>  : <?php echo  $purchase_status; ?></b><br>
              <br>
              <b><?= $this->lang->line('approver'); ?>  : <?php echo  $reference_no; ?></b>
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->

          <!-- Table row -->
          <div class="row">
            <div class="col-xs-12 table-responsive">
              <table class="table  records_table table-bordered">
                <thead class="bg-success">
                  <tr>
                    <th>#</th>
                    <th class="text-center"><?= $this->lang->line('item_name'); ?></th>
                    <th class="text-center"><?= $this->lang->line('purchase_price'); ?></th>
                    <th class="text-center"><?= $this->lang->line('quantity'); ?></th>

                    <!-- <th  class="text-center"><?= $this->lang->line('discount'); ?></th> -->
                    <th class="text-center"><?= $this->lang->line('discount_amount'); ?></th>
                    <th class="text-center"><?= $this->lang->line('tax'); ?></th>
                    <th  class="text-center"><?= $this->lang->line('tax_amount'); ?></th> 
                    <th class="text-center"><?= $this->lang->line('unit_cost'); ?></th>
                    <th class="text-center"><?= $this->lang->line('total_amount'); ?></th>
                  </tr>
                </thead>
                <tbody>

                  <?php
                  $i = 0;
                  $tot_qty = 0;
                  $tot_purchase_price = 0;
                  $tot_tax_amt = 0;
                  $tot_discount_amt = 0;
                  $tot_unit_total_cost = 0;
                  $tot_total_cost = 0;
                  $q2 = $this->db->query("SELECT a.description,c.item_name, a.purchase_qty,a.tax_type,
                                  a.price_per_unit, b.tax,b.tax_name,a.tax_amt,
                                  a.discount_input,a.discount_amt, a.unit_total_cost,
                                  a.total_cost 
                                  FROM 
                                  db_purchaseitems AS a,db_tax AS b,db_items AS c 
                                  WHERE 
                                  c.id=a.item_id AND b.id=a.tax_id AND a.purchase_id='$purchase_id'");

                  foreach ($q2->result() as $res2) {
                    $str = ($res2->tax_type == 'Inclusive') ? 'Inc.' : 'Exc.';
                    $discount = (empty($res2->discount_input) || $res2->discount_input == 0) ? '0' : store_number_format($res2->discount_input) . "%";
                    $discount_amt = (empty($res2->discount_amt) || $res2->discount_input == 0) ? '0' : $res2->discount_amt . "";
                    echo "<tr>";
                    echo "<td>" . ++$i . "</td>";
                    echo "<td>";
                    echo $res2->item_name;
                    echo (!empty($res2->description)) ? "<br><i>[" . nl2br($res2->description) . "]</i>" : '';
                    echo "</td>";
                    echo "<td class='text-right'>" . $CI->currency($res2->price_per_unit) . "</td>";
                    echo "<td class='text-right'>" . format_qty($res2->purchase_qty) . "</td>";

                    //  echo "<td class='text-right'>".$discount."</td>";
                    echo "<td class='text-right'>" . $CI->currency($discount_amt) . "</td>";
                   // echo "<td  class='text-center'>" .$res2->tax_name."[".$str."]</td>";
                   echo "<td class='text-right'>".store_number_format($res2->tax)."% " . "</td>"; 
                    echo "<td class='text-right'>".$CI->currency($res2->tax_amt)."</td>";
                    echo "<td class='text-right'>" . $CI->currency($res2->unit_total_cost) . "</td>";
                    echo "<td class='text-right'>" . $CI->currency($res2->total_cost) . "</td>";
                    echo "</tr>";


                    $tot_qty += $res2->purchase_qty;
                    $tot_purchase_price += $res2->price_per_unit;
                    $tot_tax_amt += $res2->tax_amt;
                    $tot_discount_amt += $res2->discount_amt;
                    $tot_unit_total_cost +=$res2->unit_total_cost;
                    $tot_total_cost += $res2->total_cost;


                    if ($res2->tax == 0){
                      $tot_price_zero_vat += $res2->total_cost;
                    }
                  } // foreach end
                  
                  // Standardized values from DB
                  $total_vatable = $db_taxable_amount;
                  $vat_amt = $db_vat_amt;
                  $vat_no = floatval($pur_vat_rate);
                  $total_non_tax = round($grand_total - $total_vatable - $vat_amt, 2);
                  
                  ?>

                 

                </tbody>
                <tfoot class="text-right text-bold bg-gray">
                  <tr>
                    <td colspan="3" class="text-center"><?= $this->lang->line('total'); ?></td>
                    <td class=" "><?= format_qty($tot_qty); ?></td>
                    <td><?= $CI->currency($tot_discount_amt); ?></td>
                    <td>-</td>

                    <td></td>
                    <td>-</td>
                    <td><?= $CI->currency($tot_total_cost); ?></td>
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
                      <label class="control-label  " style="font-size: 17px;">: <?= $discount_to_all_input; ?> (<?= $discount_to_all_type ?>)</label>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label for="purchase_note" class="col-sm-4 control-label" style="font-size: 17px;"><?= $this->lang->line('invoiceTerms'); ?></label>
                    <div class="col-sm-8">
                      <label class="control-label  " style="font-size: 17px;">: <?= $purchase_note; ?></label>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <table class="table table-hover table-bordered" style="width:100%" id="">
                      <h4 class="box-title text-info"><?= $this->lang->line('payments_information'); ?> : </h4>
                      <thead>
                        <tr class="bg-purple ">
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
                        if (isset($purchase_id)) {
                          $q3 = $this->db->query("select * from db_purchasepayments where purchase_id=$purchase_id");
                          if ($q3->num_rows() > 0) {
                            $i = 1;
                            $total_paid = 0;
                            foreach ($q3->result() as $res3) {
                              echo "<tr class='text-center text-bold' id='payment_row_" . $res3->id . "'>";
                              echo "<td>" . $i++ . "</td>";
                              echo "<td>" . show_date($res3->payment_date) . "</td>";
                              echo "<td>" . $res3->payment_type . "</td>";
                              echo "<td>" . get_account_name($res3->account_id) . "</td>";
                              echo "<td>" . $res3->payment_note . "</td>";
                              echo "<td class='text-right'>" . $CI->currency($res3->payment) . "</td>";
                              echo "</tr>";
                              $total_paid += $res3->payment;
                            }
                            echo "<tr class='text-right text-bold'><td colspan='5' >รวมเงิน</td><td>" . $CI->currency($total_paid) . "</td></tr>";
                          } else {
                            echo "<tr><td colspan='6' class='text-center text-bold'>No Previous Payments Found!!</td></tr>";
                          }
                        } else {
                          echo "<tr><td colspan='6' class='text-center text-bold'>Payments Pending!!</td></tr>";
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
                    <table class="col-md-11">
                       <tr >
                          <th class="text-right" style="font-size: 17px;"><b><?= $this->lang->line('subtotal'); ?></b></th>
                          <th class="text-right" style="padding-left:10%;font-size: 17px;">
                            <h4><b><?= store_number_format( $subtotal); ?></b> </h4>
                          </th>
                       </tr> 

                        <?php if(!empty($tot_discount_to_all_amt !=0)) {?>
                       <tr>
                          <th class="text-right" style="font-size: 17px;"><?= $this->lang->line('discount_on_all'); ?></th>
                          <th class="text-right" style="padding-left:10%;font-size: 17px;">
                             <h4><b><?= store_number_format($tot_discount_to_all_amt);?></b></h4>
                          </th>
                       </tr>
                        <?php } ?> 
                       
                       <?php if(!empty($total_non_tax !=0)) {?>
                       <tr >
                          <th class="text-right" style="font-size: 17px;"><?= $this->lang->line('total_zero_vat'); ?></th>
                          <th class="text-right" style="padding-left:10%;font-size: 11px;">
                             <h4><b><?php echo store_number_format($total_non_tax);?></b> </h4>
                          </th>
                       </tr>
                       <?php } ?> 
                    
                        <?php if(!empty($vat_amt !=0)) {?>
                       <tr >
                          <th class="text-right" style="font-size: 17px;"><b><?= $this->lang->line('before_amt') ; ?></b></th>
                            <th class="text-right" style="padding-left:10%;font-size: 17px;">
                            <h4><b><?= store_number_format($total_vatable);?></b>  </h4>
                          </th>
                        </tr>

                       <tr >
                        <th class="text-right" style="font-size: 17px;"><b><?= $this->lang->line('vat_amount') . store_number_format( $vat_no ) . " % "; ?></b></th>
                        <th class="text-right" style="padding-left:10%;font-size: 17px;">
                          <h4><b><?= store_number_format( $vat_amt); ?></b></h4>
                        </th>
                       </tr>
                       <?php } ?> 

                       <tr >
                        <th class="text-right" style="font-size: 18px;"><?= $this->lang->line('grand_total'); ?></th>
                        <th class="text-right" style="padding-left:10%;font-size: 18px;">
                          <h3><b><?= store_number_format($grand_total); ?></b></h3>
                        </th>
                       </tr>
                    </table>
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
        <div class="row no-print">
          <div class="col-xs-6">
            <?php if ($CI->permissions('sales_edit')) { ?>
              <a href="<?php echo $base_url; ?>purchase/update/<?php echo  $purchase_id ?>" class="btn btn-warning" style="margin-bottom:5px; margin-right: 15px; margin-bottom:10px;">
                <i class="fa  fa-edit"></i> แก้ไข
              </a>
            <?php } ?>

            <?php if ($CI->permissions('purchase_return_add')) { ?>
              <a href="<?php echo $base_url; ?>purchase_return/add/<?php echo  $purchase_id ?>" class="btn btn-danger" style="margin-bottom:5px; margin-right: 15px; margin-bottom:10px;">
                <i class="fa  fa-undo"></i> คืนสินค้า
              </a>
            <?php } ?>

            <a href='<?= base_url('items/labels/' . $purchase_id); ?>' class="btn btn-info" title='Pop Up' style="margin-bottom:5px; margin-right: 15px; margin-bottom:10px;"><i class="fa fa-barcode"></i> Barcode</a>

             <a onclick="printTemplate('<?php echo $base_url; ?>purchase/print_invoice/<?php echo $purchase_id ?>')" target="_blank" class="btn btn-primary" style="margin-bottom:5px; margin-right: 15px; margin-bottom:10px;">
        
              <i class="fa fa-print"></i>
              พิมพ์ใบสั่งซื้อ
            </a> 

             <a href="<?php echo $base_url; ?>purchase/pdf/<?php echo  $purchase_id ?>" target="_blank" class="btn btn-success" style="margin-bottom:5px; margin-right: 15px; margin-bottom:10px;">
              <i class="fa fa-file-pdf-o"></i> 
            ใบสั่งซื้อPDF
           </a>

            

          </div>
         <div class="col-xs-6 text-right">

           
          </div>
        </div>

      </section>
      <!-- /.content -->
      <div class="clearfix"></div>
    </div>
    <!-- /.content-wrapper -->
    <?php include "footer.php"; ?>


    <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
    <div class="control-sidebar-bg"></div>
  </div>
  <!-- ./wrapper -->

  <!-- SOUND CODE -->
  <?php include "comman/code_js_sound.php"; ?>
  <!-- TABLES CODE -->
  <?php include "comman/code_js.php"; ?>

  <!-- Make sidebar menu hughlighter/selector -->
  <script>
    $(".purchase-list-active-li").addClass("active");
  </script>
</body>

</html>