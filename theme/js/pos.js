
//On Enter Move the cursor to desigtation Id
function shift_cursor(kevent, target) {

  if (kevent.keyCode == 13) {
    $("#" + target).focus();
  }

}
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

function uncheck_allow_tot_advance() {
  //verify is checked ?
  if ($("#allow_tot_advance").is(':checked')) {
    $("#click_to_uncheck").trigger("click");
  }
}

$("#pay_all").on("click", function () {
  save(print = true, pay_all = true);
});


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

  iframe.onload = function () {
    iframe.contentWindow.focus();
    iframe.contentWindow.print();

    // Cleanup the iframe after printing
    setTimeout(() => {
      document.body.removeChild(iframe);
    }, 1000);
  };
}

function save(print = false, pay_all = false) {

  //$('.make_sale').on("click",function (e) {

  var base_url = $("#base_url").val();

  if ($(".items_table tr").length == 1) {
    toastr["warning"]("ไม่มีรายการขาย!!");
    return;
  }



  //RETRIVE ALL DYNAMIC HTML VALUES
  var tot_qty = $(".tot_qty").text();
  var tot_amt = $(".tot_amt").text();
  var tot_disc = $(".tot_disc").text();
  var tot_grand = $(".tot_grand").text();

  var paid_amt = (pay_all) ? tot_grand : $(".sales_div_tot_paid").text();
  var balance = (pay_all) ? 0 : parseFloat($(".sales_div_tot_balance").text());


  /* var paid_amt=$(".sales_div_tot_paid").text();
   var balance=parseFloat($(".sales_div_tot_balance").text());*/
  //var walk_in_customer_name=$("#walk_in_customer_name").text();
  var customer_id = $("#customer_id").val();

  /* walk_in_customer_name defined in pos.php */
  if ($('option:selected', "#customer_id").attr('data-delete_bit') == 1 && balance != 0) {
    toastr["warning"]("Walk-in Customer ต้องจ่ายเต็มจำนวน!!");
    return;
  }
  if (document.getElementById("sales_id")) {
    var command = 'update';
  }
  else {
    var command = 'save';
  }
  var this_btn = 'make_sale';

  //swal({ title: "Are you sure?",icon: "warning",buttons: true,dangerMode: true,}).then((sure) => {
  // if(sure) {//confirmation start


  $("." + this_btn).attr('disabled', true);  //Enable Save or Update button
  //e.preventDefault();
  var data = new Array(2);
  data = new FormData($('#pos-form')[0]);//form name
  /*Check XSS Code*/
  if (!xss_validation(data)) { return false; }

  $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
  $.ajax({
    type: 'POST',
    url: base_url + 'pos/pos_save_update?command=' + command + '&tot_qty=' + tot_qty + '&tot_amt=' + tot_amt + '&tot_disc=' + tot_disc + '&tot_grand=' + tot_grand + "&paid_amt=" + paid_amt + '&balance=' + balance + "&pay_all=" + pay_all,
    data: data,
    cache: false,
    contentType: false,
    processData: false,
    success: function (result) {
      //alert(result);//return;
      if (result.indexOf("LIMIT_EXCEEDED") > -1) {
        var msg = result.split(":")[1];
        if (confirm(msg + "\n\nDo you want to upgrade your plan?")) {
          var targetUrl = base_url + 'subscription/expired';
          var win = window.open(targetUrl, '_blank');
          if (!win || win.closed || typeof win.closed == 'undefined') {
            //Popup blocked, fallback to current window
            window.location.href = targetUrl;
          }
        }
        return;
      }

      result = result.split("<<<###>>>");

      if (result[0] == "success") {
        toastr['success']("บันทึกแล้ว!!");
        try {
          success.currentTime = 0;
          success.play();
        } catch (e) { console.log(e); }

        var warehouse_id = $("#warehouse_id").val();
        var print_done = true;
        if (print) {
          printTemplate(base_url + "pos/print_invoice_pos/" + result[1]);
        }
        if (print_done) {
          if (command == 'update') {
            window.location = base_url + "sales";
          }
          else {
            $(".items_table > tbody").empty();
            $(".discount_input").val(0);

            uncheck_allow_tot_advance();

            $('#multiple-payments-modal').modal('hide');
            var rc = $("#payment_row_count").val();
            while (rc > 1) {
              remove_row(rc);
              rc--;
            }
            $("#pos-form")[0].reset();

            $("#customer_id").find(':selected').attr('data-tot_advance', to_Fixed(result[4])).trigger('change');

            $("#search_it").val('');

            if (warehouse_module) {
              $("#warehouse_id").val(warehouse_id).select2();
            }

            final_total();
            get_details(null, true);
            hold_invoice_list();
            get_coupon_details();
          }

        }
        $("#init_code").val(result[2]);
        $("#count_id").val(result[3]);

      }
      else if (result[0] == "failed") {
        toastr['error']("บันทึกไม่ได้. ลองอีกครั้ง");
      }
      else {
        alert(result);
      }

    }
  }).fail(function () {
    toastr['error']("เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์");
  }).always(function () {
    $("." + this_btn).attr('disabled', false);
    $(".overlay").remove();
  });
  //} //confirmation sure
  //}); //confirmation end

  //e.preventDefault


  //});
}



