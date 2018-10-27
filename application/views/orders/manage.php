<?php $this->load->view("partial/header");?>

<div class="manage_buttons">
	<div class="manage-row-options" v-if="show_manage_buttons">
		<div class="email_buttons text-center">
    		<?php if (1 || $this->Employee->has_module_action_permission($controller_name, 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
    		<a @click="deleteSelected()" title="Delete"
				class="btn btn-red btn-lg delete_inactive "><span>Delete</span></a>
    		<?php } ?>
    		<a @click="clearSelection()"
				class="btn btn-lg btn-clear-selection btn-warning"><?php echo lang('common_clear_selection'); ?></a>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="container-fluid">
			<div class="row manage-table">
				<div class="panel panel-piluku">
					<div class="panel-heading" >
						<h3 class="panel-title" style="float: left;">Danh sách đơn đặt
							hàng</h3>
					</div>
					<div class="panel-body nopadding table_holder table-responsive" >
						<list-view :data="sales" :columns="displayCols"
							:pagination="pagination" :options="options" :loading="loading"
							v-on:sorting="sort" v-on:selectingrow="selectingRow"
							v-on:clickonlink="click_on_link">{{sales}}</list-view>
					</div>		
				</div>
				<div class="paginator" align="center">
					<div class="pull-left directive">
						Trang 
					</div>
					<a class="btn btn-default" onclick="grid.first_page();"><i class="ti ti-angle-double-left"></i></a>
					<a class="btn btn-default" onclick="grid.prev_page();"><i class="ti ti-angle-left"></i></a>
					
					<a class="btn btn-default" onclick="grid.next_page();"><i class="ti ti-angle-right"></i></a>
					<a class="btn btn-default" onclick="grid.last_page();"><i class="ti ti-angle-double-right"></i></a>
				</div>
			</div>
		</div>
	</div>
</div>
<?php $this->load->view("partial/footer"); ?>