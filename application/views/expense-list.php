<!DOCTYPE html>
<html>

<head>
<!-- TABLES CSS CODE -->
<?php include"comman/code_css.php"; ?>
<link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/datepicker/datepicker3.css">
</head>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <!-- Left side column. contains the logo and sidebar -->
  
  <?php include"sidebar.php"; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <?= $this->lang->line('expenses_list'); ?>
        <small>View/Search Expenses</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active"><?= $this->lang->line('expenses_list'); ?></li>
      </ol>
    </section>

    <!-- Main content -->
    <?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
    <input type="hidden" id='base_url' value="<?=$base_url;?>">
    <section class="content">
      <div class="row">
        <!-- ********** ALERT MESSAGE START******* -->
          <?php include"comman/code_flashdata.php"; ?>
            <!-- ********** ALERT MESSAGE END******* -->
        <div class="col-xs-12">
          <div class="box box-primary">
            <div class="box-header ">
              <h3 class="box-title">&nbsp;</h3>
              <?php if($CI->permissions('expense_add')) { ?>
              <div class="box-tools">
                <a class="btn btn-block btn-success" href="<?php echo $base_url; ?>expense/add">
                <i class="fa fa-plus"></i> <?= $this->lang->line('new_expense'); ?></a>
              </div>
              <?php } ?>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="expense_from_date"><?= $this->lang->line('from_date'); ?> </label>
                    <div class="input-group date">
                      <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                      </div>
                      <input type="text" class="form-control pull-right datepicker" id="expense_from_date" name="expense_from_date" onchange="load_datatable_with_destroy()">
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="expense_to_date"><?= $this->lang->line('to_date'); ?> </label>
                    <div class="input-group date">
                      <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                      </div>
                      <input type="text" class="form-control pull-right datepicker" id="expense_to_date" name="expense_to_date" onchange="load_datatable_with_destroy()">
                    </div>
                  </div>
                </div>
              </div>
              <table id="example2" class="table table-bordered " width="100%">
                <thead class="bg-success ">
                <tr>
                  <th class="text-center">
                    <input type="checkbox" class="group_check checkbox" >
                  </th>
                  <!-- <th><?= $this->lang->line('store_name'); ?></th> -->
                  <th class="text-center"><?= $this->lang->line('date'); ?></th>
                  <th  class="text-center"><?= $this->lang->line('category'); ?></th>
                  <th  class="text-center"><?= $this->lang->line('reference_no'); ?></th>
                  <th  class="text-center"><?= $this->lang->line('expense_for'); ?></th>
                  <th  class="text-center"><?= $this->lang->line('amount'); ?></th>
                  <th  class="text-center"><?= $this->lang->line('account'); ?></th>
                  <th  class="text-center"><?= $this->lang->line('note'); ?></th>
                  <th  class="text-center"><?= $this->lang->line('created_by'); ?></th>
                  <th  class="text-center"><?= $this->lang->line('action'); ?></th>
                </tr>
                </thead>
                <tbody>
				
                </tbody>
                <tfoot>
                  <tr class="bg-gray">
                      <th></th>
                      <th></th>
                      <th></th><!-- 7 -->
                      <th></th><!-- 7 -->
                      <th style="text-align:right">รวม</th>
                      <th></th>
                      <th></th>
                      <th></th>
                      <th></th><!-- 7 -->
                      <th></th><!-- 7 -->
                  </tr>
              </tfoot>
               
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
    <?= form_close();?>
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

<script type="text/javascript">
$(document).ready(function() {
    //datatables
   var table = $('#example2').DataTable({ 
        "aLengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
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
         //   { extend: 'copy', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7]} },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7]} },
         //   { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7]} },
            { extend: 'print', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7]} },
         //   { extend: 'csv', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7]} },
            { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat',footer: true, text:'Columns' },  

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
            "url": "<?php echo site_url('expense/ajax_list')?>",
            "type": "POST",
            "data": function (d) {
              d.expense_from_date = $("#expense_from_date").val();
              d.expense_to_date = $("#expense_to_date").val();
            },
            complete: function (data) {
             $('.column_checkbox').iCheck({
                checkboxClass: 'icheckbox_square-orange',
                /*uncheckedClass: 'bg-white',*/
                radioClass: 'iradio_square-orange',
                increaseArea: '10%' // optional
              });
             call_code();
              //$(".delete_btn").hide();
             },

        },

        //Set column definition initialisation properties.
        "columnDefs": [
        { 
            "targets": [ 0,9 ], //first column / numbering column
            "orderable": false, //set not orderable
        },
        {
            "targets" :[0],
            "className": "text-center",
        },
        
        ],
         /*Start Footer Total*/
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
            // Remove the formatting to get integer data for summation
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };
          
            var total = api
                .column( 5, { page: 'none'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            //$( api.column( 0 ).footer() ).html('Total');
            $( api.column( 5 ).footer() ).html(to_Fixed(total));
        },
        /*End Footer Total*/
        
    });
    new $.fn.dataTable.FixedHeader( table );
});

function load_datatable_with_destroy(){
    $('#example2').DataTable().destroy();
    load_datatable();
}
// This function name should match what's used in onchange or just use jQuery events
function load_datatable(){
    // Re-initialize logic if needed, but since the original script initializes inside (document).ready, 
    // I need to refactor it slightly.
}
// Actually, let's just use jQuery to trigger reload or re-initialize.
// The original code was inside (document).ready.
</script>

<script src="<?php echo $theme_link; ?>plugins/datepicker/bootstrap-datepicker.js"></script>
<script type="text/javascript">
  //Date picker
    $('.datepicker').datepicker({
      autoclose: true,
    format: 'dd-mm-yyyy',
     todayHighlight: true
    });

    $("#expense_from_date, #expense_to_date").on("change",function(){
        $('#example2').DataTable().ajax.reload();
    });
</script>

<script src="<?php echo $theme_link; ?>js/expense.js"></script>
<!-- Make sidebar menu hughlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
		
</body>
</html>
