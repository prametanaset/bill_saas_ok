/*
* items.js - Logic for Add/Update Items
*/

var calculation_lock = false;

$('#save,#update').on("click", function (e) {
	var base_url = $("#base_url").val();
	var flag = true;

	function check_field(id) {
		if (!$("#" + id).val()) {
			$('#' + id + '_msg').fadeIn(200).show().html('Required Field').addClass('required');
			flag = false;
		}
		else {
			$('#' + id + '_msg').fadeOut(200).hide();
		}
	}

	var item_group = $("#item_group").val();
	check_field("item_name");
	check_field("category_id");
	check_field("unit_id");
	check_field("tax_id");
	check_field("tax_type");

	if (item_group == 'Single') {
		check_field("price");
		check_field("purchase_price");
		check_field("sales_price");
	}
	else if (item_group == 'Combo') {
		if (!validate_combo_records()) {
			return;
		}
	}
	else {
		if (!validate_variants_records()) {
			return;
		}
	}

	if (flag == false) {
		toastr["warning"]("มีข้อมูลบางรายการหายไป !");
		return;
	}

	var this_id = this.id;
	var existing_row_count = 0;

	if (item_group == 'Variants') {
		existing_row_count = $("#variant_table  tr").length;
		if (existing_row_count <= 1) {
			toastr["warning"]("ไม่มีข้อมูล ในรายการตัวเลือกสินค้า !!");
			return;
		}
	}
	if (item_group == 'Combo') {
		existing_row_count = $("#current_combo_row_count").val();
		if (existing_row_count == 0) {
			toastr["warning"]("กรุณาเพิ่มสินค้าในชุดคอมโบ !!");
			return;
		}
	}

	e.preventDefault();
	var data = new FormData($('#items-form')[0]);
	if (!xss_validation(data)) { return false; }

	$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
	$("#" + this_id).attr('disabled', true);

	var url = (this_id == "save") ? 'newitems' : base_url + 'items/update_items';
	url += '?existing_row_count=' + existing_row_count;

	$.ajax({
		type: 'POST',
		url: url,
		data: data,
		cache: false,
		contentType: false,
		processData: false,
		success: function (result) {
			if (result == "success") {
				window.location = base_url + 'items';
				return;
			}
			else if (result == "failed") {
				toastr["error"]("บันทึกข้อมูลไม่สำเร็จ !");
			}
			else {
				toastr["error"](result);
			}
			$("#" + this_id).attr('disabled', false);
			$(".overlay").remove();
		},
		error: function (xhr, status, error) {
			toastr["error"]("Failed to process request: " + error);
			$("#" + this_id).attr('disabled', false);
			$(".overlay").remove();
		}
	});
});

function shift_cursor(kevent, target) {
	if (kevent.keyCode == 13) {
		$("#" + target).focus();
	}
}

function update_status(id, status) {
	var base_url = $("#base_url").val();
	$.post(base_url + "items/update_status", { id: id, status: status }, function (result) {
		if (result == "success") {
			toastr["success"]("อัพเดทเรียบร้อย!");
			success_sound.currentTime = 0;
			success_sound.play();
			var span_class = (status == 0) ? "label label-danger" : "label label-success";
			var status_text = (status == 0) ? "Inactive" : "Active";
			var next_status = (status == 0) ? 1 : 0;

			$("#span_" + id).attr('onclick', 'update_status(' + id + ',' + next_status + ')');
			$("#span_" + id).attr('class', span_class);
			$("#span_" + id).html(status_text);
		}
		else {
			toastr["error"](result == "failed" ? "อัพเดทไม่สำเร็จ พยายามอีกครั้ง !" : result);
			failed_sound.currentTime = 0;
			failed_sound.play();
		}
	});
}

function removerow_also_delete_from_database(item_id, rowid) {
	if (item_id == '') {
		removerow(rowid);
		return;
	}
	var base_url = $("#base_url").val();
	$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
	$.post(base_url + "items/delete_items", { q_id: item_id }, function (result) {
		if (result == "success") {
			toastr["success"]("ลบข้อมูลเรียบร้อย!");
			removerow(rowid);
		}
		else {
			toastr["error"](result == "failed" ? "ลบไม่สำเร็จ พยายามอีกครั้ง !" : result);
		}
		$(".overlay").remove();
	});
}

