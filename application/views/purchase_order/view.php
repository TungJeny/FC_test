<?php $this->load->view("partial/header"); ?>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<div class="row">
    <div class="btn-wp">
        <a class="btn btn-primary uppercase" href="<?php echo site_url('purchase_orders/index'); ?>">Danh sách đơn hàng</a>
        <?php if (isset($purchase_order->id)): ?>
        <a class="btn btn-primary uppercase" href="<?php echo site_url('purchase_orders/invoice/' .  get_data($purchase_order, 'id')); ?>">In hóa đơn</a>
        <?php endif; ?>
        <a class="btn btn-primary uppercase" href="<?php echo site_url('purchase_orders/view/-1'); ?>">Thêm mới</a>
    </div>
    <div class="col-md-12">
        <div class="panel panel-piluku">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <strong>Chọn nguyên vật liệu</strong>
                </h3>
            </div>
            <div class="panel-body form-horizontal pd-15">
                <div class="row">
                    <?php echo form_open("purchase_orders/search_item",array('id' => 'frm-search-item', 'autocomplete' => 'off')); ?>
                    <?php if (!empty($suppliers)): ?>
                    <div class="col-md-4">
                        <label class="font-14">Nhà cung cấp</label>
                        <select id="supplier-id" name="supplier_id" class="select2 filter-params">
                            <option value="0">Chọn  nhà cung cấp</option>
                            <?php foreach ($suppliers as $supplier): ?>
                            <option <?php if (!empty($purchase_order->supplier) && get_data($purchase_order->supplier, 'id') == get_data($supplier,'id')): ?>selected<?php endif; ?> value="<?php echo $supplier->person_id; ?>"><?php echo $supplier->company_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($categories)): ?>
                    <div class="col-md-4">
                        <label class="font-14">Loại nguyên vật liệu</label>
                        <select id="category-id" name="category_id" class="select2 filter-params">
                            <?php  foreach ($categories as $category_id => $category_name): ?>
                            <option <?php if ($filter_category_id == $category_id): ?>selected="selected"<?php endif; ?> value="<?php echo $category_id; ?>"><?php echo $category_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-4">
                        <label class="font-14">Thời điểm</label>
                        <input id="daterange" class="form-control filter-params" type="text" name="date" value="01/01/2018 - 01/15/2018" />
                    </div>
                    <div class="cl"></div>
                    <?php echo form_close(); ?>
                </div>
                <div class="row mt-15">
                    <label class="col-md-12 bold font-14">Nguyên vật liệu tìm được</label>
                    <div class="col-md-12 table-responsive" id="ajax-result"></div>
                    <div class="cl"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="panel panel-piluku">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <strong>Thông tin đơn hàng <?php if (isset($purchase_order->id)): ?><?php echo get_data($purchase_order, 'po_code'); ?><?php endif; ?></label></strong>
                </h3>
            </div>
            <div class="panel-body form-horizontal">
                <?php echo form_open("purchase_orders/save", array('id' => 'frm-search-item', 'autocomplete' => 'off', 'onsubmit' => 'return admin_po.save()')); ?>
                <input type="hidden" name="id" value="<?php echo get_data($purchase_order, 'id'); ?>" />
                <input type="hidden" name="data[supplier_id]" id="selected-supplier-id" value="<?php echo get_data($purchase_order, 'supplier_id'); ?>" />
                <div class="row">
                    <?php if (isset($purchase_order->supplier->id)): ?>
                    <div class="col-md-12">
                        <label class="bold font-14">Nhà cung cấp: <?php echo get_data($purchase_order->supplier, 'company_name'); ?></label>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-6">
                        <label class="bold font-14">Trạng thái đơn hàng</label>
                        <select name="data[status]" class="select2" onchange="admin_po.update_field('status', this.value)">
                            <?php foreach ($status_list as $value => $label): ?>
                            <option <?php if (get_data($purchase_order, 'status') == $value): ?>selected<?php endif; ?> value="<?php echo $value; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="bold font-14">Ngày nhận hàng</label>
                        <input name="data[receive_date]" id="receive-date" placeholder="Ngày nhận hàng" value="<?php echo date('d/m/Y', get_data($purchase_order, 'receive_date', time())); ?>" onchange="admin_po.update_field('receive_date', this.value)" type="text" class="form-control" data-date-format="dd/mm/yyyy" />
                    </div>
                    <div class="col-md-12">
                        <label class="bold font-14">Ghi chú</label>
                        <textarea name="data[comment]" rows="5" onchange="admin_po.update_field('comment', this.value)" class="form-control" name="data[comment]" placeholder="Lưu ý"><?php echo get_data($purchase_order, 'comment'); ?></textarea>

                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <label class="col-md-12 bold font-14">Nguyên vật liệu đã chọn</label>
                            <div class="col-md-12 table-responsive" id="ajax-reload">
                                <?php $this->load->view('purchase_order/view/items'); ?>
                            </div>
                            <div class="cl"></div>
                        </div>
                    </div>
                    <div class="cl"></div>
                </div>
                <div class="form-actions pull-right">
                    <input type="submit" value="Lưu lại" class="submit_button floating-button btn btn-primary uppercase">
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
    <div class="cl"></div>
