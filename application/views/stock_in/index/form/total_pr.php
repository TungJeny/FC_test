<div class="register-box pd-15 white-bg">
	<div class="customer-form">
		<a class="btn btn-primary uppercase" id="stock-in-history">Danh sách yêu cầu</a>
        <?php if (empty($stock_request)): ?>
		<a href="stock_in/cancel_stock_in" class="btn btn-danger uppercase" onclick="return window.confirm('Bạn muốn nhập lại từ đầu?')" style ="border: none;" id="stock-in-cancel">Nhập lại</a>
        <?php else: ?>
        <?php if (get_data($stock_request, 'status') == 1): ?>
        <a href="stock_in/cancel_stock_request/<?php echo get_data($stock_request, 'stock_id'); ?>" class="btn btn-danger uppercase" onclick="return window.confirm('Bạn có chắc muốn hủy yêu cầu này?')" style ="border: none;" id="stock-in-cancel">Hủy yêu cầu</a>
        <?php else: ?>
        <a href="stock_in/cancel_stock_in" class="btn btn-danger uppercase" onclick="return window.confirm('Bạn muốn nhập lại từ đầu?')" style ="border: none;" id="stock-in-cancel">Nhập lại</a>
        <?php endif; ?>
        <?php endif; ?>
	</div>
</div>
<!-- Select Receiver Location -->
<?php if (empty($stock_request)): ?>
<div class="register-box pd-15 mt-15 white-bg">
    <div class="customer-form">
        <?php echo form_open("stock_in/select_receiver_location",array('id' => 'frm-select-supplier', 'autocomplete' => 'off')); ?>
        <div class="cl font-14 font-normal">
            <label>Nhập TP từ xưởng</label>
            <input type="text" id="location" name="location" class="add-customer-input form-control validation" required data-value = "<?php echo $this->session->userdata('receiver_location') <=0? '': $this->session->userdata('receiver_location')?>" error-message="location-error" data-title="Đơn vị nhân hàng" placeholder="Nhập tên xưởng..." autocomplete="off"/>
        </div>
        <span class="error-message font-14" id="location-error"></span>
        <?php echo form_close(); ?>
        <?php if (!empty($receiver_location)): ?>
        <div class="supplier mt-10 font-14">
            <a tabindex="-1" href="<?php echo site_url("location/view/$receiver_location"); ?>" class="bold">
                <?php echo character_limiter(H($receiver_location_name), 60); ?>
            </a>
            <div class="cl"></div>
        </div>
        <?php endif; ?>
    </div>
</div>
<div class="register-box pd-15 mt-15 white-bg">
    <div class="customer-form">
        <?php echo form_open("stock_in/select_package",array('id' => 'frm-select-package', 'autocomplete' => 'off')); ?>
        <div class="cl font-14 font-normal">
            <label>Mã lô VT</label>
            <input type="text" id="package" name="package" class="add-customer-input form-control validation" required data-value="<?php echo (isset($package) ? $package : '');  ?>" error-message = "package-error" error-message="package-error" data-title="Mã lô VT" placeholder="Tìm kiếm mã lô VT..." autocomplete="off"/>
        </div>
        <span class="error-message font-14" id="package-error"></span>
        <?php echo form_close(); ?>
        <?php if (!empty($package)): ?>
        <div class="package mt-10 font-14">
            <a tabindex="-1" href="<?php echo site_url("package/view/$package_id"); ?>" class="bold font-14">
                <?php echo $package; ?>
            </a>
            <div class="cl"></div>
        </div>
        <?php endif; ?>
    </div>
</div>
<div class="register-box pd-15 white-bg mt-15">
    <div class="employee-form">
        <?php echo form_open("stock_in/select_employee",array('id' => 'frm-select-employee', 'autocomplete' => 'off')); ?>
        <label class="font-14">Nhân viên nhập kho</label>
        <div class="input-group">
            <span class="input-group-btn">
                <?php echo anchor_popup("employees/view/-1","<i class='ion-plus'></i>", array('class' => 'btn btn-primary', 'title' => lang('stock_in_new_employee'), 'id' => 'new-employee')); ?>
            </span>
            <input type="text" id="employee" required data-value ="<?php echo  $this->session->userdata('employee') <= 0? '' : $this->session->userdata('employee');?>" name="employee" class="add-employee-input form-control validation" error-message="employee-error"  data-title="Nhà cung cấp" placeholder="Nhập tên nhân viên nhập kho..." autocomplete="off"/>
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
<div class="register-box pd-15 white-bg mt-15">
    <div class="cl">
        <label class="font-14" id="comment-label" for="comment"><?php echo lang('common_comments'); ?> </label>
        <?php echo form_textarea(array('name' => 'comment', 'id' => 'comment', 'placeholder' => 'Nhập lưu ý', 'value' => $comment, 'rows' => '2', 'class' => 'form-control', 'data-title' => lang('common_comments'))); ?>
    </div>
    <?php echo form_open("stock_in/complete", array('id' => 'frm-finish', 'autocomplete'=> 'off')); ?>
    <div class="mt-15">
        <div class="pull-left hidden">
            <div class="mt-5">
                <span class="key"><?php echo lang('common_total'); ?>: </span>
                <span class="value font-18"><?php echo to_currency($total); ?></span>
            </div>
        </div>
        <div class="pull-right">
            <button class="btn btn-lg uppercase btn-primary">Yêu cầu nhập kho</button>
        </div>
        <div class="cl"></div>
    </div>
    <?php echo form_close(); ?>
