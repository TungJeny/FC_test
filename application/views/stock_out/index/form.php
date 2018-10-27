<div class="modal fade hidden-print" id="stock-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Ajax Content -->
        </div>
    </div>
</div>
<div id="ajax-result" class="font-arial" href="<?php echo site_url('stock_out/index'); ?>">
    <?php if (isset($stock_request)): ?>
    <div class="heading pdl-15"><i class="icon ti-upload"></i> Yêu cầu xuất kho</div>
    <?php endif; ?>
    <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 no-padding-right">
        <?php echo form_open("stock_out/add_item", array('id' => 'frm-add-item', 'autocomplete' => 'off')); ?>
        <?php if (!empty($stock_request)): ?>
        <input class="mb-15" type="hidden" name="stock_request_id" value="<?php echo get_data($stock_request, 'stock_id'); ?>" />
        <?php if (get_data($stock_request, 'status') != \Models\Stock::STATUS_ACCEPTED): ?>
        <div class="box-search white-bg pd-15">
            <div class="input-group">
            <input name="item" id="input-filter" type="text" class="form-control" placeholder="Tìm kiếm theo <?php echo get_data($view_data, 'placeholder'); ?>" />
            <?php if (!empty($customers) && $stock_type == \Models\Stock_out::STOCK_TYPE_ORDER): ?>
            <span class="input-group-btn">
                <a class="btn btn-success uppercase change-customer"><?php echo get_data($view_data, 'customer'); ?></a>
            </span>
            <?php endif; ?>
            <span class="input-group-btn">
            <a class="btn btn-primary uppercase change-mode"><?php echo get_data($view_data, 'button_change_mode'); ?></a>
        </span>
        </div>
            <?php if (!empty($customers) && $stock_type == \Models\Stock_out::STOCK_TYPE_ORDER): ?>
                <ul class="dropdown-menu sales-dropdown list-mode-style" id="customer-dropdown">
                    <?php foreach($customers as $customer):?>
                        <li><a href="#" id="<?php echo get_data($customer, 'code'); ?>" class="selected-customer"><?php echo get_data($customer, 'code'); ?></a></li>
                    <?php endforeach;?>
                </ul>
            <?php endif; ?>
            <ul class="dropdown-menu sales-dropdown list-mode-style" id="mode-dropdown">
                <?php foreach($modes as $code => $name):?>
                    <li><a id="<?php echo $code; ?>" class="selected-mode"><?php echo $name; ?></a></li>
                <?php endforeach;?>
            </ul>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="box-search white-bg pd-15">
            <div class="input-group">
                <input name="item" id="input-filter" type="text" class="form-control" placeholder="Tìm kiếm theo <?php echo get_data($view_data, 'placeholder'); ?>" />
                <?php if (!empty($customers) && $stock_type == \Models\Stock_out::STOCK_TYPE_ORDER): ?>
                    <span class="input-group-btn">
                <a class="btn btn-success uppercase change-customer"><?php echo get_data($view_data, 'customer'); ?></a>
            </span>
                <?php endif; ?>
                <span class="input-group-btn">
                <a class="btn btn-primary uppercase change-mode"><?php echo get_data($view_data, 'button_change_mode'); ?></a>
            </span>
            </div>
            <?php if (!empty($customers) && $stock_type == \Models\Stock_out::STOCK_TYPE_ORDER): ?>
                <ul class="dropdown-menu sales-dropdown list-mode-style" id="customer-dropdown">
                    <?php foreach($customers as $customer):?>
                        <li><a href="#" id="<?php echo get_data($customer, 'code'); ?>" class="selected-customer"><?php echo get_data($customer, 'code'); ?></a></li>
                    <?php endforeach;?>
                </ul>
            <?php endif; ?>
            <ul class="dropdown-menu sales-dropdown list-mode-style" id="mode-dropdown">
                <?php foreach($modes as $code => $name):?>
                    <li><a id="<?php echo $code; ?>" class="selected-mode"><?php echo $name; ?></a></li>
                <?php endforeach;?>
            </ul>
        </div>
        <?php endif; ?>
        <?php echo form_close(); ?>
        <div class="box-result font-14 white-bg">
            <?php
            switch ($stock_type) {
                case \Models\Stock_out::STOCK_TYPE_ORDER:
                    echo form_open("Stock_out/complete", array('id' => 'frm-stock-request', 'autocomplete' => 'off'));
                    $this->load->view('stock_out/index/form/items_order');
                    echo form_close();
                    break;
                case \Models\Stock_out::STOCK_TYPE_PACKAGE:
                    echo form_open("Stock_out/complete", array('id' => 'frm-stock-request', 'autocomplete' => 'off'));
                    $this->load->view('stock_out/index/form/items_package');
                    echo form_close();
                    break;
                default:
                    $this->load->view('stock_out/index/form/items');
                    break;
            }
            ?>
        </div>
    </div>
    <div class="col-lg-4 col-md-5 col-sm-12 col-xs-12">
        <?php
        switch ($stock_type) {
            case \Models\Stock_out::STOCK_TYPE_ORDER:
                $this->load->view('stock_out/index/form/total_order');
                break;
            case \Models\Stock_out::STOCK_TYPE_PACKAGE:
                $this->load->view('stock_out/index/form/total_package');
                break;
            default:
                $this->load->view('stock_out/index/form/total');
                break;
        }
        ?>
    </div>
