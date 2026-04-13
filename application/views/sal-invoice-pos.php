<!DOCTYPE html>
<html>
<head>
	<title>Invoice POS</title>
<!-- TABLES CSS CODE -->
<?php include"comman/code_css.php"; ?>
<style type="text/css">
	body{
		font-family: arial;
		font-size: 12px;
		font-weight: normal;
		padding-top:7px;
	}

	@media print {
        .no-print { display: none; }
    }

</style>
</head>
<body onload="window.print();">
	<?php
	$CI =& get_instance();
	
    
  	$q3=$this->db->query("SELECT b.coupon_id,b.coupon_amt, b.created_by, b.customer_previous_due,b.customer_total_due,b.store_id,a.customer_name,a.mobile,a.phone,a.gstin,a.tax_number,a.email,a.delete_bit,b.invoice_terms,
                           a.opening_balance,a.country_id,a.state_id,a.city,
                           a.postcode,a.address,b.sales_date,b.created_time,b.reference_no,
                           b.sales_code,b.sales_note,a.sales_due,
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
                           b.payment_status

                           FROM db_customers a,
                           db_sales b 
                           WHERE 
                           a.`id`=b.`customer_id` AND 
                           b.`id`='$sales_id' 
                           ");
                        
    
    $res3=$q3->row();
    $customer_name=$res3->customer_name;
    $customer_mobile=$res3->mobile;
    $customer_phone=$res3->phone;
    $customer_email=$res3->email;
    $customer_country=$res3->country_id;
    $customer_state=$res3->state_id;
    $customer_address=$res3->address;
    $customer_postcode=$res3->postcode;
    $customer_gst_no=$res3->gstin;
    $customer_tax_number=$res3->tax_number;
    $customer_opening_balance=$res3->opening_balance;
    $sales_date=show_date($res3->sales_date);
    $reference_no=$res3->reference_no;
    $created_time=show_time($res3->created_time);
    $sales_code=$res3->sales_code;
    $sales_note=$res3->sales_note;
    $customer_delete_bit=$res3->delete_bit;
   // $invoice_terms=nl2br($res3->invoice_terms);

    $previous_due=$res3->sales_due-($res3->grand_total-$res3->paid_amount);//$res3->customer_previous_due;
    $previous_due = ($previous_due>0) ? $previous_due : 0;
    $total_due=$res3->sales_due;//$res3->customer_total_due;

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
    //$discount_to_all_type = ($discount_to_all_type=='in_percentage') ? '%' : 'Fixed';
    $tot_discount_to_all_amt=$res3->tot_discount_to_all_amt;
    $round_off=$res3->round_off;
    $payment_status=$res3->payment_status;
    
    if($discount_to_all_input>0){
    	$str="($discount_to_all_input%)";
    }else{
    	$str="(Fixed)";
    }


    if(!empty($customer_state)){
      $q6 = $this->db->query("select state from db_states where id='$customer_state'");
      if($q6->num_rows()>0){
      	$customer_state = $q6->row()->state;
      }
    }

    $overall_discounted = $tot_discount_to_all_amt + $coupon_amt;

    $q1=$this->db->query("select * from db_store where id=".$res3->store_id." ");
    $res1=$q1->row();
    $store_name		=$res1->store_name;
    $company_mobile		=$res1->mobile;
    $company_phone		=$res1->phone;
    $company_email		=$res1->email;
    $company_country	=$res1->country;
    $company_state		=$res1->state;
    $company_city		=$res1->city;
    $company_address	=$res1->address;
    $company_postcode	=$res1->postcode;
    $company_gst_no		=$res1->gst_no;//Goods and Service Tax Number (issued by govt.)
    $company_vat_number		=$res1->vat_no;//Goods and Service Tax Number (issued by govt.)
    $store_logo=(!empty($res1->store_logo)) ? $res1->store_logo : store_demo_logo();
    $store_website		=$res1->store_website;
    $mrp_column		=$res1->mrp_column;
    $previous_balance_bit	=$res1->previous_balance_bit;
    $pos_invoice_format_id	=$res1->pos_invoice_format_id;
    $t_and_c_status_pos	=$res1->t_and_c_status_pos;


    ?>
	<table width="95%" align="center">
		<tr>
			<td align="center" >
				<img src="<?= base_url($store_logo);?>" style= "height= " width="90px">
			</td>
		</tr>
		
		
		<tr >
			<td align="center" >
			<br/>
				<span>													 
                <strong style="font-size:16px; "><?= $store_name; ?></strong><br>
		<!--	<?php echo (!empty(trim($company_vat_number)) && vat_number()) ? $this->lang->line(' ')."  ".$company_vat_number."<br>" : '';?>  -->
        	<?php echo (!empty(trim($company_address))) ? $this->lang->line('company_address')."".$company_address." " : '';?> 	
			<?php
  $city_name = '';
  $state_name = '';
  $country_name = '';

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

  // รวมและแสดงผล
  $addressParts = [];

  if (!empty($city_name)) $addressParts[] = $city_name;
  if (!empty($state_name)) $addressParts[] = $state_name;
  if (!empty($country_name)) $addressParts[] = $country_name;

  echo implode(' ', $addressParts); // ต. อ. จ.

  if (!empty($company_postcode)) {
    echo ' ' . $company_postcode;
  }
?> <br/>	         
		      		        
		     <?php echo (!empty(trim($company_gst_no)) && gst_number()) ? $this->lang->line('gst_number').": ".$company_gst_no."<br>" : '';?>
	    
		         
		            <?php if(!empty(trim($company_mobile))) 
		            		{ 
		            			echo $this->lang->line('phone').": ".$company_mobile;
		            			if(!empty($company_mobile)){
		            				echo " , ".$company_phone;
		            			}
		            			echo "<br>";
		            		}
		            //		echo (!empty($company_email)) ? $company_email."," : '';
		            //		echo (!empty($store_website)) ? $store_website."<br>" : '';

		            ?> 
			</span>
			</td>
		</tr>
		<tr><td align="center"><strong>-----------------<?= $this->lang->line('invoice'); ?>-----------------</strong></td></tr>
		<tr>
			<td>
				<table width="100%">
					<tr>
						<td width="%"><?= $this->lang->line('invoice_no'); ?> :  <b><?= $sales_code; ?></b></td>
						<!--<td><b>#<?= $sales_code; ?></b></td>-->
					</tr>
					<tr>
						<!-- <td><?= $this->lang->line('name'); ?> :</td> -->
						<td > <?= $this->lang->line('name'); ?> : <?= $customer_name; ?></td>
					
					</tr>
				

			    	<?php
			    	$full_customer_address = get_full_address_thai($res3->address, $res3->country_id, $res3->state_id, $res3->city, $res3->postcode);
			    	if(!empty($full_customer_address)) {?>
                       <tr class='text-primary'>
					   <td><?= $this->lang->line('address').": ".$full_customer_address; ?></td>
                       </tr>
					            			    
                       <?php } ?> 
				
					<tr>
						<td><?= $this->lang->line('seller'); ?> : <?= ucfirst($res3->created_by) ?></td>
						<!--<td><?= ucfirst($res3->created_by) ?></td>-->
					</tr>
					<tr>
						<td><?= $this->lang->line('date').":".$sales_date; ?></td>
						<td style="text-align: right;"><?= $this->lang->line('time').":".$created_time; ?></td>
					</tr>
				</table>
				
			</td>
		</tr> 
		  <tr>
                    <td style="height:5px;"></td>
                   </tr>
		   <tr>
			 <td>

				<table width="100%" cellpadding="0" cellspacing="0"  >
					<thead>
					<tr style="border-top-style: dashed;border-bottom-style: dashed;border-width: 0.1px;">
						<th style="font-size: 11px; text-align: left;padding-left: 2px; padding-right: 2px;">#</th>
						<th style="font-size: 11px; text-align: left;padding-left: 2px; padding-right: 2px;"><?= $this->lang->line('description'); ?></th>
						
						<th style="font-size: 11px; text-align: center;padding-left: 2px; padding-right: 2px;"><?= $this->lang->line('quantity'); ?></th>
				
						<th style="font-size: 11px; text-align: right;padding-left: 2px; padding-right: 2px;"><?= $this->lang->line('price_pos'); ?></th>
						<th style="font-size: 11px; text-align: right;padding-left: 2px; padding-right: 2px;"><?= $this->lang->line('total'); ?></th>
					</tr>
					</thead>
					<tbody style="border-bottom-style: dashed;border-width: 0.1px;">
						
						<?php
			              $i=0;
			              $tot_qty=0;
			              $subtotal=0;
			              $tax_amt=0;
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
			                  echo "<tr>";  
			                  echo "<td style='padding-left: 2px; padding-right: 2px;' valign='top'>".++$i."</td>";
			                  echo "<td style='padding-left: 2px; padding-right: 2px;'>".$res2->item_name."</td>";
			                  
			                  echo "<td style='text-align: center;padding-left: 2px; padding-right: 2px;'>".format_qty($res2->sales_qty)."</td>";
			                  if($mrp_column){
			                  //	echo "<td style='text-align: right;padding-left: 2px; padding-right: 2px;'>".store_number_format($res2->mrp)."</td>";
			                  }
			                  echo "<td style='text-align: right;padding-left: 2px; padding-right: 2px;'>".store_number_format($res2->total_cost / $res2->sales_qty)."</td>";
			                  echo "<td style='text-align: right;padding-left: 2px; padding-right: 2px;' >".store_number_format($res2->total_cost)."</td>";
			                  echo "</tr>";  
			                  //$tot_qty+=$res2->sales_qty;
			                  $subtotal+=($res2->total_cost);
			                  $tax_amt+=$res2->tax_amt;
			                  $overall_discounted+=$res2->discount_amt;
			              }
			              $before_tax = $subtotal-$tax_amt;



			              ?>
					
				   </tbody>
					<tfoot>
					
					<!-- End -->

					
     			<?php if ($other_charges_amt > 0) { ?>
    			 <tr>
    			 <td style=" padding-left: 2px; padding-right: 2px; " colspan="<?=$mrp_column+4?>" align="right"><?= $this->lang->line('other_charges'); ?></td>
    			<td style=" padding-left: 2px; padding-right: 2px;" align="right"><?= store_number_format($other_charges_amt); ?></td>
    			 </tr>
    			 <?php } ?>
		

	              <?php if(!empty($tot_discount_to_all_amt) && $tot_discount_to_all_amt!=0) {?>
					<tr>
						<td style=" padding-left: 2px; padding-right: 2px;" colspan="<?=$mrp_column+4?>" align="right"><?= $this->lang->line('discount_pos'); ?> <?= ($discount_to_all_type=='in_percentage') ? $discount_to_all_input .'%' : '฿' ;?></td>
						<td style=" padding-left: 2px; padding-right: 2px;" align="right"><?= store_number_format($tot_discount_to_all_amt); ?></td>
					</tr>
				<?php } ?>				
					<tr>

					 <tr><td style="border-bottom-style: dashed;border-width: 0.1px;" colspan="5"></td></tr>   
					
					<tr>
						<td style=" padding-left: 2px; padding-right: 2px; " colspan="<?=$mrp_column+4?>" align="right"><?= $this->lang->line('grand_total'); ?></td>
						<td style=" padding-left: 2px; padding-right: 2px;" align="right"><?= store_number_format($grand_total); ?></td>
					</tr>
					
					
					<!-- change_return_status -->
					<?php if(change_return_status()) {
						$change_return_amount = get_change_return_amount($sales_id); ?>
						<tr>
							<td style=" padding-left: 2px; padding-right: 2px;" colspan="<?=$mrp_column+4?>" align="right"><?= $this->lang->line('paid_amount'); ?></td>
							<td style=" padding-left: 2px; padding-right: 2px;" align="right"><?= store_number_format($paid_amount+$change_return_amount); ?></td>
						</tr>
						<tr>
							<td style=" padding-left: 2px; padding-right: 2px;" colspan="<?=$mrp_column+4?>" align="right"><?= $this->lang->line('refund'); ?></td>
							<td style=" padding-left: 2px; padding-right: 2px;" align="right"><?= store_number_format($change_return_amount); ?></td>
						</tr>		<?php }
					else{ ?>
						<tr>
						<td style=" padding-left: 2px; padding-right: 2px;" colspan="<?=$mrp_column+4?>" align="right"><?= $this->lang->line('paid_amount'); ?></td>
						<td style=" padding-left: 2px; padding-right: 2px;" align="right"><?= store_number_format($paid_amount); ?></td>
					</tr>
					
					<?php } ?>

			   <?php if(!empty($total_due - $previous_due)) {?>
					<tr>
						<td style=" padding-left: 2px; padding-right: 2px;" colspan="<?=$mrp_column+4?>" align="right"><?= $this->lang->line('previous_due'); ?></td>
						<td style=" padding-left: 2px; padding-right: 2px;" align="right"><?= store_number_format($total_due - $previous_due); ?></td>
					</tr>
				<!--   <tr>
						<td style=" padding-left: 2px; padding-right: 2px;" colspan="<?=$mrp_column+4?>" align="right"><?= $this->lang->line('due'); ?></td>
						<td style=" padding-left: 2px; padding-right: 2px;" align="right"><?= store_number_format($total_due - $previous_due); ?></td>
					</tr> -->
					<?php } ?> 	
				
				<tr><td style="border-bottom-style: dashed;border-width: 0.1px;" colspan="5"></td></tr>  

			
				<?php if(!empty($overall_discounted)) {?>
					<tr>
						<td style=" padding-left: 2px; padding-right: 2px; font-size:larger;" colspan="<?=$mrp_column+4?>" align="right"><?= $this->lang->line('bill_discounted_amt'); ?></td>
						<td style=" padding-left: 2px; padding-right: 2px;" align="right"><?= store_number_format($overall_discounted); ?></td> 
					</tr> 
				<?php } ?>
				

				
						<!--	<?php 
                            if(isset($sales_id)){
                              $q4 = $this->db->query("select * from db_salespayments where sales_id=$sales_id");
                              if($q4->num_rows()>0){
                                $i=1;
                                $total_paid = 0;
                                foreach ($q4->result() as $res4) {
                                  echo "<tr>";
                                  echo '<td style="border:1px solid;padding-left:2px;">'.$i++."</td>";
                                  echo '<td style="border:1px solid;padding-left:2px;">';
                                    echo $res4->payment_type;
                                  echo '</td>';
                                  echo '<td style="border:1px solid;text-align: right;padding-right:2px;">'.store_number_format($res4->payment).'</td>';
                                  echo "</tr>";
                                  $total_paid +=$res4->payment;
                                }

                                echo '<tr>';
                                	echo '<td colspan="2" style="border:1px solid;" class="text-center">';
                                			echo $this->lang->line('total');
                                	echo '</td>';
                                	echo '<td  class="text-right">';
                                			echo store_number_format($total_paid);
                                	echo '</td>';
                                echo '</tr>';
                               
                                
                              }
                              else{
                                echo "<tr><td colspan='3' class='text-center text-bold'ยังไม่ชำระ!!</td></tr>";
                              }

                            }
                            else{
                              echo "<tr><td colspan='3' class='text-center text-bold'>กำลังดำเนินการชำระ!!</td></tr>";
                            }
                          ?> -->
							
							</table>
						</td>
					</tr> 
					<br>

                 <tr>
                 <td style="height:10px;"></td>
                 </tr>

					<?php
						if($t_and_c_status_pos){ ?>
							<tr>
								
							</tr>
							<tr style='border:0px solid;'>
						<td colspan="<?=$mrp_column+5?>" align="left">
							<b><u><?= $this->lang->line('note'); ?>:</u></b> <br> 
							<i><?=nl2br(get_invoice_terms_for_pos());?></i> <br>
						</td>
					</tr>
						<?php }
					 ?> 

                     <tr>
                    <td style="height:10px;"></td>
                   </tr>
					<tr><td style="border-bottom-style: dashed;border-width: 0.1px;" colspan="5"></td></tr> 

					<tr >
						<td colspan="<?=$mrp_column+5?>" align="center">----------<?= $this->lang->line('thanks_you_visit_again'); ?>----------</td>
					</tr>

					            <tr>
						         <td colspan="<?=$mrp_column+5?>" align="center">
						         <?php 
								//if the parameter value has slash
								 $sales_code = str_replace('=', '-', str_replace('/', '_', base64_encode($sales_code)));
						            ?>
							     <div style="display:inline-block;vertical-align:middle;line-height:16px !important;">
								
								<?php

							//	echo $CI->print_qr($sales_code);
								?>

							     </div>
						
						          </td>
					            </tr> 

					</tfoot>
				</table>
			</td>
		</tr>
	</table>
	<center >
  <div class="row no-print">
  <div class="col-md-12">
  <div class="col-md-2 col-md-offset-5 col-xs-4 col-xs-offset-4 form-group">
    <button type="button" id="" class="btn btn-block btn-success btn-xs" onclick="window.print();" title="Print">พิมพ์ใบเสร็จ</button>
	<!-- new button -->
	<button type="button" id="" class="btn btn-block btn-danger btn-xs" onclick="self.close();" title="Print">ปิด</button>

   </div>
   </div>
   </div>

</center>
</body>
</html>