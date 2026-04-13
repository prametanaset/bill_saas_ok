<!DOCTYPE html>
<html>
<head>
<!-- TABLES CSS CODE -->
<?php $this->load->view('comman/code_css.php');?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php $this->load->view('sidebar');?>
  <?php
	if(!isset($this->lang)){
		$this->load->library('language');
	}
  ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>
        Offline Requests
        <small>Bank Transfer Verifications</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Offline Requests</li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">Requests List</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn <?= ($offline_auto_email == 1) ? 'btn-success' : 'btn-danger' ?> btn-sm" id="btn-toggle-email" onclick="toggle_auto_email(<?= ($offline_auto_email == 1) ? 0 : 1 ?>)">
                  <i class="fa <?= ($offline_auto_email == 1) ? 'fa-envelope' : 'fa-envelope-o' ?>"></i> 
                  Auto Email: <?= ($offline_auto_email == 1) ? 'ON' : 'PAUSED' ?>
                </button>
              </div>
            </div>
            <div class="box-body">
              <table id="example2" class="table table-bordered table-striped" width="100%">
                <thead>
                <tr>
                  <th>ID</th>
                  <th>Store</th>
                  <th>Package</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Amount</th>
                  <th>Slip</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <?php $this->load->view('footer');?>
</div>
<?php $this->load->view('comman/code_js.php');?>

<script>
var table;
$(document).ready(function() {
    // Datatables initialization
    table = $('#example2').DataTable({ 
        "processing": true,
        "serverSide": false, // Handled as simple JSON for now
        "order": [], 
        "responsive": true,
        "ajax": {
            "url": "<?php echo site_url('offline_requests/ajax_list')?>",
            "type": "POST",
            "data": {
                "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>"
            }
        },
        "columnDefs": [
            { "targets": [ 6, 7, 8 ], "orderable": false }
        ],
    });

    // Automatic polling every 60 seconds
    setInterval(function(){
        table.ajax.reload(null, false); // user paging is not reset on reload
    }, 60000); 
});

function update_status(id, status) {
    if(confirm("Are you sure?")) {
        var btn = event.target;
        var originalHtml = $(btn).html();
        $(btn).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Wait...');
        
        toastr.info('Processing... Please wait. Generating PDF and sending email might take a few seconds.', '', {timeOut: 10000});

        $.post('<?= base_url("offline_requests/update_status") ?>', {id: id, status: status, <?php echo $this->security->get_csrf_token_name();?>: '<?php echo $this->security->get_csrf_hash();?>'}, function(res){
            if(res == 'success') {
                toastr.success('Action successfully processed!');
                table.ajax.reload(null, false);
            } else {
                alert('Failed to update status: ' + res);
                $(btn).prop('disabled', false).html(originalHtml);
            }
        }).fail(function(err){
            alert('Server Error. Please check logs.');
            $(btn).prop('disabled', false).html(originalHtml);
        });
    }
}

function delete_request(id){
    if(confirm("Are you sure you want to delete this request? This cannot be undone.")) {
        $.post('<?= base_url("offline_requests/delete_request") ?>', {id: id, <?php echo $this->security->get_csrf_token_name();?>: '<?php echo $this->security->get_csrf_hash();?>'}, function(res){
            if(res == 'success') {
                toastr.success('Request Deleted Successfully');
                table.ajax.reload(null, false);
            } else {
                toastr.error('Failed to delete: ' + res);
            }
        });
    }
}
function toggle_auto_email(new_status) {
    var label = (new_status == 1) ? "Enable" : "Pause";
    if(confirm("Are you sure you want to " + label + " automatic emails?")) {
        $.post("<?= base_url('offline_requests/toggle_auto_email') ?>", {status: new_status}, function(result) {
            if(result == 'success') {
                toastr.success("Auto Email status updated!");
                location.reload();
            } else {
                toastr.error("Failed to update status.");
            }
        });
    }
}
</script>
</body>
</html>
