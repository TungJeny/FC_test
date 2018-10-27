<div class="modal fade hidden-print" id="stock-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Ajax Content -->
        </div>
    </div>
</div>
<div id="ajax-result" class="font-arial" href="<?php echo site_url('stock_in/index'); ?>">
    <?php if (isset($stock_request)): ?>
    <div class="heading pdl-15"><i class="icon ti-download"></i> Yêu cầu nhập kho</div>
    <?php endif; ?>
    <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 no-padding-right">
        <?php echo form_open("stock_in/add_item", array('id' => 'frm-add-item', 'autocomplete' => 'off')); ?>
        <?php if (!empty($stock_request)): ?>
        <input type="hidden" name="stock_request_id" value="<?php echo get_data($stock_request, 'stock_id'); ?>" />
        <?php endif; ?>
        <div class="box-search white-bg pd-15">
            <div class="input-group">
                <input name="item" id="input-filter" type="text" class="form-control" placeholder="Tìm kiếm theo <?php echo $view_data['placeholder']?>" />
                <span class="input-group-btn">
                    <a class="btn btn-primary uppercase change-mode"><?php echo $view_data['button_change_mode'];?></a>
                </span>
            </div>
	        <ul class="dropdown-menu sales-dropdown list-mode-style">
	        <?php foreach($this->config->item('stock_in_mode') as $code =>$name):?>
	        	<li><a id="<?php echo  $code; ?>" class="selected-mode"><?php echo $name; ?></a></li>
        	<?php endforeach;?>
			</ul>
        </div>
        <?php if(!empty($po_code)):?>
        <div class=" white-bg pd-15">
        <h4>Nhập kho theo đơn hàng po : <?php echo $po_code; ?></h4>
        </div>
        <?php endif; ?>
        
        <?php echo form_close(); ?>
        <div class="box-result font-14 white-bg table-responsive">
        <?php
            switch ($stock_type) {
                case Stock_in::STOCK_TYPE_PO:
                    $this->load->view('stock_in/index/form/items_po');
                    break;
                case Stock_in::STOCK_TYPE_PR:
                    echo form_open("stock_in/complete", array('id' => 'frm-stock-request', 'autocomplete' => 'off'));
                    $this->load->view('stock_in/index/form/items_pr');
                    echo form_close();
                    break;
                default:
                    $this->load->view('stock_in/index/form/items');
                    break;
            }
        ?>
        </div>
    </div>
    <div class="col-lg-4 col-md-5 col-sm-12 col-xs-12">
        <?php
            switch ($stock_type) {
                case Stock_in::STOCK_TYPE_PO:
                    $this->load->view('stock_in/index/form/total_po');
                    break;
                case Stock_in::STOCK_TYPE_PR:
                    $this->load->view('stock_in/index/form/total_pr');
                    break;
                default:
                    $this->load->view('stock_in/index/form/total');
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
                url: '<?php echo site_url("stock_in/search_item"); ?>',
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
            '<div class="name font-14 font-arial">' +
            item.label +
            '</div>' +
            '<span class="attributes">' +
            '<?php echo lang("common_category"); ?>' + ' : <span class="value">' + (item.category ? item.category : <?php echo json_encode(lang('common_none')); ?>) + '</span>' +
            '</span>' +
            '</div>')
        .appendTo(ul);
    };
    $('#frm-finish').submit(function(){
        if (!validates()) {
            return false;
        }
    });
    if ($("#frm-stock-request").size() > 0) {
        $("#frm-stock-request").submit(function() {
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
    function validates() {
        var validate = true;
        $('.validation').each(function(){
        	if($(this).prop('required')){
            	if ($(this).attr('data-value') == '') {
                	if ($(this).hasClass('hidden')) {
                		toastr.error($(this).attr('error-message'));
                		validate = false;
                	} else {
                		$('#'+$(this).attr('error-message')).html('Không thể rỗng');
                		$('#'+$(this).attr('error-message')).css('color', 'red');
                		validate = false;
                	}
                	
            	}
        	}
        });
        return validate;
    }
    $('#frm-add-item').ajaxForm({target: "#ajax-result", beforeSubmit: beforeSubmit, success: afterSubmit});
    $('.delete-item').click(function(event)
    {
        event.preventDefault();
        $("#ajax-result").load($(this).attr('href'));
    });
    // Editable
    $('.xeditable').editable({
        validate: function(value) {
            console.log($.isNumeric(value));
            if ($.isNumeric(value) == '' && $(this).data('validate-number')) {
                return <?php echo json_encode(lang('common_only_numbers_allowed')); ?>;
            }
            if (parseFloat(value) <= 0  && $(this).data('validate-number')) {
                return <?php echo json_encode('Số lượng phải lớn hơn 0'); ?>;
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
        }, 10);
    };
    $('.change-mode').on('click', function(){
        $('.list-mode-style').toggleClass('display-block');
    });
    $('.selected-mode').on('click', function(){
        var stock_type = $(this).attr('id');
        $('.list-mode-style').removeClass('display-block');
        $.ajax({
            url: '<?php echo site_url("stock_in/change_stock_type"); ?>',
            type: 'POST',
            data: {
            	stock_type : stock_type,
            },
            success: function(responses) {
            	location.reload();
            }
        });
    });
    $(".status_check, .note").on("change",  function(){
    	var item_id = $(this).attr('data-id');
    	var name = $(this).attr('name');
    	var type = $(this).attr('type');
    	var value = $(this).val();
    	if (type == 'radio') {
    		value = $(this).prop('checked')? 1 : 0
    	}
		$.ajax({
			url: '<?php echo site_url('stock_in/update_item/'); ?>'+item_id,
			type: 'POST',
			data: {name : name, value : value},
			success: function(response) {
			 	$("#ajax-result").html(response);
			}
		});
		$.ajax({
			url: '<?php echo site_url('stock_in/create_package_code/'); ?>',
			type: 'POST',
			data: {item_id : item_id},
			success: function(response) {
				$("#ajax-result").html(response);
			}
		});
    });
</script>
