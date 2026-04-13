<?php $CI =& get_instance(); ?>
<input type="hidden" id="base_url" value="<?php echo base_url(); ?>">
<!DOCTYPE html>
<html>
<?php if(!isset($sumcount)){ $sumcount = array('tot_subs'=>0,'tot_active'=>0,'tot_expired'=>0,'tot_free'=>0); } ?>
<head>
<!-- FORM CSS CODE -->
  <?php include"comman/code_css.php"; ?>
  <!-- Custom Style for ETAX Box -->
  <style>
    .bg-etax {
      background-image: linear-gradient(to right, #11998e 0%, #38ef7d 51%, #11998e 100%) !important;
      text-transform: uppercase;
      transition: 0.5s;
      background-size: 200% auto;
      color: white !important;
      box-shadow: 0 0 20px #ccc;
      border-radius: 10px;
      display: block;
    }
    .bg-etax:hover {
      background-position: right center;
    }

    /* Keep dashboard tables visually consistent and full-width. */
    .dashboard-table-wrap {
      width: 100%;
      overflow-x: auto;
    }
    .dashboard-uniform-table {
      width: 100% !important;
      table-layout: auto;
    }
    .dashboard-uniform-table thead th,
    .dashboard-uniform-table tbody td,
    .dashboard-uniform-table tfoot th {
      vertical-align: middle;
      white-space: normal;
      word-break: break-word;
      font-size: 14px;
    }
    .dashboard-uniform-table thead th {
      font-weight: 600;
    }
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
      font-size: 12px;
    }

    /* Keep dashboard table sections exactly the same visual width. */
    .dashboard-table-section {
      width: 100%;
      margin: 25px auto 0 auto !important;
    }

    /* Ensure dashboard datatables render full section width. */
    #sales_table_dashboard,
    #sales_table_dashboard_wrapper,
    #sales_table_dashboard_wrapper table,
    #purchase_table_dashboard,
    #purchase_table_dashboard_wrapper,
    #purchase_table_dashboard_wrapper table,
    #expense_table_dashboard,
    #expense_table_dashboard_wrapper,
    #expense_table_dashboard_wrapper table,
    #example2,
    #example2_wrapper,
    #example2_wrapper table {
      width: 100% !important;
    }

  </style>
<!-- </copy> -->  

</head>
<body class="hold-transition skin-blue sidebar-mini  ">

