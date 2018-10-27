<div class="register-box pd-15 white-bg">
    <div class="customer-form">
        <a class="btn btn-primary uppercase" id="stock-out-history">Danh sách yêu cầu</a>
        <?php if (empty($stock_request)): ?>
        <a href="stock_out/cancel_stock_out" class="btn btn-danger uppercase" onclick="return window.confirm('Bạn muốn nhập lại từ đầu?')" style ="border: none;" id="stock_out-cancel">Nhập lại</a>
        <?php else: ?>
        <?php if (get_data($stock_request, 'status') == \Models\Stock::STATUS_ACCEPTED): ?>
        <a href="stock_out/cancel_stock_out" class="btn btn-danger uppercase" onclick="return window.confirm('Bạn muốn nhập lại từ đầu?')" style ="border: none;" id="stock_out-cancel">Nhập lại</a>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php if (empty($stock_request)): ?>
<div class="register-box pd-15 white-bg">
    <?php echo form_open("stock_out/select_sale_daily",array('id' => 'frm-select-select-sale-daily', 'autocomplete' => 'off')); ?>
    <div class="customer-form">
        <label class="font-14">Tìm kiếm đơn hàng theo ngày</label>
        <input value="<?php if (!empty($sale_daily)): ?><?php echo render_date(get_data($sale_daily, 'date')); ?><?php endif; ?>" type="text" id="input-filter-date" name="sale_daily_id" class="form-control validation font-arial font-14" error-message="sale-daily-error" required data-value="<?php if (!empty($sale_daily)): ?><?php echo render_date(get_data($sale_daily, 'date')); ?><?php endif; ?>" data-title="Tìm kiếm đơn hàng theo ngày" placeholder="Tìm kiếm đơn hàng theo ngày..." autocomplete="off" />
        <span class="error-message font-14" id="sale-daily-error"></span>
    </div>
    <?php echo form_close(); ?>
    <?php if (!empty($sale_daily)): ?>
    <div class="employee mt-10">
        <a tabindex="-1" target="_blank" href="<?php echo site_url("orders/update/$customer/" . get_data($sale_daily, 'sale_monthly_id')); ?>" class="bold font-14">
            Đơn hàng ngày <?php echo render_date(get_data($sale_daily, 'date')); ?>
        </a>
        <div class="cl"></div>
    </div>
    <?php endif; ?>
</div>
<?php if (!empty($sale_daily)): ?>
<?php if (!empty($ports)): ?>
<div class="register-box pd-15 mt-15 white-bg">
    <?php echo form_open('stock_out/set_port',array('id' => 'frm-select-employee', 'autocomplete' => 'off')); ?>
    <div class="customer-form">
        <label class="font-14">Cổng giao hàng (<?php echo count($ports); ?>)</label>
        <div class="row">
            <?php foreach ($ports as $port): ?>
            <div class="col-md-6">
                <?php
                $checked = false;
                if (!empty($selected_ports)) {
                    foreach ($selected_ports as $selected_port) {
                        if (strstr($port, $selected_port)) {
                            $checked = true;
                        }
                    }
                }
                echo form_checkbox(array(
                    'name' => 'stock_out[ports][]',
                    'id' => 'chk-item-' . create_slug($port),
                    'value' => $port,
                    'class' => 'chk-port-item',
                    'checked' => $checked
                ));
                ?>
                <label for="<?php echo 'chk-item-' . create_slug($port); ?>"><span></span></label>
                <label class="mt-5 pointer" for="<?php echo 'chk-item-' . create_slug($port); ?>"><?php echo $port; ?></label>
            </div>
            <?php endforeach; ?>
            <div class="cl"></div>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?php endif; ?>
