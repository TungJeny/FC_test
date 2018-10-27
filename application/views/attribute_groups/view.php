<?php
$this->load->view("partial/header");
$this->load->helper('demo');
?>
	<div class="col-md-6">
		<div class="panel panel-piluku">
			<div class="panel-heading">
				<h3 class="panel-title">
					<i class="ion-edit"></i> 
        			<?php echo lang("employees_basic_information"); ?> 
        			<small>(<?php echo lang('common_fields_required_message'); ?>)</small>
				</h3>
			</div>

			<div class="panel-body form-horizontal">
				<input type="hidden" class="form-control" v-model="attribute_group.id">
				<div class="form-group">
					<label class="col-sm-3 col-md-3 col-lg-2 control-label"><?php echo lang('attribute_group_name');?>:</label>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<input type="text" name ="attr-group-name" class="form-control" v-validate ="{required: true}" v-model="attribute_group.name">
						<span class="help message" v-show="errors.has('attr-group-name')">{{errors.first('attr-group-name')}}</span>
					</div>
				</div>
				<div class="form-group">
					<label for="attr-gr-code"
						class="col-sm-3 col-md-3 col-lg-2 control-label"><?php echo lang('attribute_group_code');?>:</label>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<input v-bind:data-attr-group-id="attribute_group.id" type="text" v-validate="{required: true, exists: true}"  class="form-control" name ="attr-group-code" v-model="attribute_group.code"> 
						<span class="help message" v-show="errors.has('attr-group-code')">{{errors.first('attr-group-code')}}</span>
					</div>
				</div>
				<div class="form-group">
					<label for="attr-gr-description" class="col-sm-3 col-md-3 col-lg-2 control-label"><?php echo lang('common_description');?>:</label>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<input type="text" class="form-control" v-model="attribute_group.description">
					</div>
				</div>
				<div class="form-actions pull-right">
					<input type="submit" value="Submit" class="submit_button floating-button btn btn-primary" @click="fnSave">
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-piluku">
			<div class="panel-heading">
				<h3 class="panel-title">
					<i class="ion-edit"></i> 
                    <?php echo lang("employees_basic_information"); ?>
        			<small>(<?php echo lang('common_fields_required_message'); ?>)</small>
				</h3>
			</div>

			<div class="panel-body form-horizontal">
				<div v-for="(label, id) in all_related_obj">
					<input type="checkbox" v-bind:id="id" v-bind:value="id"  v-model="related_object"> 
    				<label v-bind:for="id"><span></span></label> 
    				<label v-bind:for="id" style="cursor: pointer; font-weight: normal;"><span>{{label}}</span></label> 
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-12">
		<div class="panel panel-piluku">
			<div class="panel-heading">
				<h3 class="panel-title">
					<i class="ion-edit"></i> 
                    <?php echo lang("employees_basic_information"); ?>
        			<small>(<?php echo lang('common_fields_required_message'); ?>)</small>
				</h3>
			</div>
			<div class="panel-body form-horizontal">
			<div v-drag-and-drop:options="options" class="drag-wrapper">
                  <ul v-bind:id="id" v-for="(items, id)  in options.itemList">
                  	<li v-for="item in items" v-bind:data-id="item.id">{{item.label}}</li>
                  </ul>
                </div>
		</div>
		</div>
		
	</div>
<?php $this->load->view("partial/footer"); ?>