<div class="wrapper">
  
  
  <?php include"sidebar.php"; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <?=$page_title;?>
      </h1>     
    </section>
    <div class="row">
    <div class="col-md-12">
      <!-- ********** ALERT MESSAGE START******* -->
     <?php //include"comman/code_flashdata.php"; ?> 
       <!-- ********** ALERT MESSAGE END******* -->
     </div>
     </div>
      
    <!-- Main content -->
    <section class="content">

      <div class="row">
        <div class="col-md-12">
           <div class="col-md-3 pull-right">
            <?= form_open('dashboard', array('class' => '', 'id' => 'dashboard_form', 'method' => 'post')); ?>
              <!-- Store Code -->
              <?php 
                if(is_admin()){ ?>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-university"></i></span>
                    <select class="form-control select2" id="store_id" name="store_id" style="width: 100%;">
                      <?=get_store_select_list('', true);?>
                    </select>
                  </div>
                <?php } else {
                  echo "<input type='hidden' name='store_id' id='store_id' value='".get_current_store_id()."'>";
                }
              ?>
              <!-- Store Code end -->
              <?= form_close();?>
           </div>
        </div>
      </div>
      <?php if(is_admin()){ ?>
        <div class="row">
          <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
              <span class="info-box-icon bg-aqua"><i class="fa fa-university"></i></span>
              <div class="info-box-content">
                <span class="info-box-text" style="font-size: 14px;">TOTAL SUBSCRIPTIONS</span>
                <span class="info-box-number" style="font-size: 32px;"><?= (isset($sumcount)) ? $sumcount['tot_subs'] : '0'; ?></span>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
              <span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
              <div class="info-box-content">
                <span class="info-box-text" style="font-size: 14px;">ACTIVE STORES</span>
                <span class="info-box-number" style="font-size: 32px;"><?= (isset($sumcount)) ? $sumcount['tot_active'] : '0'; ?></span>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
              <span class="info-box-icon bg-yellow"><i class="fa fa-clock-o"></i></span>
              <div class="info-box-content">
                <span class="info-box-text" style="font-size: 14px;">EXPIRED STORES</span>
                <span class="info-box-number" style="font-size: 32px;"><?= (isset($sumcount)) ? $sumcount['tot_expired'] : '0'; ?></span>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
              <span class="info-box-icon bg-red"><i class="fa fa-gift"></i></span>
              <div class="info-box-content">
                <span class="info-box-text" style="font-size: 14px;">FREE STORES</span>
                <span class="info-box-number" style="font-size: 32px;"><?= (isset($sumcount)) ? $sumcount['tot_free'] : '0'; ?></span>
              </div>
            </div>
          </div>
        </div>
      <?php } else if($CI->permissions('dashboard_info_box_1')){ ?> 
        <div class="row">
            <div class="box-header">      
              <div class="btn-group pull-right">
                <button type="button" title="Today" class="btn btn-default btn-info get_tab_records active">Today</button>
                <button type="button" title="Current Week" class="btn btn-default btn-info get_tab_records">Weekly</button>
                <button type="button" title="Current Month" class="btn btn-default btn-info get_tab_records ">Monthly</button>
                <button type="button" title="Current Year" class="btn btn-default btn-info get_tab_records">Yearly</button>
                <button type="button" title="All Years" class="btn btn-default btn-info get_tab_records ">All</button>
              </div> 
             </div><br>


         <div class="col-md-3 col-sm-6 col-xs-6">
              <div class="small-box bg-1">
                <div class="inner">
                <p style="font-size: larger;"><?= $this->lang->line('total_purchases_active'); ?></p>
                <h6 class="tot_pur" style="font-size:x-large;"><?= $CI->currency (0); ?></h6>                
                </div>
         
              </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-6">
              <div class="small-box bg-1">
                <div class="inner">
                <p style="font-size: larger;"><?= $this->lang->line('total_purchases'); ?></p>
                  <h6 class="tot_pur_grand_total" style="font-size:x-large;"><?= $CI->currency(0); ?></h6>
                
                </div>
          
              </div>
            </div>

             <div class="col-md-3 col-sm-6 col-xs-6">
              <div class="small-box bg-2">
                <div class="inner">
                  <p style="font-size: larger;"><?= $this->lang->line('total_purchase_due'); ?></p>
                  <h6 class="purchase_due" style="font-size:x-large;"><?= $CI->currency(0); ?></h6>
                 
                </div>
            
              </div>
            </div> 

            <div class="col-md-3 col-sm-6 col-xs-6">
              <div class="small-box bg-2">
                <div class="inner">
                  <p style="font-size: larger;"><?= $this->lang->line('total_expense'); ?></p>
               <h6 class="tot_exp" style="font-size:x-large;"><?= $CI->currency(0); ?></h6>
              
                </div>
          
              </div>
            </div>
       
            <div class="col-md-3 col-sm-6 col-xs-6">
              <div class="small-box bg-3">
                <div class="inner">
                <p style="font-size: larger;"><?= $this->lang->line('total_sales_active'); ?></p>
                  <h6 class="tot_sal" style="font-size:x-large;"><?= $CI->currency(0); ?></h6>               
                </div>
              </div>
            </div>

             <div class="col-md-3 col-sm-6 col-xs-6">
              <div class="small-box bg-3">
                <div class="inner">
                  <p style="font-size: larger;"><?= $this->lang->line('total_sales'); ?></p>
                   <h6 class="tot_sal_grand_total" style="font-size:x-large;"><?= $CI->currency (0); ?></h6>                                         
                </div>
              </div>
            </div> 

            <div class="col-md-3 col-sm-6 col-xs-6">
              <div class="small-box bg-4">
                <div class="inner">
                  <p style="font-size: larger;"><?= $this->lang->line('item_sales_due'); ?></p>
                  <h6 class="sales_due" style="font-size:x-large;"><?= $CI->currency(0); ?></h6>             
                </div>
              </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-6">
              <div class="small-box bg-4">
                <div class="inner">
                 <p style="font-size: larger;"><?= $this->lang->line('sales_payment'); ?></p>
               <h6 class="sales_pay" style="font-size:x-large;"><?= $CI->currency(0); ?></h6>                            
                </div>    
              </div>
            </div>

            <!-- ETAX Quota Box -->
            <div class="col-md-3 col-sm-6 col-xs-6">
              <div class="small-box bg-etax">
                <div class="inner">
                  <p style="font-size: larger;">e-TAX by Time Stamp</p>
                  <h6 class="etax_usage_display" style="font-size:x-large;">0 / 0</h6>
                  <div class="progress progress-xs" style="margin-top: 10px; background: rgba(0,0,0,0.1);">
                    <div class="progress-bar progress-bar-white etax_progress" style="width: 0%"></div>
                  </div>
                </div>
                <div class="icon">
                  <i class="fa fa-envelope"></i>
                </div>
              </div>
            </div>
             
          </div>
      <?php } ?>
            <?php // Sales List Relocated downwards ?>



       
      <!-- ############################# GRAPHS ############################## -->
      <?php if(is_admin() && store_module()){ ?>
      <div class="row">
        <div class="col-md-12 animated">
          <div class="box box-primary" style="margin-top:25px;">

            <div class="box-header ">
              <h3 class="box-title"><b>New Subscriptions</b></h3>
              <div class="btn-group pull-right hide">
                <button type="button" title="Today" class="btn btn-default btn-info get_storewise_details ">Today</button>
                <button type="button" title="Current Week" class="btn btn-default btn-info get_storewise_details">Weekly</button>
                <button type="button" title="Current Month" class="btn btn-default btn-info get_storewise_details ">Monthly</button>
                <button type="button" title="Current Year" class="btn btn-default btn-info get_storewise_details">Yearly</button>
                <button type="button" title="All Years" class="btn btn-default btn-info get_storewise_details active">All</button>
              </div>
            </div>

            <!-- /.box-header -->
            <div class="box-body table-responsive dashboard-table-wrap">
              <table id="stores_details" class="table dashboard-uniform-table">
                <thead>
                <tr class=''>
                  <th>#</th>
                  <th><?= $this->lang->line('store_name'); ?></th>
                  <th>Date / Time</th>
                  <th>Package</th>
                   <th>Status</th>
                   <th>Action</th>
                 </tr>
                </thead>
                <tbody>
                  <?= $CI->get_storewise_details(); ?>
                </tbody>
                
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->

        </div>
        <!-- /.col (RIGHT) -->

        </div>
        
        <!-- Support Status Table -->
        <div class="row">
           <div class="col-md-12">
              <div class="box box-info">
                 <div class="box-header with-border">
                    <h3 class="box-title"><b>Customer Support / Contact Status</b></h3>
                    <div class="box-tools pull-right">
                        <select id="store_status_filter" class="form-control" style="width: 200px;" onchange="filter_store_status(this.value)">
                            <option value="all">Show All</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Expired">Expired</option>
                            <option value="Pending Payment">Pending Payment</option>
                            <option value="New Store">New Store</option>
                        </select>
                    </div>
                 </div>
                 <div class="box-body table-responsive dashboard-table-wrap">
                    <table id="support_table" class="table table-bordered table-striped dashboard-uniform-table">
                       <thead>
                          <tr>
                             <th>Date</th>
                             <th>Store Name</th>
                             <th>Mobile</th>
                             <th>Email</th>
                             <th>Created Date</th>
                             <th>Expire Date</th>
                             <th>Status</th>
                             <th>Support Status</th>
                             <th>Note</th>
                             <th>Action</th>
                          </tr>
                       </thead>
                       <tbody>
                          <?php if(!empty($support_list)): ?>
                             <?php foreach($support_list as $store): 
                                // Determine Status
                                $display_status = '';
                                $label_class = 'label-default';
                                $row_status = 'Active';

                                $is_inactive = ($store->status == 0);
                                $is_expired = (!empty($store->expire_date) && $store->expire_date < date('Y-m-d'));
                                $is_pending = ($store->payment_status == 'Pending');
                                $is_new = (strtotime($store->created_date) > strtotime('-7 days'));
                                $is_upgraded = (isset($store->trial_days) && $store->trial_days == 0);

                                 if($is_expired){
                                    $display_status = 'Pending';
                                    $row_status = 'Expired';
                                    $label_class = 'label-warning';
                                }
                                elseif($is_inactive){
                                    $display_status = 'Inactive';
                                    $row_status = 'Inactive';
                                    $label_class = 'label-danger';
                                }
                                elseif($is_pending){
                                    $display_status = 'Pending Payment';
                                    $row_status = 'Pending Payment';
                                    $label_class = 'label-warning';
                                }
                                elseif($is_upgraded){
                                    $display_status = 'RenewPlan';
                                    $row_status = 'Active';
                                    $label_class = 'label-success';
                                }
                                elseif($is_new){
                                    $display_status = 'New Store';
                                    $row_status = 'New Store';
                                    $label_class = 'label-info';
                                }
                                else{
                                    $display_status = 'Active';
                                    $row_status = 'Active';
                                    $label_class = 'label-success';
                                }
                             ?>
                             <tr class="store-row" data-status="<?= $row_status ?>">
                                <td><?= show_date($store->created_date) ?></td>
                                <td><?= $store->store_name ?></td>
                                <td><?= $store->mobile ?></td>
                                <td><?= $store->email ?></td>
                                <td><?= show_date($store->created_date) ?></td>
                                <td data-order="<?= $store->expire_status_weight ?? 3 ?>">
                                    <?php
                                        $expire_label_class = 'label-success';
                                        if (!empty($store->expire_date)) {
                                            $day_diff = date_difference(date('Y-m-d'), $store->expire_date);
                                            
                                            if ($day_diff < 0) {
                                                $expire_label_class = 'label-danger'; // Expired
                                            } elseif ($day_diff <= 10) {
                                                $expire_label_class = 'label-warning'; // Expiring soon
                                            } else {
                                                $expire_label_class = 'label-success'; // Active
                                            }
                                            echo '<span class="label ' . $expire_label_class . '">' . show_date($store->expire_date) . '</span>';
                                        } else {
                                            echo '-';
                                        }
                                    ?>
                                </td>
                                <td><span class="label <?= $label_class ?>"><?= $display_status ?></span></td>
                                <td>
                                   <select class="form-control input-sm support-status" data-id="<?= $store->id ?>" style="width: 150px;">
                                      <option value="Pending" <?= ($store->support_status == 'Pending') ? 'selected' : '' ?>>Pending</option>
                                      <option value="Contacted" <?= ($store->support_status == 'Contacted') ? 'selected' : '' ?>>Contacted</option>
                                      <option value="Follow Up" <?= ($store->support_status == 'Follow Up') ? 'selected' : '' ?>>Follow Up</option>
                                      <option value="Resolved" <?= ($store->support_status == 'Resolved') ? 'selected' : '' ?>>Resolved</option>
                                      <option value="Support Needed" <?= ($store->support_status == 'Support Needed') ? 'selected' : '' ?>>Support Needed</option>
                                   </select>
                                </td>
                                <td>
                                   <input type="text" class="form-control input-sm support-note" style="width:100%" data-id="<?= $store->id ?>" value="<?= $store->support_note ?>" placeholder="Enter note...">
                                </td>
                                <td>
                                    <button class="btn btn-xs btn-success save-support" data-id="<?= $store->id ?>" title="Save Status & Note"><i class="fa fa-save"></i></button>
                                    <?php if(!empty($store->sales_id)){ ?>
                                       <a href="<?= base_url("sales/invoice/$store->sales_id") ?>" target="_blank" class="btn btn-xs btn-info" title="View Invoice"><i class="fa fa-file-text-o"></i></a>
                                    <?php } ?>
                                    <a href="javascript:void(0);" onclick="delete_store('<?= $store->id ?>');" class="btn btn-xs btn-danger" title="Delete Store"><i class="fa fa-trash"></i></a>
                                </td>
                             </tr>
                             <?php endforeach; ?>
                          <?php endif; ?>
                       </tbody>
                    </table>
                 </div>
              </div>
           </div>
        </div>
        <!-- END NEW TABLES -->

        <?php } ?>
        


      
     <div class="box-body table-responsive">    
      <?php if(!is_user() && $CI->permissions('dashboard_pur_sal_chart')){ ?> 
        
       <div class="col-md-8 animated">
      <!-- BAR CHART -->
          <div class="box box-primary" style="margin-top:25px;">
             <div class="box-header with-border">
              <h3 class="box-title"><?= $this->lang->line('purchase_sales_and_expense_bar_chart'); ?></h3>

              
             </div>
             <div class="box-body">
              <div class="chart">
                <canvas class="bar-chartcanvas" style="height: 300px;"></canvas>
              </div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
       
        <?php if($CI->permissions('dashboard_trending_items_chart')){ ?> 
          <div class="col-md-4 ">
          
             <!-- PRODUCT LIST -->
             <div class="box box-primary" style="margin-top:25px;">
                <div class="box-header with-border">
              <h3 class="box-title text-uppercase"><?= $this->lang->line('top_10_trending_items'); ?></h3>
            </div>
            <div class="box-body" style="position: relative; height: 300px;">
                <!-- /.box-header -->
                <canvas id="doughnut-chart"></canvas>
                <!-- /.box-body -->
              </div>
             </div>
             <!-- /.box -->
             
        </div>
        <?php } ?>
      
        <?php } ?>
        <!-- /.col -->
        
        <!-- /.col -->
     </div>
 
 
      <div class="row">

        <div class="col-md-12" id="sales_list_section">
          <div class="nav-tabs-custom dashboard-table-section" style="margin-top:25px;">
            <ul class="nav nav-tabs">
              <?php if(is_admin()){ ?>
                <li class="active"><a href="#store_list_tab" data-toggle="tab"><?= $this->lang->line('store_list'); ?></a></li>
              <?php } else { ?>
                <li class="active"><a href="#sales_list_tab" data-toggle="tab"><?= $this->lang->line('sales_list'); ?></a></li>
              <?php } ?>
            </ul>
            <div class="tab-content">
              <?php if(is_admin()){ ?>
                <div class="tab-pane active" id="store_list_tab">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="box-body table-responsive dashboard-table-wrap">
                        <table id="example2-store-table" class="table table-bordered custom_hover dashboard-uniform-table" width="100%">
                          <thead>
                            <tr class="bg-success">
                              <th class="text-center" width="5%"><input type="checkbox" class="group-checkable"></th>
                              <th width="10%">Store Code</th>
                              <th width="15%">Store Name</th>
                              <th width="10%">Mobile</th>
                              <th width="15%">Address</th>
                              <th width="10%">Created Date</th>
                              <th width="10%">Created By</th>
                              <th width="10%">Package Name</th>
                              <th width="10%">Expire Date</th>
                              <th width="5%">Status</th>
                              <th width="10%">Action</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php if(!empty($all_stores_list_php)): ?>
                               <?php foreach($all_stores_list_php as $store): ?>
                               <tr>
                                  <td class="text-center">
                                     <input type="checkbox" name="checkbox[]" value="<?= $store->id ?>" class="column_checkbox checkbox">
                                  </td>
                                  <td><?= $store->store_code ?></td>
                                  <td><?= $store->store_name ?></td>
                                  <td><?= $store->mobile ?></td>
                                  <td><?= $store->address ?></td>
                                 <td data-order="<?= $store->created_date ?>"><?= show_date($store->created_date) ?></td>
                                  <td><?= $store->created_by ?></td>
                                  <td><?= $store->package_name ?></td>
                                  <td>
                                     <?php
                                        $expire_label = 'label-success';
                                        if(!empty($store->expire_date)){
                                            $day_diff = date_difference(date('Y-m-d'), $store->expire_date);
                                            if($day_diff < 0) $expire_label = 'label-danger';
                                            else if($day_diff <= 10) $expire_label = 'label-warning';
                                            echo '<span class="label '.$expire_label.'">'.show_date($store->expire_date).'</span>';
                                        } else {
                                            echo '-';
                                        }
                                     ?>
                                  </td>
                                  <td>
                                     <?php 
                                        $status_label = ($store->status == 1) ? 'label-success' : 'label-danger';
                                        $status_text = ($store->status == 1) ? 'Active' : 'Inactive';
                                     ?>
                                     <span class="label <?= $status_label ?>"><?= $status_text ?></span>
                                  </td>
                                  <td>
                                     <div class="btn-group">
                                        <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown">Action <span class="caret"></span></button>
                                        <ul role="menu" class="dropdown-menu dropdown-light pull-right">
                                           <li><a href="<?= base_url('subscribers/list/'.$store->id) ?>"><i class="fa fa-list"></i> Subscription List</a></li>
                                           <li><a href="<?= base_url('store_profile/update/'.$store->id) ?>"><i class="fa fa-edit"></i> Edit</a></li>
                                           <li><a href="javascript:void(0);" onclick="delete_store_with_reason(<?= $store->id ?>)"><i class="fa fa-trash"></i> Delete</a></li>
                                        </ul>
                                     </div>
                                  </td>
                               </tr>
                               <?php endforeach; ?>
                            <?php else: ?>
                               <tr><td colspan="12" class="text-center">No data found</td></tr>
                            <?php endif; ?>
                          </tbody>
                        </table>
                     <script>
                     $(document).ready(function() {
                         if($('#support_table').length > 0){
                             $('#support_table').DataTable({
                               "aLengthMenu": [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, "All"]],
                               "autoWidth": false,
                               dom: '<"row"<"col-sm-6"l><"col-sm-6 text-right"Bf>>rtip',
                               buttons: {
                                 buttons: [
                                   { extend: 'excel', title: 'Customer Support Status', className: 'btn bg-teal btn-flat margin-left-10', exportOptions: { columns: [0,1,2,3,4,5,6,7,9]} },
                                   { extend: 'print', title: 'Customer Support Status', className: 'btn bg-teal btn-flat margin-left-10', exportOptions: { columns: [0,1,2,3,4,5,6,7,9]} }
                                 ]
                               },
                               "order": [[ 5, "asc" ]],
                               "responsive": true,
                               "columnDefs": [
                                 { "targets": [ 7, 8, 9 ], "orderable": false },
                               ]
                             });
                         }
                     });
                     </script>
                        <script>
                        $(document).ready(function() {
                            if($('#example2-store-table').length > 0){
                                $('#example2-store-table').DataTable({
                                  "aLengthMenu": [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, "All"]],
                                  "autoWidth": false,
                                  dom: '<"row"<"col-sm-6"l><"col-sm-6 text-right"Bf>>rtip',
                                  buttons: {
                                    buttons: [
                                      { extend: 'excel', title: 'รายการร้านค้า', className: 'btn bg-teal btn-flat margin-left-10', exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
                                      { extend: 'print', title: 'รายการร้านค้า', className: 'btn bg-teal btn-flat margin-left-10', exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} }
                                    ]
                                  },
                                  "order": [[ 5, "desc" ]],
                                  "responsive": true,
                                  "columnDefs": [
                                    { "targets": [ 0, 10 ], "orderable": false },
                                    { "targets" :[0], "className": "text-center" },
                                  ]
                                });
                            }
                        });
                        </script>
                      </div>
                    </div>
                  </div>
                </div>
              <?php } else { ?>
                <div class="tab-pane active" id="sales_list_tab">
                <div class="row">
                  <div class="col-md-12">
                    <?php if (warehouse_module() && warehouse_count() > 1) {
                      echo '<div class="col-md-4">';
                      $this->load->view('warehouse/warehouse_code', array('show_warehouse_select_box' => true, 'div_length' => '',
                        'label_length' => '', 'show_all' => 'true', 'show_all_option' => true, 'remove_star' => true));
                      echo '</div>';
                    } else {
                      echo "<input type='hidden' name='warehouse_id' id='warehouse_id' value='" . get_store_warehouse_id() . "'>";
                    }?>
                    <div class="col-md-3">
                      <div class="form-group">
                         <label for="search_customer_id"><?=$this->lang->line('customers');?> </label>
                         <select class="form-control select2" id="search_customer_id" name="search_customer_id"  style="width: 100%;">
                       </select>
                         <span id="search_customer_id_msg" style="display:none" class="text-danger"></span>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                         <label for="users"><?=$this->lang->line('users');?> </label>
                         <select class="form-control select2" id="users" name="users"  style="width: 100%;">
                          <?=get_users_select_list($this->session->userdata("role_id"), get_current_store_id());?>
                       </select>
                         <span id="users_msg" style="display:none" class="text-danger"></span>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                         <label for="sales_from_date"><?=$this->lang->line('from_date');?> </label>
                         <div class="input-group date">
                           <div class="input-group-addon">
                              <i class="fa fa-calendar"></i>
                           </div>
                           <input type="text" class="form-control pull-right datepicker"  id="sales_from_date" name="sales_from_date">
                        </div>
                         <span id="sales_from_date_msg" style="display:none" class="text-danger"></span>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                         <label for="sales_to_date"><?=$this->lang->line('to_date');?> </label>
                         <div class="input-group date">
                           <div class="input-group-addon">
                              <i class="fa fa-calendar"></i>
                           </div>
                           <input type="text" class="form-control pull-right datepicker"  id="sales_to_date" name="sales_to_date">
                        </div>
                         <span id="sales_to_date_msg" style="display:none" class="text-danger"></span>
                      </div>
                    </div>
                  </div>
                </div>
                <br>
                <div class="row">
                  <div class="col-md-12 table-responsive dashboard-table-wrap">
                    <table id="sales_table_dashboard" class="table table-bordered table-hover dashboard-uniform-table" width="100%">
                      <thead class="bg-success ">
                        <tr>
                          <th class="text-center">
                            <input type="checkbox" class="group_check checkbox" >
                          </th>
                          <th><?=$this->lang->line('sales_date');?></th>
                          <th><?=$this->lang->line('due_date');?></th>
                          <th><?=$this->lang->line('sales_code');?></th>
                          <th>Ref. Sales Code</th>
                          <th>Tax Invoice Type</th>
                          <th><?=$this->lang->line('customer_name');?></th>
                          <th><?=$this->lang->line('amount');?></th>
                          <th>VAT Amount</th>
                          <th>Paid Payment</th>
                          <th><?=$this->lang->line('status');?></th>
                          <th><?=$this->lang->line('action');?></th>
                        </tr>
                      </thead>
                      <tbody></tbody>
                      <tfoot>
                        <tr class="bg-gray">
                          <th></th><th></th><th></th><th></th><th></th><th></th>
                          <th style="text-align:right">รวม</th>
                          <th></th><th></th><th></th><th></th><th></th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            <?php } ?>
          </div>
         </div>
         <?php if(get_current_store_id() != 1){ ?>
        <div class="col-md-12" id="purchase_list_section">
          <div class="nav-tabs-custom dashboard-table-section" style="margin-top:25px;">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#purchase_list_tab" data-toggle="tab"><?= $this->lang->line('purchase_list'); ?></a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="purchase_list_tab">
                <div class="row">
                  <div class="col-md-12">
                    <div class="col-md-4">
                      <div class="form-group">
                         <label for="purchase_from_date"><?=$this->lang->line('from_date');?> </label>
                         <div class="input-group date">
                           <div class="input-group-addon">
                              <i class="fa fa-calendar"></i>
                           </div>
                           <input type="text" class="form-control pull-right datepicker"  id="purchase_from_date" name="purchase_from_date">
                        </div>
                         <span id="purchase_from_date_msg" style="display:none" class="text-danger"></span>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                         <label for="purchase_to_date"><?=$this->lang->line('to_date');?> </label>
                         <div class="input-group date">
                           <div class="input-group-addon">
                              <i class="fa fa-calendar"></i>
                           </div>
                           <input type="text" class="form-control pull-right datepicker"  id="purchase_to_date" name="purchase_to_date">
                        </div>
                         <span id="purchase_to_date_msg" style="display:none" class="text-danger"></span>
                      </div>
                    </div>
                  </div>
                </div>
                <br>
                <div class="row">
                  <div class="col-md-12 table-responsive dashboard-table-wrap">
                    <table id="purchase_table_dashboard" class="table table-bordered table-hover dashboard-uniform-table" width="100%">
                      <thead class="bg-success ">
                        <tr>
                          <th class="text-center">
                            <input type="checkbox" class="group_check checkbox" >
                          </th>
                          <th><?=$this->lang->line('purchase_date');?></th>
                          <th><?=$this->lang->line('purchase_code');?></th>
                          <th><?=$this->lang->line('purchase_status');?></th>
                          <th><?=$this->lang->line('reference_no');?></th>
                          <th><?=$this->lang->line('supplier_name');?></th>
                          <th><?=$this->lang->line('amount');?></th>
                          <th><?=$this->lang->line('tax_amount');?></th>
                          <th><?=$this->lang->line('paid_amount');?></th>
                          <th><?=$this->lang->line('payment_status');?></th>
                          <th><?=$this->lang->line('action');?></th>
                        </tr>
                      </thead>
                      <tbody></tbody>
                      <tfoot>
                        <tr class="bg-gray">
                          <th></th><th></th><th></th><th></th><th></th>
                          <th style="text-align:right">รวม</th>
                          <th></th><th></th><th></th><th></th><th></th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
         </div>
         <?php } ?>
       </div>        

        <?php if(get_current_store_id() != 1){ ?>
        <div class="col-md-12" id="expense_list_section">
          <div class="nav-tabs-custom dashboard-table-section" style="margin-top:25px;">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#expense_list_tab" data-toggle="tab"><?= $this->lang->line('expenses_list'); ?></a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="expense_list_tab">
                <div class="row">
                  <div class="col-md-12">
                    <div class="col-md-4">
                      <div class="form-group">
                         <label for="expense_from_date"><?=$this->lang->line('from_date');?> </label>
                         <div class="input-group date">
                           <div class="input-group-addon">
                              <i class="fa fa-calendar"></i>
                           </div>
                           <input type="text" class="form-control pull-right datepicker"  id="expense_from_date" name="expense_from_date">
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                         <label for="expense_to_date"><?=$this->lang->line('to_date');?> </label>
                         <div class="input-group date">
                           <div class="input-group-addon">
                              <i class="fa fa-calendar"></i>
                           </div>
                           <input type="text" class="form-control pull-right datepicker"  id="expense_to_date" name="expense_to_date">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <br>
                <div class="row">
                  <div class="col-md-12 table-responsive dashboard-table-wrap">
                    <table id="expense_table_dashboard" class="table table-bordered table-hover dashboard-uniform-table" width="100%">
                      <thead class="bg-success ">
                        <tr>
                          <th class="text-center">
                            <input type="checkbox" class="group_check checkbox" >
                          </th>
                          <th><?=$this->lang->line('date');?></th>
                          <th><?=$this->lang->line('category');?></th>
                          <th><?=$this->lang->line('reference_no');?></th>
                          <th><?=$this->lang->line('expense_for');?></th>
                          <th><?=$this->lang->line('amount');?></th>
                          <th><?=$this->lang->line('account');?></th>
                          <th><?=$this->lang->line('note');?></th>
                          <th><?=$this->lang->line('action');?></th>
                        </tr>
                      </thead>
                      <tbody></tbody>
                      <tfoot>
                        <tr class="bg-gray">
                          <th></th><th></th><th></th><th></th>
                          <th style="text-align:right">รวม</th>
                          <th></th><th></th><th></th><th></th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
         </div>
         <?php } ?>

        <?php if(get_current_store_id() != 1){ ?>
        <div class="col-md-12" id="stock_alert_section">
          <div class="box box-primary dashboard-table-section" style="margin-top:25px;">
            <div class="box-header">
              <h3 class="box-title text-uppercase"><?= $this->lang->line('stock_alert'); ?></h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive dashboard-table-wrap">
              <table id="example2" class="table table-bordered table-hover dashboard-uniform-table" width="100%">
                <thead>
                <tr class='bg-warning'>
                  <th>#</th>
                  <th><?= $this->lang->line('item_name'); ?></th>
                  <th><?= $this->lang->line('category_name'); ?></th>
                  <th><?= $this->lang->line('brand_name'); ?></th>
                  <th><?= $this->lang->line('stock'); ?></th>
                </tr>
                </thead>
                <tbody>
                </tbody>
                
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->

        </div>
        <?php } ?>
        <!-- /.col (RIGHT) -->
 



      
 

      </div>


  
      
      <!-- ############################# GRAPHS END############################## -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <?php $this->load->view('footer'); ?>
  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>

