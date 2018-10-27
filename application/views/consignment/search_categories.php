	<h4 style=" margin-right:  10px" >
<div align="right" ><a class="ti-close" href="<?php echo base_url('Manage_consignment/ltp')?>"></a></div>
	</h4>
	<table class="table table-hover table-search">
		<thead>
			<tr>
				<th width="30x">STT</th>
				<th class="pointer" title="" dir="ASC" width="140px" order = "package_code">Mã hiệu lô thành phẩm</th>
				<th title="" dir="ASC" width="150px">Mã hiệu lô vật tư</th>
				<th class="pointer" title="" dir="ASC" width="150px" order = "name">Tên phụ tùng</th>
				<th class="pointer" title="" dir="ASC" width="90px" order = "name2">Loại</th>
				<th class="pointer" title="" dir="ASC" width="70px" order = "quantity">Số lượng</th>
				<th class="pointer" title="" dir="ASC" width="90px" order = "created_at">Ngày nhập lô</th>
				<th class="pointer" title="" dir="ASC" width="140px" order = "full_name">Nhân viên nhập kho</th>
				<th class="pointer" title="" dir="ASC" width="80px" order = "note">Ghi chú</th>
				<th width="200px"><center>IN</center></th>
			</tr>
		</thead>
		
		<tbody>
			<?php $i = +1; foreach ($collection as $row): ?>
			<tr >
				<td><?php echo $i; ?></td>
				<td colspan="" style="font-weight: 500;"><?php echo $row['package_code']; ?></td>
				<td><?php echo $row['package_id'] ? $row['package_id'] : " " ; ?></td>
			</tr>
		<?php $j =0; if(!empty($row['items'])){foreach ($row['items'] as $item): ?>	
				<tr>
					<td></td>			
					<td></td>
					<td></td>		
					<td><a class="ltp-click" href="<?php echo site_url()."home/view_item_modal/".$row['items'][$j]['item_id']; ?>" data-toggle="modal" data-target="#myModal" ><?php echo $item['name'] ? $item['name'] : ''; ?></a></td>
					<td><?php echo $item['name2'] ? $item['name2'] : '' ; ?></td>
					<td><?php echo $item['quantity'] ? $item['quantity'] : ''; ?></td>
					<td><?php echo date('d/m/y', $item['created_at']) ?></td>
					<td><?php echo $item['full_name'] ? $item['full_name'] : ''; ?></td>
					<td><?php echo $item['note']? $item['note'] : '';  ?></td>
					<td>
						<a href=" <?php echo site_url('stock_in/view_print_container/').$item['stock_id'].'/'.$item['item_id']; ?> " class="btn btn-primary uppercase">Lô</a>
						<a href=" <?php echo site_url('stock_in/view_print_barcode/').$item['item_id']; ?> " class="btn btn-primary uppercase">Barcode</a>
						<a href=" <?php echo site_url('stock_in/view_print_qrcode/').$item['stock_id'].'/'.$item['item_id']; ?> " class="btn btn-primary uppercase">QRcode</a>	
					</td>
				</tr>	
			<?php $j++; endforeach; }?>
		</tbody>
		<?php $i++; endforeach; ?> 

	</table>
	<!-- <div class="paginatin"  align="center"><?php echo $pagination; ?></div> -->
<div align="center" ><a class="ti-close" href="<?php echo base_url('Manage_consignment/ltp')?>"></a></div>