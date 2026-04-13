/*Email validation code*/
function validateEmail(sEmail) {
    var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,9}|[0-9]{1,3})(\]?)$/;
    if (filter.test(sEmail)) {
        return true;
    }
    else {
        return false;
    }
}
/*Email validation code end*/

/*$(document).submit(function(event) {
	event.preventDefault();
	if(document.getElementById('save')){
		$("#save").trigger('click');
	}
	else{
		$("#update").trigger('click');	
	}
});*/

$('#save,#update').on("click",function (e) {

	var base_url=$("#base_url").val();
    /*Initially flag set true*/
    var flag=true;

    function check_field(id)
    {

      if(!$("#"+id).val() ) //Also check Others????
        {

            $('#'+id+'_msg').fadeIn(200).show().html('Required Field').addClass('required');
            $('#'+id).css({'background-color' : '#E8E2E9'});
            flag=false;
        }
        else
        {
             $('#'+id+'_msg').fadeOut(200).hide();
             $('#'+id).css({'background-color' : '#FFFFFF'});    //White color
        }
    }


    //Validate Input box or selection box should not be blank or empty
	check_field("customer_name");
	//check_field("mobile");
	//check_field("state");

    var email=$("#email").val();
    if (email!='' && !validateEmail(email)) {
            $("#email_msg").html("Invalid Email!").show();
             //flag=false;
             toastr["warning"]("กรุณาป้อน Email ให้ถูกต้อง.")
			return;
        }
        else{
        	$("#email_msg").html("อีเมลไม่ถูกต้อง!").hide();
        }

	if(flag==false)
    {
		toastr["warning"]("ข้อมูลบางอย่างขาดหายไป!")
		return;
    }

    var this_id=this.id;

    if(this_id=="save")  //Save start
    {

					//if(confirm("Do You Wants to Save Record ?")){
						e.preventDefault();
						data = new FormData($('#customers-form')[0]);//form name
						/*Check XSS Code*/
						if(!xss_validation(data)){ return false; }
						
						$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
						$("#"+this_id).attr('disabled',true);  //Enable Save or Update button
						$.ajax({
						type: 'POST',
						url: base_url+'customers/newcustomers',
						data: data,
						cache: false,
						contentType: false,
						processData: false,
						success: function(result){
             // alert(result);return;
							if(result=="success")
							{
								//alert("Record Saved Successfully!");
								window.location=base_url+"customers";
								return;
							}
							else if(result=="failed")
							{
							   toastr['error']("บันทึกไม่สำเร็จ กรุณาลองใหม่.");
							   //	return;
							}
							else
							{
								toastr['error'](result);
							}
							$("#"+this_id).attr('disabled',false);  //Enable Save or Update button
							$(".overlay").remove();
					   }
					   });
				//}

				//e.preventDefault


    }//Save end
	
	else if(this_id=="update")  //Update start
    {
						
					//if(confirm("Do You Wants to Save Record ?")){
						e.preventDefault();
						data = new FormData($('#customers-form')[0]);//form name
						/*Check XSS Code*/
						if(!xss_validation(data)){ return false; }
						
						$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
						$("#"+this_id).attr('disabled',true);  //Enable Save or Update button
						$.ajax({
						type: 'POST',
						url: base_url+'customers/update_customers',
						data: data,
						cache: false,
						contentType: false,
						processData: false,
						success: function(result){
              //alert(result);return;
							if(result=="success")
							{
								//toastr["success"]("Record Updated Successfully!");
								window.location=base_url+"customers";
								//return;
							}
							else if(result=="failed")
							{
							   //alert("Sorry! Failed to save Record.Try again");
							   toastr["error"]("บันทึกไม่สำเร็จ กรุณาลองใหม่.!");
							   //	return;
							}
							else
							{
								toastr['error'](result);
							}
							$("#"+this_id).attr('disabled',false);  //Enable Save or Update button
							$(".overlay").remove();
							//return;
					   }
					   });
			//	}

				//e.preventDefault


    }//Save end
	

});


//On Enter Move the cursor to desigtation Id
function shift_cursor(kevent,target){

    if(kevent.keyCode==13){
		$("#"+target).focus();
    }
	
}