</div>
<!-- ./wrapper -->


<!-- TABLES CODE -->
<?php include"comman/code_js.php"; ?>
<!-- bootstrap datepicker -->

<!-- ChartJS 1.0.1 -->
<script src="<?php echo $theme_link; ?>plugins/chartjs/Chart.min.js"></script>
<script src="<?php echo $theme_link; ?>plugins/chartjs/chartjs-plugin-colorschemes.min.js"></script>
<script>
// Chart.js extension for rounded corners
Chart.elements.Rectangle.prototype.draw = function() {
    var ctx = this._chart.ctx;
    var vm = this._view;
    var left, right, top, bottom, signX, signY, borderSkipped;
    var borderWidth = vm.borderWidth;
    var cornerRadius = this._chart.config.options.cornerRadius || 0;

    if (!vm.horizontal) {
        left = vm.x - vm.width / 2;
        right = vm.x + vm.width / 2;
        top = vm.y;
        bottom = vm.y;
        signX = 1;
        signY = bottom > top ? 1 : -1;
        borderSkipped = vm.borderSkipped || 'bottom';
    }
    
    ctx.beginPath();
    ctx.fillStyle = vm.backgroundColor;
    ctx.strokeStyle = vm.borderColor;
    ctx.lineWidth = borderWidth;

    var width = right - left;
    var height = bottom - top;
    var radius = cornerRadius;
    if (radius > Math.abs(height) / 2) radius = Math.abs(height) / 2;
    if (radius > Math.abs(width) / 2) radius = Math.abs(width) / 2;

    var x = left, y = top;
    // Draw rounded rect (top corners only)
    ctx.moveTo(x, y + height);
    ctx.lineTo(x, y + radius);
    ctx.quadraticCurveTo(x, y, x + radius, y);
    ctx.lineTo(x + width - radius, y);
    ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
    ctx.lineTo(x + width, y + height);
    ctx.lineTo(x, y + height);

    ctx.fill();
    if (borderWidth) ctx.stroke();
};
</script>
<script>

  'use strict';