</div>
<?php if ($this->config->item('confirm_error_adding_item') && isset($error)):  ?>
<script type="text/javascript">
    bootbox.confirm(<?php echo json_encode($error); ?>, function(result) {
        setTimeout(function() {$('#item').focus();}, 50);
    });
</script>
<?php endif ?>
<script type="text/javascript">
<?php
if(isset($error) && !$this->config->item('confirm_error_adding_item')) {
    echo "show_feedback('error', ".json_encode($error).", ".json_encode(lang('common_error')).");";
}
if (isset($warning)) {
    echo "show_feedback('warning', ".json_encode($warning).", ".json_encode(lang('common_warning')).");";
}
if (isset($success)) {
    echo "show_feedback('success', ".json_encode($success).", ".json_encode(lang('common_success')).");";
}
?>
</script>
<script type="text/javascript">
    var submitting = false;
    $("#input-filter").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '<?php echo site_url("stock_out/search_item"); ?>',
                dataType: "json",
                data: {
                    term: $("#input-filter").val(),
                    data_type : $("#input-filter-hidden").val(),
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        delay: 150,
        autoFocus: false,
        minLength: 0,
        select: function(event, ui) {
            $("#input-filter").val(ui.item.value);
            $('#frm-add-item').ajaxSubmit({target: "#ajax-result", beforeSubmit: beforeSubmit, success: afterSubmit});
        },
    }).data("ui-autocomplete")._renderItem = function (ul, item) {
        return $("<li class='item-suggestions'></li>")
            .data("item.autocomplete", item)
            .append('<a class="suggest-item"><div class="item-image">' +
                '<img src="' + item.image + '" alt="">' +
                '</div>' +
                '<div class="details">' +
                '<div class="name">' +
                item.label +
                '</div>' +
                '<span class="attributes">' +
                '<?php echo lang("common_category"); ?>' + ' : <span class="value">' + (item.category ? item.category : <?php echo json_encode(lang('common_none')); ?>) + '</span>' +
                '</span>' +
                '</div>')
            .appendTo(ul);
    };
    $('#frm-add-item').ajaxForm({target: "#ajax-result", beforeSubmit: beforeSubmit, success: afterSubmit});
    $('.delete-item').click(function(event)
    {
        event.preventDefault();
        $("#ajax-result").load($(this).attr('href'));
    });
    // Editable
    $('.xeditable').editable({
        validate: function(value) {
            if ($.isNumeric(value) == '' && $(this).data('validate-number')) {
                return <?php echo json_encode(lang('common_only_numbers_allowed')); ?>;
            }
        },
        success: function(response, newValue) {
            last_focused_id = $(this).attr('id');
            $("#ajax-result").html(response);
        }
    });
    function beforeSubmit(formData, jqForm, options)
    {
        if (submitting)
        {
            return false;
        }
        submitting = true;
        $("#ajax-loader").show();
    };
    function afterSubmit(responseText, statusText, xhr, $form)
    {
        setTimeout(function() {
            $('#input-filter').focus();
        }, 5);
    };
    $('#frm-finish').submit(function(){
        if (!validates()) {
            return false;
        }
    });
    function validates() {
        var validate = true;
        $('.validation').each(function(){
        	if($(this).prop('required')){
            	if ($(this).attr('data-value') == '') {
            		$('#'+$(this).attr('error-message')).html('Không thể rỗng');
            		$('#'+$(this).attr('error-message')).css('color', 'red');
            		validate = false;
            	}
        	}
        });
        return validate;
    }
    $('.change-mode').on('click', function(){
        $('.sales-dropdown').removeClass('display-block');
        $('#mode-dropdown').toggleClass('display-block');
    });
    $('.change-customer').on('click', function(){
        $('.sales-dropdown').removeClass('display-block');
        $('#customer-dropdown').toggleClass('display-block');
    });
    $('.selected-mode').on('click', function(){
        var stock_type = $(this).attr('id');
        $('.list-mode-style').removeClass('display-block');
        $.ajax({
            url: '<?php echo site_url("stock_out/change_stock_type"); ?>',
            type: 'POST',
            data: {
                stock_type : stock_type,
            },
            success: function(responses) {
                location.reload();
            }
        });
    });
    $('.selected-customer').on('click', function(){
        var customer = $(this).attr('id');
        $('.list-mode-style').removeClass('display-block');
        $.ajax({
            url: '<?php echo site_url("stock_out/change_customer"); ?>',
            type: 'POST',
            data: {
                customer : customer,
            },
            success: function(responses) {
                location.reload();
            }
        });
    });
</script>