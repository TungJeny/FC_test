<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="\u0110o\u0301ng">
        <span aria-hidden="true" class="ti-close"></span>
    </button>
    <h4 class="modal-title uppercase">Báo cáo nhập xuất tồn tháng <?php echo $selected_month; ?></h4>
</div>
<div class="modal-body">
    <div class="table-responsive">
        <table id="fixed-detail-table" width="100%" class="table table-bordered table-hover table-striped">
            <thead>
            <tr>
                <td rowspan="3">Tên SP</td>
                <?php foreach ($dates as $date): ?>
                <td colspan="<?php echo (5 + count($receiver_locations)); ?>" width="990"><?php echo get_data($date, 'date'); ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <?php foreach ($dates as $date): ?>
                <td colspan="2" width="210">Nhập&nbsp;</td>
                <td colspan="<?php echo count($receiver_locations); ?>" width="430">Xuất</td>
                <td rowspan="2" width="113">GT xuất</td>
                <td colspan="2" width="237">Tồn</td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <?php foreach ($dates as $date): ?>
                <td width="83">Số lượng</td>
                <td width="127">Gi&aacute; trị</td>
                <?php foreach ($receiver_locations as $receiver_location): ?>
                <td width="70"><?php echo get_data($receiver_location, 'name'); ?></td>
                <?php endforeach; ?>
                <td width="92">Số lượng</td>
                <td width="145">Gi&aacute; trị</td>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><strong><?php echo get_data($item, 'item_name'); ?></strong></td>
                <?php foreach ($dates as $date): ?>
                <td>
                    <?php if (!empty($date['stock_in'])): ?>
                    <?php $stock_in_quantity = get_data(get_data($date, 'stock_in', []), 'trans_quantity', 0); ?>
                    <?php else: ?>
                    <?php $stock_in_quantity = 0; ?>
                    <?php endif; ?>
                    <?php $stock_in_quantity = intval($stock_in_quantity); $count_total_quantity += $stock_in_quantity; echo $stock_in_quantity; ?>
                </td>
                <td><?php echo to_currency($stock_in_quantity * get_data($item, 'cost_price')); ?></td>
                <?php foreach ($receiver_locations as $receiver_location): ?>
                <td>
                    <?php if (!empty($date->stock_out_receiver_location)): ?>
                    <?php $stock_out_receiver_location = get_data($date->stock_out_receiver_location, get_data($receiver_location, 'id'), []); ?>
                    <?php $receiver_location_quantities[get_data($receiver_location, 'id')] = get_data($stock_out_receiver_location, 'quantity', 0); ?>
                    <?php else: ?>
                    <?php $receiver_location_quantities[get_data($receiver_location, 'id')] = 0; ?>
                    <?php endif; ?>
                    <?php $count_total_quantity -= $receiver_location_quantities[get_data($receiver_location, 'id')]; echo $receiver_location_quantities[get_data($receiver_location, 'id')]; ?>
                </td>
                <?php endforeach; ?>
                <td>
                    <?php if (!empty($date['stock_out'])): ?>
                    <?php $stock_out_quantity = get_data(get_data($date, 'stock_out', []), 'trans_quantity', 0); ?>
                    <?php else: ?>
                    <?php $stock_out_quantity = 0; ?>
                    <?php endif; ?>
                    <?php $stock_out_quantity = intval($stock_out_quantity); echo to_currency($stock_out_quantity * get_data($item, 'cost_price')); ?>
                </td>
                <td><?php echo $count_total_quantity; ?></td>
                <td><?php echo to_currency($count_total_quantity * get_data($item, 'cost_price')); ?></td>
                <?php endforeach; ?>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<script type="text/javascript">
    // $("#fixed-detail-table").tableHeadFixer();
</script>