window.chartColors = {
  red: 'rgb(223, 20, 47)',
  orange: 'rgb(231, 97, 63)',
  yellow: 'rgb(230, 184, 0)',
  green: 'rgb(11, 173, 11)',
  blue: 'rgb(0, 0, 230)',
  purple: 'rgb(134, 0, 179)',
  grey: 'rgb(117, 117, 163)'
};

(function(global) {
  var MONTHS = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December'
  ];

  var COLORS = [
    '#56D0FC',
    '#f67019',
    '#f53794',
    '#5E88D4',
    '#acc236',
    '#1F8BB9',
    '#18C569',
    '#58595b',
    '#9153C7'
  ];

  var Samples = global.Samples || (global.Samples = {});
  var Color = global.Color;

  Samples.utils = {
    // Adapted from http://indiegamr.com/generate-repeatable-random-numbers-in-js/
    srand: function(seed) {
      this._seed = seed;
    },

    rand: function(min, max) {
      var seed = this._seed;
      min = min === undefined ? 0 : min;
      max = max === undefined ? 1 : max;
      this._seed = (seed * 9301 + 49297) % 233280;
      return min + (this._seed / 233280) * (max - min);
    },

    numbers: function(config) {
      var cfg = config || {};
      var min = cfg.min || 0;
      var max = cfg.max || 1;
      var from = cfg.from || [];
      var count = cfg.count || 8;
      var decimals = cfg.decimals || 8;
      var continuity = cfg.continuity || 1;
      var dfactor = Math.pow(10, decimals) || 0;
      var data = [];
      var i, value;

      for (i = 0; i < count; ++i) {
        value = (from[i] || 0) + this.rand(min, max);
        if (this.rand() <= continuity) {
          data.push(Math.round(dfactor * value) / dfactor);
        } else {
          data.push(null);
        }
      }

      return data;
    },

    labels: function(config) {
      var cfg = config || {};
      var min = cfg.min || 0;
      var max = cfg.max || 100;
      var count = cfg.count || 8;
      var step = (max - min) / count;
      var decimals = cfg.decimals || 8;
      var dfactor = Math.pow(10, decimals) || 0;
      var prefix = cfg.prefix || '';
      var values = [];
      var i;

      for (i = min; i < max; i += step) {
        values.push(prefix + Math.round(dfactor * i) / dfactor);
      }

      return values;
    },

    months: function(config) {
      var cfg = config || {};
      var count = cfg.count || 12;
      var section = cfg.section;
      var values = [];
      var i, value;

      for (i = 0; i < count; ++i) {
        value = MONTHS[Math.ceil(i) % 12];
        values.push(value.substring(0, section));
      }

      return values;
    },

    color: function(index) {
      return COLORS[index % COLORS.length];
    },

    transparentize: function(color, opacity) {
      var alpha = opacity === undefined ? 0.5 : 1 - opacity;
      return Color(color).alpha(alpha).rgbString();
    }
  };

  // DEPRECATED
  window.randomScalingFactor = function() {
    return Math.round(Samples.utils.rand(-100, 100));
  };

  // INITIALIZATION

  Samples.utils.srand(Date.now());

}(this));
</script>

