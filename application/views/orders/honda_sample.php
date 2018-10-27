
<?php $this->load->view("partial/header");?>
<script>
 	var DATA_VIEW_HONDA_SAMPLE = {};

 	DATA_VIEW_HONDA_SAMPLE.sale_monthly_id = <?php echo (!empty($sale_monthly_id) && !$is_clone )? json_encode($sale_monthly_id):json_encode(false);?>;
 	DATA_VIEW_HONDA_SAMPLE.data = <?php echo !empty($data)? json_encode(json_decode($data)):json_encode(false);?>;
 	DATA_VIEW_HONDA_SAMPLE.month = <?php echo !empty($month)? json_encode(json_decode($month)):json_encode(false);?>;
 	DATA_VIEW_HONDA_SAMPLE.cell = <?php echo !empty($cell)? json_encode(json_decode($cell)):json_encode(false);?>;
 	DATA_VIEW_HONDA_SAMPLE.merge_cell = <?php echo !empty($merge_cell) ? json_encode(json_decode($merge_cell)): json_encode(false);?>;
 	DATA_VIEW_HONDA_SAMPLE.end_row_body = <?php echo !empty($end_row_body) ? $end_row_body: 0;?>;

 	if (DATA_VIEW_HONDA_SAMPLE.data) localStorage.setItem('hot_settings_data_honda_sample', JSON.stringify(DATA_VIEW_HONDA_SAMPLE.data));
 	if (DATA_VIEW_HONDA_SAMPLE.cell) localStorage.setItem('hot_settings_cell_style_samples', JSON.stringify(DATA_VIEW_HONDA_SAMPLE.cell));
 	if (DATA_VIEW_HONDA_SAMPLE.merge_cell) localStorage.setItem('hot_settings_merge_cell_sample', JSON.stringify(DATA_VIEW_HONDA_SAMPLE.merge_cell));
 	
</script>
<style>
.spinner div {
	height: 90px !important;
}
</style>
<div id="content-honda">
	<div class="mask" v-show="loading">
		<div class="spinner">
			<div class="rect1"></div>
			<div class="rect2"></div>
			<div class="rect3"></div>
		</div>
	</div>
	
	<div class="manage_buttons">
		<div class="row">
			<div class="col-md-6">
				<vuejs-datepicker v-validate={required:true} name="month"
					v-bind:class="[errors.has('month')? 'validate-error vue-datepicker':  'vue-datepicker']"
					v-model="month" minimum-view = "month" format="MM-yyyy" placeholder="Chọn tháng"></vuejs-datepicker>
				<div class="buttons-list">
					<a class="btn btn-primary btn-lg" @click="create_table"
						id="generate-hot-table">Tạo bảng</a>
				</div>
				<div v-if ="is_show" class=" floating-button pull-right">
					<a class="btn btn-primary" @click="clear_hot_data">Xóa dữ liệu</a> 
					<a class="btn btn-primary" @click="save_change_hot_table">Lưu tạm</a>
					<a class="btn btn-primary" @click="save_hot_data">Lưu dữ liệu</a>
				</div>
			</div>
			<div v-if ="is_show" class="col-md-6" style="padding: 20px 10px;">
				<autocomplete :resource="resource" :refresh="true"
					v-on:onselected="on_selected"></autocomplete>
			</div>
		</div>
	</div>
	<div v-bind:class="{ hidden: !is_show }" class="container-fluid">
		<div class="row hottable">
			<div class="panel panel-piluku">
				<div class="panel-heading">
					<h3 class="panel-title">
				<?php echo lang('common_list_of').' '.lang('module_'.$controller_name); ?>
				</h3>
				</div>
				<div class="panel-body nopadding table_holder table-responsive">

					<hot-table :root="root" :settings="hotSettings" ref="myHotTable"> </hot-table>
				</div>
			</div>
		</div>
	</div>
</div>
<?php $this->load->view("partial/footer"); ?>