<?php $this->load->view("partial/header");?>
<div class="manage_buttons">
	<div class="row">
		<div class="col-md-5">
		</div>
		<div class="col-md-7">	
			<div class="buttons-list">
				<div class="pull-right-btn">
					<?php if (0 || $this->Employee->has_module_action_permission($controller_name, 'add_update', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
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
<div class="row">
<div class="col-md-6">
	<div class="container-fluid">
    	<div class="row manage-table">
    		<div class="panel panel-piluku">
    			<div class="panel-heading">
    				<h3 class="panel-title" style="float: left;">
    					Danh sách thành phẩm
    				</h3>
    				<input type="text" class="form-control" style="float: right; width: 50%" @keyup.enter="on_enter" v-model="s_term" placeholder="Enter item name or scan barcode">
    			</div>
    			<div class="panel-body nopadding table_holder table-responsive" >
    				<list-view :data="items" :columns="displayCols" :pagination="pagination" :options="options" :loading="loading" v-on:sorting="sort" v-on:selectingrow="selectingRow"></list-view>
    			</div>
    		</div>
    	</div>
    </div>
</div>

<div class="col-md-6">
	<div class="container-fluid">
    	<div class="row manage-table">
    		<div class="panel panel-piluku">
    			<div class="panel-heading">
    				<h3 class="panel-title" style="float: left;">
    					Định mức vật tư: {{selected_item.name}}
    				</h3>
    				<a v-bind:href="href_new_bom" v-show="selected_item.item_id > 0" style="float: right;" title="materials_new" class="btn btn-primary">
        				<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
        				<span> Thêm mới</span>
    				</a>
    			</div>
    			<div class="panel-body nopadding table_holder table-responsive" >
    				<list-view :data="boms" :columns="bom_displayCols" :pagination="bom_pagination" :options="bom_options" :loading="bom_loading" v-on:clickonlink="click_on_link" v-show="boms.length"></list-view>
    			</div>
    		</div>
    	</div>
    </div>
</div>
</div>
<?php $this->load->view("partial/footer"); ?>