<script>
  $(document).ready(function(){
     // Save Support Status
     $(".save-support").on("click", function(){
        var id = $(this).data("id");
        var status = $(".support-status[data-id='"+id+"']").val();
        var note = $(".support-note[data-id='"+id+"']").val();
        var btn = $(this);

        btn.html('<i class="fa fa-spinner fa-spin"></i>');
        
        $.ajax({
           url: '<?= base_url("dashboard/update_support_status_ajax") ?>',
           type: 'POST',
           data: {store_id: id, status: status, note: note},
           success: function(response){
              if(response == 'success'){
                 toastr.success('Support status updated!');
                 btn.html('<i class="fa fa-check"></i>');
                 setTimeout(function(){ btn.html('<i class="fa fa-save"></i>'); }, 2000);
              }else{
                 toastr.error('Failed to update!');
                 btn.html('<i class="fa fa-save"></i>');
              }
           },
           error: function(){
               toastr.error('Error occurred!');
               btn.html('<i class="fa fa-save"></i>');
           }
        });
     });
  });
</script>


<?php if(!is_user()){ ?>
<script>
$(document).ready(function(){
  // BAR CHART
  var ctx = $(".bar-chartcanvas")[0].getContext("2d");
  var data = {
      labels: [
                  "<?=$month[11]?>", 
                  "<?=$month[10]?>", 
                  "<?=$month[9]?>", 
                  "<?=$month[8]?>", 
                  "<?=$month[7]?>", 
                  "<?=$month[6]?>", 
                  "<?=$month[5]?>", 
                  "<?=$month[4]?>", 
                  "<?=$month[3]?>", 
                  "<?=$month[2]?>", 
                  "<?=$month[1]?>", 
                  "<?=$month[0]?>", 
              ],
      datasets: [
        {
          label: "<?= $this->lang->line('purchase'); ?>",
          data: [   
                    "<?=$purchase[11]?>", 
                    "<?=$purchase[10]?>", 
                    "<?=$purchase[9]?>", 
                    "<?=$purchase[8]?>", 
                    "<?=$purchase[7]?>", 
                    "<?=$purchase[6]?>", 
                    "<?=$purchase[5]?>", 
                    "<?=$purchase[4]?>", 
                    "<?=$purchase[3]?>", 
                    "<?=$purchase[2]?>", 
                    "<?=$purchase[1]?>", 
                    "<?=$purchase[0]?>", 
                ],
          borderColor: '#BA68C8',
          backgroundColor: '#BA68C8',
          borderWidth: 1
        },
        {
          label: "<?= $this->lang->line('sales'); ?>",
          data: [   
                    "<?=$sales[11]?>", 
                    "<?=$sales[10]?>", 
                    "<?=$sales[9]?>", 
                    "<?=$sales[8]?>", 
                    "<?=$sales[7]?>", 
                    "<?=$sales[6]?>", 
                    "<?=$sales[5]?>", 
                    "<?=$sales[4]?>", 
                    "<?=$sales[3]?>", 
                    "<?=$sales[2]?>", 
                    "<?=$sales[1]?>", 
                    "<?=$sales[0]?>", 
                ],
          borderColor: '#81C784',
          backgroundColor: '#81C784',
          borderWidth: 1
        },
        {
          label: "<?= $this->lang->line('expense'); ?>",
          data: [   
                    "<?=$expense[11]?>", 
                    "<?=$expense[10]?>", 
                    "<?=$expense[9]?>", 
                    "<?=$expense[8]?>", 
                    "<?=$expense[7]?>", 
                    "<?=$expense[6]?>", 
                    "<?=$expense[5]?>", 
                    "<?=$expense[4]?>", 
                    "<?=$expense[3]?>", 
                    "<?=$expense[2]?>", 
                    "<?=$expense[1]?>", 
                    "<?=$expense[0]?>", 
                ],
          borderColor: '#FFB74D',
          backgroundColor: '#FFB74D',
          borderWidth: 1
        }
      ]
    };

  //options
  var options = {
    cornerRadius: 8,
    responsive: true,
    maintainAspectRatio: false,
    title: {
      display: true,
      position: "top",
      fontSize: 18,
      fontColor: "#111"
    },
    legend: {
      display: true,
      position: "top",
      labels: {
        fontColor: "#333",
        fontSize: 16
      }
    },
    scales: {
      yAxes: [{
        ticks: {
          min: 0,
          callback: function(value, index, values) {
            return value.toLocaleString();
          }
        }
      }]
    }
  };
  //create Chart class object
  var chart = new Chart(ctx, {
    type: "bar",
    data: data,
    options: options
  });

  // REST OF SCRIPTS
});
</script>
<script>
  //PIE CHART
  $(function(){
      if(document.getElementById("doughnut-chart")){
      new Chart(document.getElementById("doughnut-chart"), {
        type: 'doughnut',
        data: {
          labels: 
                  [
                    <?php if($tranding_item['tot_rec'] > 0){?>
                        <?php for($i=$tranding_item['tot_rec']; $i>0; $i--){ ?>
                            '<?= $tranding_item[$i]['name'] ?>',
                        <?php } ?>
                    <?php } ?>
                  ],
          datasets: [
            {
              label: "Top Items",
              backgroundColor: ["#7979FD", "#ff3399","#0BD8A5","#F3F33E","#F89959",""],
              data: [
                    <?php if($tranding_item['tot_rec'] > 0){?>
                        <?php for($i=$tranding_item['tot_rec']; $i>0; $i--){ ?>
                            '<?= $tranding_item[$i]['sales_qty'] ?>',
                        <?php } ?>
                    <?php } ?>
                  ],
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          title: {
            display: true,
            text: '<?= $this->lang->line('top_10_trending_items'); ?>'
          }
        }
    });
      }
  });
</script>
<?php } ?>






<!-- Make sidebar menu hughlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
    <?php if(is_admin() && store_module()){ ?>
    <script>
      $(document).ready(function(){
        $("#stores_details").DataTable({
          "autoWidth": false
        });
      });
    </script>
    <?php } ?>
<script>
  /* $(function () {
    $('#example3').DataTable({
      "pageLength" : 5,
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false
    });
  });*/


 $(document).ready(function() {
    //datatables
   var table = $('#example2').DataTable({ 
      "autoWidth": false,

      /* FOR EXPORT BUTTONS START*/
  dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>tip',
 /* dom:'<"row"<"col-sm-12"<"pull-left"B><"pull-right">>> <"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>tip',*/
      buttons: {
        buttons: [
            {
                className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left',
                text: 'Delete',
                action: function ( e, dt, node, config ) {
                    multi_delete();
                }
            },
          //  { extend: 'copy', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [0,1,2,3,4]} },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [0,1,2,3,4]} },
         //   { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [0,1,2,3,4]} },
            { extend: 'print', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [0,1,2,3,4]} },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [0,1,2,3,4]} },
            { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat',text:'Columns' },  

            ]
        },
        /* FOR EXPORT BUTTONS END */

        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        "order": [], //Initial no order.
        "responsive": true,
        language: {
            processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>'
        },
        // Load data for the table's content from an Ajax source
        "ajax": {
            "url": "<?php echo site_url('dashboard/ajax_list')?>",
            "type": "POST",
            "data": {
                "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>"
            },
            complete: function (data) {
             },

        },

        //Set column definition initialisation properties.
        "columnDefs": [
        { 
            //"targets": [ 0,4 ], //first column / numbering column
            "orderable": false, //set not orderable
        },
        {
            //"targets" :[0],
            //"className": "text-center",
        },
        
        ],
    });
    new $.fn.dataTable.FixedHeader( table );
});