<div class="register-box pd-15 mt-15 white-bg">
    <div class="employee-form">
        <label class="font-14">Người giao hàng</label>
        <!-- if the employee is not set , show employee adding form -->
        <?php echo form_open("stock_out/select_employee",array('id' => 'frm-select-employee', 'autocomplete' => 'off')); ?>
        <div class="input-group">
            <span class="input-group-btn">
                <?php echo anchor_popup("employees/view/-1","<i class='ion-plus'></i>", array('class' => 'btn btn-primary', 'title' => lang('stock_in_new_employee'), 'id' => 'new-employee')); ?>
            </span>
            <input type="text" id="employee" name="employee" class="add-employee-input form-control validation" required data-value="<?php echo $this->session->userdata('employee') <=0? '': $this->session->userdata('employee') ?>" error-message="employee-error" data-title="Nhân viên giao hàng" placeholder="Nhập tên nhân viên giao hàng..." autocomplete="off" />
        </div>
        <span class="error-message font-14" id="employee-error"></span>
        <?php echo form_close(); ?>
        <?php if (!empty($employee)): ?>
            <div class="employee mt-15">
                <div class="avatar pull-left mr-15">
                    <img width="50px" src="<?php echo $employee_avatar; ?>" alt="">
                </div>
                <a tabindex="-1" href="<?php echo site_url("employees/view/$employee_id/1"); ?>" class="bold font-14">
                    <?php echo character_limiter(H($employee), 30); ?>
                </a>
                <div class="cl"></div>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="register-box pd-15 mt-15 white-bg">
    <div class="employee-form">
        <label class="font-14">Số xe</label>
        <?php echo form_open('stock_out/select_license_plates', array('id' => 'frm-select-license-plates', 'autocomplete' => 'off')); ?>
        <select error-message="license-plates-error" required id="license-plate" class="form-control">
            <option value="">Chọn biển số xe</option>
            <?php foreach ($license_plates as $license_plate): ?>
            <option <?php if (isset($selected_license_plate) && $selected_license_plate == $license_plate): ?>selected<?php endif; ?>><?php echo $license_plate; ?></option>
            <?php endforeach; ?>
        </select>
        <span class="error-message font-14" id="license-plates-error"></span>
        <?php echo form_close(); ?>
    </div>
</div>
<div class="register-box pd-15 mt-15 white-bg">
    <?php echo form_open("stock_out/select_received_day",array('id' => 'frm-select-select-received-day', 'autocomplete' => 'off')); ?>
    <div class="customer-form">
        <label class="font-14">Thời gian thực giao hàng</label>
        <input value="<?php if (!empty($received_day)): ?><?php echo $received_day; ?><?php endif; ?>"
               data-value="<?php if (!empty($received_day)): ?><?php echo $received_day; ?><?php endif; ?>"
               type="text" id="received-day"
               name="received_day"
               class="form-control validation font-arial font-14"
               error-message="received-day-error"
               required data-title="Thời gian thực giao hàng"
               placeholder="Thời gian thực giao hàng"
               autocomplete="off" />
        <span class="error-message font-14" id="received-day-error"></span>
    </div>
    <?php echo form_close(); ?>
</div>
<div class="register-box pd-15 mt-15 white-bg">
    <div class="cl">
        <label class="font-14" id="comment-label" for="comment"><?php echo lang('common_comments'); ?> </label>
        <?php echo form_textarea(
                array(
                        'name' => 'comment',
                        'id' => 'comment',
                        'value' => $comment,
                        'placeholder' => 'Lưu ý',
                        'rows' => '2',
                        'class' => 'form-control',
                        'data-title' => lang('common_comments')));
        ?>
    </div>
    <?php echo form_open('stock_out/save_stock_out_request', array('id' => 'frm-finish', 'autocomplete'=> 'off')); ?>
    <div class="mt-15">
        <div class="pull-left hidden">
            <div class="mt-5">
                <span class="key"><?php echo lang('common_total'); ?>: </span>
                <span class="value font-18"><?php echo to_currency($total); ?></span>
            </div>
        </div>
        <div class="pull-right">
            <button id="btn-finish" class="btn uppercase btn-primary">Yêu cầu xuất kho</button>
        </div>
        <div class="cl"></div>
    </div>
    <?php echo form_close(); ?>
</div>
<?php endif; ?>
<?php else: ?>
<div class="register-box pd-15 white-bg">
    <div class="customer-form">
        <?php if (!empty($sale_daily)): ?>
        <div class="employee">
            <a tabindex="-1" target="_blank" href="<?php echo site_url("orders/update/$customer/" . get_data($sale_daily, 'sale_monthly_id')); ?>" class="bold font-14">
                Đơn hàng ngày <?php echo render_date(get_data($sale_daily, 'date')); ?>
            </a>
            <div class="cl"></div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php if (!empty($ports)): ?>