/* *********************** HOLD INVOICE START****************************/
$('#hold_invoice').on("click", function (e) {

  //table should not be empty
  if ($(".items_table tr").length == 1) {
    toastr["error"]("โปรด เลือกสินค้า!!");
    failed.currentTime = 0;
    failed.play();
    return;
  }

  swal({
    title: "พักบิลขาย ? ถ้าชื่อบิลซ้ำ บิลเก่าจะถูกบันทึกทับ!!", icon: "warning", buttons: true, dangerMode: true,
    content: {
      element: "input", attributes:
      {
        placeholder: "โปรด ป้อนชื่อบิล  !",
        type: "text",

        inputAttributes: {
          maxlength: '5'
        }
      },
    },
  }).then(name => {
    //If input box blank Throw Error
    if (!name) { throw null; return false; }
    var reference_id = name;
    /* ********************************************************** */
    var base_url = $("#base_url").val();

    //RETRIVE ALL DYNAMIC HTML VALUES
    var tot_qty = $(".tot_qty").text();
    var tot_amt = $(".tot_amt").text();
    var tot_disc = $(".tot_disc").text();
    var tot_grand = $(".tot_grand").text();
    var hidden_rowcount = $("#hidden_rowcount").val();

    var this_id = this.id;//id=save or id=update

    e.preventDefault();
    data = new FormData($('#pos-form')[0]);//form name
    /*Check XSS Code*/
    if (!xss_validation(data)) { return false; }

    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $("#" + this_id).attr('disabled', true);  //Enable Save or Update button				
    $.ajax({
      type: 'POST',
      url: base_url + 'pos/hold_invoice?command=' + this_id + '&tot_qty=' + tot_qty + '&tot_amt=' + tot_amt + '&tot_disc=' + tot_disc + '&tot_grand=' + tot_grand + "&reference_id=" + reference_id,
      data: data,
      cache: false,
      contentType: false,
      processData: false,
      success: function (result) {
        //alert(result);return;
        $("#hidden_invoice_id").val('');
        result = result.split("<<<###>>>");

        if (result[0] == "success") {
          $('#pos-form-tbody').html('');
          //CALCULATE FINAL TOTAL AND OTHER OPERATIONS
          final_total();

          hold_invoice_list();
          try {
            success.currentTime = 0;
            success.play();
          } catch (e) { console.log(e); }
        }
        else if (result[0] == "failed") {
          toastr['error']("บันทึกไม่สำเร็จ. ลองใหม่อีกครั้ง");
        }
        else {
          alert(result);
        }

      }
    }).fail(function () {
      toastr['error']("เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์");
    }).always(function () {
      $("#" + this_id).attr('disabled', false);
      $(".overlay").remove();
    });
    /* ********************************************************** */

  }) //name end
    .catch(err => {
      toastr['error']("บันทึก พักบิลขายไม่ได้! <br/>โปรด ป้อนชื่อบิล");
      try {
        failed.currentTime = 0;
        failed.play();
      } catch (e) { console.log(e); }
    });//swal end

}); //hold_invoice end