function delete_items(q_id) {
	if (confirm("คุณต้องการลบสินค้า ?")) {
		$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
		$.post($("#base_url").val() + "items/delete_items", { q_id: q_id }, function (result) {
			if (result == "success") {
				toastr["success"]("ลบข้อมูลเรียบร้อย!");
				$('#example2').DataTable().ajax.reload();
			}
			else {
				toastr["error"](result == "failed" ? "ลบไม่สำเร็จ พยายามอีกครั้ง !" : result);
			}
			$(".overlay").remove();
		});
	}
}

function multi_delete() {
	var base_url = $("#base_url").val();
	var this_id = this.id;
	if (confirm("Are you sure ?")) {
		$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
		$("#" + this_id).attr('disabled', true);
		var data = new FormData($('#table_form')[0]);
		$.ajax({
			type: 'POST',
			url: base_url + 'items/multi_delete',
			data: data,
			cache: false,
			contentType: false,
			processData: false,
			success: function (result) {
				if (result == "success") {
					toastr["success"]("ลบข้อมูลเรียบร้อย!");
					success_sound.currentTime = 0;
					success_sound.play();
					$('#example2').DataTable().ajax.reload();
					$(".delete_btn").hide();
					$(".group_check").prop("checked", false).iCheck('update');
				}
				else {
					toastr["error"](result == "failed" ? "บันทึกข้อมูลไม่สำเร็จ พยายามอีกครั้ง !" : result);
					failed_sound.currentTime = 0;
					failed_sound.play();
				}
				$("#" + this_id).attr('disabled', false);
				$(".overlay").remove();
			}
		});
	}
}

// CALCULATION FUNCTIONS
function calculate_purchase_price() {
	if (calculation_lock) return;
	calculation_lock = true;
	try {
		var price = (isNaN(parseFloat($("#price").val()))) ? 0 : parseFloat($("#price").val());
		var tax_amt = (isNaN(parseFloat($('option:selected', "#tax_id").attr('data-tax')))) ? 0 : parseFloat($('option:selected', "#tax_id").attr('data-tax'));
		var tax_type = $("#tax_type").val();

		var purchase_price = (tax_type == 'Inclusive') ? price : price + parseFloat(calculate_exclusive(price, tax_amt));
		$("#purchase_price").val(to_Fixed(purchase_price));

		calculate_sales_price_inner();
	} finally {
		calculation_lock = false;
	}
}

function calculate_sales_price_inner() {
	var price = (isNaN(parseFloat($("#price").val()))) ? 0 : parseFloat($("#price").val());
	var profit_margin = (isNaN(parseFloat($("#profit_margin").val()))) ? 0 : parseFloat($("#profit_margin").val());
	var profit_amt = (price * profit_margin) / 100;
	var sales_price = price + profit_amt;
	$("#sales_price").val(to_Fixed(sales_price));
}

function calculate_sales_price() {
	if (calculation_lock) return;
	calculation_lock = true;
	try {
		calculate_sales_price_inner();
	} finally {
		calculation_lock = false;
	}
}

function calculate_profit_margin() {
	if (calculation_lock) return;
	calculation_lock = true;
	try {
		var price = (isNaN(parseFloat($("#price").val()))) ? 0 : parseFloat($("#price").val());
		var sales_price = (isNaN(parseFloat($("#sales_price").val()))) ? 0 : parseFloat($("#sales_price").val());
		var profit_margin = (price == 0) ? 0 : (sales_price - price) / price * 100;
		$("#profit_margin").val(to_Fixed(profit_margin));
	} finally {
		calculation_lock = false;
	}
}

// Event Listeners for calculations
$("#price").keyup(function (event) {
	calculate_purchase_price();
});
$("#tax_id,#tax_type").on("change", function (event) {
	calculate_purchase_price();
	calculate_purchase_price_of_all_row();
});
$("#profit_margin").on("change", function (event) {
	calculate_sales_price();
});
$("#sales_price").on("change", function (event) {
	calculate_profit_margin();
});

function view_warehouse_wise_stock_item(item_id) {
	var base_url = $("#base_url").val();
	$.post(base_url + 'warehouse/view_warehouse_wise_stock_item', { item_id: item_id }, function (result) {
		$(".view_warehouse_wise_stock_item").html('').html(result);
		$('#view_warehouse_wise_stock_item_model').modal('toggle');
	});
}