<div class="register-box pd-15 mt-15 white-bg">
    <?php echo form_open('stock_out/set_port',array('id' => 'frm-select-employee', 'autocomplete' => 'off')); ?>
    <div class="customer-form">
        <label class="font-14">Cổng giao hàng (<?php echo count($ports); ?>)</label>
        <div class="row">
            <?php foreach ($ports as $port): ?>
                <div class="col-md-6">
                    <?php
                    $checked = false;
                    if (!empty($selected_ports)) {
                        foreach ($selected_ports as $selected_port) {
                            if (strstr($port, $selected_port)) {
                                $checked = true;
                            }
                        }
                    }
                    echo form_checkbox(array(
                        'name' => 'stock_out[ports][]',
                        'id' => 'chk-item-' . create_slug($port),
                        'value' => $port,
                        'class' => 'chk-port-item',
                        'checked' => $checked,
                        'disabled' => true
                    ));
                    ?>
                    <label for="<?php echo 'chk-item-' . create_slug($port); ?>"><span></span></label>
                    <label class="mt-5 pointer" for="<?php echo 'chk-item-' . create_slug($port); ?>"><?php echo $port; ?></label>
                </div>
            <?php endforeach; ?>
            <div class="cl"></div>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?php endif; ?>
<div class="register-box pd-15 mt-15 white-bg">
    <div class="employee-form">
        <label class="font-14">Người giao hàng</label>
        <?php if (!empty($employee)): ?>
        <div class="employee mt-10">
            <div class="avatar pull-left mr-15">
                <img width="50px" src="<?php echo $employee_avatar; ?>" alt="">
            </div>
            <a tabindex="-1" href="<?php echo site_url("employees/view/$employee_id/1"); ?>" class="bold font-14">
                <?php echo character_limiter(H($employee), 30); ?>
            </a>
            <div class="cl"></div>
        </div>
        <?php endif; ?>
    </div>
</div>
<div class="register-box pd-15 mt-15 white-bg">
    <div class="employee-form">
        <label class="font-14">Số xe</label>
        <select disabled error-message="license-plates-error" required id="license-plate" class="form-control">
            <option value="">Chọn biển số xe</option>
            <?php foreach ($license_plates as $license_plate): ?>
            <option <?php if (isset($selected_license_plate) && $selected_license_plate == $license_plate): ?>selected<?php endif; ?>><?php echo $license_plate; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<div class="register-box pd-15 mt-15 white-bg">
    <div class="customer-form">
        <label class="font-14">Thời gian thực giao hàng</label>
        <input value="<?php if (!empty($received_day)): ?><?php echo $received_day; ?><?php endif; ?>"
               data-value="<?php if (!empty($received_day)): ?><?php echo $received_day; ?><?php endif; ?>"
               type="text" id="received-day"
               name="received_day"
               class="form-control validation font-arial font-14"
               error-message="received-day-error"
               required data-title="Thời gian thực giao hàng"
               placeholder="Thời gian thực giao hàng"
               autocomplete="off"
               disabled
        />
        <span class="error-message font-14" id="received-day-error"></span>
    </div>
</div>
<div class="register-box pd-15 mt-15 white-bg">
    <div class="cl">
        <label class="font-14" id="comment-label" for="comment"><?php echo lang('common_comments'); ?></label>
        <?php echo form_textarea(
            array(
                'name' => 'comment',
                'id' => 'comment',
                'value' => $comment,
                'placeholder' => 'Lưu ý',
                'rows' => '2',
                'class' => 'form-control',
                'disabled' => true,
                'data-title' => lang('common_comments')));
        ?>
    </div>
    <?php echo form_open('stock_out/complete_stock_out_order ', array('id' => 'frm-request', 'autocomplete'=> 'off')); ?>
    <input type="hidden" value="<?php echo get_data($stock_request, 'stock_id'); ?>" name="stock_request_id" />
    <div class="mt-15">
        <?php if (get_data($stock_request, 'status') != \Models\Stock::STATUS_ACCEPTED): ?>
        <div class="pull-right">
            <a href="stock_out/cancel_stock_request/<?php echo get_data($stock_request, 'stock_id'); ?>" class="btn btn-danger uppercase" onclick="return window.confirm('Bạn có chắc muốn hủy yêu cầu này?')" style ="border: none;" id="stock-out-cancel">Hủy yêu cầu</a>
            <button class="btn uppercase btn-primary">Xuất kho</button>
        </div>
        <?php else: ?>
        <div class="pull-right uppercase font-14">
            Tình trạng: <strong>Đã xuất kho</strong>
        </div>
        <?php endif; ?>
        <div class="cl"></div>
    </div>
    <?php echo form_close(); ?>