function hold_invoice_list() {
  var base_url = $("#base_url").val();
  $.post(base_url + "pos/hold_invoice_list", {}, function (result) {
    //alert(result);
    var data = jQuery.parseJSON(result)
    $("#hold_invoice_list").html('').html(data['result']);
    $(".hold_invoice_list_count").html('').html(data['tot_count']);
  });
}
function hold_invoice_delete(invoice_id) {
  swal({ title: "Are you sure?", icon: "warning", buttons: true, dangerMode: true, }).then((sure) => {
    if (sure) {//confirmation start
      var base_url = $("#base_url").val();
      $.post(base_url + "pos/hold_invoice_delete/" + invoice_id, {}, function (result) {
        result = result;
        if (result == 'success') {
          toastr["success"]("ลบบิล สำเร็จ!!");
          try {
            success.currentTime = 0;
            success.play();
          } catch (e) { console.log(e); }
          hold_invoice_list();
        }
        else {
          toastr['error']("ลบบิล ไม่ได้ !  ลองใหม่อีกครั้ง!!");
          try {
            failed.currentTime = 0;
            failed.play();
          } catch (e) { console.log(e); }
        }
      });
    } //confirmation sure
  }); //confirmation end
}

function hold_invoice_edit(id) {

  swal({ title: "Are you sure?", icon: "warning", buttons: true, dangerMode: true, }).then((sure) => {
    if (sure) {//confirmation start
      var base_url = $("#base_url").val();

      $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
      $.post(base_url + "pos/hold_invoice_edit", { hold_id: id }, function (result) {

        console.log(result);

        result = result.split("<<<###>>>");
        $('#pos-form-tbody').html('').append(result[0]);
        $('#discount_input').val(result[1]);
        $('#discount_type').val(result[2]);
        /*if(store_module){
          $('#store_id').val(result[4]).select2();
        }
        else{*/
        $('#store_id').val(result[4]);
        /*}*/
        console.log("warehouse = " + result[5]);
        if (warehouse_module) {
          $('#warehouse_id').val(result[5]).select2();
        }
        else {
          $('#warehouse_id').val(result[5]);
        }

        //$('#customer_id').val(result[3]).select2();
        //$("#customer_id").trigger("change");

        //$('#temp_customer_id').val(result[3]);

        autoLoadFirstCustomer(result[3]);
        //$('#customer_id').val(result[3]).select2();
        $("#hidden_invoice_id").val(result[7]);
        $("#hidden_rowcount").val(parseInt($(".items_table tr").length) - 1);
        final_total();
        get_details(null, true);
        $(".overlay").remove();

        if (result[5] == 1) {
          $("#binvoice").prop("checked", true);
          $('#binvoice').parent('div').addClass('checked');
        }
      }).fail(function () {
        toastr['error']("Failed to load hold invoice. Please try again.");
      }).always(function () {
        $(".overlay").remove();
      });


    } //confirmation sure
  }); //confirmation end
}
/* *********************** HOLD INVOICE END****************************/
/* *********************** ORDER INVOICE START****************************/
function get_id_value(id) {
  return $("#" + id).val();
}
$('#collect_customer_info').on("click", function (e) {

  //table should not be empty
  if ($(".items_table tr").length == 1) {
    toastr["error"]("โปรด เลือกสินค้า !!");
    failed.currentTime = 0;
    failed.play();
    return;
  }
  if (get_id_value('customer_id') == 1) {
    //$('#customer-modal').modal('toggle');
    toastr["error"]("โปรด เลือกลูกค้า!!");
    failed.currentTime = 0;
    failed.play();
    return false;
  }
  else {
    $('#delivery-info').modal('toggle');
  }
}); //hold_invoice end
$('.show_payments_modal').on("click", function (e) {

  //table should not be empty
  if ($(".items_table tr").length == 1) {
    toastr["error"]("โปรด เลือกสินค้า!!");
    failed.currentTime = 0;
    failed.play();
    return;
  }
  else {
    adjust_payments();
    $("#add_payment_row,#payment_type_1").parent().show();
    $("#amount_1").parent().parent().removeClass('col-md-12').addClass('col-md-6');

    // Auto-fill Amount with Grand Total
    $("#amount_1").val($(".sales_div_tot_payble").text());
    calculate_payments();

    $('#multiple-payments-modal').modal('toggle');
  }
}); //hold_invoice end
$('#show_cash_modal').on("click", function (e) {
  //table should not be empty
  if ($(".items_table tr").length == 1) {
    toastr["error"]("โปรด เลือกสินค้า!!");
    failed.currentTime = 0;
    failed.play();
    return;
  }
  else {
    adjust_payments();
    $("#add_payment_row,#payment_type_1").parent().hide();
    $("#amount_1").focus();
    $("#amount_1").parent().parent().removeClass('col-md-6').addClass('col-md-12');
    $('#multiple-payments-modal').modal('toggle');
  }
}); //hold_invoice end

