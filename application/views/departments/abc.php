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
		<div v-drag-and-drop:options="options" class="drag-wrapper">
          <ul v-bind:id="id" v-for="(items, id)  in options.itemList">
          	<li v-for="item in items" v-bind:data-id="item.id">{{item.label}}</li>
          </ul>
        </div>
        <div>
        	<autocomplete :resource="resource" v-on:onselected="on_selected"></autocomplete>
        	<p>selected: {{selected_item.label}}</p>
        </div>
        
        <div class="form-group">	
			<v-upload :url="upload_url" v-on:completed="upload_completed" ></v-upload>
		</div>
	</div>
</div>
<?php $this->load->view("partial/footer"); ?>