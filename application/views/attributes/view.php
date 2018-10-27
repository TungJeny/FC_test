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

		<input type="hidden" class="form-control" v-model="attribute.id">
		<div class="form-group">
			<label for="attr-name" class="col-sm-3 col-md-3 col-lg-2 control-label"><?php echo lang('attribute_name');?>:</label>
			<div class="col-sm-9 col-md-9 col-lg-10">
				<input type="text" v-validate="{required: true}" name="attribute-name" data-label="<?php echo lang('attribute_name');?>" class="form-control" v-model="attribute.name">
				 <span v-show="errors.has('attribute-name')" class="help message">{{ errors.first('attribute-name') }}</span>
			</div>
		</div>
		<div class="form-group">
			<label for="attr-code" class="col-sm-3 col-md-3 col-lg-2 control-label"><?php echo lang('attribute_code');?>:</label>
			<div class="col-sm-9 col-md-9 col-lg-10">
				<input  id = "attr-code" v-validate="{required: true, exists: true}" v-bind:data-attr-id ="attribute.id"  name="attribute-code" type="text" class="form-control" v-model="attribute.code">
				<span class="help message" v-show="errors.has('attribute-code')">{{errors.first('attribute-code')}}</span>
				
			</div>
		</div>
		<div class="form-group">
			<label for="attr-description" class="col-sm-3 col-md-3 col-lg-2 control-label"><?php echo lang('attribute_description');?>:</label>
			<div class="col-sm-9 col-md-9 col-lg-10">
				<input type="text" class="form-control" v-model="attribute.description">
			</div>
		</div>
		<div class="form-actions pull-right">
			<input type="submit" value="Submit" class="submit_button floating-button btn btn-primary" @click="fnSave" >
		</div>
	</div>
</div>
<?php $this->load->view("partial/footer"); ?>