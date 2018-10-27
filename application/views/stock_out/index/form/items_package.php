<table class="table table-hover">
    <thead>
        <tr class="register-items-header">
            <th></th>
            <th>STT</th>
            <th class="item_name_heading">Mã lô vật tư</th>
            <th class="sales_price">Tên vật tư</th>
            <th class="sales_quantity">SL đã xuất/ SL lô</th>
            <th>ĐVT</th>
            <th>SL xuất</th>
            <th>Ghi chú</th>
        </tr>
    </thead>
    <tbody class="register-item-content">
    <?php $cart_count = 0; if (empty($cart)): ?>

        <tr class="cart_content_area">
            <td colspan='7'>
                <div class='text-center text-warning' > <h3><?php echo lang('common_no_items_in_cart'); ?> <span class="flatBluec"> [<?php echo lang('module_stock_out') ?>]</span></h3></div>
            </td>
        </tr>
    <?php else: ?>
    <?php $count = 1; foreach(array_reverse($cart, true) as $line => $item): ?>
	<?php   $item_id = $item['item_id'];
        	$package_id = $item['package_id'];
        	$quantity = $item['quantity'];
        	$total_quantity =$item['total_quantity'];
        	$total_stock_qty =$item['total_stock_qty'];
        	$unit_name = $item['unit_name'];
        	$note = $item['note'];
	?>
        <tr class="register-item-details">
        	<td class="text-center"> <?php echo anchor("stock_out/delete_package_item/$line",'<i class="icon ion-android-cancel"></i>', array('class' => 'delete-item')); ?> </td>
            <td class="text-center"><?php echo $count++;?> </td>
            <td><?php echo $item['package_code']?></td>
            <td><?php echo $item['name']?></td>
			<td class="text-center" width="20%">
			<a>
           	<?php echo to_quantity($total_stock_qty) . ' / ' . $total_quantity;?>
           	</a>
           	</td>
           	<td class="text-center"><?php echo $unit_name; ?></td>
			<td class="text-center">
                <a href="#" id="quantity_<?php echo $line; ?>"
				class="xeditable" data-type="text" data-validate-number="true"
				data-value="<?php echo H(to_quantity($quantity)); ?>"
				data-pk="1" data-name="quantity"
				data-url="<?php echo site_url('stock_out/update_item_package/'.$line); ?>"
				data-title="<?php echo lang('common_quantity') ?>"><?php echo to_quantity($quantity); ?></a>
            </td>
            <td class="text-center">                
            	<a href="#" id="note_<?php echo $line; ?>"
				class="xeditable" data-type="text" 
				data-value="<?php echo H(to_quantity($note)); ?>"
				data-pk="1" data-name="note"
				data-url="<?php echo site_url('stock_out/update_item_package/'.$line); ?>"
				data-title="<?php echo lang('common_quantity') ?>"><?php echo $note; ?></a>
				</td>
            
        </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