$('#add_payment_row').on("click", function (e) {

  var base_url = $("#base_url").val();
  //table should not be empty
  if ($(".items_table tr").length == 1) {
    toastr["error"]("โปรด เลือกสินค้า!!");
    failed.currentTime = 0;
    failed.play();
    return;
  }
  /*if(get_id_value('customer_id')==1){
    //$('#customer-modal').modal('toggle');
    toastr["error"]("Please Select Customer!!");
    failed.currentTime = 0;
failed.play();
    return false;
  }*/
  else {
    /*BUTTON LOAD AND DISABLE START*/
    var this_id = this.id;
    var this_val = $(this).html();
    $("#" + this_id).html('<i class="fa fa-spinner fa-spin"></i> Please Wait..');
    $("#" + this_id).attr('disabled', true);
    /*BUTTON LOAD AND DISABLE END*/

    var payment_row_count = get_id_value("payment_row_count");
    $.post(base_url + "pos/add_payment_row", { payment_row_count: payment_row_count }, function (result) {
      $('.payments_div').parent().append(result);
      $("#payment_row_count").val(parseInt(payment_row_count) + 1);

      try {
        failed.currentTime = 0;
        failed.play();
      } catch (e) { console.log(e); }
      adjust_payments();
    }).fail(function () {
      toastr['error']("Failed to add payment row. Please try again.");
    }).always(function () {
      /*BUTTON LOAD AND DISABLE START*/
      $("#" + this_id).html(this_val);
      $("#" + this_id).attr('disabled', false);
      /*BUTTON LOAD AND DISABLE END*/
    });
  }
}); //hold_invoice end
function remove_row(id) {
  $(".payments_div_" + id).html('');
  failed.currentTime = 0;
  failed.play();
  adjust_payments();
}
function calculate_payments() {
  adjust_payments();
}

function get_item_details(item_id) {
  var base_url = $("#base_url").val();
  var warehouse_id = $("#warehouse_id").val();
  $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
  $.post(base_url + "pos/get_item_details", { item_id: item_id, warehouse_id: warehouse_id }, function (result) {
    console.log(result);
    var item = jQuery.parseJSON(result);

    var obj = {};
    obj['item_id'] = item['id'];
    obj['item_name'] = item['item_name'];
    obj['stock'] = item['stock'];
    obj['sales_price'] = item['sales_price'];
    obj['purchase_price'] = item['purchase_price'];
    obj['tax_id'] = item['tax_id'];
    obj['tax_type'] = item['tax_type'];
    obj['tax'] = item['tax'];
    obj['tax_name'] = item['tax_name'];
    obj['item_tax_amt'] = item['item_tax_amt'];
    obj['discount_type'] = item['discount_type'];
    obj['discount'] = item['discount'];
    obj['service_bit'] = item['service_bit'];
    addrow(null, obj);
    $(".overlay").remove();
  }).fail(function () {
    $(".overlay").remove();
    toastr['error']("Failed to load item details.");
  });

}

/* *********************** ORDER INVOICE END****************************/


