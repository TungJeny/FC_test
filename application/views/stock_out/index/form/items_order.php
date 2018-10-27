<?php if (!empty($stock_request)): ?>
<input type="hidden" value="<?php echo get_data($stock_request, 'stock_id'); ?>" name="stock_request_id" />
<?php endif; ?>
<table class="table table-hover">
    <thead>
    <tr class="register-items-header">
        <th></th>
        <th class="item_name_heading font-14 font-arial">Tên phụ tùng</th>
        <th class="sales_quantity font-14 font-arial">Số lượng cần giao</th>
        <th class="sales_quantity font-14 font-arial">Số lượng thực xuất</th>
        <th class="font-14 font-arial">Quy cách</th>
    </tr>
    </thead>
    <tbody class="register-item-content">
    <?php $cart_count = 0;
    if (empty($cart)): ?>
        <tr class="cart_content_area">
            <td colspan='5'>
                <div class='text-center text-warning'>
                    <h3> <?php echo lang('common_no_items_in_cart'); ?><span class="flatBluec"> [<?php echo lang('module_stock_out') ?>]</span></h3>
                </div>
            </td>
        </tr>
    <?php else: ?>
    <?php foreach (array_reverse($cart, true) as $item_id => $item): ?>
    <?php
        if ($item['quantity'] > 0 && $item['name'] != lang('common_store_account_payment')) {
            $cart_count = $cart_count + $item['quantity'];
        }
    ?>
    <tr class="register-item-details">
        <td class="text-center">
            <?php if (empty($stock_request)): ?>
            <?php echo anchor("stock_out/delete_item/$item_id", '<i class="icon ion-android-cancel"></i>', array('class' => 'delete-item')); ?>
            <input type="hidden" class="chk-request-item" value="<?php echo $item_id; ?>" />
            <?php else: ?>
            <?php if (get_data($stock_request, 'status') != \Models\Stock::STATUS_ACCEPTED): ?>
            <?php
            echo form_checkbox(array(
                'name' => 'stock_request['.get_data($item, 'item_id').'][item_id]',
                'id' => 'chk-item-' . get_data($item, 'item_id'),
                'value' => get_data($item, 'item_id'),
                'checked' => isset($selected_items[get_data($item, 'item_id')]),
                'class' => 'stock-request-data chk-request-item'
            ));
            ?>
            <label for="<?php echo 'chk-item-' . get_data($item, 'item_id'); ?>"><span></span></label>
            <?php endif; ?>
            <?php endif; ?>
        </td>
        <td>
            <a tabindex="-1" href="<?php echo isset($item['item_id']) ? site_url('home/view_item_modal/' . $item['item_id']) : site_url('home/view_item_kit_modal/' . $item['item_kit_id']); ?>" data-toggle="modal" data-target="#myModal" class="register-item-name"><?php echo H($item['name']); ?></a>
        </td>
        <td class="text-center">
            <?php echo get_data($item, 'quantity'); ?>
        </td>
        <td class="text-center">
            <?php if (empty($stock_request)): ?>
            <?php if ($this->config->item('virtual_keyboard') == 'all' || ($this->agent->is_mobile() && $this->config->item('virtual_keyboard') == 'mobile') || (!$this->agent->is_mobile() && $this->config->item('virtual_keyboard') == 'desktop')): ?>
                <a href="javascript:void(0)" id="quantity_received_<?php echo $item_id; ?>"
                   class="edit-on-click editable-click" data-keyboard-type="numpad"
                   data-value="<?php echo get_data($item, 'quantity_received'); ?>" data-name="quantity_received"
                   data-url="<?php echo site_url('stock_out/update_item/' . $item_id); ?>"
                   data-title="<?php echo lang('common_stock_out_quantity_received') ?>"><?php echo get_data($item, 'quantity_received'); ?></a>
            <?php else: ?>
                <a href="#" id="quantity_received_<?php echo $item_id; ?>" class="xeditable" data-type="number"
                   data-validate-number="true" data-value="<?php echo get_data($item, 'quantity_received', get_data($item, 'quantity')); ?>"
                   data-pk="1" data-name="quantity_received"
                   data-url="<?php echo site_url('stock_out/update_item/' . $item_id); ?>"
                   data-title="<?php echo lang('common_stock_out_quantity_received') ?>"><?php echo get_data($item, 'quantity_received', get_data($item, 'quantity')); ?></a>
            <?php endif; ?>
            <?php else: ?>
                <strong><?php echo get_data($item, 'quantity_received', get_data($item, 'quantity')); ?></strong>
            <?php endif; ?>
        </td>
        <td class="text-center">
            <?php echo get_unit_text(get_data($item, 'item_id'), get_data($item, 'quantity_received', get_data($item, 'quantity'))); ?>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>