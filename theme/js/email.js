
/*Email validation code end*/
$('.send').on("click",function (e) {
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

    // *** SYNC wysihtml5 editor content to textarea BEFORE validation ***
    // wysihtml5 renders in a sandboxed iframe - we must manually sync
    try {
        var wysiData = $('#compose-textarea').data("wysihtml5");
        if(wysiData && wysiData.editor) {
            var editorContent = wysiData.editor.getValue();
            $("#compose-textarea").val(editorContent);
        } else {
            // Fallback: read from iframe body directly
            var iframe = $('.wysihtml5-sandbox');
            if(iframe.length > 0) {
                var iframeContent = iframe.contents().find('body').html();
                if(iframeContent && iframeContent.trim() !== '' && iframeContent !== '<br>') {
                    $("#compose-textarea").val(iframeContent);
                }
            }
        }
    } catch(syncErr) {
        console.warn("wysihtml5 sync warning: " + syncErr.message);
    }

    //Validate Input box or selection box should not be blank or empty
	check_field("email_to");
	check_field("email_subject");
	

    
	if(flag==false)
    {
		toastr["warning"]("มีข้อมูลบางรายการหายไป !")
		return;
    }


    var attachment = $('input[name="attachment"]').val();
    // ตรวจสอบจาก textarea ที่ sync แล้ว (หรือตรวจสอบ document_id ว่ามีการเลือกเอกสารแนบ PDF)
    var docId = $("#document_id").val();
    var textareaContent = $("#compose-textarea").val().replace(/<[^>]*>/g, '').trim(); // strip HTML tags
    if(textareaContent == '' && !attachment && !docId){
        toastr["warning"]("เนื้อหาอีเมล ไม่ควรเว้นว่าง!! (โปรดระบุหรือแนบเอกสาร)");
        return;
    }

    // แสดง overlay loading ก่อน
    var this_id = this.id;
    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $("#"+this_id).attr('disabled', true);

    // ตรวจสอบ: email_to ต้องไม่มี placeholder ค้างอยู่ก่อนส่ง
    var emailToVal = $("#email_to").val().trim();
    if(/\{\{\$/.test(emailToVal) || /\[\[\$/.test(emailToVal)) {
        // มี placeholder ค้างอยู่ — พยายาม replace ด้วย current_doc_data อีกครั้ง
        if(current_doc_data && current_doc_data.customer_email) {
            emailToVal = emailToVal.replace(/\{\{\$customer_email\}\}/g, current_doc_data.customer_email);
            emailToVal = emailToVal.replace(/\{\{\$[a-zA-Z_]+\}\}/g, '').replace(/\[\[\$[a-zA-Z_]+\]\]/g, '').trim();
            $("#email_to").val(emailToVal);
        } else {
            toastr["warning"]("กรุณาระบุอีเมลผู้รับ หรือเลือกเอกสารก่อนกดส่ง");
            $(".overlay").remove();
            $("#"+this_id).attr('disabled', false);
            return;
        }
    }

	$("#email-form").submit();

   

});





//On Enter Move the cursor to desigtation Id
function shift_cursor(kevent,target){

    if(kevent.keyCode==13){
		$("#"+target).focus();
    }
	
}

$('#update').on("click",function (e) {
	var base_url=$("#base_url").val();
    
    var this_id=this.id;
    
   // swal({ title: "Are you sure?",icon: "warning",buttons: true,dangerMode: true,}).then((sure) => {
	if(confirm("Are you sure ?")) {//confirmation start
		$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
		$("#"+this_id).attr('disabled',true);  //Enable Save or Update button
		e.preventDefault();
		data = new FormData($('#api-form')[0]);//form name
		$.ajax({
			type: 'POST',
			url: base_url+'email/api_update',
			data: data,
			cache: false,
			contentType: false,
			processData: false,
			success: function(result){
				//alert(result);//return;

					if(result=="success")
					{
						//window.location=base_url+"sales";
						location.reload();
					}
					else if(result=="failed")
					{
					   toastr['error']("บันทึกข้อมูลไม่สำเร็จ พยายามอีกครั้ง ");
					}
					else
					{
						swal(result);
					}
				
				$("#"+this_id).attr('disabled',false);  //Enable Save or Update button
				$(".overlay").remove();
		   }
	   });
	} //confirmation sure
	//}); //confirmation end

});

// Global to store document data for later use in template application
var current_doc_data = null;

// Handle Template Selection
$("#template_id").on("change", function() {
    var template_id = $(this).val();
    var template_text = $(this).find("option:selected").text();
    var base_url = $("#base_url").val();

    // Filter Document Type based on ETAX template selection
    if(template_text.indexOf("ETAX - Invoice") !== -1 || template_text.indexOf("ETAX-Invoice") !== -1) {
        $("#document_type option").each(function() {
            var val = $(this).val();
            // Show only Sales, Sales Return, Sales Debit (and empty option)
            if(val == "" || val == "Sales" || val == "Sales Return" || val == "Sales Debit") {
                $(this).prop('disabled', false).show();
            } else {
                $(this).prop('disabled', true).hide();
            }
        });
        
        // If current selection is now hidden, reset it
        var current_doc_type = $("#document_type").val();
        if(current_doc_type != "" && current_doc_type != "Sales" && current_doc_type != "Sales Return" && current_doc_type != "Sales Debit") {
            $("#document_type").val("").trigger("change");
        }
    } else {
        // Show all options
        $("#document_type option").prop('disabled', false).show();
    }
    // Refresh Select2 to reflect hidden/disabled states
    $("#document_type").select2();

    if(template_id != "") {
        $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
        $.ajax({
            type: 'POST',
            url: base_url + 'email_templates/get_template_content',
            data: { id: template_id, [csrf_token_name]: csrf_token_value },
            dataType: 'json',
            success: function(res) {
                // Update Body
                update_editor_content(apply_placeholders(res.content, current_doc_data));
                
                // Update To (if defined)
                if(res.to_email) {
                    var to_val = apply_placeholders(res.to_email, current_doc_data);
                    // ตรวจสอบว่าไม่มี placeholder ค้างอยู่ก่อน set ค่า
                    var has_unresolved = /[\[\{]{2}\$[\]\}]{0,2}/.test(to_val);
                    if(to_val && !has_unresolved) {
                        $("#email_to").val(to_val);
                    } else if(!to_val) {
                        // empty is ok, don't override existing value
                    }
                    // ถ้า to_val ยังมี placeholder และ current_doc_data ยังไม่พร้อม
                    // ให้รอก่อน — email_to จะถูก set จาก get_document_details แทน
                }
                
                // Update CC (if defined)
                if(res.cc) {
                    var cc_val = apply_placeholders(res.cc, current_doc_data);
                    if(cc_val) {
                      $("#email_cc").val(cc_val);
                      $("#cc-row").show(); // Ensure CC row is visible if we populate it
                    }
                }

                // Update Subject (if defined)
                if(res.subject) {
                    var subject_val = apply_placeholders(res.subject, current_doc_data);
                    if(subject_val) $("#email_subject").val(subject_val);
                }

                $(".overlay").remove();
            },
            error: function() {
                toastr["error"]("ไม่สามารถดึงข้อมูลเทมเพลตได้ !");
                $(".overlay").remove();
            }
        });
    }
});

function apply_placeholders(text, data) {
    if(!text) return "";
    // ถ้า data เป็น null/undefined ให้ลบ placeholder ออก (ป้องกัน {{$customer_email}} ถูกส่งดิบๆ)
    if(!data) {
        // ลบ [[...]] และ {{...}} placeholders ทั้งหมดออก
        return text
            .replace(/\[\[\$[a-zA-Z_]+\]\]/g, '')
            .replace(/\{\{\$[a-zA-Z_]+\}\}/g, '');
    }

    var result = text;
    // Core ETDA/E-TAX placeholders
    result = result.replace(/\[\[\$sales_date\]\]/g, "[" + (data.sales_date_be || "") + "]");
    result = result.replace(/\[\[\$doc_tag\]\]/g, "[" + (data.doc_tag || "") + "]");
    result = result.replace(/\[\[\$sales_code\]\]/g, "[" + (data.sales_code || "") + "]");
    
    // Reference code - only add brackets if reference exists
    if(data.ref_code) {
        result = result.replace(/\[\[\$ref_code\]\]/g, "[" + data.ref_code + "]");
    } else {
        result = result.replace(/\[\[\$ref_code\]\]/g, "");
    }

    // Customer email variable
    result = result.replace(/\{\{\$customer_email\}\}/g, data.customer_email || "");

    // Catch-all: ลบ placeholder ที่ยังไม่ถูก replace ออก
    result = result
        .replace(/\[\[\$[a-zA-Z_]+\]\]/g, '')
        .replace(/\{\{\$[a-zA-Z_]+\}\}/g, '');

    return result;
}

// Document Type Change
$("#document_type").on("change", function() {
    var type = $(this).val();
    var base_url = $("#base_url").val();
    // Reset document selection
    $("#document_id").empty().append('<option value="">Select Document (เลือกเอกสาร)</option>');
    if(type != "") {
        $("#document_id").select2({
            ajax: {
                url: base_url + 'email/get_documents_json',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        term: params.term,
                        type: type
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            },
            minimumInputLength: 0
        });
    }
});

// Document Selection Change
$("#document_id").on("change", function() {
    var doc_id = $(this).val();
    var doc_type = $("#document_type").val();
    var base_url = $("#base_url").val();
    if(doc_id != null && doc_id != "") {
        $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
        $.ajax({
            type: 'GET',
            url: base_url + 'email/get_document_details',
            data: { id: doc_id, type: doc_type },
            dataType: 'json',
            success: function(result) {
                if(result.status == 'success') {
                    // Update metadata for placeholders
                    current_doc_data = result.extra;

                    // Update Content
                    update_editor_content(result.content);
                    // Update To
                    if(result.email) {
                        $("#email_to").val(result.email);
                    }
                    // Update Subject
                    if(result.subject) {
                        $("#email_subject").val(result.subject);
                    }

                    // If a template is picked, re-apply placeholders from the template
                    var template_id = $("#template_id").val();
                    if(template_id != "") {
                        // Re-trigger template loading or manually apply if we want to be efficient
                        // For now, let's just trigger a re-load of template content to be safe and consistent
                        $("#template_id").trigger("change");
                    }
                }
                $(".overlay").remove();
            },
            error: function() {
                toastr["error"]("ไม่สามารถดึงข้อมูลเอกสารได้ !");
                $(".overlay").remove();
            }
        });
    }
});

function update_editor_content(content) {
    try {
        var wysiData = $('#compose-textarea').data("wysihtml5");
        var editorSet = false;
        
        if(wysiData && wysiData.editor){
            wysiData.editor.setValue(content);
            editorSet = true;
        } 
        
        if(!editorSet){
            // Fallback: Try setting iframe content directly if editor object is missing/broken
            var iframe = $('.wysihtml5-sandbox');
            if(iframe.length > 0){
                iframe.contents().find('body').html(content);
                editorSet = true;
            }
        }

        // Always sync to the underlying textarea as well
        $("#compose-textarea").val(content);
        
    } catch(err) {
        console.error("Error setting content: " + err.message);
        $("#compose-textarea").val(content);
    }
}

// Show attachment info when file is selected
$('input[name="attachment"]').on("change", function() {
    var file = this.files[0];
    if (file) {
        var fileSizeMB = (file.size / 1024 / 1024).toFixed(2); // MB
        
        // 3MB limit validation
        if(parseFloat(fileSizeMB) > 3.0) {
            toastr["error"]("ไฟล์มีขนาดใหญ่เกินไป! กรุณาแนบไฟล์ขนาดไม่เกิน 3.0 MB (ขนาดปัจจุบัน: " + fileSizeMB + " MB)");
            reset_attachment();
            return;
        }

        var html = '<div class="alert alert-info alert-dismissible" style="padding: 15px; margin-bottom: 0; border-left: 5px solid #00c0ef;">';
        // Larger close button (X)
        html += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true" onclick="reset_attachment()" style="font-size: 30px; opacity: 0.8; right: 10px; top: 5px;">×</button>';
        html += '<h4><i class="icon fa fa-file"></i> เอกสารแนบบร้อยแล้ว!</h4>';
        html += '<strong>ชื่อไฟล์:</strong> ' + file.name + '<br>';
        html += '<strong>ขนาด:</strong> ' + fileSizeMB + ' MB';
        html += '</div>';
        $('#attachment-info').html(html);
    } else {
        $('#attachment-info').html('');
    }
});

function reset_attachment() {
    $('input[name="attachment"]').val('');
    $('#attachment-info').html('');
}

// CC and BCC Toggles
$("#toggle-cc").on("click", function() {
    $("#cc-row").toggle();
});
$("#toggle-bcc").on("click", function() {
    $("#bcc-row").toggle();
});