$("#item_search").bind("paste", function (e) {
  $("#item_search").autocomplete('search');
});



$("#item_search").autocomplete({
  minLength: 0,
  source: function (data, cb) {
    $.ajax({
      autoFocus: true,
      url: $("#base_url").val() + 'items/get_json_items_details',
      method: 'GET',
      dataType: 'json',
      /*showHintOnFocus: true,
autoSelect: true, 
	
selectInitial :true,*/

      data: {
        name: data.term,
        store_id: $("#store_id").val(),
        warehouse_id: $("#warehouse_id").val(),
        search_for: "sales",
      },
      beforeSend: function () {
        if ($("#warehouse_id").val() == '') {
          toastr['warning']("โปรด เลือกคลังเก็บสินค้า!");
          $("#warehouse_id").select2('open');
          $("#item_search").removeClass('ui-autocomplete-loading');
          return;
        }
        $("#item_search").addClass('ui-autocomplete-loading');
      },
      success: function (res) {
        //console.log(res);
        var result;
        result = [
          {
            //label: 'No Records Found '+data.term,
            label: 'No Records Found ',
            value: ''
          }
        ];

        if (res.length) {
          result = $.map(res, function (el) {
            return {
              label: el.item_code + '--[Qty:' + el.stock + '] --' + el.label,
              value: '',
              id: el.id,
              item_name: el.value,
              stock: el.stock,
              service_bit: el.service_bit,
              // mobile: el.mobile,
              //customer_dob: el.customer_dob,
              //address: el.address,
            };
          });
        }

        cb(result);
      }
    });
  },
  response: function (e, ui) {
    if (ui.content.length == 1) {
      $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
      $(this).autocomplete("close");
    }
    //console.log(ui.content[0].id);
  },
  //loader start
  search: function (e, ui) {
  },
  select: function (e, ui) {



    if (typeof ui.content != 'undefined') {
      console.log("Autoselected first");
      if (isNaN(ui.content[0].id)) {
        return;
      }
      var stock = ui.content[0].stock;
      var item_id = ui.content[0].id;
      var service_bit = ui.content[0].service_bit;
    }
    else {
      console.log("manual Selected");
      var stock = ui.item.stock;
      var item_id = ui.item.id;
      var service_bit = ui.item.service_bit;
    }
    if (service_bit == 0 && parseFloat(stock) <= 0) {
      toastr["warning"](stock + " มีสินค้าในสต็อก!!");
      failed.currentTime = 0;
      failed.play();
      return false;
    }

    /* if(service_bit==1){
       return_row_with_data(item_id);  
     }
     else {
       if(restrict_quantity(item_id)){
         return_row_with_data(item_id);  
       }
     }*/
    //addrow(item_id);
    get_item_details(item_id);
    $("#item_search").val('');


  },
  //loader end
});


function set_previous_due(previous_due, tot_advance) {
  $(".customer_previous_due").html(previous_due);
  $(".customer_tot_advance").html(tot_advance);
}



function get_coupon_details() {
  var input_box = $("#coupon_code");
  var coupon_code = $.trim(input_box.val());
  var customer_id = $("#customer_id").val();
  var base_url = $("#base_url").val();

  var coupon_type = '';
  var coupon_value = 0;
  if (coupon_code != '') {
    input_box.addClass('ui-autocomplete-loading');
    $.post(base_url + 'customer_coupon/get_coupon_details', { invoice_type: 'sales', coupon_code: coupon_code, customer_id: customer_id }, function (data, textStatus, xhr) {
      var json = $.parseJSON(data);
      coupon_value = json.coupon_value;
      coupon_type = json.coupon_type;



      $(".coupon_value").html(to_Fixed(coupon_value));
      $(".coupon_type").html(coupon_type);

      $(".div1,.div2").removeClass('hide');

      if (json.expire_status == 'Valid') {
        $(".msg_color").removeClass('alert-warning').addClass('alert-success');
      }
      else {
        $(".msg_color").removeClass('alert-success').addClass('alert-warning');
        $(".div2").addClass('hide');
      }
      $("#coupon_code_msg").text(json.message);

      final_total();
      adjust_payments();
    }).fail(function () {
      toastr['error']("Failed to verify coupon.");
    }).always(function () {
      input_box.removeClass('ui-autocomplete-loading');
    });
  } else {
    $(".div1, .div2").addClass('hide');
    $(".coupon_value").html(to_Fixed(coupon_value));
    $(".coupon_type").html(coupon_type);
    final_total();
    adjust_payments();
  }
}