//update status start
function update_status(id,status)
{
	var base_url=$("#base_url").val();
	$.post(base_url+"customers/update_status",{id:id,status:status},function(result){
		if(result=="success")
        {
          toastr["success"]("บันทึก อัพเดทเรียบร้อย!");
          success.currentTime = 0; 
          success_sound.play();
          if(status==0)
          {
            status="Inactive";
            var span_class="label label-danger";
            $("#span_"+id).attr('onclick','update_status('+id+',1)');
          }
          else{
            status="Active";
             var span_class="label label-success";
             $("#span_"+id).attr('onclick','update_status('+id+',0)');
            }

          $("#span_"+id).attr('class',span_class);
          $("#span_"+id).html(status);
         
        }
        else if(result=="failed"){
          toastr["error"]("อัพเดทไม่สำเร็จ.กรุณาลองใหม่!");
          failed.currentTime = 0; 
          failed_sound.play();
        }
        else{
         toastr["error"](result);
         failed.currentTime = 0; 
         failed_sound.play();
        }
         return false;
	});
}
//update status end


//Delete Record start
function delete_customers(q_id)
{
	var base_url=$("#base_url").val();
   if(confirm("Do You Wants to Delete Record ?")){
   	$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
   $.post(base_url+"customers/delete_customers",{q_id:q_id},function(result){
  // alert(result);return;
	   if(result=="success")
				{
				  toastr["success"]("ลบบันทึก เรียบร้อย!");
				  $('#example2').DataTable().ajax.reload();
				}
				else if(result=="failed"){
				  toastr["error"]("ลบไม่สำเร็จ .ลองอีกครั้ง!");
				}
				else{
				  toastr["error"](result);
				}
				$(".overlay").remove();
				return false;
   });
   }//end confirmation
}
//Delete Record end
function multi_delete(){
	var base_url=$("#base_url").val();
    var this_id=this.id;
    
		if(confirm("Are you sure ?")){
			$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
			$("#"+this_id).attr('disabled',true);  //Enable Save or Update button
			
			data = new FormData($('#table_form')[0]);//form name
			$.ajax({
			type: 'POST',
			url: base_url+'customers/multi_delete',
			data: data,
			cache: false,
			contentType: false,
			processData: false,
			success: function(result){
				result=result;
  //alert(result);return;
				if(result=="success")
				{
					toastr["success"]("ลบบันทึกเรียบร้อย!");
					success.currentTime = 0; 
				  	success_sound.play();
					$('#example2').DataTable().ajax.reload();
					$(".delete_btn").hide();
					$(".group_check").prop("checked",false).iCheck('update');
				}
				else if(result=="failed")
				{
				   toastr["error"]("บันทึกข้อมูลไม่สำเร็จ .ลองอีกครั้ง!");
				   failed.currentTime = 0; 
				   failed_sound.play();
				}
				else
				{
					toastr["error"](result);
					failed.currentTime = 0; 
				  	failed_sound.play();
				}
				$("#"+this_id).attr('disabled',false);  //Enable Save or Update button
				$(".overlay").remove();
		   }
		   });
	}
	//e.preventDefault
}
function pay_now(customer_id){

  $.post($("#base_url").val()+'customers/show_pay_now_modal', {customer_id: customer_id}, function(result) {
    $(".pay_now_modal").html('').html(result);
    //Date picker
    $('.datepicker').datepicker({
      autoclose: true,
    format: 'dd-mm-yyyy',
     todayHighlight: true
    });
    $('#pay_now').modal('toggle');

  });
}
function save_payment(customer_id){
  var base_url=$("#base_url").val();




    //Initially flag set true
    var flag=true;

    function check_field(id)
    {

      if(!$("#"+id).val() ) //Also check Others????
        {

            $('#'+id+'_msg').fadeIn(200).show().html('Required Field').addClass('required');
           // $('#'+id).css({'background-color' : '#E8E2E9'});
            flag=false;
        }
        else
        {
             $('#'+id+'_msg').fadeOut(200).hide();
             //$('#'+id).css({'background-color' : '#FFFFFF'});    //White color
        }
    }


   //Validate Input box or selection box should not be blank or empty
    check_field("amount");
    check_field("payment_date");


    var payment_date=$("#payment_date").val();
    var amount=$("#amount").val();
    var payment_type=$("#payment_type").val();
    var payment_note=$("#payment_note").val();
    var account_id=$("#account_id").val();

    if(amount == 0){
      toastr["error"]("กรุณาป้อนจำนวนเงิน!");
      return false; 
    }

    if(amount > parseFloat($("#amount").attr('data-due-amt'))){
      toastr["error"]("จำนวนเงินที่ป้อน ไม่ควรมากกว่าจำนวนเงินที่ค้างชำระ!");
      return false;
    }

    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $(".payment_save").attr('disabled',true);  //Enable Save or Update button
    $.post(base_url+'customers/save_payment', {account_id:account_id,customer_id: customer_id,payment_type:payment_type,amount:amount,payment_date:payment_date,payment_note:payment_note}, function(result) {
      result=result;
  //alert(result);return;
        if(result=="success")
        {
          $('#pay_now').modal('toggle');
          toastr["success"]("บันทึกการชำระเงินเรียบร้อยแล้ว!");
          success.currentTime = 0; 
          success_sound.play();
          $('#example2').DataTable().ajax.reload();
        }
        else if(result=="failed")
        {
           toastr["error"]("บันทึกไม่สำเร็จ .พยายามอีกครั้ง!");
           failed.currentTime = 0; 
           failed_sound.play();
        }
        else
        {
          toastr["error"](result);
          failed.currentTime = 0; 
          failed_sound.play();
        }
        $(".payment_save").attr('disabled',false);  //Enable Save or Update button
        $(".overlay").remove();
    });
}

