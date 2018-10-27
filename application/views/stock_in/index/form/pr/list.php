<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="\u0110o\u0301ng">
        <span aria-hidden="true" class="ti-close"></span>
    </button>
    <h4 class="modal-title uppercase">Danh sách yêu cầu nhập kho TP</h4>
</div>
<div class="modal-body">
    <?php if (!empty($collection)): ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <tbody>
                <tr>
                    <th class="font-14 font-arial">Nhân viên nhập kho</th>
                    <th class="font-14 font-arial">Kho</th>
                    <th class="font-14 font-arial">SL</th>
                    <th class="font-14 font-arial">Thời điểm</th>
                    <th class="font-14 font-arial">Thao tác</th>
                </tr>
                <?php foreach ($collection as $item): ?>
                <tr>
                    <td class="font-14 font-arial"><?php echo get_data($item, 'first_name') . ' ' . get_data($item, 'last_name'); ?></td>
                    <td class="font-14 font-arial"><?php echo get_data($item, 'location'); ?></td>
                    <td class="font-14 font-arial"><?php echo get_data($item, 'quantity'); ?></td>
                    <td class="font-14 font-arial"><?php echo date('d/m/Y, H:i', get_data($item, 'created_at')); ?></td>
                    <td class="font-14 font-arial"><a href="<?php echo site_url('stock_in/view/') . get_data($item, 'stock_id'); ?>" class="font-14 font-arial">Xem chi tiết</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <p align="center" class="font-16"><i> -- Chưa có yêu cầu nhập kho TP nào cần xử lý --</i></p>
    <?php endif; ?>
</div>