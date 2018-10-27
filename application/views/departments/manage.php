<?php $this->load->view("partial/header");
$this->load->helper('demo');
?>
<div class="manage_buttons">

	<!-- Css Loader  -->
	<div class="spinner" id="ajax-loader" style="display:none">
	  <div class="rect1"></div>
	  <div class="rect2"></div>
	  <div class="rect3"></div>
	</div>
<div class="manage-row-options" v-if="show_manage_buttons">
	<div class="email_buttons text-center">
		<?php if (1 || $this->Employee->has_module_action_permission($controller_name, 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
		<a @click="deleteSelected()" title="Delete" class="btn btn-red btn-lg delete_inactive "><span>Delete</span></a>
		<?php } ?>
		<a @click="clearSelection()" class="btn btn-lg btn-clear-selection btn-warning"><?php echo lang('common_clear_selection'); ?></a>
	</div>
</div>
	<div class="row">
		<div class="col-md-5">
			<div class="search no-left-border">
				<input type="text" class="form-control" @keyup.enter="search"  v-model="s_term" placeholder="<?php echo lang('common_search'); ?> <?php echo lang('module_'.$controller_name); ?>"/>
			</div>
			<div class="clear-block hidden">
				<a class="clear" href="<?php echo site_url($controller_name.'/clear_state'); ?>">
					<i class="ion ion-close-circled"></i>
				</a>	
			</div>
			
		</div>
		<div class="col-md-7">	
			<div class="buttons-list">
				<div class="pull-right-btn">
					<?php if (1 || $this->Employee->has_module_action_permission($controller_name, 'add_update', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
					<?php echo anchor("$controller_name/view/-1",
						'<span class="">'.lang($controller_name.'_new').'</span>',
						array('id' => 'new-person-btn', 'class'=>'btn btn-primary btn-lg', 'title'=>lang($controller_name.'_new')));
					}	
					?>
				</div>
			</div>				
		</div>
	</div>
</div>

<div class="container-fluid">
	<div class="row manage-table">
		<div class="panel panel-piluku">
			<div class="panel-heading">
				<h3 class="panel-title">
					<?php echo lang('common_list_of').' '.lang('module_'.$controller_name); ?>
					<span title="total <?php echo $controller_name?>" class="badge bg-primary tip-left">{{pagination.total_row}}</span>
				</h3>
			</div>
			<div class="panel-body nopadding table_holder table-responsive" >
				<list-view :data="departments" :columns="displayCols" :pagination="pagination" :options="options" :loading="loading" v-on:sorting="sort" v-on:clickonlink="click_on_link" v-on:selectingrow="selectingRow">{{departments}}</list-view>
			</div>
		</div>
	</div>
</div>
<?php $this->load->view("partial/footer"); ?>