// AUTOCOMPLETE SETTINGS
$("#variant_search").autocomplete({
	source: function (data, cb) {
		$.ajax({
			url: $("#base_url").val() + 'items/get_json_variant_details',
			method: 'GET',
			dataType: 'json',
			data: { name: data.term },
			beforeSend: function () {
				if ($("#tax_id").val() == '') {
					toastr['warning']("กรุณาเลือกชนิด ภาษี !");
					$("#tax_id").select2('open');
					$("#variant_search").removeClass('ui-autocomplete-loading');
					return false;
				}
				$("#variant_search").addClass('ui-autocomplete-loading');
			},
			success: function (res) {
				var result = res.length ? $.map(res, function (el) {
					return { label: el.variant_name + " - " + el.description, value: '', id: el.id };
				}) : [{ label: 'No Records Found ', value: '' }];
				cb(result);
			}
		});
	},
	select: function (e, ui) {
		if (ui.item.id) {
			return_variant_data_in_row(ui.item.id);
		}
		$("#variant_search").val('');
	}
});

function return_variant_data_in_row(variant_id) {
	$("#variant_search").addClass('ui-autocomplete-loader-center');
	var base_url = $("#base_url").val();
	var rowcount = $("#hidden_rowcount").val();
	$.post(base_url + "items/return_variant_data_in_row/" + rowcount + "/" + variant_id, {}, function (result) {
		$('#variant_table tbody').append(result);
		$("#hidden_rowcount").val(parseInt(rowcount) + 1);
		success_sound.currentTime = 0;
		success_sound.play();
		$("#variant_search").removeClass('ui-autocomplete-loader-center ui-autocomplete-loading');
	});
}

function removerow(id) {
	$("#row_" + id).remove();
	failed_sound.currentTime = 0;
	failed_sound.play();
}

function calculate_purchase_price_of_all_row() {
	var rowcount = parseInt($("#hidden_rowcount").val());
	for (var i = 1; i <= rowcount; i++) {
		if (document.getElementById("td_data_" + i + "_3")) {
			var price = get_float_type_data("#td_data_" + i + "_3");
			var purchase_price = calculate_purchase_price_new(price);
			$("#td_data_" + i + "_4").val(purchase_price);
		}
	}
	calculate_sales_price_of_all_row();
}

function calculate_purchase_price_new(price) {
	var tax = (isNaN(parseFloat($('option:selected', "#tax_id").attr('data-tax')))) ? 0 : parseFloat($('option:selected', "#tax_id").attr('data-tax'));
	var tax_type = $("#tax_type").val();
	var purchase_price = (tax_type == 'Inclusive') ? price : parseFloat(price) + parseFloat(calculate_exclusive(price, tax));
	return to_Fixed(purchase_price);
}

function calculate_sales_price_of_all_row() {
	var rowcount = parseInt($("#hidden_rowcount").val());
	for (var i = 1; i <= rowcount; i++) {
		if (document.getElementById("td_data_" + i + "_3")) {
			var price = get_float_type_data("#td_data_" + i + "_3");
			var profit_margin = get_float_type_data("#td_data_" + i + "_5");
			var sales_price = calculate_sales_price_new(price, profit_margin);
			$("#td_data_" + i + "_6").val(sales_price);
		}
	}
}

function calculate_sales_price_new(price, profit_margin) {
	var profit_amt = (profit_margin / 100) * price;
	var sales_price = price + profit_amt;
	return to_Fixed(sales_price);
}

function calculate_profit_margin_of_all_row() {
	var rowcount = parseInt($("#hidden_rowcount").val());
	for (var i = 1; i <= rowcount; i++) {
		if (document.getElementById("td_data_" + i + "_3")) {
			var price = get_float_type_data("#td_data_" + i + "_4");
			var sales_price = get_float_type_data("#td_data_" + i + "_6");
			var profit_margin = calculate_profit_margin_new(price, sales_price);
			$("#td_data_" + i + "_5").val(profit_margin);
		}
	}
}

function calculate_profit_margin_new(price, sales_price) {
	var profit_margin = (price == 0) ? 0 : (sales_price - price) / price * 100;
	return to_Fixed(profit_margin);
}

