<table class="table table-hover">
    <thead>
    <tr class="register-items-header">
        <th></th>
        <th class="item_name_heading"><?php echo lang('stock_in_item_name'); ?></th>
        <th class="sales_price"><?php echo lang('stock_in_cost'); ?></th>
        <th class="sales_quantity"><?php echo lang('common_quantity'); ?></th>
        <th><?php echo lang('stock_in_total'); ?></th>
    </tr>
    </thead>
    <tbody class="register-item-content">
    <?php $cart_count = 0; if (empty($cart)): ?>
        <tr class="cart_content_area">
            <td colspan='6'>
                <div class='text-center text-warning' > <h3><?php echo lang('common_no_items_in_cart'); ?> <span class="flatBluec"> [<?php echo lang('module_stock_in') ?>]</span></h3></div>
            </td>
        </tr>
    <?php else: ?>
    <?php foreach(array_reverse($cart, true) as $item_id => $item): ?>
        <?php
        if ($item['quantity'] > 0 && $item['name'] != lang('common_store_account_payment')) {
            $cart_count = $cart_count + $item['quantity'];
        }
        ?>
        <tr class="register-item-details">
            <td class="text-center"> <?php echo anchor("stock_in/delete_item/$item_id",'<i class="icon ion-android-cancel"></i>', array('class' => 'delete-item')); ?> </td>
            <td>
                <a tabindex = "-1" href="<?php echo isset($item['item_id']) ? site_url('home/view_item_modal/'.$item['item_id']) : site_url('home/view_item_kit_modal/'.$item['item_kit_id']) ; ?>" data-toggle="modal" data-target="#myModal" class="register-item-name" ><?php echo H($item['name']); ?><?php echo $item['size'] ? ' ('.H($item['size']).')': ''; ?></a>
            </td>
            <td class="text-center">
                <?php if ($items_module_allowed): ?>
                <?php if($this->config->item('virtual_keyboard') == 'all' || ($this->agent->is_mobile() && $this->config->item('virtual_keyboard')  == 'mobile') ||  (!$this->agent->is_mobile() && $this->config->item('virtual_keyboard')  == 'desktop')): ?>
                <a href="javascript:void(0)" id="price_<?php echo $item_id;?>" class="edit-on-click editable-click" data-keyboard-type="numpad" data-value="<?php echo H(to_currency_no_money($item['price'],10)); ?>" data-name="price" data-url="<?php echo site_url('stock_in/update_item/' . $item_id); ?>" data-title="<?php echo H(lang('common_price')); ?>"><?php echo to_currency($item['price'],10); ?></a>
                <?php else: ?>
                <a href="#" id="price_<?php echo $item_id; ?>" class="xeditable xeditable-price" data-validate-number="true" data-type="text" data-value="<?php echo H(to_currency_no_money($item['price'],10)); ?>" data-pk="1" data-name="price" data-url="<?php echo site_url('stock_in/update_item/' . $item_id); ?>" data-title="<?php echo H(lang('common_price')); ?>"><?php echo to_currency($item['price'],10); ?></a>
                <?php endif; ?>
                <?php else: ?>
                <?php echo to_currency($item['price']); ?>
                <?php endif; ?>
            </td>
            <td class="text-center">
                <?php if ($this->config->item('virtual_keyboard') == 'all' || ($this->agent->is_mobile() && $this->config->item('virtual_keyboard')  == 'mobile') ||  (!$this->agent->is_mobile() && $this->config->item('virtual_keyboard')  == 'desktop')): ?>
                <a href="javascript:void(0)" id="quantity_<?php echo $item_id; ?>" class="edit-on-click editable-click" data-keyboard-type="numpad" data-value="<?php echo H(to_quantity($item['quantity'])); ?>" data-name="quantity" data-url="<?php echo site_url('stock_in/update_item/'.$item_id); ?>" data-title="<?php echo lang('common_quantity') ?>"><?php echo to_quantity($item['quantity']); ?></a><?php if (!empty($quantity_remain[$item_id])): ?><a>/<?php echo to_quantity($quantity_remain[$item_id]); ?></a><?php endif;?>
                <?php else: ?>
                <a href="#" id="quantity_<?php echo $item_id; ?>" class="xeditable" data-type="text"  data-validate-number="true" data-value="<?php echo H(to_quantity($item['quantity'])); ?>" data-pk="1" data-name="quantity" data-url="<?php echo site_url('stock_in/update_item/'.$item_id); ?>" data-title="<?php echo lang('common_quantity') ?>"><?php echo to_quantity($item['quantity']); ?></a><?php if (!empty($quantity_remain[$item_id])): ?><a>/<?php echo to_quantity($quantity_remain[$item_id]); ?></a><?php endif;?>
                <?php endif; ?>
            </td>
            <td class="text-center"><?php echo to_currency($item['price'] * $item['quantity'] - $item['price'] * $item['quantity'] * $item['discount'] / 100, 10); ?></td>
        </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>