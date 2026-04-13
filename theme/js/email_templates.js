
$('#save,#update').on("click",function (e) {
    e.preventDefault();
	var base_url=$("#base_url").val();

    // Sync Wysihtml5 content back to textarea before validation and formData creation
    try {
        var wysiData = $('#content').data("wysihtml5");
        if(wysiData && wysiData.editor){
            var content = wysiData.editor.getValue();
            $('#content').val(content);
        }
    } catch(err) {
        console.error("Error syncing Wysihtml5: " + err.message);
    }

    //Initially flag set true
    var flag=true;

    function check_field(id)
    {
      if(!$("#"+id).val() ) 
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
	check_field("template_name");	
	check_field("content");	

	
    if(flag==false)
    {
		toastr["warning"]("มีข้อมูลบางรายการหายไป !")
		return;
    }

    var this_id=this.id;

    if(this_id=="save" || this_id=="update")
    {
        var target_url = (this_id=="save") ? base_url+'email_templates/newtemplate' : base_url+'email_templates/update_template';
        
        if(confirm("Are you sure ?")) {
            $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
            $("#"+this_id).attr('disabled',true);
            
            try {
                var form = $('#template-form')[0];
                if(!form) {
                    throw new Error("Form #template-form not found!");
                }
                var data = new FormData(form);

                // Explicitly set wysihtml5 content in FormData to ensure it's submitted
                try {
                    var wysi = $('#content').data("wysihtml5");
                    if(wysi && wysi.editor){
                        data.set('content', wysi.editor.getValue());
                    }
                } catch(syncErr) {
                    console.error("Error setting content in FormData: " + syncErr.message);
                }

                $.ajax({
                    type: 'POST',
                    url: target_url,
                    data: data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(result){
                        if(result.trim()=="success")
                        {
                            window.location=base_url+"email_templates/email";
                            return;
                        }
                        else if(result.trim()=="failed")
                        {
                            toastr["error"]("บันทึกข้อมูลไม่สำเร็จ พยายามอีกครั้ง !");
                        }
                        else
                        {
                            toastr["error"](result);
                        }
                        $("#"+this_id).attr('disabled',false);
                        $(".overlay").remove();
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        toastr["error"]("เกิดข้อผิดพลาดในการโหลดข้อมูล (Error "+xhr.status+") !");
                        $("#"+this_id).attr('disabled',false);
                        $(".overlay").remove();
                    }
                });
            } catch(formErr) {
                console.error("Error during form preparation/AJAX: " + formErr.message);
                toastr["error"]("เกิดข้อผิดพลาด: " + formErr.message);
                $("#"+this_id).attr('disabled',false);
                $(".overlay").remove();
            }
        }
    }//Save/Update end
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
	
	$.post(base_url+"email_templates/update_status",{id:id,status:status},function(result){
		if(result=="success")
				{
				  toastr["success"]("อัพเดทข้อมูลสำเร็จ!");
				  success.currentTime = 0; 
				  success.play();
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
				  toastr["error"]("อัพเดทไม่สำเร็จ พยายามอีกครั้ง !");
				  failed.currentTime = 0; 
				  failed.play();
				}
				else{
				 toastr["error"](result);
				 failed.currentTime = 0; 
				 failed.play();
				}
				 return false;
	}).fail(function(xhr, status, error) {
		toastr["error"]("เกิดข้อผิดพลาดในการโหลดข้อมูล (Error "+xhr.status+") !");
	});
}
//update status end

//Delete Record start
function delete_template(q_id)
{
	var base_url=$("#base_url").val();
	if(confirm("Are you sure ?")) {//confirmation start
   	$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
   $.post(base_url+"email_templates/delete_template",{q_id:q_id},function(result){
	   if(result=="success")
				{
					toastr["success"]("ลบข้อมูลเรียบร้อย!");
					$('#example2').DataTable().ajax.reload();
				}
				else if(result=="failed"){
				  	toastr["error"]("ลบไม่สำเร็จ พยายามอีกครั้ง !");
				}
				else{
					toastr["error"](result);
				}
				$(".overlay").remove();
				return false;
   }).fail(function(xhr, status, error) {
		toastr["error"]("เกิดข้อผิดพลาดในการโหลดข้อมูล (Error "+xhr.status+") !");
		$(".overlay").remove();
	});
}
}
//Delete Record end