$("#item_group").on("change", function (event) {
	var item_group = $(this).val();
	if (item_group == 'Variants') {
		$("#price,#purchase_price,#profit_margin,#sales_price,#mrp,#hsn,#sku,#custom_barcode,#adjustment_qty").parent().addClass('hide');
		$(".variant_div").show();
		$(".combo_div").hide();
	}
	else if (item_group == 'Combo') {
		$("#price,#profit_margin,#sales_price,#mrp,#hsn,#sku,#custom_barcode").parent().removeClass('hide');
		$("#purchase_price,#adjustment_qty").parent().addClass('hide');
		$(".variant_div").hide();
		$(".combo_div").show();
	}
	else {
		$("#price,#purchase_price,#profit_margin,#sales_price,#mrp,#hsn,#sku,#custom_barcode,#adjustment_qty").parent().removeClass('hide');
		$(".variant_div").hide();
		$(".combo_div").hide();
	}
});

function validate_variants_records() {
	var rowcount = parseInt($("#hidden_rowcount").val());
	var available_rows = 0;
	for (var i = 1; i <= rowcount; i++) {
		if (document.getElementById("td_data_" + i + "_3")) {
			available_rows++;
			var purchase_price = get_float_type_data("#td_data_" + i + "_4");
			var sales_price = get_float_type_data("#td_data_" + i + "_6");
			if (purchase_price == 0 || sales_price == 0) {
				$("#td_data_" + i + "_3").focus();
				toastr["warning"]("ราคาขาย ตามตัวเลือกย่อยสินค้า ต้องกรอกข้อมูล!");
				return false;
			}
		}
	}
	if (available_rows == 0) {
		toastr["warning"]("ไม่มีข้อมูล ในรายการตัวเลือกสินค้า!!");
		return false;
	}
	return true;
}

$(document).ready(function () {
	$(window).keydown(function (event) {
		if (event.keyCode == 13) {
			event.preventDefault();
			return false;
		}
	});
});

// COMBO ITEM LOGIC
$("#combo_item_search").autocomplete({
	source: function (data, cb) {
		$.ajax({
			url: $("#base_url").val() + 'items/get_json_items_details',
			method: 'GET',
			dataType: 'json',
			data: {
				name: data.term,
				store_id: $("#store_id").val(),
				warehouse_id: $("#warehouse_id").val(),
				search_for: 'purchase'
			},
			success: function (res) {
				var result = res.length ? $.map(res, function (el) {
					return { label: el.label, value: '', id: el.id };
				}) : [{ label: 'No Records Found ', value: '' }];
				cb(result);
			}
		});
	},
	select: function (e, ui) {
		if (ui.item.id) {
			return_combo_data_in_row(ui.item.id);
		}
		$("#combo_item_search").val('');
	},
});

function return_combo_data_in_row(item_id) {
	var base_url = $("#base_url").val();
	var rowcount = parseInt($("#current_combo_row_count").val()) + 1;
	$.post(base_url + "items/return_combo_data_in_row/" + rowcount + "/" + item_id, {}, function (result) {
		$('#combo_table tbody').append(result);
		$("#current_combo_row_count").val(rowcount);
		calculate_combo_total_cost();
	});
}

function remove_combo_row(rowcount) {
	$("#tr_combo_" + rowcount).remove();
	calculate_combo_total_cost();
}

function calculate_combo_cost(rowcount) {
	var qty = parseFloat($("#combo_item_qty_" + rowcount).val()) || 0;
	var price = parseFloat($("#combo_item_price_" + rowcount).val()) || 0;
	var total = qty * price;
	$("#combo_item_total_" + rowcount).val(total.toFixed(2));
	calculate_combo_total_cost();
}

function calculate_combo_total_cost() {
	var rowcount = parseInt($("#current_combo_row_count").val());
	var total_cost = 0;
	for (var i = 1; i <= rowcount; i++) {
		if ($("#tr_combo_" + i).length) {
			var line_total = parseFloat($("#combo_item_total_" + i).val());
			if (!isNaN(line_total)) total_cost += line_total;
		}
	}
	$("#price").val(total_cost.toFixed(2));
	calculate_purchase_price();
}

function validate_combo_records() {
	var rowcount = parseInt($("#current_combo_row_count").val());
	var has_items = false;
	for (var i = 1; i <= rowcount; i++) {
		if ($("#tr_combo_" + i).length) {
			has_items = true;
			var qty = parseFloat($("#combo_item_qty_" + i).val());
			if (isNaN(qty) || qty <= 0) {
				toastr["warning"]("Quantity must be greater than 0");
				$("#combo_item_qty_" + i).focus();
				return false;
			}
		}
	}
	if (!has_items) {
		toastr["warning"]("Please add at least one item to the combo");
		return false;
	}
	return true;
}
