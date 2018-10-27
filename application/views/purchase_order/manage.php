<?php $this->load->view("partial/header"); ?>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/grid/function.js"></script>
<div class="spinner" id="ajax-loader" style="display:none">
    <div class="rect1"></div>
    <div class="rect2"></div>
    <div class="rect3"></div>
</div>
<div id="ajax-result">
    <form class="ajax-form" data-result-id="ajax-result" id="frm-filter-po" method="GET" action="<?php echo site_url('purchase_orders'); ?>">
        <!-- Current page -->
        <input type="hidden" id="current-page" name="current_page" value="<?php echo $current_page; ?>" />
        <!-- For Sorting List -->
        <input type="hidden" id="sorter-name" name="sorter[name]" value="<?php echo get_filter($sorter, 'name', 'receive_date'); ?>" />
        <input type="hidden" id="sorter-value" name="sorter[value]" value="<?php echo get_filter($sorter, 'value', 'DESC'); ?>" />
        <!-- End For Sorting List -->
        <!-- Checked Items -->
        <input type="hidden" id="selected-ids" name="selected_ids" value="<?php echo implode(',', $selected_ids); ?>" />
        <div class="row">
            <div class="btn-wp">
                <a class="btn btn-primary uppercase" href="<?php echo site_url('purchase_orders/index'); ?>">Danh sách đơn hàng</a>
                <a class="btn btn-primary uppercase" href="<?php echo site_url('purchase_orders/view/-1'); ?>">Thêm mới</a>
            </div>
            <div class="col-md-12">
                <div class="panel panel-piluku">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <strong>Danh sách đơn hàng cho nhà cung cấp</strong> <label class="badge bg-primary"><?php echo $total; ?></label>
                        </h3>
                    </div>
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th width="80px">STT</th>
                            <th class="pointer" title="po_code" dir="ASC" width="120px" onclick="return grid.action_sort(this)">Mã PO <?php echo render_sorter($sorter, 'po_code'); ?></th>
                            <th class="pointer" title="supplier_id" dir="ASC" width="120px" onclick="return grid.action_sort(this)">Nhà cung cấp <?php echo render_sorter($sorter, 'supplier_id'); ?></th>
                            <th class="pointer" title="purchase_orders.person_id" dir="ASC" width="120px" onclick="return grid.action_sort(this)">Nhân viên nhập <?php echo render_sorter($sorter, 'purchase_orders.person_id'); ?></th>
                            <th class="pointer" title="receive_date" dir="ASC" width="200px" onclick="return grid.action_sort(this)">Ngày nhận <?php echo render_sorter($sorter, 'receive_date'); ?></th>
                            <th width="200px">Ngày tạo</th>
                            <th width="300px">Ghi chú</th>
                            <th width="300px">Xử lý</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($collection)): ?>
                            <?php $index = 1; foreach ($collection as $item): ?>
                                <tr>
                                    <td><?php echo ((($current_page - 1) * $record_per_page) + $index); ?></td>
                                    <td><a href="<?php echo site_url('purchase_orders/view/' . get_data($item, 'id')); ?>" data-toggle="tooltip" title="Sửa"><?php echo get_data($item, 'po_code'); ?></a></td>
                                    <td><?php echo get_data($item, 'company_name'); ?></td>
                                    <td><?php echo get_data($item, 'first_name') . ' ' . get_data($item, 'last_name'); ?></td>
                                    <td><?php echo date('d/m/Y', get_data($item, 'receive_date', time())); ?></td>
                                    <td><?php echo date('d/m/Y', get_data($item, 'created_at', time())); ?></td>
                                    <td><?php echo get_data($item, 'comment'); ?></td>
                                    <td>
                                        <a
									href="<?php echo site_url('purchase_orders/view/' . get_data($item, 'id')); ?>"
									data-toggle="tooltip" title="Sửa"
									class="btn btn-primary uppercase">Sửa</a>
                                    <a onclick ="javascript: alert('Bạn có chắc chắn xóa');"
									href="<?php echo site_url('purchase_orders/delete/' . get_data($item, 'id')); ?>"
									data-toggle="tooltip" title="Xóa"
									class="btn btn-primary uppercase">Xóa</a>
								</td>
                                </tr>
                                <?php $index++; endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
       
        <div class="paginator" align="center">
            <div class="pull-left directive">
                Trang <?php echo $current_page; ?>/<?php echo $pager['upper']; ?>
            </div>
            <a class="btn btn-default" onclick="grid.first_page();"><i class="ti ti-angle-double-left"></i></a>
            <a class="btn btn-default" onclick="grid.prev_page();"><i class="ti ti-angle-left"></i></a>
            <?php for ($index = $pager['lower']; $index <= $pager['upper']; $index++) :?>
                <a class="btn  <?php if ($index == $current_page) :?>btn-primary<?php else: ?>btn-default<?php endif;?>" onclick="grid.go_page(<?php echo $index; ?>);"><?php echo $index; ?></a>
            <?php endfor; ?>
            <a class="btn btn-default" onclick="grid.next_page(<?php echo $pager['total']; ?>);"><i class="ti ti-angle-right"></i></a>
            <a class="btn btn-default" onclick="grid.last_page(<?php echo $pager['total']; ?>);"><i class="ti ti-angle-double-right"></i></a>
        </div>
        
    </form>
    <script type="text/javascript">
        $('.select2').select2();
        if ($("#frm-filter-po").length > 0) {
            grid.form = $("#frm-filter-po");
            grid.page = $("#current-page");
            grid.sorter = {
                "field_name": $("#sorter-name"),
                "field_value": $("#sorter-value")
            };
            grid.selected_input = $('#selected-ids');
        }
    </script>
</div>
<?php $this->load->view("partial/footer"); ?>