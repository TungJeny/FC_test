<?php if (!empty($stock_request)): ?>
<input type="hidden" value="<?php echo get_data($stock_request, 'stock_id'); ?>" name="stock_request_id" />
<?php endif; ?>
<table class="table table-hover">
    <thead>
    <tr class="register-items-header">
        <th></th>
        <th class="item_name_heading font-14"><?php echo lang('stock_in_item_name'); ?></th>
        <th class="sales_quantity font-14"><?php echo lang('common_quantity'); ?></th>
        <th class="sales_comment font-14"><?php echo lang('common_note'); ?></th>
    </tr>
    </thead>
    <tbody class="register-item-content">
    <?php $cart_count = 0; if (empty($cart)): ?>
        <tr class="cart_content_area">
            <td colspan='5'>
                <div class='text-center text-warning' > <h3><?php echo lang('common_no_items_in_cart'); ?> <span class="flatBluec"> [<?php echo lang('module_stock_in') ?>]</span></h3></div>
            </td>
        </tr>
    <?php else: ?>
    <?php if (empty($stock_request)): ?>
    <?php foreach(array_reverse($cart, true) as $item_id => $item): ?>
        <tr class="register-item-details">
            <td class="text-center">
                <?php echo anchor("stock_in/delete_item/$item_id",'<i class="icon ion-android-cancel"></i>', array('class' => 'delete-item')); ?>
            </td>
            <td>
                <a tabindex = "-1" href="<?php echo isset($item['item_id']) ? site_url('home/view_item_modal/'.$item['item_id']) : site_url('home/view_item_kit_modal/'.$item['item_kit_id']) ; ?>" data-toggle="modal" data-target="#myModal" class="register-item-name" ><?php echo H($item['name']); ?></a>
            </td>
            <td class="text-center">
                <?php if ($this->config->item('virtual_keyboard') == 'all' || ($this->agent->is_mobile() && $this->config->item('virtual_keyboard')  == 'mobile') ||  (!$this->agent->is_mobile() && $this->config->item('virtual_keyboard')  == 'desktop')): ?>
                <a href="javascript:void(0)" id="quantity_<?php echo $item_id; ?>" class="edit-on-click editable-click" data-keyboard-type="numpad" data-value="<?php echo H(to_quantity($item['quantity'])); ?>" data-name="quantity" data-url="<?php echo site_url('stock_in/update_item/'.$item_id); ?>" data-title="<?php echo lang('common_quantity') ?>"><?php echo to_quantity($item['quantity']); ?></a><?php if (!empty($quantity_remain[$item_id])): ?><a>/<?php echo to_quantity($quantity_remain[$item_id]); ?></a><?php endif;?>
                <?php else: ?>
                <a href="#" id="quantity_<?php echo $item_id; ?>" class="xeditable" data-type="text"  data-validate-number="true" data-value="<?php echo H(to_quantity($item['quantity'])); ?>" data-pk="1" data-name="quantity" data-url="<?php echo site_url('stock_in/update_item/'.$item_id); ?>" data-title="<?php echo lang('common_quantity') ?>"><?php echo to_quantity($item['quantity']); ?></a><?php if (!empty($quantity_remain[$item_id])): ?><a>/<?php echo to_quantity($quantity_remain[$item_id]); ?></a><?php endif;?>
                <?php endif; ?>
            </td>
            <td class="text-center">
                <?php if ($this->config->item('virtual_keyboard') == 'all' || ($this->agent->is_mobile() && $this->config->item('virtual_keyboard')  == 'mobile') ||  (!$this->agent->is_mobile() && $this->config->item('virtual_keyboard')  == 'desktop')): ?>
                <a href="javascript:void(0)" id="note_<?php echo $item_id; ?>" class="edit-on-click editable-click" data-value="<?php echo get_data($item, 'note'); ?>" data-name="note" data-url="<?php echo site_url('stock_in/update_item/'.$item_id); ?>" data-title="<?php echo lang('common_note') ?>"><?php echo get_data($item, 'note'); ?></a>
                <?php else: ?>
                <a href="#" id="note_<?php echo $item_id; ?>" class="xeditable" data-type="text" data-value="<?php echo get_data($item, 'note'); ?>" data-pk="<?php echo $item_id; ?>" data-name="note" data-url="<?php echo site_url('stock_in/update_item/'.$item_id); ?>" data-title="<?php echo lang('common_note') ?>"><?php echo get_data($item, 'note'); ?></a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php else: ?>
    <?php foreach(array_reverse($cart, true) as $item): ?>
    <?php $item_id = get_data($item, 'item_id'); ?>
    <tr class="register-item-details">
        <td>
            <?php
                echo form_checkbox(array(
                    'name' => 'stock_request['.$item_id.'][item_id]',
                    'id' => 'chk-item-' . $item_id,
                    'value' => $item_id,
                    'checked' => isset($selected_items[$item_id]),
                    'class' => 'stock-request-data chk-request-item'
                ));
            ?>
            <label for="<?php echo 'chk-item-' . $item_id; ?>"><span></span></label>
        </td>
        <td>
            <a tabindex = "-1" href="<?php echo isset($item['item_id']) ? site_url('home/view_item_modal/'.$item['item_id']) : site_url('home/view_item_kit_modal/'.$item['item_kit_id']) ; ?>" data-toggle="modal" data-target="#myModal" class="register-item-name" ><?php echo H($item['name']); ?></a>
        </td>
        <td class="text-center">
            <?php if (get_data($stock_request, 'status') != \Models\Stock::STATUS_ACCEPTED): ?>
            <?php if ($this->config->item('virtual_keyboard') == 'all' || ($this->agent->is_mobile() && $this->config->item('virtual_keyboard')  == 'mobile') ||  (!$this->agent->is_mobile() && $this->config->item('virtual_keyboard')  == 'desktop')): ?>
            <a href="javascript:void(0)" id="quantity_<?php echo $item_id; ?>" class="edit-on-click editable-click" data-keyboard-type="numpad" data-value="<?php echo H(to_quantity($item['quantity'])); ?>" data-name="quantity" data-url="<?php echo site_url('stock_in/update_item/'.$item_id.'?stock_request_id=' . get_data($stock_request, 'stock_id')); ?>" data-title="<?php echo lang('common_quantity') ?>"><?php echo to_quantity($item['quantity']); ?></a><?php if (!empty($quantity_remain[$item_id])): ?><a>/<?php echo to_quantity($quantity_remain[$item_id]); ?></a><?php endif;?>
            <?php else: ?>
            <a href="#" id="quantity_<?php echo $item_id; ?>" class="xeditable" data-type="text"  data-validate-number="true" data-value="<?php echo H(to_quantity($item['quantity'])); ?>" data-pk="<?php echo $item_id; ?>" data-name="quantity" data-url="<?php echo site_url('stock_in/update_item/'.$item_id.'?stock_request_id=' . get_data($stock_request, 'stock_id')); ?>" data-title="<?php echo lang('common_quantity') ?>"><?php echo to_quantity($item['quantity']); ?></a><?php if (!empty($quantity_remain[$item_id])): ?><a>/<?php echo to_quantity($quantity_remain[$item_id]); ?></a><?php endif;?>
            <?php endif; ?>
            <input name="stock_request[<?php echo $item_id; ?>][quantity]" type="hidden" value="<?php echo get_data($item, 'quantity'); ?>" />
            <?php else: ?>
            <?php echo get_data($item, 'quantity'); ?>
            <?php endif; ?>
        </td>
        <td class="text-center">
            <?php if (get_data($stock_request, 'status') != \Models\Stock::STATUS_ACCEPTED): ?>
            <?php if ($this->config->item('virtual_keyboard') == 'all' || ($this->agent->is_mobile() && $this->config->item('virtual_keyboard')  == 'mobile') ||  (!$this->agent->is_mobile() && $this->config->item('virtual_keyboard')  == 'desktop')): ?>
            <a href="javascript:void(0)" id="note_<?php echo $item_id; ?>" class="edit-on-click editable-click" data-keyboard-type="numpad" data-value="<?php echo get_data($item, 'note'); ?>" data-name="note" data-url="<?php echo site_url('stock_in/update_item/'.$item_id.'?stock_request_id=' . get_data($stock_request, 'stock_id')); ?>" data-title="<?php echo lang('common_note') ?>"><?php echo get_data($item, 'note'); ?></a>
            <?php else: ?>
            <a href="#" id="note_<?php echo $item_id; ?>" class="xeditable" data-type="text" data-value="<?php echo get_data($item, 'note'); ?>" data-pk="<?php echo $item_id; ?>" data-name="note" data-url="<?php echo site_url('stock_in/update_item/'.$item_id.'?stock_request_id=' . get_data($stock_request, 'stock_id')); ?>" data-title="<?php echo lang('common_note') ?>"><?php echo get_data($item, 'note'); ?></a>
            <?php endif; ?>
            <input name="stock_request[<?php echo $item_id; ?>][note]" type="hidden" value="<?php echo get_data($item, 'note'); ?>" />
            <?php else: ?>
            <?php echo get_data($item, 'note'); ?>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    <?php endif; ?>
    </tbody>
</table>