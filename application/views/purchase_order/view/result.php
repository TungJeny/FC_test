<table class="table table-bordered table-hover">
    <thead>
    <tr>
        <th>STT</th>
        <th>Tên hàng</th>
        <th>ĐVT</th>
        <th>Đã mua / Số lượng cần</th>
        <th>Giá</th>
        <th>Thời gian cần về cho SX</th>
        <th>NCC</th>
        <th>Chọn</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($plan_items)): ?>
    <tr>
        <th colspan="8"><p class="font-14 mt-10" align="center">Không tìm thấy nguyên vật liệu phù hợp với yêu cầu tìm kiếm</p></th>
    </tr>
    <?php else: ?>
    <?php $index = 1; foreach ($plan_items as $plan_item): ?>
    <tr>
        <td><?php echo $index; ?></td>
        <td><a target="_blank" href="<?php echo site_url('items/view/' . $plan_item->item_id . '/2'); ?>"><?php echo $plan_item->name; ?></a></td>
        <td><?php echo $plan_item->unit->name; ?></td>
        <td>
            <?php foreach ($plan_item->material_plan->info as $key => $item): ?>
            <?php if ($plan_item->item_id == $key): ?>
            <?php if ($plan_item->category_id !=3): ?>
            <?php $quantity_check = (intval(get_data($plan_item, 'quantity_in_stock', 0)) - intval(get_data($item, 'target_month_qty'))) >= 0; ?>
            <span class="badge font-arial font-14  <?php if($quantity_check):?>bg-danger<?php else: ?>bg-success<?php endif; ?>">
            <?php echo get_data($plan_item, 'quantity_in_stock', 0); ?> / <?php $plan_item->target_month_qty = intval(get_data($item, 'target_month_qty')); echo $plan_item->target_month_qty; ?>
            </span>
            <?php else:?>
            <?php $quantity_check = (intval(get_data($plan_item, 'quantity_in_stock', 0)) - intval(get_data($item, 'calculated_by_unit'))) >= 0; ?>
            <span class="badge font-arial font-14  <?php if($quantity_check):?>bg-danger<?php else: ?>bg-success<?php endif; ?>">
            <?php echo get_data($plan_item, 'quantity_in_stock', 0); ?> / <?php $plan_item->calculated_by_unit = intval(get_data($item, 'calculated_by_unit')); echo $plan_item->calculated_by_unit; ?>
            </span>
            <?php endif; ?>
            <?php endif; ?>
            <?php endforeach; ?>
        </td>
        <td><?php echo to_currency($plan_item->cost_price); ?></td>
        <td><?php echo $plan_item->material_plan->month; ?></td>
        <td>
            <?php if (!empty($plan_item->suppliers)): ?>
            <?php foreach ($plan_item->suppliers as $supplier): ?>
            <a target="_blank" href="<?php echo site_url('suppliers/view/'.$supplier->person_id.'/2'); ?>" class="btn btn-success"><?php echo $supplier->company_name; ?></a>
            <?php endforeach; ?>
            <?php else: ?>
            <?php echo lang('common_unknown'); ?>
            <?php endif; ?>
        </td>
        <td>
            <button data-unit-id="<?php echo $plan_item->unit->id; ?>"
                    data-unit-name="<?php echo $plan_item->unit->name; ?>"
                    data-item-id="<?php echo $plan_item->item_id; ?>"
                    <?php if ($plan_item->category_id !=3): ?>
                    data-quater-qty="<?php echo ($plan_item->target_month_qty - get_data($plan_item, 'quantity_in_stock', 0)); ?>"
                    <?php else:?>
                    data-quater-qty="<?php echo ($plan_item->calculated_by_unit - get_data($plan_item, 'quantity_in_stock', 0)); ?>"
                    <?php endif; ?>
                    data-item-cost-price="<?php echo $plan_item->cost_price; ?>"
                    data-item-name="<?php echo $plan_item->name; ?>"
                    data-month="<?php echo $plan_item->material_plan->month; ?>"
                    data-suppliers="<?php foreach ($plan_item->suppliers as $supplier): ?><?php echo $supplier->id; ?>,<?php endforeach; ?>"
                    onclick="admin_po.select_item(this)" type="button" class="btn btn-primary">
                <i class="glyphicon glyphicon-arrow-down"></i>
            </button>
        </td>
    </tr>
    <?php $index++; endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
