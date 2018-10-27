<?php
$this->load->view("partial/header");
$this->load->helper('demo');
?>
<script>
 	var DATA_VIEW_HONDA = {};
 	DATA_VIEW_HONDA.sale_monthly_id = <?php echo !empty($sale_monthly_id)? json_encode($sale_monthly_id):json_encode(false);?>;
 	DATA_VIEW_HONDA.is_clone = <?php echo !empty($is_clone)? json_encode($is_clone):json_encode(false);?>;
 	DATA_VIEW_HONDA.order_for = <?php echo !empty($order_for)? json_encode($order_for):json_encode(false);?>;
 	</script>
<div class="panel panel-piluku" id="content-honda">
	<div class="panel-heading">
		<h3 class="panel-title">
			<i class="ion-edit"></i> UPLOAD đơn hàng
		</h3>
	</div>

	<div class="panel-body form-horizontal">
		<div class=" floating-button pull-right">
			<a class="btn btn-primary" @click="save_hot_data">Lưu dữ liệu</a>
		</div>
		<div class="form-group">
			<v-upload :url="upload_url" v-on:completed="upload_completed"></v-upload>
		</div>
		<div v-show="message.length" style="text-align: center;">{{message}}</div>
		<hot-table :root="root" :settings="hotSettings" ref="myHotTable"> </hot-table>
	</div>
</div>
<?php $this->load->view("partial/footer"); ?>