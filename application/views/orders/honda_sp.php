
<?php $this->load->view("partial/header");?>
<script>
 	var DATA_VIEW_HONDA_SP = {};

 	DATA_VIEW_HONDA_SP.sale_monthly_id = <?php echo (!empty($sale_monthly_id) && !$is_clone )? json_encode($sale_monthly_id):json_encode(false);?>;
 	DATA_VIEW_HONDA_SP.data = <?php echo !empty($data)? json_encode(json_decode($data)):json_encode(false);?>;
 	DATA_VIEW_HONDA_SP.cell = <?php echo !empty($cell)? json_encode(json_decode($cell)):json_encode(false);?>;
 	DATA_VIEW_HONDA_SP.merge_cell = <?php echo !empty($merge_cell) ? json_encode(json_decode($merge_cell)): json_encode(false);?>;
 	DATA_VIEW_HONDA_SP.end_row_body = <?php echo !empty($end_row_body) ? $end_row_body: 0;?>;
 	DATA_VIEW_HONDA_SP.start_date = DATA_VIEW_HONDA_SP.data? DATA_VIEW_HONDA_SP.data[4][0] : false;
 	DATA_VIEW_HONDA_SP.end_date = DATA_VIEW_HONDA_SP.data? DATA_VIEW_HONDA_SP.data[DATA_VIEW_HONDA_SP.end_row_body][0] : false;
 	
 	if (DATA_VIEW_HONDA_SP.data) localStorage.setItem('hot_settings_data', JSON.stringify(DATA_VIEW_HONDA_SP.data));
 	if (DATA_VIEW_HONDA_SP.cell) localStorage.setItem('hot_settings_cell_style', JSON.stringify(DATA_VIEW_HONDA_SP.cell));
 	if (DATA_VIEW_HONDA_SP.merge_cell) localStorage.setItem('hot_settings_merger_cell', JSON.stringify(DATA_VIEW_HONDA_SP.merge_cell));
 	if (DATA_VIEW_HONDA_SP.end_row_body) localStorage.setItem('hot_settings_params_honda_sp', JSON.stringify({
		total_row :DATA_VIEW_HONDA_SP.end_row_body +2, 
		total_money :DATA_VIEW_HONDA_SP.end_row_body +4, 
		cost :DATA_VIEW_HONDA_SP.end_row_body +3,
		end_date: DATA_VIEW_HONDA_SP.end_date.replace(/[/]/g,'-').split("-").reverse().join("-"),
		start_date: DATA_VIEW_HONDA_SP.start_date.replace(/[/]/g,'-').split("-").reverse().join("-")
		}));
 	
</script>
<style>
.spinner div {
	height: 90px !important;
}
</style>
<div id="content-yamaha">
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
				<vuejs-datepicker v-validate={required:true} name="start-date"
					v-bind:class="[errors.has('start-date')? 'validate-error vue-datepicker':  'vue-datepicker']"
					v-model="start_date" format="dd-MM-yyyy" placeholder="Ngày bắt đầu"></vuejs-datepicker>
				<vuejs-datepicker v-validate={required:true} name="end-date"
					v-bind:class="[errors.has('end-date')? 'validate-error vue-datepicker':  'vue-datepicker']"
					v-model="end_date" format="dd-MM-yyyy" placeholder="Ngày kết thúc"></vuejs-datepicker>

				<div class="buttons-list">
					<a class="btn btn-primary btn-lg" @click="create_table"
						id="generate-hot-table">Tạo bảng</a>
				</div>
				<div class=" floating-button pull-right">
					<a class="btn btn-primary" @click="clear_hot_data">Xóa dữ liệu</a> 
					<a class="btn btn-primary" @click="save_change_hot_table">Lưu tạm</a>
					<a class="btn btn-primary" @click="save_hot_data">Lưu dữ liệu</a>
				</div>
			</div>
			<div class="col-md-6" style="padding: 20px 10px;">
				<autocomplete :resource="resource" :refresh="true"
					v-on:onselected="on_selected"></autocomplete>
			</div>
		</div>
	</div>
	<div class="container-fluid">
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