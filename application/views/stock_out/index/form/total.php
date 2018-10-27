<div class="register-box pd-15 white-bg">
    <div class="customer-form">
        <!-- if the supplier is not set , show supplier adding form -->
        <?php echo form_open("stock_out/select_receiver_location",array('id' => 'frm-select-supplier', 'autocomplete' => 'off')); ?>
        <div class="input-group">
            <input type="text" id="location" name="location" class="add-customer-input form-control validation" required data-value = "<?php echo $this->session->userdata('receiver_location') <=0? '': $this->session->userdata('receiver_location')?>" error-message = "location-error" data-title="Đơn vị nhân hàng" placeholder="Nhập tên đơn vị nhận hàng..." autocomplete="off"/>
        </div>
        <span id = "location-error"></span>
        <?php echo form_close(); ?>
        <?php if (!empty($receiver_location)): ?>
        <div class="supplier mt-15">
            <div class="avatar pull-left">
            </div>
            <a tabindex="-1" href="" class="bold">
                <?php echo character_limiter(H($receiver_location_name), 60); ?>
            </a>
            <div class="cl"></div>
        </div>
        <?php endif; ?>
    </div>
</div>
<div class="register-box pd-15 white-bg mt-15">
    <div class="employee-form">
        <!-- if the employee is not set , show employee adding form -->
        <?php echo form_open("stock_out/select_employee",array('id' => 'frm-select-employee', 'autocomplete' => 'off')); ?>
        <div class="input-group">
            <span class="input-group-btn">
                <?php echo anchor("employee/view/-1/1","<i class='ion-plus'></i>", array('class' => 'btn btn-primary', 'title' => lang('stock_out_new_employee'), 'id' => 'new-employee')); ?>
            </span>
            <input type="text" id="employee" name="employee" class="add-employee-input form-control validation" required data-value = "<?php echo $this->session->userdata('employee') <=0? '': $this->session->userdata('employee')?>" error-message = "employee-error" data-title="Nhà cung cấp" placeholder="Nhập tên nhân viên..." autocomplete="off"/>
        </div>
         <span id = "employee-error"></span>
        <?php echo form_close(); ?>
        <?php if (!empty($employee)): ?>
            <div class="employee mt-15">
                <div class="avatar pull-left mr-15">
                    <img width="50px" src="<?php echo $employee_avatar; ?>" alt="">
                </div>
                <a tabindex="-1" href="<?php echo site_url("employees/view/$employee_id/1"); ?>" class="bold">
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
        <?php echo form_textarea(array('name' => 'comment', 'id' => 'comment', 'value' => $comment, 'rows' => '2', 'class' => 'form-control', 'data-title' => lang('common_comments'))); ?>
    </div>
    <?php echo form_open("stock_out/complete", array('id' => 'frm-finish', 'autocomplete'=> 'off')); ?>
    <div class="mt-15">
        <div class="pull-left">
            <div class="mt-5">
                <span class="key"><?php echo lang('common_total'); ?>: </span>
                <span class="value font-18"><?php echo to_currency($total); ?></span>
            </div>
        </div>
        <div class="pull-right">
            <button class="btn btn-lg uppercase btn-primary"><?php echo lang('common_stock_out_complete_stock_out'); ?></button>
        </div>
        <div class="cl"></div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript">
    $( "#location" ).autocomplete({
        source: '<?php echo site_url("stock_out/search_receiver_location");?>',
        delay: 150,
        autoFocus: false,
        minLength: 0,
        select: function(event, ui)
        {
            $.post('<?php echo site_url("stock_out/select_receiver_location");?>', {receiver_location: ui.item.value }, function(response) {
                $("#ajax-result").html(response);
            });
        },
    }).data("ui-autocomplete")._renderItem = function (ul, item) {
        return $("<li class='customer-badge suggestions'></li>")
        .data("item.autocomplete", item)
        .append('<a class="suggest-item"><div class="avatar">' +
            '<img src="' + item.avatar + '" alt="">' +
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
    $( "#employee" ).autocomplete({
        source: '<?php echo site_url("employees/suggest");?>',
        delay: 150,
        autoFocus: false,
        minLength: 0,
        select: function(event, ui)
        {
            $.post('<?php echo site_url("stock_out/select_employee");?>', {employee: ui.item.value }, function(response) {
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
    $('#comment').change(function() {
        $.post('<?php echo site_url("stock_out/set_comment");?>', {comment: $('#comment').val()});
    });
    $('#btn-finish').on('click',function(e){
        e.preventDefault();
        $('#frm-finish').ajaxSubmit({target: "#ajax-result", beforeSubmit: beforeSubmit});
    });

    $('#new-receiver-location').on('click', function(){
        $.ajax({
			url: 'stock_out/add_new_receiver_location',
			type: 'post',
			data: {location: $('#location').val() },
			success: function(response) {
				$("#ajax-result").html(response);
			}
        });
    });
</script>