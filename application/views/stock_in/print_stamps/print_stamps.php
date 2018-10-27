<?php $this->load->view("partial/header");?>

<style type="text/css">
	#text-manage li{
		vertical-align: middle;
	}
	tr.titel_tr_stamps {
    	background: #e4e0e0;
	}
</style>
<script type="text/javascript">
    $(function () {
        $('#datetimepicker6').datetimepicker();
        $('#datetimepicker7').datetimepicker({
            useCurrent: false //Important! See issue #1075
        });
        $("#datetimepicker6").on("dp.change", function (e) {
            $('#datetimepicker7').data("DateTimePicker").minDate(e.date);
        });
        $("#datetimepicker7").on("dp.change", function (e) {
            $('#datetimepicker6').data("DateTimePicker").maxDate(e.date);
        });
    });
</script>

<div  class="panel panel-piluku" style="margin-bottom: -20px;padding-bottom: 10px;">
	<div class="panel-heading">
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#detail" aria-controls="home" role="tab" data-toggle="tab" style="font-weight:bold;" >Yều Cầu Nhập Kho</a></li>
		</ul>
	</div>
	
</div>

<div class="panel panel-piluku">
	<h4 style=" margin-left: 10px" >
		<strong>Danh Sách Yêu cầu Nhập Kho</strong> 
	</h4>
	<table class="table table-hover">
		<thead>
			<tr>
				<!-- <th width="50px">STT</th> -->
				<th class="pointer" title="" dir="ASC" width="140px" onclick="return grid.action_sort(this)">Nhân viên nhập kho</th>

				<th class="pointer" title="" dir="ASC" width="160px" onclick="return grid.action_sort(this)">Kho</th>

				<th class="pointer" title="" dir="ASC" width="180px" onclick="return grid.action_sort(this)">Mã Lô Vật Tư</th>

				<th class="pointer" title="" dir="ASC" width="100px" onclick="return grid.action_sort(this)">Mã Lô Thành Phẩm</th>

				<th class="pointer" title="" dir="ASC" width="60px" onclick="return grid.action_sort(this)">Số lượng</th>
				<!-- <th class="pointer" title="" dir="ASC" width="100px" onclick="return grid.action_sort(this)">ĐVT</th> -->
				<th class="pointer" title="" dir="ASC" width="200px" onclick="return grid.action_sort(this)">Tên Sản Phẩm</th>
				<th class="pointer" title="" dir="ASC" width="120px" onclick="return grid.action_sort(this)">Thời Điểm</th>
				
				
				<th width="">Xử lý</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($all_data as $item) { ?> 
				<tr class="titel_tr_stamps">
					<!-- <td><?php echo $i++ ?></td> -->
					<td>
						<b>
							<?php echo $item['first_name']." ".$item['last_name'];  ?>
						</b>
					</td>
					<td> <?php echo $item['location']; ?> </td>

					<td>
						<?php echo $item['consignment']; ?>
					</td>
					<td><?php echo $item['package_type'] == 2 ? $item['package_code']:" "; ?></td>

					<td> <?php echo $item['quantity']; ?> </td> 
					
					<td> </td>

					<td> <?php echo date('d/m/Y, H:i',$item['created_at']); ?></td>
					<td>
						
					</td>
				</tr>
				<!-- tên sản phẩm -->
				<?php foreach($item['items'] as $table_item): ?>
				<tr>
					
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td>
						<?php echo $table_item['quantity']; ?>
					</td>
					<td>
						<?php echo $table_item['name']; ?>
					</td>
					<td></td>
					<td>
						<a href=" <?php echo site_url('stock_in/view_print_container_change/').$item['stock_id'].'/'.$table_item['item_id']; ?> " class="btn btn-primary uppercase">In Lô</a>
						<a href=" <?php echo site_url('stock_in/view_print_barcode/').$table_item['item_id']; ?> " class="btn btn-primary uppercase">In Barcode</a>
						<a href=" <?php echo site_url('stock_in/view_print_qrcode/').$item['stock_id'].'/'.$table_item['item_id']; ?> " class="btn btn-primary uppercase">In QRcode</a>
					</td>
				</tr>
				<?php endforeach; ?>
			<?php  } ?> 
		</tbody>
	</table>
</div>

<?php if ($pagination) { ?>
    <div class="text-center">
        <div class="row pagination hidden-print alternate text-center" id="pagination_bottom">
            <?php echo $pagination; ?>
        </div>
    </div>
<?php } ?>
	

</form>

<?php $this->load->view('partial/footer')?>
