<!DOCTYPE html>
<html>

<head>
<!-- TABLES CSS CODE -->
<?php include"comman/code_css.php"; ?>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
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
        <?=$page_title;?>
        <small>View Payments</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active"><?=$page_title;?></li>
      </ol>
    </section>

    <!-- Main content -->
    <?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
    <input type="hidden" id='base_url' value="<?=$base_url;?>">
    <section class="content">
      <!-- /.row -->
      <div class="row">
        <!-- ********** ALERT MESSAGE START******* -->
        <?php include"comman/code_flashdata.php"; ?>
        <!-- ********** ALERT MESSAGE END******* -->
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title"><?=$page_title;?></h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="row">
                <!-- Store Filter -->
                <?php //if(true) {$this->load->view('store/store_code',array('show_store_select_box'=>true,'div_length'=>'col-md-3','show_all'=>'true','form_group_remove' => 'true')); }?> 
                
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="from_date"><?= $this->lang->line('from_date'); ?></label>
                    <div class="input-group date">
                      <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                      </div>
                      <input type="text" class="form-control pull-right datepicker" id="from_date" name="from_date" value="<?=show_date(date('d-m-Y', strtotime('-30 days')))?>">
                    </div>
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label for="to_date"><?= $this->lang->line('to_date'); ?></label>
                    <div class="input-group date">
                      <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                      </div>
                      <input type="text" class="form-control pull-right datepicker" id="to_date" name="to_date" value="<?=show_date(date('d-m-Y'))?>">
                    </div>
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label>&nbsp;</label><br>
                    <button type="button" id="view" class="btn btn-primary btn-block" onclick="table.ajax.reload();" title="Click to Search">
                      <i class="fa fa-search"></i> <?= $this->lang->line('view'); ?> / เรียกดูข้อมูล
                    </button>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-12">
                  <hr style="margin-top: 5px; margin-bottom: 10px;">
                  <div class="pull-right">
                    <?php $this->load->view('components/export_btn',array('tableId' => 'example2'));?>
                  </div>
                </div>
              </div>
              
              <div class="table-responsive">
                <table id="example2" class="table table-bordered custom_hover" width="100%">
                <thead class="bg-gray ">
                <tr>
                  <th>#</th>
                  <th><?= $this->lang->line('store_name'); ?></th>
                  <th><?= $this->lang->line('package_name'); ?></th>
                  <th><?= $this->lang->line('payment_date'); ?></th>
                  <th><?= $this->lang->line('time'); ?></th>
                  <th><?= $this->lang->line('payment_type'); ?></th>
                  <th><?= $this->lang->line('amount'); ?></th>
                  <th><?= $this->lang->line('status'); ?></th>
                  <th><?= $this->lang->line('action'); ?></th>
                </tr>
                </thead>
                <tbody>
				
                </tbody>
                <tfoot>
                    <tr class="bg-gray">
                        <th colspan="6" style="text-align:right">ยอดรวม</th>
                        <th></th>
                        <th></th>
                        <th></th>
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
<!-- TABLE EXPORT CODE -->
<?php include"comman/code_js_export.php"; ?>

<script type="text/javascript">
$(document).ready(function() {
    //datatables
   table = $('#example2').DataTable({ 

      "processing": true, //Feature control the processing indicator.
      "serverSide": true, //Feature control DataTables' server-side processing mode.
      "order": [], //Initial no order.
      "responsive": true,
      
      // Load data for the table's content from an Ajax source
      "ajax": {
          "url": "<?php echo site_url('subscription_payments/ajax_list')?>",
          "type": "POST",
          "data": function ( data ) {
                data.store_id = $('#store_id').val();
                data.from_date = $('#from_date').val();
                data.to_date = $('#to_date').val();
          },
      },

      //Set column definition initialisation properties.
      "columnDefs": [
      { 
          "targets": [ 0 ], //first column / numbering column
          "orderable": false, //set not orderable
      },
      ],
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
 
            // Remove the formatting to get integer data for summation
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };
 
            // Total over all pages
            total = api
                .column( 6 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
 
            // Total over this page
            pageTotal = api
                .column( 6, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
 
            // Update footer
            $( api.column( 6 ).footer() ).html(
                //'Page Total: '+pageTotal +' ( Total: '+ total +')'
                pageTotal
            );
        }
  });

});

// Custom Export Filename
function downloadExcel(tableId){
    $('#'+tableId).tableExport({type:'xlsx',escape:'false',fileName: 'Store Payment'});
}
</script>
<script>
    function delete_payment(id){
      var base_url=$("#base_url").val();
      
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

        $.post(base_url+"subscription_payments/delete_payment", {id: id, reason: reason}, function(result){
          if(result=="success"){
            swal("Deleted!", "Record Deleted Successfully!", "success");
            $('#example2').DataTable().ajax.reload();
          }else if(result=="failed"){
            swal("Error", "Failed to Delete .Try again!", "error");
          }else{
            swal("Error", result, "error");
          }
        });
      });
    }
</script>
</body>
</html>
