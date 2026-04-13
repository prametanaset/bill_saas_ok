<!DOCTYPE html>
<html>
<head>
<!-- TABLES CSS CODE -->
<?php include"comman/code_css.php"; ?>
<style>
  @media(max-width: 480px){
  .small-box h3 {
    font-size: 23px;
    font-weight: bold;
    margin: 0 0 10px 0;
    white-space: nowrap;
    padding: 0;
}
}
</style>
<!-- bootstrap datepicker -->
<link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/datepicker/datepicker3.css">
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <!-- Left side column. contains the logo and sidebar -->
  
  <?php include"sidebar.php"; ?>
  <?php 
      /*Total Invoices*/
      $total_invoices = $this->db->select("COUNT(*) as total")->from("db_invoice")->where("store_id", get_current_store_id())->get()->row()->total;
  ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <?=$page_title;?>
        <small>View/Search Invoices</small>
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
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <!-- ********** ALERT MESSAGE START******* -->
              <?php include "comman/code_flashdata.php";?>
              <!-- ********** ALERT MESSAGE END******* -->
      </div>

      <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box " >
            <span class="info-box-icon bg-5" ><i class="fa fa-file-text-o"></i></span>
            <div class="info-box-content">
              <span class="info-box-text" >Total Invoices</span>
              <span class="info-box-number"><?=$total_invoices;?></span>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?=$page_title;?></h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="from_date"><?= $this->lang->line('from_date'); ?> </label>
                    <div class="input-group date">
                      <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                      </div>
                      <input type="text" class="form-control pull-right datepicker" id="from_date" name="from_date" onchange="load_datatable_with_destroy()">
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="to_date"><?= $this->lang->line('to_date'); ?> </label>
                    <div class="input-group date">
                      <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                      </div>
                      <input type="text" class="form-control pull-right datepicker" id="to_date" name="to_date" onchange="load_datatable_with_destroy()">
                    </div>
                  </div>
                </div>
              </div>
              <table id="example2" class="table table-bordered table-striped custom_hover" width="100%">
                <thead class="bg-primary ">
                <tr>
                  <th class="text-center">
                    <input type="checkbox" class="group_check checkbox" >
                  </th>
                  <th class='text-center'><?= $this->lang->line('date'); ?></th>
                  <th class='text-center'><?= $this->lang->line('invoice_no'); ?></th>
                  <th class='text-center'><?= $this->lang->line('sales_code'); ?></th>
                  <th class='text-center'><?= $this->lang->line('customer_name'); ?></th>
                  <th class='text-center'><?= $this->lang->line('grand_total'); ?></th>
                  <th class='text-center'><?= $this->lang->line('created_by'); ?></th>
                  <th><?= $this->lang->line('action'); ?></th>
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
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
    <?= form_close();?>
  </div>
  <!-- /.content-wrapper -->
  <?php include"footer.php"; ?>
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<!-- SOUND CODE -->
<?php include"comman/code_js_sound.php"; ?>
<!-- TABLES CODE -->
<?php include"comman/code_js.php"; ?>
<!-- bootstrap datepicker -->
<script src="<?php echo $theme_link; ?>plugins/datepicker/bootstrap-datepicker.js"></script>
<script type="text/javascript">
  //Date picker
    $('.datepicker').datepicker({
      autoclose: true,
    format: 'dd-mm-yyyy',
     todayHighlight: true
    });

    function load_datatable(){
        var table = $('#example2').DataTable({ 
          "aLengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
          dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>tip',
          buttons: {
              buttons: [
                  {
                      className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left',
                      text: 'Delete',
                      action: function ( e, dt, node, config ) {
                          multi_delete();
                      }
                  },
                  { extend: 'excel', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6]} },
                  { extend: 'print', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6]} },
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
                  "url": "<?php echo site_url('invoices/ajax_list')?>",
                  "type": "POST",
                  "data": function (d) {
                      d.from_date = $("#from_date").val();
                      d.to_date = $("#to_date").val();
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
              { 
                  "targets": [ 0, 7 ],
                  "orderable": false,
              },
              {
                  "targets" :[0],
                  "className": "text-center",
              },
              ],
          });
          new $.fn.dataTable.FixedHeader( table );
      }
      $(document).ready(function() {
         load_datatable();
      });
      function load_datatable_with_destroy(){
          $('#example2').DataTable().destroy();
          load_datatable();
      }

    function invoice_delete_record(id){
        if(confirm("Do You Wants to Delete Record ?")){
            var base_url = $("#base_url").val();
            var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
            var csrf_hash = '<?php echo $this->security->get_csrf_hash(); ?>';
            
            var data = {q_id:id};
            data[csrf_name] = csrf_hash;

            $.post(base_url+'invoices/delete_invoice',data,function(result){
                if($.trim(result)=="success") {
                    toastr["success"]("Record Deleted Successfully!");
                    load_datatable_with_destroy();
                } else {
                    toastr["error"](result);
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                toastr["error"]("Server Error: " + errorThrown);
            });
        }
    }

    function multi_delete(){
        if(confirm("Are you sure ?")){
            var base_url=$("#base_url").val();
            var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
            var csrf_hash = '<?php echo $this->security->get_csrf_hash(); ?>';

            var data = $("#table_form").serializeArray();
            data.push({name: csrf_name, value: csrf_hash});

            $.post(base_url+'invoices/multi_delete',data,function(result){
                if($.trim(result)=="success") {
                    toastr["success"]("Records Deleted Successfully!");
                    load_datatable_with_destroy();
                } else {
                    toastr["error"](result);
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                toastr["error"]("Server Error: " + errorThrown);
            });
        }
    }
</script>
<!-- Make sidebar menu hughlighter/selector -->
<script>$(".invoice_list-active-li").addClass("active");</script>
</body>
</html>