function pay_return_due(customer_id){

  $.post($("#base_url").val()+'customers/show_pay_return_due_modal', {customer_id: customer_id}, function(result) {
    $(".pay_return_due_modal").html('').html(result);
    //Date picker
    $('.datepicker').datepicker({
      autoclose: true,
    format: 'dd-mm-yyyy',
     todayHighlight: true
    });
    $('#pay_return_due').modal('toggle');

  });
}
function save_return_due_payment(customer_id){
  var base_url=$("#base_url").val();




    //Initially flag set true
    var flag=true;

    function check_field(id)
    {

      if(!$("#"+id).val() ) //Also check Others????
        {

            $('#'+id+'_msg').fadeIn(200).show().html('Required Field').addClass('required');
           // $('#'+id).css({'background-color' : '#E8E2E9'});
            flag=false;
        }
        else
        {
             $('#'+id+'_msg').fadeOut(200).hide();
             //$('#'+id).css({'background-color' : '#FFFFFF'});    //White color
        }
    }


   //Validate Input box or selection box should not be blank or empty
    check_field("return_due_amount");
    check_field("return_due_payment_date");


    var payment_date=$("#return_due_payment_date").val();
    var amount=$("#return_due_amount").val();
    var payment_type=$("#return_due_payment_type").val();
    var payment_note=$("#return_due_payment_note").val();
    var account_id=$("#account_id").val();

    if(amount == 0){
      toastr["error"]("กรุณากรอกจำนวนเงินที่ถูกต้อง!");
      return false; 
    }

    if(amount > parseFloat($("#return_due_amount").attr('data-due-amt'))){
      toastr["error"]("จำนวนเงินที่ป้อน ไม่ควรมากกว่าจำนวนเงินที่ค้างชำระ!");
      return false;
    }

    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $(".payment_save").attr('disabled',true);  //Enable Save or Update button
    $.post(base_url+'customers/save_return_due_payment', {account_id:account_id,customer_id: customer_id,payment_type:payment_type,amount:amount,payment_date:payment_date,payment_note:payment_note}, function(result) {
      result=result;
  //alert(result);return;
        if(result=="success")
        {
          $('#pay_return_due').modal('toggle');
          toastr["success"]("บันทึกการชำระเงินเรียบร้อยแล้ว!");
          success.currentTime = 0; 
          success_sound.play();
          $('#example2').DataTable().ajax.reload();
        }
        else if(result=="failed")
        {
           toastr["error"]("บันทึกไม่สำเร็จ. พยายามอีกครั้ง!");
           failed.currentTime = 0; 
           failed_sound.play();
        }
        else
        {
          toastr["error"](result);
          failed.currentTime = 0; 
          failed_sound.play();
        }
        $(".return_due_payment_save").attr('disabled',false);  //Enable Save or Update button
        $(".overlay").remove();
    });
}

function delete_opening_balance_entry(entry_id){
 if(confirm("Do You Wants to Delete Record ?")){
    var base_url=$("#base_url").val();
    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
   $.post(base_url+"customers/delete_opening_balance_entry",{entry_id:entry_id,customer_id:$("#q_id").val()},function(result){
   //alert(result);//return;
   result=result;
     if(result=="success")
        { 
          location.reload(true);
        }
        else if(result=="failed"){
          toastr["error"]("ลบไม่สำเร็จ พยายามอีกครั้ง !");
          failed.currentTime = 0; 
          failed_sound.play();
        }
        else{
          toastr["error"](result);
          failed.currentTime = 0; 
          failed_sound.play();
        }
        $(".overlay").remove();
   });
   }//end confirmation   
  }
  /*27-06-2020*/
  function view_payments(customer_id){

  $.post($("#base_url").val()+'customers/view_payment_list_modal', {customer_id: customer_id}, function(result) {
    $(".pay_now_modal").html('').html(result);
    //Date picker
    $('.datepicker').datepicker({
      autoclose: true,
    format: 'dd-mm-yyyy',
     todayHighlight: true
    });
    $('#view_payments_modal').modal('toggle');

  });
}
/*End*/