</div>
<script type="text/javascript">
    $('#receive-date').datepicker();
    $('.select2').select2();
    var start = moment().subtract(29, 'days');
    var end = moment();
    function cb(start, end) {
        $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }
    $('#daterange').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
            'Tháng này': [moment().startOf('month'), moment().endOf('month')],
            'Tháng trước': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        locale: {
            format: 'MM/YYYY',
            applyLabel: 'Tìm kiếm',
            cancelLabel: 'Đóng',
            fromLabel: 'Từ ngày',
            toLabel: 'Đến ngày',
            customRangeLabel: 'Tự chọn',
            daysOfWeek: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6','T7'],
            monthNames: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
            firstDay: 1
        }
    }, cb);
    cb(start, end);
    $('#btn-filter').click(function(evt) {
        evt.preventDefault();
        ajax_search();
        return false;
    });
    <?php if (!isset($purchase_order->id) || $purchase_order->id<= 0):?>
    ajax_search(true);
    <?php endif;?>
    $('#supplier-id').change(function() {
        $('#selected-supplier-id').val($(this).val());
    });
    $('.filter-params').change(function() {
        ajax_search();
    });
    function ajax_search(first_load) {
    	if($('#supplier-id').val() == 0) {
        	if (!first_load) {
    			bootbox.alert('Bạn chưa chọn nhà cung cấp');
        	}
    		return false;
    	} else if ($('#category-id').val() == 0) {
    		return false;
    	} else {
       		 $('#frm-search-item').ajaxSubmit({
       		 	target: "#ajax-result",
       			success: function(response) {
           			if(!first_load) {
       					admin_po.reload();
           			}
       			}
       		 });
    	}
        return false;
    }
    var admin_po = {};
    admin_po.select_item = function(btn) {
        var $btn = $(btn);
        var suppliers = $btn.attr('data-suppliers').toString();
        var data = {
            'data[unit_id]': parseInt($btn.attr('data-unit-id').toString()),
            'data[unit_name]': $btn.attr('data-unit-name').toString(),
            'data[item_id]': parseInt($btn.attr('data-item-id').toString()),
            'data[quantity]': parseInt($btn.attr('data-quater-qty').toString()),
            'data[item_name]': $btn.attr('data-item-name').toString(),
            'data[cost_price]': $btn.attr('data-item-cost-price').toString(),
            'data[month]': $btn.attr('data-month').toString(),
            'data[suppliers]': suppliers.substr(0, suppliers.length - 1)
        };
        $.ajax({
            url: '<?php echo site_url('purchase_orders/select_item'); ?>',
            data: data,
            type: "POST",
            success: function() {
                admin_po.reload();
            }
        });
        return this;
    };
    admin_po.unselect_item = function(item_id) {
        var data = {'item_id': item_id};
        $.ajax({
            url: '<?php echo site_url('purchase_orders/unselect_item'); ?>',
            data: data,
            type: "POST",
            success: function() {
                admin_po.reload();
            }
        });
        return this;
    };
    admin_po.update_item = function(item_id, field, value) {
        var data = {'item_id': item_id, 'field': field, 'value': value};
        $.ajax({
            url: '<?php echo site_url('purchase_orders/update_item'); ?>',
            data: data,
            type: "POST",
            success: function() {
                admin_po.reload();
            }
        });
        return this;
    };
    admin_po.update_field = function(field, value) {
        var data = {'field': field, 'value': value};
        $.ajax({
            url: '<?php echo site_url('purchase_orders/update_field'); ?>',
            data: data,
            type: "POST"
        });
        return this;
    };
    admin_po.validate_field = function(field, error_message) {
        var data = {'field': field};
        $.ajax({
            url: '<?php echo site_url('purchase_orders/validate_field'); ?>',
            data: data,
            type: "POST",
            success: function(result) {
                result = $.parseJSON(result);
                if (!result.success) {
                    bootbox.alert(error_message);
                    return false;
                }
            }
        });
        return false;
    }
    admin_po.save = function() {
        var $supplier = $('#supplier-id');
        var supplier_id = parseInt($supplier.val());
        var $receive_date = $('#receive-date');
        var receive_date = $receive_date.val().toString().trim();
        if (supplier_id <= 0) {
            bootbox.alert('Bạn hãy chọn nhà cung cấp');
            $supplier.focus();
            return false;
        }
        if (receive_date <= 0) {
            bootbox.alert('Bạn hãy chọn ngày nhận hàng');
            $receive_date.focus();
            return false;
        }
        var $po_items = $('.po-item');
        if ($po_items.size() == 0) {
            bootbox.alert('Bạn chưa chọn nguyên vật liệu');
            return false;
        }
        return true;
    };
    admin_po.reload = function() {
        $.get('<?php echo site_url('purchase_orders/reload'); ?>', function(output) {
            $('#ajax-reload').html(output);
        });
    };
    $(document).ready(function() {
	  $(window).keydown(function(event){
	    if(event.keyCode == 13) {
	      event.preventDefault();
	    }
	  });
	});

	$(document).on('keydown', '.input-quantity', function(){
	    if(event.keyCode == 13) {
		      $(this).blur();
	    }
	});
</script>
<?php $this->load->view("partial/footer"); ?>