</script>



</script>
<script>
   function filter_store_status(status){
      console.log("Filter Status: " + status);
      
      // Check if DataTable
      if ($.fn.DataTable.isDataTable('#support_table')) {
         var table = $('#support_table').DataTable();
         if(status == 'all'){
            table.search('').columns().search('').draw();
         } else {
            // Column 6 is Status
            table.column(6).search(status).draw();
         }
      } else {
         // Standard Table
         if(status == 'all'){
            $(".store-row").show();
         } else {
            $(".store-row").hide();
            $('.store-row').each(function(){
               // Match data-status OR text content of status column (fallback)
               var row_status = $(this).attr('data-status');
               if(row_status == status){
                  $(this).show();
               }
            });
         }
      }
   }


   var base_url='<?= base_url(); ?>';
   function get_dashboard_values(dates=''){
      var store_id =<?= (isset($store_id)) ? $store_id : get_current_store_id();?>;
      var csrf_name = '<?= $this->security->get_csrf_token_name(); ?>';
      var csrf_value = '<?= $this->security->get_csrf_hash(); ?>';
      var post_data = {store_id:store_id,dates:dates};
      post_data[csrf_name] = csrf_value;

      $.ajax({
          url: "<?= site_url('dashboard/dashboard_values') ?>",
          type: "POST",
          data: post_data,
          success: function(result) {
              if(!result) return;
              var data = (typeof result == 'string' && result.trim() != '') ? jQuery.parseJSON(result) : result;
              if(data){
                $.each(data, function(index, element) {
                    if(index == 'etax_usage' || index == 'etax_limit'){
                        var usage = parseInt(data.etax_usage) || 0;
                        var limit = data.etax_limit;
                        var limit_text = (limit == -1 || limit == '∞') ? '∞' : limit;
                        $(".etax_usage_display").html(usage + " / " + limit_text);
                        
                        var percentage = (limit == -1 || limit == '∞') ? 0 : (usage / parseInt(limit)) * 100;
                        if(percentage > 100) percentage = 100;
                        $(".etax_progress").css("width", percentage + "%");
                    } else {
                        $("." + index).html(element);
                    }
                });
              }
          },
          error: function(xhr, status, error) {
              console.error("Dashboard data load failed: " + error);
          }
      });
   }

   $(document).ready(function($) {
      get_dashboard_values('Today');
   });

   $(".get_tab_records").on("click",function(event) {
      $(".get_tab_records").removeClass('active');
      $(this).addClass('active');
      get_dashboard_values($(this).html());
   });

   function delete_store(id){
      if(confirm("Are you sure you want to delete this store? This action cannot be undone!")){
         window.location.href = '<?= base_url("super_admin/delete_store/") ?>' + id;
      }
   }
</script>

<script>
  $(document).ready(function(){
    loadMonthlyComparisonChart($('#chart_year_selector').val());
  });

  function loadMonthlyComparisonChart(year) {
      $.ajax({
          url: '<?= base_url("dashboard/get_monthly_paid_comparison_ajax") ?>',
          type: 'POST',
          data: {
              year: year,
              "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>"
          },
          success: function(response){
              var result = JSON.parse(response);
              renderMonthlyComparisonChart(result);
          }
      });
  }

  function renderMonthlyComparisonChart(data) {
      var ctx = document.getElementById('monthly_comparison_chart').getContext('2d');
      if(window.monthlyComparisonChart) {
          window.monthlyComparisonChart.destroy();
      }

      // Update hidden table for export
      var tableBody = '';
      var total_sales = 0;
      var total_debit = 0;
      var total_purchase = 0;
      var total_return = 0;
      var total_expense = 0;

      for (var i = 0; i < data.labels.length; i++) {
          tableBody += '<tr>';
          tableBody += '<td>' + data.labels[i] + '</td>';
          tableBody += '<td>' + data.sales_paid[i] + '</td>';
          tableBody += '<td>' + data.sales_debit_paid[i] + '</td>';
          tableBody += '<td>' + data.purchase_paid[i] + '</td>';
          tableBody += '<td>' + data.purchase_return_paid[i] + '</td>';
          tableBody += '<td>' + data.expense[i] + '</td>';
          tableBody += '</tr>';

          total_sales += parseFloat(data.sales_paid[i]);
          total_debit += parseFloat(data.sales_debit_paid[i]);
          total_purchase += parseFloat(data.purchase_paid[i]);
          total_return += parseFloat(data.purchase_return_paid[i]);
          total_expense += parseFloat(data.expense[i]);
      }

      // Add Yearly Total row
      tableBody += '<tr style="font-weight:bold; background-color: #f2f2f2;">';
      tableBody += '<td><?= $this->lang->line("yearly_total"); ?></td>';
      tableBody += '<td>' + total_sales + '</td>';
      tableBody += '<td>' + total_debit + '</td>';
      tableBody += '<td>' + total_purchase + '</td>';
      tableBody += '<td>' + total_return + '</td>';
      tableBody += '<td>' + total_expense + '</td>';
      tableBody += '</tr>';

      $('#monthly_paid_comparison_table_body').html(tableBody);

      window.monthlyComparisonChart = new Chart(ctx, {
          type: 'line',
          data: {
              labels: data.labels,
              datasets: [
                  {
                      label: "<?= $this->lang->line('sales_paid'); ?>",
                      data: data.sales_paid,
                      borderColor: '#00a65a', // Green
                      backgroundColor: 'rgba(0, 166, 90, 0.1)',
                      fill: false,
                      tension: 0.1
                  },
                  {
                      label: "<?= $this->lang->line('sales_debit_paid'); ?>",
                      data: data.sales_debit_paid,
                      borderColor: '#00c0ef', // Aqua
                      backgroundColor: 'rgba(0, 192, 239, 0.1)',
                      fill: false,
                      tension: 0.1
                  },
                  {
                      label: "<?= $this->lang->line('purchase_paid'); ?>",
                      data: data.purchase_paid,
                      borderColor: '#f39c12', // Yellow
                      backgroundColor: 'rgba(243, 156, 18, 0.1)',
                      fill: false,
                      tension: 0.1
                  },
                  {
                      label: "<?= $this->lang->line('purchase_return_paid'); ?>",
                      data: data.purchase_return_paid,
                      borderColor: '#dd4b39', // Red
                      backgroundColor: 'rgba(221, 75, 57, 0.1)',
                      fill: false,
                      tension: 0.1
                  },
                  {
                      label: "<?= $this->lang->line('admin_expense'); ?>",
                      data: data.expense,
                      borderColor: '#605ca8', // Purple
                      backgroundColor: 'rgba(96, 92, 168, 0.1)',
                      fill: false,
                      tension: 0.1
                  }
              ]
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              tooltips: {
                  mode: 'index',
                  intersect: false,
              },
              hover: {
                  mode: 'nearest',
                  intersect: true
              },
              scales: {
                  xAxes: [{
                      display: true,
                      scaleLabel: {
                          display: true,
                          labelString: 'Month'
                      }
                  }],
                  yAxes: [{
                      display: true,
                      scaleLabel: {
                          display: true,
                          labelString: 'Amount'
                      },
                      ticks: {
                          beginAtZero: true,
                          callback: function(value, index, values) {
                              return value.toLocaleString();
                          }
                      }
                  }]
              }
          }
      });
  }

  function exportChartToExcel() {
      var year = $('#chart_year_selector').val();
      var table = document.getElementById("monthly_paid_comparison_table").outerHTML;
      var style = '<style>table { border-collapse: collapse; } th, td { border: 1px solid black; padding: 5px; }</style>';
      var blob = new Blob(['\ufeff', style + table], {
          type: 'application/vnd.ms-excel'
      });
      var url = window.URL.createObjectURL(blob);
      var a = document.createElement("a");
      a.href = url;
      a.download = "Monthly_Paid_Comparison_" + year + ".xls";
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
  }