</div>
<?php else: ?>
<div class="register-box pd-15 white-bg mt-15">
    <div class="customer-form">
        <div class="cl font-14 font-normal">
            <label>Mã lô TP</label>
        </div>
        <?php if (!empty($product_package)): ?>
        <div class="cl">
            <a tabindex="-1" href="<?php echo site_url("package/view/") . get_data($product_package, 'id'); ?>" class="bold font-14">
                <?php echo get_data($product_package, 'package_code'); ?>
            </a>
            <div class="cl"></div>
        </div>
        <?php endif; ?>
    </div>
</div>
<div class="register-box pd-15 white-bg mt-15">
    <div class="customer-form">
        <div class="cl font-14 font-normal">
            <label>Thành phẩm từ xưởng</label>
        </div>
        <?php if (!empty($receiver_location)): ?>
        <div class="cl font-14">
            <a tabindex="-1" href="<?php echo site_url("location/view/$receiver_location"); ?>" class="bold font-14">
                <?php echo character_limiter(H($receiver_location_name), 60); ?>
            </a>
            <div class="cl"></div>
        </div>
        <?php endif; ?>
    </div>
</div>
<div class="register-box pd-15 mt-15 white-bg">
    <div class="customer-form">
        <div class="cl font-14 font-normal">
            <label>Mã lô VT</label>
        </div>
        <?php if (!empty($package)): ?>
            <div class="package font-14">
                <a tabindex="-1" href="<?php echo site_url("package/view/$package_id"); ?>" class="bold font-14">
                    <?php echo character_limiter(H($package), 60); ?>
                </a>
                <div class="cl"></div>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="register-box pd-15 white-bg mt-15">
    <div class="employee-form">
        <label class="font-14">Nhân viên nhập kho</label>
        <?php if (!empty($employee)): ?>
            <div class="employe mt-10">
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
<div class="register-box pd-15 white-bg mt-15">
    <div class="cl">
        <label class="font-14" id="comment-label" for="comment"><?php echo lang('common_comments'); ?> </label>
        <div class="font-14"><?php echo $comment; ?></div>
    </div>
    <?php if (get_data($stock_request, 'status') != \Models\Stock::STATUS_ACCEPTED): ?>
    <div class="cl">
        <div class="pull-right">
            <button onclick="$('#frm-stock-request').submit();" id="btn-action-product-package" class="btn btn-lg uppercase btn-primary">Nhập kho TP</button>
        </div>
        <div class="cl"></div>
    </div>
    <?php else: ?>
    <div class="cl uppercase font-14">
        <div class="pull-right">
            Tình trạng: <strong>Đã nhập kho</strong>
        </div>
        <div class="cl"></div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php if (empty($stock_request)): ?>
<script type="text/javascript">
    $("#location").autocomplete({
        source: '<?php echo site_url('stock_in/search_receiver_location'); ?>',
        delay: 150,
        autoFocus: false,
        minLength: 0,
        select: function(event, ui)
        {
            $.post('<?php echo site_url('stock_in/select_receiver_location'); ?>', {receiver_location: ui.item.value }, function(response) {
                $("#ajax-result").html(response);
            });
        },
    }).data("ui-autocomplete")._renderItem = function (ul, item) {
        return $("<li class='customer-badge suggestions'></li>")
        .data("item.autocomplete", item)
        .append('<a class="suggest-item font-arial font-14">' + item.label + '</a>')
        .appendTo(ul);
    };
    $( "#employee" ).autocomplete({
        source: '<?php echo site_url('stock_in/search_employee'); ?>',
        delay: 150,
        autoFocus: false,
        minLength: 0,
        select: function(event, ui)
        {
            $.post('<?php echo site_url('stock_in/select_employee'); ?>', {employee: ui.item.value }, function(response) {
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
            '<div class="name font-14 font-arial">' +
            item.label +
            '</div>' +
            '<span class="email">' +
            item.subtitle +
            '</span>' +
            '</div></a>')
        .appendTo(ul);
    };
    $("#package").autocomplete({
        source: '<?php echo site_url('stock_in/search_package'); ?>',
        delay: 50,
        autoFocus: false,
        minLength: 0,
        select: function(event, ui)
        {
            $.post('<?php echo site_url('stock_in/select_package'); ?>', {package: ui.item.value}, function(response) {
                $("#ajax-result").html(response);
            });
        },
    }).data("ui-autocomplete")._renderItem = function (ul, item) {
        return $("<li class='customer-badge suggestions'></li>").data("item.autocomplete", item)
            .append('<a class="suggest-item font-14 font-arial">' + item.label + '</a>')
            .appendTo(ul);
    };
    $('#comment').change(function() {
        $.post('<?php echo site_url("stock_in/set_comment");?>', {comment: $('#comment').val()});
    });
</script>
<?php endif; ?>
<script type="text/javascript">
    <?php if (!empty($stock_request)): ?>
    $('.chk-request-item').each(function() {
        $(this).click(function() {
            var checked = $(this).prop('checked');
            var item_id = parseInt($(this).val().toString());
            if (!checked) {
                $.post('<?php echo site_url('stock_in/unselect_item?stock_request_id=' . get_data($stock_request, 'stock_id')); ?>', {item_id: item_id});
            } else {
                $.post('<?php echo site_url('stock_in/select_item?stock_request_id=' . get_data($stock_request, 'stock_id')); ?>', {item_id: item_id});
            }
        });
    });
    <?php endif; ?>
    $("#stock-in-history").click(function() {
        $.ajax({
            url: "<?php echo site_url('stock_in/get_list_stock_request'); ?>"
        }).done(function(html) {
            $("#stock-modal .modal-content").first().html(html);
            $("#stock-modal").modal();
        });
        return false;
    });
</script>
