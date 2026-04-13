
$("#save").on("click",function(){
      var base_url=$("#base_url").val();
      var currentpass=document.getElementById("current_pass").value;
      var newpass=document.getElementById("pass").value;
      var retypepass=document.getElementById("confirm").value;
      //document.getElementById("change_pass_btn").disabled=true;
      if(currentpass=="")
      {
          toastr["warning"]("ป้อนรหัสผ่าน ปัจจุบัน!");
          document.getElementById("current_pass").focus();
          return;
      }
      if(newpass=="")
      {
         toastr["warning"]("โปรดป้อนรหัสผ่านใหม่!");
         $("#pass").focus();
         return;
      }
      if(retypepass=="")
      {
         toastr["warning"]("โปรดยืนยันรหัสผ่าน!");
         $("#confirm").focus();
         return;
      }
      if(newpass!=retypepass)
      {
         toastr["error"]("รหัสผ่านไม่ตรงกัน !");
         document.getElementById("pass").focus();

         //document.getElementById("change_pass_btn").disabled=false;
         return;
      }
      else
      {

        if(confirm("Are you Sure ?")){
          /*Check XSS Code*/
          if(!xss_validation(currentpass)){ return false; }
          if(!xss_validation(newpass)){ return false; }
          
          //Send data form to php
         $.post(base_url+"users/password_update",{currentpass:currentpass,newpass:newpass},function(result){

            if(result=="success")
            {
                toastr["success"]("อัพเดท รหัสผ่านเรียบร้อย !");
                $("#current_pass,#pass,#confirm").val('');
                success.play();  
                return false;
            }
            else if(result=="failed") {
                toastr["error"]("รหัสผ่าน ไม่อัพเดท ลองอีกครั้ง !");
                failed.play();
                return false;
            }
            else
            {
                toastr["error"](result);
                failed.play();
                return false;
            }

        });
       }

      }
});


//On Enter Move the cursor to desigtation Id
function shift_cursor(kevent,target){

    if(kevent.keyCode==13){
		$("#"+target).focus();
    }
	
}