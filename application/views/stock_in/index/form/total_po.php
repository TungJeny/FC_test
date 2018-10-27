<div class="register-box pd-15 white-bg">
	<div class="customer-form">
		<a href="stock_in/cancel_stock_in" class ="btn btn-danger" style ="border: none;" id = "stock-in-cancel">Hủy đơn hàng</a>
	</div>
</div>
<div class="register-box pd-15 white-bg  mt-15">

    <div class="customer-form">
        <!-- if the supplier is not set , show supplier adding form -->
        <?php echo form_open("stock_in/select_supplier",array('id' => 'frm-select-supplier', 'autocomplete' => 'off')); ?>
        <span id = "supplier-error"></span>
        <?php echo form_close(); ?>
        <?php if (!empty($supplier)): ?>
        <div class="supplier">
            <div class="avatar pull-left mr-15">
                <img width="50px" src="<?php echo $avatar; ?>" alt="">
            </div>
            <a tabindex="-1" href="<?php echo site_url("suppliers/view/$supplier_id/1"); ?>" class="bold">
                <?php echo character_limiter(H($supplier), 30); ?>
                <?php if ($this->config->item('suppliers_store_accounts') && isset($supplier_balance)): ?>
                <span class="<?php echo $has_balance ? 'text-danger' : 'text-success'; ?> balance">(<?php echo to_currency($supplier_balance); ?>)</span>
                <?php endif; ?>
            </a>
            <?php if (!empty($supplier_email)): ?>
            <span class="email">
                <?php echo character_limiter(H($supplier_email), 25); ?>
            </span>
            <?php endif; ?>
            <div class="cl"></div>
        </div>
        <?php endif; ?>
    </div>
</div>
<div class="register-box pd-15 white-bg mt-15">
    <div class="employee-form">
        <!-- if the employee is not set , show employee adding form -->
        <?php echo form_open("stock_in/select_employee",array('id' => 'frm-select-employee', 'autocomplete' => 'off')); ?>
        <div class="input-group">
            <span class="input-group-btn"> <a id="new-employee"
				href="javascript:void(0)" class="btn btn-primary"> <i
					class='ion-plus'></i>
			</a>
			</span>
            <input type="text" id="employee" required data-value ="<?php echo  $this->session->userdata('employee') <= 0? '' : $this->session->userdata('employee');?>" name="employee" class="add-employee-input form-control validation" error-message="employee-error"  data-title="Nhà cung cấp" placeholder="Nhập tên nhân viên..." autocomplete="off"/>
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
    <?php echo form_open("stock_in/complete", array('id' => 'frm-finish', 'autocomplete'=> 'off')); ?>
    <div class="mt-15">
        <div class="pull-left">
            <div class="mt-5">
                <span class="key"><?php echo lang('common_total'); ?>: </span>
                <span class="value font-18"><?php echo to_currency($total); ?></span>
            </div>
        </div>
        <div class="pull-right">
            <button class="btn btn-lg uppercase btn-primary">Tạo phiếu nhập kho</button>
        </div>
        <div class="cl"></div>
    </div>
    <?php echo form_close(); ?>
</div>

<script type="text/javascript">
    $( "#employee" ).autocomplete({
        source: '<?php echo site_url("employees/suggest");?>',
        delay: 150,
        autoFocus: false,
        minLength: 0,
        select: function(event, ui)
        {
            $.post('<?php echo site_url("stock_in/select_employee");?>', {employee: ui.item.value }, function(response) {
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
        $.post('<?php echo site_url("stock_in/set_comment");?>', {comment: $('#comment').val()});
    });

</script>