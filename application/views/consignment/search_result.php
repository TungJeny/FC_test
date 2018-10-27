
	<h4 style=" margin-left: 10px" >
	</h4>
	<table class="table table-hover table-search">
			<thead>
				<tr>
					<th width="30x">STT</th>
					<th class="pointer" title="" dir="ASC" width="250px"  order = "package_code">Mã hiệu lô vật tư</th>
					<th class="pointer" title="" dir="ASC" width="250px"   order = "name">Tên vật tư</th>
					<th class="pointer" title="" dir="ASC" width="80px"  order = "quantity">Số lượng</th>
					<th class="pointer" title="" dir="ASC" width="120px"  order = "company_name">Nhà CC</th>
					<th class="pointer" title="" dir="ASC" width="100px"  order = "created_at">Ngày nhập lô</th>
					<th class="pointer" title="" dir="ASC" width="180px"  order = "full_name">Nhân viên nhập kho</th>
					<th class="pointer" title="" dir="ASC" width="200px"  order = "note">Ghi chú</th>
				</tr>
			</thead>

			<tbody>
				<?php $i = $offset+1; foreach ($temp as $row): ?>
					<tr >
						<td><?php echo $i; ?></td>
						<td style=" font-weight: 500;"><?php echo $row['package_code']; ?></td>
						<td><a class="lvt-click" href="<?php echo site_url()."home/view_item_modal/".$row['item_id']; ?>" data-toggle="modal" data-target="#myModal" ><?php echo $row['name']; ?></a></td> 
						<td><?php echo $row['quantity']; ?></td>
						<td><?php echo $row['company_name'] ?></td>
						<td><CENTER><?php echo date('d/m/y', $row['created_at']) ?></CENTER></td>
						<td><?php echo $row['full_name'] ?></td>
						<td><?php echo $row['note'] ?></td>
						<td></td>
					</tr>	
			</tbody>
		<?php $i++; endforeach; ?>
	</table> 
	
	<div class="paginatin"  align="center"><?php echo $pagination; ?></div>
<div align="center" ><a class="ti-close" href="<?php echo base_url('Manage_consignment/lvt')?>"></a></div>

		

	