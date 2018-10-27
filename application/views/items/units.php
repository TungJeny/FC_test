<?php $this->load->view("partial/header"); ?>
<?php echo form_open('items/save_unit/',array('id'=>'unit_form','class'=>'form-horizontal')); ?>
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-piluku">
					<div class="panel-heading"><?php echo lang("items_manage_units"); ?></div>
					<div class="panel-body">
						<a href="javascript:void(0);" class="add_unit" data-unit_id="0">[<?php echo lang('items_add_unit'); ?>]</a>
							<div id="unit_list" class="unit-tree">
								<?php echo $unit_list; ?>
							</div>
						<a href="javascript:void(0);" class="add_unit" data-unit_id="0">[<?php echo lang('items_add_unit'); ?>]</a>
					</div>
				</div>
			</div>
		</div><!-- /row -->
		<?php  echo form_close(); ?>
	</div>

			
<script type='text/javascript'>

$(document).on('click', ".edit_unit",function()
{
	var unit_id = $(this).data('unit_id');
	bootbox.prompt({
	  title: <?php echo json_encode(lang('items_please_enter_unit_name')); ?>,
	  value: $(this).data('name'),
	  callback: function(unit_name) {
		  
	  	if (unit_name)
	  	{
	  		$.post('<?php echo site_url("items/save_unit");?>'+'/'+unit_id, {unit_name : unit_name},function(response) {	
	  			show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);
	  			if (response.success)
	  			{
	  				$('#unit_list').load("<?php echo site_url("items/unit_list"); ?>");
	  			}
	  		}, "json");

	  	}
	  }
	});
});

$(document).on('click', ".add_unit",function()
{
	bootbox.prompt(<?php echo json_encode(lang('items_please_enter_unit_name')); ?>, function(unit_name)
	{
		if (unit_name)
		{
			$.post('<?php echo site_url("items/save_unit");?>', {unit_name : unit_name},function(response) {
			
				show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);

				//Refresh tree if success
				if (response.success)
				{
					$('#unit_list').load("<?php echo site_url("items/unit_list"); ?>");
				}
			}, "json");

		}
	});
});

$(document).on('click', ".delete_unit",function()
{
	var unit_id = $(this).data('unit_id');
	if (unit_id)
	{
		bootbox.confirm(<?php echo json_encode(lang('items_unit_delete_confirmation')); ?>, function(result)
		{
			if (result)
			{
				$.post('<?php echo site_url("items/delete_unit");?>', {unit_id : unit_id},function(response) {
				
					show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);

					//Refresh tree if success
					if (response.success)
					{
						$('#unit_list').load("<?php echo site_url("items/unit_list"); ?>");
					}
				}, "json");
			}
		});
	}
	
});

</script>
<?php $this->load->view('partial/footer'); ?>
