<div class="modal fade " id="customer-modal" tabindex='-1'>
                <?= form_open('#', array('class' => '', 'id' => 'customer-form')); ?>
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header header-custom">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <label aria-hidden="true">&times;</label></button>
                      <h4 class="modal-title text-center"><?= $this->lang->line('add_customer'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                          <div class="col-md-4">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="customer_name"><?= $this->lang->line('customer_name'); ?>*</label>
                                <label id="customer_name_msg" class="text-danger text-right pull-right"></label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="" >
                              </div>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="mobile"><?= $this->lang->line('mobile'); ?></label>
                                <label id="mobile_msg" class="text-danger text-right pull-right"></label>
                                <input type="tel"  class="form-control no_special_char_no_space " id="mobile" name="mobile" placeholder="0651236547"  >
                              </div>
                            </div>
                          </div>

                          <div class="col-md-4">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="phone"><?= $this->lang->line('phone'); ?></label>
                                <label id="phone_msg" class="text-danger text-right pull-right"></label>
                                <input type="tel" maxlength="10" class="form-control maxlength no_special_char_no_space " id="phone" name="phone" placeholder="021236521"  >
                              </div>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="email"><?= $this->lang->line('email'); ?></label>
                                <label id="email_msg" class="text-danger text-right pull-right"></label>
                                <input type="email" class="form-control " id="email" name="email" placeholder=""  >
                              </div>
                            </div>
                          </div>
                          <?php if(gst_number()){ ?>
                          <div class="col-md-4">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="gstin_msg"><?= $this->lang->line('gst_number'); ?></label>
                                <label id="gstin_msg" class="text-danger text-right pull-right"></label>
                            <!--    <span class="pull-right"><a class="pointer text-bold" target="_blank" href="https://services.gst.gov.in/services/searchtp">Verify</a></span>-->
                                <input type="text" class="form-control maxlength  " id="gstin" name="gstin" placeholder=""  >

                              </div>
                            </div>
                          </div>
                          <?php } ?>

                          <div class="col-md-4">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="tax_number"><?= $this->lang->line('branch'); ?></label>
                                <label id="tax_number_msg" class="text-danger text-right pull-right"></label>
                                <input type="text"  class="form-control maxlength  " id="tax_number" name="tax_number" placeholder=" 00000"  >
                              </div>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="credit_limit"><?= $this->lang->line('credit_limit'); ?></label>
                                <label class="text-success text-right pull-right">-1 for No Limit</label>
                                <label id="credit_limit_msg" class="text-danger text-right pull-right"></label>
                                <input type="text"  class="form-control only_currency" id="credit_limit" name="credit_limit" value='-1' placeholder=""  >
                              </div>
                            </div>
                          </div>

                          <div class="col-md-4">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="opening_balance"><?= $this->lang->line('previous_due'); ?></label>
                                <label id="opening_balance_msg" class="text-danger text-right pull-right"></label>
                                <input type="text"  class="form-control only_currency" id="opening_balance" name="opening_balance" placeholder=""  >
                              </div>
                            </div>
                          </div>
                          </div><!-- row/ -->

                        <div class="row">
                          <div class="col-md-4">
                            <div class="form-group">
                             <h5 class="box-title text-uppercase text-success " id="">
                                <i class="fa fa-fw fa-truck"></i>
                                <ins><?= $this->lang->line('address'); ?></ins>
                                </h5>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                        <div class="col-md-4">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="country_id">จังหวัด</label>
                                <label id="country_msg" class="text-danger text-right pull-right"></label>
                               <select class="form-control select2" id="country" name="country"  style="width: 100%;" onchange="fetchDistricts(this.value)">
                                  <option value="">- เลือกจังหวัด -</option>
                                   <?php
                                     $query = $this->db->query("SELECT code, name_in_thai FROM provinces ORDER BY name_in_thai ASC");
                                     foreach ($query->result() as $row) {
                                       echo "<option value='{$row->code}'>{$row->name_in_thai}</option>";
                                     }
                                   ?>
                                </select>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="state_id">อำเภอ</label>
                                <label id="state_msg" class="text-danger text-right pull-right"></label>
                               <select class="form-control select2" id="state" name="state"  style="width: 100%;" onchange="fetchSubdistricts(this.value)">
                                  <option value="">- เลือกอำเภอ -</option>
                                  </select>
                              </div>
                            </div>
                          </div> 
                       <div class="col-md-4">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="city">ตำบล</label>
                                <label id="city_msg" class="text-danger text-right pull-right"></label>
                                <select class="form-control select2" id="city" name="city" style="width: 100%;" onchange="setZipCode(this)">
                                  <option value="">- เลือกตำบล -</option>
                                 </select>
                              </div>
                            </div>
                          </div> 
                          <div class="col-md-4">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="postcode"><?= $this->lang->line('postcode'); ?></label>
                                <label id="postcode_msg" class="text-danger text-right pull-right"></label>
                                <input type="text" class="form-control no_special_char_no_space" id="postcode" name="postcode" placeholder="เติมอัตโนมัติ" readonly >
                              </div>
                            </div>
                          </div> 
                       
                          <div class="col-md-12">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="address"><?= $this->lang->line('address'); ?></label>
                                <label id="address_msg" class="text-danger text-right pull-right"></label>
                                <textarea type="text" class="form-control" id="address" name="address" placeholder="" ></textarea>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="location_link"><?= $this->lang->line('location_link'); ?></label>
                                <label id="location_link_msg" class="text-danger text-right pull-right"></label>
                                <input type="text" class="form-control" id="location_link" name="location_link" placeholder="" >
                              </div>
                            </div>
                          </div> 

                        </div>

                        <!-- กำลังพัฒนา: ส่วนที่อยู่ส่งของ 
                     <div class="row">
                          <div class="col-md-4">
                            <div class="form-group">
                             <h5 class="box-title text-uppercase text-success " id="">
                                <i class="fa fa-fw fa-truck"></i>
                                <span><?= $this->lang->line('shipping_address'); ?></span>
                                </h5>
                            </div>
                          </div>

                          <div class="col-md-4">
                            <div class="form-group">
                               <label for="copy_address" class="control-label"><?= $this->lang->line('copy_address'); ?> ?</label>
                              <input type="checkbox" class="form-control" id="copy_address" name="copy_address" >
                            </div>
                          </div>

                        </div>


                        <div class="row">
                     <div class="col-md-4" >
                            <div class="box-body">
                              <div class="form-group">
                                <label for="shipping_country">จังหวัด</label>
                                <label id="shipping_country_msg" class="text-danger text-right pull-right"></label>
                               <select class="form-control select2" id="shipping_country" name="shipping_country"  style="width: 100%;" onchange="fetchShippingDistricts(this.value)">
                                  <option value="">- เลือกจังหวัด -</option>
                                   <?php
                                     $query = $this->db->query("SELECT code, name_in_thai FROM provinces ORDER BY name_in_thai ASC");
                                     foreach ($query->result() as $row) {
                                       echo "<option value='{$row->code}'>{$row->name_in_thai}</option>";
                                     }
                                   ?>
                                </select>
                              </div>
                            </div>
                          </div> 
                            <div class="col-md-4">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="shipping_state">อำเภอ</label>
                                <label id="shipping_state_msg" class="text-danger text-right pull-right"></label>
                               <select class="form-control select2" id="shipping_state" name="shipping_state"  style="width: 100%;" onchange="fetchShippingSubdistricts(this.value)">
                                    <option value="">- เลือกอำเภอ -</option>
                                 </select>
                              </div>
                            </div>
                          </div>
                        <div class="col-md-4">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="shipping_city">ตำบล</label>
                                <label id="city_msg" class="text-danger text-right pull-right"></label>
                                 <select class="form-control select2" id="shipping_city" name="shipping_city" style="width: 100%;" onchange="setShippingZipCode(this)">
                                  <option value="">- เลือกตำบล -</option>
                                 </select>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="shipping_postcode"><?= $this->lang->line('postcode'); ?></label>
                                <label id="shipping_postcode_msg" class="text-danger text-right pull-right"></label>
                                <input type="text" class="form-control no_special_char_no_space" id="shipping_postcode" name="shipping_postcode" placeholder="เติมอัตโนมัติ" readonly >
                              </div>
                            </div>
                          </div> 
                       
                          <div class="col-md-12">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="shipping_address"><?= $this->lang->line('address'); ?></label>
                                <label id="address_msg" class="text-danger text-right pull-right"></label>
                                <textarea type="text" class="form-control" id="shipping_address" name="shipping_address" placeholder="" ></textarea>
                              </div>
                            </div>
                          </div>
                         <div class="col-md-4">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="shipping_location_link"><?= $this->lang->line('location_link'); ?></label>
                                <label id="location_link_msg" class="text-danger text-right pull-right"></label>
                                <input type="text" class="form-control" id="shipping_location_link" name="shipping_location_link" placeholder="" >
                              </div>
                            </div>
                          </div> 

                        </div>
                        จบส่วนที่อยู่ส่งของ -->
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-info" data-dismiss="modal">ปิด</button>
                      <button type="button" class="btn btn-primary add_customer">บันทึก</button>
                    </div>
                  </div>
                  <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
                <?= form_close();?>
              </div>
              <!-- /.modal -->

<script type="text/javascript">
  function fetchDistricts(provinceCode, selectedDistrictCode = null) {
    if (!provinceCode) {
      $('#state').html('<option value="">- เลือกอำเภอ -</option>');
      $('#city').html('<option value="">- เลือกตำบล -</option>');
      $('#postcode').val('');
      return;
    }
    $.post("<?= base_url('location/get_districts'); ?>", { province_id: provinceCode }, function (data) {
      $('#state').html(data);
      $('#city').html('<option value="">- เลือกตำบล -</option>');
      $('#postcode').val('');
      
      if (selectedDistrictCode) {
        $('#state').val(selectedDistrictCode).trigger('change');
      }
    });
  }

  function fetchSubdistricts(districtCode, selectedSubdistrictCode = null) {
    if (!districtCode) {
       $('#city').html('<option value="">- เลือกตำบล -</option>');
       $('#postcode').val('');
       return;
    }
    $.post("<?= base_url('location/get_subdistricts'); ?>", { district_id: districtCode }, function (data) {
      const parsed = JSON.parse(data);
      let options = '<option value="">- เลือกตำบล -</option>';
      parsed.forEach(function (item) {
        options += `<option value="${item.id}" data-zip="${item.zip_code}">${item.name_in_thai}</option>`;
      });
      $('#city').html(options);

      if (selectedSubdistrictCode) {
          $('#city').val(selectedSubdistrictCode).trigger('change');
      } else {
          $('#postcode').val('');
      }
    });
  }

  function setZipCode(selectObj) {
    const zip = $('option:selected', selectObj).data('zip');
    $('#postcode').val(zip || '');
  }

  function fetchShippingDistricts(provinceCode, selectedDistrictCode = null) {
    if (!provinceCode) {
      $('#shipping_state').html('<option value="">- เลือกอำเภอ -</option>');
      $('#shipping_city').html('<option value="">- เลือกตำบล -</option>');
      $('#shipping_postcode').val('');
      return;
    }
    $.post("<?= base_url('location/get_districts'); ?>", { province_id: provinceCode }, function (data) {
      $('#shipping_state').html(data);
      $('#shipping_city').html('<option value="">- เลือกตำบล -</option>');
      $('#shipping_postcode').val('');
      
      if (selectedDistrictCode) {
        $('#shipping_state').val(selectedDistrictCode).trigger('change');
      }
    });
  }

  function fetchShippingSubdistricts(districtCode, selectedSubdistrictCode = null) {
    if (!districtCode) {
       $('#shipping_city').html('<option value="">- เลือกตำบล -</option>');
       $('#shipping_postcode').val('');
       return;
    }
    $.post("<?= base_url('location/get_subdistricts'); ?>", { district_id: districtCode }, function (data) {
      const parsed = JSON.parse(data);
      let options = '<option value="">- เลือกตำบล -</option>';
      parsed.forEach(function (item) {
        options += `<option value="${item.id}" data-zip="${item.zip_code}">${item.name_in_thai}</option>`;
      });
      $('#shipping_city').html(options);

      if (selectedSubdistrictCode) {
          $('#shipping_city').val(selectedSubdistrictCode).trigger('change');
      } else {
          $('#shipping_postcode').val('');
      }
    });
  }

  function setShippingZipCode(selectObj) {
    const zip = $('option:selected', selectObj).data('zip');
    $('#shipping_postcode').val(zip || '');
  }
</script>