$("#coupon_code, #customer_id").on("change", function () {
  get_coupon_details();
});
$('#coupon_code').keypress(function (e) {
  var key = e.which;
  // the enter key code
  if (key == 13) {
    get_coupon_details();
  }
});

/*Calculate Coupon Discount Amount*/

function discount_coupon_tot(subtotal) {
  var coupon_value = parseFloat($(".coupon_value").html());
  coupon_value = isNaN(coupon_value) ? 0 : coupon_value;

  var coupon_type = $(".coupon_type").html();

  var discount_amt = 0;
  if (coupon_type != '' && coupon_value > 0) {

    if (coupon_type == 'Percentage') {
      discount_amt = (subtotal * coupon_value) / 100;
    }
    else {//Fixed
      discount_amt = coupon_value;
    }
  }
  return discount_amt;
}

function calulate_discount(discount_input, discount_type, total) {
  if (discount_type == 'in_percentage') {
    return parseFloat((total * discount_input) / 100);
  }
  else {//in_fixed
    return parseFloat(discount_input);
  }
}

$('#item_search').keypress(function (e) {
  var key = e.which;
  // the enter key code
  if (key == 13) {
    $("#item_search").autocomplete('search');
  }
});



//Calculate Subtotal

//Calculate Subtotal
function make_subtotal(item_id, rowcount) {
  var sales_price = parseFloat($("#sales_price_" + rowcount).val().replace(/,/g, ''));
  var item_qty = parseFloat($("#item_qty_" + item_id).val().replace(/,/g, ''));

  if (isNaN(sales_price)) sales_price = 0;
  if (isNaN(item_qty)) item_qty = 0;

  var subtotal = sales_price * item_qty;
  $("#td_data_" + rowcount + "_4").val(to_Fixed(subtotal));

  final_total();
}