</script>
<script src="<?php echo $theme_link; ?>js/ajaxselect/customer_select_ajax.js"></script>  
<script src="<?php echo $theme_link; ?>js/sales.js"></script>
<script src="<?php echo $theme_link; ?>js/purchase.js"></script>
<script src="<?php echo $theme_link; ?>js/expense.js"></script>
<script>
  // Override delete functions for dashboard table IDs
  function delete_expense(q_id) {
    if(confirm("Do You Wants to Delete Record ?")){
      $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
      $.post('<?= base_url("expense/delete_expense") ?>',{q_id:q_id},function(result){
        if(result=="success") {
          toastr["success"]("ลบบันทึกเรียบร้อย!");
          $('#expense_table_dashboard').DataTable().ajax.reload();
        } else {
          toastr["error"](result);
        }
        $(".overlay").remove();
      });
    }
  }

  function delete_purchase(q_id) {
    if(confirm("Do You Wants to Delete Record ?")){
      $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
      $.post('<?= base_url("purchase/delete_purchase") ?>',{q_id:q_id},function(result){
        if(result=="success") {
          toastr["success"]("ลบบันทึกเรียบร้อย!");
          $('#purchase_table_dashboard').DataTable().ajax.reload();
        } else {
          toastr["error"](result);
        }
        $(".overlay").remove();
      });
    }
  }
