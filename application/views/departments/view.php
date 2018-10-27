<?php $this->load->view("partial/header");
$this->load->helper('demo');
?>
<div class="panel panel-piluku">
	<div class="panel-heading">
        <h3 class="panel-title">
            <i class="ion-edit"></i> 
            <?php echo lang("employees_basic_information"); ?>
			<small>(<?php echo lang('common_fields_required_message'); ?>)</small>
        </h3>
    </div>

	<div class="panel-body form-horizontal">
		<div class="form-group">
			<label for="commission_percent" class="col-sm-3 col-md-3 col-lg-2 control-label"><?php echo lang('department_name');?>:</label>
			<div class="col-sm-9 col-md-9 col-lg-10">
				<input type="text" class="form-control" v-validate="{required: true}" name="department_name" v-model="department.name">
				<span v-show="errors.has('department_name')" class="help message">{{ errors.first('department_name') }}</span>
			</div>
		</div>
		<div class="form-group">
			<label for="commission_percent" class="col-sm-3 col-md-3 col-lg-2 control-label"><?php echo lang('department_code');?>:</label>
			<div class="col-sm-9 col-md-9 col-lg-10">
				<input type="text" class="form-control" v-validate="{required: true}" name="department_code" v-model="department.code">
				<span v-show="errors.has('department_code')" class="help message">{{ errors.first('department_code') }}</span>
			</div>
		</div>
		<div class="form-group">
			<label for="commission_percent" class="col-sm-3 col-md-3 col-lg-2 control-label"><?php echo lang('department_description');?>:</label>
			<div class="col-sm-9 col-md-9 col-lg-10">
				<input type="text" class="form-control" v-model="department.description">
			</div>
		</div>
		<div class="form-actions pull-right">
			<input type="submit" value="Submit" class="submit_button floating-button btn btn-primary" @click="fnSave" >
		</div>
	</div>
</div>
<?php $this->load->view("partial/footer"); ?>