function final_total() {
  var rowcount = parseFloat($("#hidden_rowcount").val());
  var total_quantity = 0;
  var subtotal_all = 0;
  
  // Grouping for high-precision calculation (Sequence A)
  var tax_groups = {}; 
  var total_exempt_base = 0;
  var max_tax_rate = 0;
  
  for (var i = 0; i <= rowcount; i++) { 
    if (document.getElementById("tr_item_id_" + i)) {
       var item_id = $("#tr_item_id_" + i).val();
       var qty_str = $("#item_qty_" + item_id).val() || "0";
       var qty = parseFloat(qty_str.replace(/,/g, '')) || 0;
       
       if (qty > 0) {
          total_quantity += qty;
          var row_total_str = $("#td_data_" + i + "_4").val() || "0";
          var row_total = parseFloat(row_total_str.replace(/,/g, '')) || 0;
          subtotal_all += row_total;
          
          var tax_type = $("#tr_tax_type_" + i).val();
          var tax_rate = parseFloat($("#tr_tax_value_" + i).val()) || 0;
          if(tax_rate > max_tax_rate) max_tax_rate = tax_rate;
          
          if (tax_rate > 0) {
             // High precision de-taxing (Always de-tax before discount as per user recipe)
             var de_taxed = (row_total * 100) / (100 + tax_rate);
             if (!tax_groups[tax_rate]) tax_groups[tax_rate] = 0;
             tax_groups[tax_rate] = (tax_groups[tax_rate] || 0) + de_taxed;
          } else {
             total_exempt_base += row_total;
          }
       }
    }
  }

  // Other Charges
  var other_charges_input_str = $("#other_charges_input").val() || "0";
  var other_charges_input = parseFloat(other_charges_input_str.replace(/,/g, '')) || 0;
  var other_charges_type = $("#other_charges_type").val();
  var other_charges_amt = 0;
  var oc_tax_rate = 0;

  if (other_charges_input != 0) {
     var oc_base_raw = (other_charges_type == 'Percentage' || other_charges_type == 'in_percentage') ? (subtotal_all * other_charges_input / 100) : other_charges_input;
     
     var oc_tax_id = $('option:selected', '#other_charges_tax_id').attr('data-tax');
     oc_tax_rate = parseFloat(oc_tax_id) || 0;
     
     // Fallback to prevailing rate if no tax selected for service fee (Sequence A requirement)
     if(oc_tax_rate == 0 && max_tax_rate > 0) { oc_tax_rate = max_tax_rate; }

     if (oc_tax_rate > 0) {
        // De-tax the service fee from inclusive raw amount
        var oc_base_de_taxed = (oc_base_raw * 100) / (100 + oc_tax_rate);
        var oc_tax_amt = (oc_base_de_taxed * oc_tax_rate / 100);
        other_charges_amt = oc_base_de_taxed + oc_tax_amt; // Should equal oc_base_raw
        
        if (!tax_groups[oc_tax_rate]) tax_groups[oc_tax_rate] = 0;
        tax_groups[oc_tax_rate] = (tax_groups[oc_tax_rate] || 0) + oc_base_de_taxed;
     } else {
        other_charges_amt = oc_base_raw;
        total_exempt_base += oc_base_raw;
     }
  }

  // Base sum
  var total_base_sum = total_exempt_base;
  for (var rate in tax_groups) { total_base_sum += tax_groups[rate]; }

  // Discount to all
  var discount_input_str = $("#discount_input").val() || "0";
  var discount_input = parseFloat(discount_input_str.replace(/,/g, '')) || 0;
  var discount_type = $("#discount_type").val();
  
  // DISCOUNT SHOULD BE CALCULATED ON INCLUSIVE TOTAL (Subtotal + Other Charges)
  var inclusive_total = subtotal_all + other_charges_amt;
  var discount_to_all = (discount_type == 'in_percentage' || discount_type == 'Percentage') ? (inclusive_total * discount_input / 100) : discount_input;
  if(isNaN(discount_to_all)) discount_to_all = 0;

  var total_global_discount = discount_to_all;

  // Prorate and calculate final components
  var final_grand_total = 0;
  var ratio = (total_base_sum > 0) ? (total_global_discount / total_base_sum) : 0;
  
  // Exempt portion
  var net_exempt = total_exempt_base - (total_exempt_base * ratio);
  final_grand_total += Math.round(net_exempt * 100) / 100;
  
  // Taxable portions
  for (var rate in tax_groups) {
     var base = tax_groups[rate];
     var net_base = base - (base * ratio);
     var rounded_net_base = Math.round(net_base * 100) / 100;
     var vat_amt = Math.round((rounded_net_base * parseFloat(rate) / 100) * 100) / 100;
     final_grand_total += rounded_net_base + vat_amt;
  }

  // Display Total (2 decimals)
  var display_total = to_Fixed(final_grand_total);

  // Update Main UI
  $(".tot_qty").html(to_Fixed(total_quantity));
  $(".tot_amt").html(to_Fixed(subtotal_all));
  $(".tot_other_charges").html(to_Fixed(other_charges_amt));
  
  $(".tot_disc").html(to_Fixed(discount_to_all));
  $("#tot_disc").val(to_Fixed(discount_to_all));
  
  $(".tot_grand").html(display_total);

  // Update Payment Modal Totals
  $(".sales_div_tot_qty").html(to_Fixed(total_quantity));
  $(".sales_div_tot_amt").html(to_Fixed(subtotal_all));
  $(".sales_div_tot_discount").html(to_Fixed(discount_to_all));
  $(".sales_div_tot_other_charges").html(to_Fixed(other_charges_amt));
  $(".sales_div_tot_payble").html(display_total);

  calculate_payments();
}

//Delete Item Row
function removerow(id) {
  $("#row_" + id).remove();
  final_total();
  try {
    failed.currentTime = 0;
    failed.play();
  } catch (e) { console.log(e); }
}
window.removerow = removerow;