</script>
<script>
  function printTemplate(invoiceUrl) {
    const templateUrl = invoiceUrl;
    const iframe = document.createElement("iframe");
    iframe.style.position = "absolute";
    iframe.style.top = "-10000px";
    iframe.style.width = "0";
    iframe.style.height = "0";
    iframe.style.border = "none";
    document.body.appendChild(iframe);

    iframe.src = templateUrl;

    iframe.onload = function() {
      iframe.contentWindow.focus();
      iframe.contentWindow.print();

      // Cleanup the iframe after printing
      setTimeout(() => {
        document.body.removeChild(iframe);
      }, 1000);
    };
  }

  function print_invoice(id){
    window.open("<?=base_url();?>pos/print_invoice_pos/"+id, "_blank", "scrollbars=1,resizable=1,height=500,width=500");
  }

  function load_sales_datatable(){
    var table = $('#sales_table_dashboard').DataTable({
      "aLengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
      "autoWidth": false,
      dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>tip',
      buttons: {
        buttons: [
          { extend: 'excel', title: 'รายการขาย', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10]} },
          { extend: 'print', title: 'รายการขาย', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10]} },
          { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat',footer: true, text:'Columns' },
        ]
      },
      "processing": true,
      "serverSide": true,
      "order": [],
      "responsive": true,
      "scrollX": true,
      language: {
          processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>'
      },
      "ajax": {
          "url": "<?php echo site_url('sales/ajax_list') ?>",
          "type": "POST",
          "data": function(d) {
              d.page = 'dashboard';
              d.warehouse_id = $("#warehouse_id").val();
              d.store_id = $("#store_id").val();
              d.sales_from_date = $("#sales_from_date").val();
              d.sales_to_date = $("#sales_to_date").val();
              d.users = $("#users").val();
              d.customer_id = $("#search_customer_id").val();
              d.<?= $this->security->get_csrf_token_name(); ?> = '<?= $this->security->get_csrf_hash(); ?>';
            },
          error: function(xhr, textStatus, errorThrown) {
            var msg = 'โหลดข้อมูลรายการขายไม่สำเร็จ';
            if (xhr && xhr.responseText) {
              var rawText = xhr.responseText.replace(/<[^>]*>/g, ' ').trim();
              if (rawText.length > 0) {
                msg += ' : ' + rawText.substring(0, 250);
              }
            } else if (errorThrown) {
              msg += ' : ' + errorThrown;
            } else if (textStatus) {
              msg += ' : ' + textStatus;
            }
            toastr["error"](msg);
          },
          complete: function (data) {
           $('.column_checkbox').iCheck({
              checkboxClass: 'icheckbox_square-orange',
              radioClass: 'iradio_square-orange',
              increaseArea: '10%'
            });
            call_code();
           },
      },
      "columnDefs": [
        { "targets": [ 0, 11 ], "orderable": false },
        { "targets" :[0], "className": "text-center" },
      ],
      "footerCallback": function ( row, data, start, end, display ) {
          var api = this.api(), data;
          var intVal = function ( i ) {
              return typeof i === 'string' ? i.replace(/[\$,]/g, '')*1 : typeof i === 'number' ? i : 0;
          };
          var total = api.column( 7, { page: 'none'} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );
          var tax = api.column( 8, { page: 'none'} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );
          var paid = api.column( 9, { page: 'none'} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );

          $( api.column( 7 ).footer() ).html(total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
          $( api.column( 8 ).footer() ).html(tax.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
          $( api.column( 9 ).footer() ).html(paid.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
      },
    });
    $('#sales_table_dashboard').off('error.dt.dashboardSales').on('error.dt.dashboardSales', function(e, settings, techNote, message) {
      toastr["error"]('DataTables error: ' + message);
    });
    new $.fn.dataTable.FixedHeader( table );
    table.columns.adjust();
  }

  function load_store_datatable(){
    var table = $('#store_table_dashboard').DataTable({
      "aLengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
      "autoWidth": false,
      dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>tip',
      buttons: {
        buttons: [
          { extend: 'excel', title: 'รายการร้านค้า', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
          { extend: 'print', title: 'รายการร้านค้า', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
          { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat', text:'Columns' },
        ]
      },
      "processing": true,
      "serverSide": true,
      "order": [],
      "responsive": true,
      language: {
          processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>'
      },
      "ajax": {
          "url": "<?php echo site_url('store/ajax_list') ?>",
          "type": "POST",
          "data": function(d) {
              d.page = 'dashboard';
            },
          complete: function (data) {
           $('.column_checkbox').iCheck({
              checkboxClass: 'icheckbox_square-orange',
              radioClass: 'iradio_square-orange',
              increaseArea: '10%'
            });
            call_code();
           },
      },
      "columnDefs": [
        { "targets": [ 0, 10 ], "orderable": false },
        { "targets" :[0], "className": "text-center" },
      ],
    });
    new $.fn.dataTable.FixedHeader( table );
    table.columns.adjust();
  }

  function load_purchase_datatable(){
    var table = $('#purchase_table_dashboard').DataTable({
      "aLengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
      "autoWidth": false,
      dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>tip',
      buttons: {
        buttons: [
          { extend: 'excel', title: 'รายการซื้อ', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
          { extend: 'print', title: 'รายการซื้อ', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
          { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat',footer: true, text:'Columns' },
        ]
      },
      "processing": true,
      "serverSide": true,
      "order": [],
      "responsive": true,
      "scrollX": true,
      language: {
          processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>'
      },
      "ajax": {
          "url": "<?php echo site_url('purchase/ajax_list') ?>",
          "type": "POST",
          "data": function(d) {
              d.page = 'dashboard';
              d.warehouse_id = $("#warehouse_id").val();
              d.purchase_from_date = $("#purchase_from_date").val();
              d.purchase_to_date = $("#purchase_to_date").val();
              d.<?= $this->security->get_csrf_token_name(); ?> = '<?= $this->security->get_csrf_hash(); ?>';
            },
          complete: function (data) {
           $('.column_checkbox').iCheck({
              checkboxClass: 'icheckbox_square-orange',
              radioClass: 'iradio_square-orange',
              increaseArea: '10%'
            });
            call_code();
           },
      },
      "columnDefs": [
        { "targets": [ 0, 10 ], "orderable": false },
        { "targets" :[0], "className": "text-center" },
      ],
      "footerCallback": function ( row, data, start, end, display ) {
          var api = this.api(), data;
          var intVal = function ( i ) {
              return typeof i === 'string' ? i.replace(/[\$,]/g, '')*1 : typeof i === 'number' ? i : 0;
          };
          var total = api.column( 6, { page: 'none'} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );
          var tax = api.column( 7, { page: 'none'} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );
          var paid = api.column( 8, { page: 'none'} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );

          $( api.column( 6 ).footer() ).html(total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
          $( api.column( 7 ).footer() ).html(tax.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
          $( api.column( 8 ).footer() ).html(paid.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
      },
      "initComplete": function () {
        $('#purchase_table_dashboard_wrapper').css('width', '100%');
        $('#purchase_table_dashboard').css('width', '100%');
        this.api().columns.adjust();
      }
    });
    new $.fn.dataTable.FixedHeader( table );
    table.columns.adjust();
    setTimeout(function(){ table.columns.adjust(); }, 150);
  }

  function load_expense_datatable(){
    var table = $('#expense_table_dashboard').DataTable({
      "aLengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
      "autoWidth": false,
      dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>tip',
      buttons: {
        buttons: [
          { extend: 'excel', title: 'รายการรายจ่าย', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7]} },
          { extend: 'print', title: 'รายการรายจ่าย', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7]} },
          { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat',footer: true, text:'Columns' },
        ]
      },
      "processing": true,
      "serverSide": true,
      "order": [],
      "responsive": true,
      language: {
          processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>'
      },
      "ajax": {
          "url": "<?php echo site_url('expense/ajax_list') ?>",
          "type": "POST",
          "data": function(d) {
              d.page = 'dashboard';
              d.warehouse_id = $("#warehouse_id").val();
              d.expense_from_date = $("#expense_from_date").val();
              d.expense_to_date = $("#expense_to_date").val();
              d.<?= $this->security->get_csrf_token_name(); ?> = '<?= $this->security->get_csrf_hash(); ?>';
            },
          complete: function (data) {
            $('.column_checkbox').iCheck({
              checkboxClass: 'icheckbox_square-orange',
              radioClass: 'iradio_square-orange',
              increaseArea: '10%'
            });
            call_code();
          },
      },
      "columnDefs": [
        { "targets": [ 0, 8 ], "orderable": false },
        { "targets" :[0], "className": "text-center" },
      ],
      "footerCallback": function ( row, data, start, end, display ) {
          var api = this.api(), data;
          var intVal = function ( i ) {
              return typeof i === 'string' ? i.replace(/[\$,]/g, '')*1 : typeof i === 'number' ? i : 0;
          };
          var total = api.column( 5, { page: 'none'} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );

          $( api.column( 5 ).footer() ).html(total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
      },
    });
    new $.fn.dataTable.FixedHeader( table );
    table.columns.adjust();
  }

  function adjustDashboardTablesColumns(){
    var tableIds = [
      '#sales_table_dashboard',
      '#purchase_table_dashboard',
      '#expense_table_dashboard',
      '#store_table_dashboard',
      '#stores_details',
      '#support_table',
      '#example2-store-table',
      '#example2'
    ];
    tableIds.forEach(function(tableId){
      if ($.fn.DataTable.isDataTable(tableId)) {
        $(tableId).DataTable().columns.adjust();
      }
    });
  }

  function formatDateDdMmYyyy(dateObj){
    var dd = ('0' + dateObj.getDate()).slice(-2);
    var mm = ('0' + (dateObj.getMonth() + 1)).slice(-2);
    var yyyy = dateObj.getFullYear();
    return dd + '-' + mm + '-' + yyyy;
  }

  function applyDefault30DayDateRange(){
    var today = new Date();
    var from = new Date();
    from.setDate(today.getDate() - 30);

    var fromVal = formatDateDdMmYyyy(from);
    var toVal = formatDateDdMmYyyy(today);

    // Sales
    if($('#sales_from_date').length && !$('#sales_from_date').val()){ $('#sales_from_date').val(fromVal); }
    if($('#sales_to_date').length && !$('#sales_to_date').val()){ $('#sales_to_date').val(toVal); }

    // Purchase
    if($('#purchase_from_date').length && !$('#purchase_from_date').val()){ $('#purchase_from_date').val(fromVal); }
    if($('#purchase_to_date').length && !$('#purchase_to_date').val()){ $('#purchase_to_date').val(toVal); }

    // Expense
    if($('#expense_from_date').length && !$('#expense_from_date').val()){ $('#expense_from_date').val(fromVal); }
    if($('#expense_to_date').length && !$('#expense_to_date').val()){ $('#expense_to_date').val(toVal); }
  }

  $(document).ready(function() {
      <?php if(is_admin()){ ?>
        // load_store_datatable(); // Migrated to PHP rendering
      <?php } else { ?>
        // Keep only the 4 tables contiguous and in this order:
        // Purchase -> Sales -> Expenses -> Stock Alert
        var $purchaseSection = $('#purchase_list_section');
        var $salesSection = $('#sales_list_section');
        var $expenseSection = $('#expense_list_section');
        var $stockSection = $('#stock_alert_section');
        if($purchaseSection.length && $salesSection.length && $expenseSection.length && $stockSection.length){
          $purchaseSection.insertBefore($salesSection);
          $expenseSection.insertAfter($salesSection);
          $stockSection.insertAfter($expenseSection);
        }

        // Default filter range: last 30 days for all dashboard tables.
        applyDefault30DayDateRange();

        load_sales_datatable();
        load_purchase_datatable();
        load_expense_datatable();
        adjustDashboardTablesColumns();
      <?php } ?>
  });

  $(window).on('resize', function(){
    adjustDashboardTablesColumns();
  });

  $("#store_id,#warehouse_id,#sales_from_date,#sales_to_date,#users,#search_customer_id").on("change",function(){
      $('#sales_table_dashboard').DataTable().ajax.reload();
  });

  $("#warehouse_id,#purchase_from_date,#purchase_to_date").on("change",function(){
      $('#purchase_table_dashboard').DataTable().destroy();
      load_purchase_datatable();
  });

  $("#warehouse_id,#expense_from_date,#expense_to_date").on("change",function(){
      $('#expense_table_dashboard').DataTable().destroy();
      load_expense_datatable();
  });

  function getCustomerSelectionId() {
    return '#search_customer_id';
  }

  $(document).ready(function() {
      // Initialize Select2 for Customers with AJAX (Replicating logic from sales module)
      if($("#search_customer_id").length > 0){
          $("#search_customer_id").select2({
              allowClear: true,
              placeholder: "Select Customer",
              ajax: {
                  url: '<?= base_url("customers/getCustomers") ?>',
                  type: "post",
                  dataType: 'json',
                  delay: 250,
                  data: function (params) {
                      return {
                          searchTerm: params.term, // search term
                          store_id: $("#store_id").val(),
                      };
                  },
                  processResults: function (response) {
                      return {
                          results: response
                      };
                  },
                  cache: true
              }
          });
      }
  });

  // Action functions for table buttons (if they exist in dashboard rows)
  function pay_now(sales_id) {
    $.post('<?= base_url('sales/show_pay_now_modal') ?>', { sales_id: sales_id }, function (result) {
      $(".pay_now_modal").html('').html(result);
      $('.datepicker').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        todayHighlight: true
      });
      $('#pay_now').modal('toggle');
    });
  }
  function view_payments(sales_id) {
    $.post('<?= base_url('sales/view_payments_modal') ?>', { sales_id: sales_id }, function (result) {
      $(".view_payments_modal").html('').html(result);
      $('#view_payments_modal').modal('toggle');
    });
  }
  function print_invoice(id){
    window.open("<?=base_url();?>pos/print_invoice_pos/"+id, "_blank", "scrollbars=1,resizable=1,height=500,width=500");
  }
  function show_receipt(id){
    window.open("<?=base_url();?>sales/print_show_receipt/"+id, "_blank", "scrollbars=1,resizable=1,height=500,width=500");
  }

  // PORTED BRANCH MANAGEMENT FUNCTIONS
  function update_status(id,status)
  {
    $.post("<?= base_url('store/update_status') ?>",{id:id,status:status},function(result){
      if(result=="1")
      {
        toastr["success"]("Status Updated Successfully!");
        $('#store_table_dashboard').DataTable().ajax.reload();
      }
      else
      {
        toastr["error"]("Status Not Updated. Try Again!");
      }
    });
  }

  function delete_store_with_reason(id){
      swal({
        title: "Are you sure?",
        text: "Please enter the reason for deletion:",
        content: "input",
        icon: "warning",
        buttons: true,
        dangerMode: true,
      })
      .then((reason) => {
        if (reason === null) {
            return; // Cancelled
        }
        if (reason.trim() === "") {
             swal("Error", "Reason is required!", "error");
             return;
        }

        $.post("<?= base_url('store/delete_store') ?>", {q_id: id, reason: reason}, function(result){
          if(result=="success"){
            toastr["success"]("Record Deleted Successfully!");
            $('#store_table_dashboard').DataTable().ajax.reload();
          }else if(result=="failed"){
            toastr["error"]("Failed to Delete .Try again!");
          }else{
            toastr["error"](result);
          }
        });
      });
    }

  function restore_store(id){
    swal({
      title: "Are you sure?",
      text: "You want to restore this store?",
      icon: "warning",
      buttons: true,
      dangerMode: false,
    })
    .then((willDelete) => {
      if (willDelete) {
        $.post("<?= base_url('store/restore_store') ?>", {id: id}, function(result){
          if(result=="success"){
            toastr["success"]("Store Restored Successfully!");
            $('#store_table_dashboard').DataTable().ajax.reload();
          }else if(result=="failed"){
            toastr["error"]("Failed to Restore .Try again!");
          }else{
            toastr["error"](result);
          }
        });
      }
    });
  }
</script>

<div class="pay_now_modal"></div>
<div class="view_payments_modal"></div>

<script>
$(document).ready(function() {
    // Superadmin: Customer Support Table Moved to direct placement for reliability

    // DEFINITIVE INITIALIZATION FOR SUPERADMIN STORE LIST
    if($('#example2-store-table').length > 0){
        $('#example2-store-table').DataTable({
          "aLengthMenu": [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, "All"]],
          // Simplified dom that forces Search (f), Length (l), and Buttons (B)
          dom: '<"row"<"col-sm-6"l><"col-sm-6 text-right"Bf>>rtip',
          buttons: {
            buttons: [
              { extend: 'excel', title: 'รายการร้านค้า', className: 'btn bg-teal btn-flat margin-left-10', exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
              { extend: 'print', title: 'รายการร้านค้า', className: 'btn bg-teal btn-flat margin-left-10', exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} }
            ]
          },
          "order": [[ 5, "desc" ]],
          "responsive": true,
          "columnDefs": [
            { "targets": [ 0, 10 ], "orderable": false },
            { "targets" :[0], "className": "text-center" },
          ]
        });
    }
});
</script>
</body>
</html>