</div>
<?php endif; ?>
<script type="text/javascript">
    $('#input-filter-date').datepicker({
        format: 'mm/yyyy',
        viewMode: "months",
        minViewMode: "months"
    }).on('beforeShow', function(e){
        $("#input-filter-date").autocomplete('close');
    }).on('changeDate', function(e){
        $(this).datepicker('hide');
    });
    $("#input-filter-date").autocomplete({
        source: '<?php echo site_url("stock_out/search_sale_daily"); ?>',
        delay: 10,
        autoFocus: false,
        minLength: 0,
        select: function(event, ui) {
            $("#input-filter-date").val(ui.item.value);
            $.post('<?php echo site_url("stock_out/select_sale_daily"); ?>', {sale_daily: ui.item.value}, function(response) {
                location.reload();
            });
        },
    }).data("ui-autocomplete")._renderItem = function (ul, item) {
        return $("<li class='item-suggestions'></li>")
        .data("item.autocomplete", item)
        .append('<a class="suggest-item font-14 bold">' + item.label + '</a>')
        .appendTo(ul);
    };
    $('#input-filter-date').change(function() {
        $(this).autocomplete("search");
    });
    $( "#employee" ).autocomplete({
        source: '<?php echo site_url("stock_out/search_employee");?>',
        delay: 50,
        autoFocus: false,
        minLength: 0,
        select: function(event, ui)
        {
            $.post('<?php echo site_url("stock_out/select_employee");?>', {employee: ui.item.value}, function(response) {
                $("#ajax-result").html(response);
            });
        },
    }).data("ui-autocomplete")._renderItem = function (ul, item) {
        return $("<li class='customer-badge suggestions'></li>")
        .data("item.autocomplete", item)
        .append('<a class="suggest-item"><div class="avatar">' +
            '<img width="auto" height="35px" src="' + item.avatar + '" alt="">' +
            '</div>' +
            '<div class="details">' +
            '<div class="name">' +
            item.label +
            '</div>' +
            '<span class="email">' +
            item.subtitle +
            '</span>' +
            '</div></a>')
        .appendTo(ul);
    };
    $('.chk-port-item').each(function() {
        $(this).click(function() {
            var port = $(this).val();
            var checked = ($(this).prop('checked')) ? 1 : 0;
            $.post('<?php echo site_url("stock_out/set_port");?>', {port: port, selected: checked}, function(response) {
                $("#ajax-result").html(response);
            });
        });
    });
    $('#comment').change(function() {
        $.post('<?php echo site_url("stock_out/set_comment");?>', {comment: $('#comment').val()});
    });
    $('#license-plate').change(function() {
        $.post('<?php echo site_url("stock_out/set_license_plate");?>', {license_plate: $('#license-plate').val()});
    });
    $('#received-day').datepicker({
        format: 'dd/mm/yyyy'
    }).on('changeDate', function(e){
        $(this).attr("data-value", $(this).val());
        $.post('<?php echo site_url("stock_out/set_received_day");?>', {received_day: $(this).val()});
    });
</script>
<script type="text/javascript">
    <?php if (!empty($stock_request)): ?>
    $('.chk-request-item').each(function() {
        $(this).click(function() {
            var checked = $(this).prop('checked');
            var item_id = parseInt($(this).val().toString());
            if (!checked) {
                $.post('<?php echo site_url('stock_out/unselect_item?stock_request_id=' . get_data($stock_request, 'stock_id')); ?>', {item_id: item_id});
            } else {
                $.post('<?php echo site_url('stock_out/select_item?stock_request_id=' . get_data($stock_request, 'stock_id')); ?>', {item_id: item_id});
            }
        });
    });
    if ($("#frm-request").size() > 0) {
        $("#frm-request").submit(function() {
            var $checked_items = $(".chk-request-item:checked");
            if ($checked_items.size() == 0) {
                bootbox.confirm('<span class="font-arial font-14">Bạn phải chọn Thành phẩm!</span>', function() {
                    setTimeout(function() {$('#input-filter').focus();}, 50);
                });
                return false;
            }
            return true;
        });
    }
    <?php else: ?>
    if ($("#frm-finish").size() > 0) {
        $("#frm-finish").bind("submit", function() {
            var $checked_items = $(".chk-request-item");
            if ($checked_items.size() == 0) {
                bootbox.confirm('<span class="font-arial font-14">Bạn phải chọn Thành phẩm!</span>', function() {
                    setTimeout(function() {$('#input-filter').focus();}, 50);
                });
                return false;
            }
            return true;
        });
    }
    <?php endif; ?>
    $("#stock-out-history").click(function() {
        $.ajax({
            url: "<?php echo site_url('stock_out/get_list_stock_request'); ?>"
        }).done(function(html) {
            $("#stock-modal .modal-content").first().html(html);
            $("#stock-modal").modal();
        });
        return false;
    });
</script>