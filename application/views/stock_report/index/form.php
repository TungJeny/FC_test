<style>
    .panel-piluku > .panel-heading {padding: 0px; border-bottom: none;}
    .nav-tabs>li>a {border-radius: 0px !important; border-top: none !important;}
    .nav-tabs>li:first-child>a {border-left: none !important;}
    .no-margin {margin: 0px !important;}
    #fixed-table thead td {white-space: nowrap}
</style>
<script type="text/javascript" src="https://www.jqueryscript.net/demo/jQuery-Plugin-For-Fixed-Table-Header-Footer-Columns-TableHeadFixer/tableHeadFixer.js"></script>
<div class="modal fade hidden-print" id="stock-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width: 98%">
        <div class="modal-content">
            <!-- Ajax Content -->
        </div>
    </div>
</div>
<div id="ajax-result" class="main-content">
    <div class="panel panel-piluku">
        <div class="panel-heading">
            <ul class="nav nav-tabs">
                <?php foreach ($months as $month): ?>
                <li <?php if ($selected_month == $month): ?>class="active"<?php endif; ?>>
                    <a class="uppercase" href="<?php echo site_url('stock_report/view_by_month/' . $month); ?>">Tháng <?php echo $month . '/' . $selected_year; ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div><strong class="font-14">Đơn vị: <?php echo get_data($location, 'name'); ?></strong></div>
                    <div class="mt-10"><strong class="font-14"><?php echo $title; ?></strong> <?php echo date('d/m/Y'); ?></div>
                    <div class="mt-10"></div>
                </div>
                <div class="col-md-6"></div>
                <div class="cl"></div>
            </div>
            <div class="scroll-table" id="scroll-table" style="height: 500px;">
                <table id="fixed-table" width="100%" class="table table-bordered">
                    <thead style="position: relative;top:-2px !important;">
                        <tr>
                            <td rowspan="3" width="55" bgcolor="#f3f3f3">STT</td>
                            <td rowspan="3" width="180" bgcolor="#f3f3f3">Quy c&aacute;ch vật tư</td>
                            <td rowspan="3" width="54" bgcolor="#f3f3f3">Đơn vị</td>
                            <td rowspan="3" width="140" bgcolor="#f3f3f3">T&ecirc;n sản phẩm</td>
                            <td rowspan="3" width="89" bgcolor="#f3f3f3">Kh&aacute;ch h&agrave;ng</td>
                            <td colspan="2" rowspan="2" width="132" bgcolor="#f3f3f3">Kế hoạch nhập h&agrave;ng</td>
                            <td colspan="3" width="304" align="center" bgcolor="#f3f3f3">Tồn đầu th&aacute;ng</td>
                            <td rowspan="3" width="74" bgcolor="#f3f3f3">B&aacute;o động thừa- thiếu th&eacute;p</td>
                            <td rowspan="3" width="90" bgcolor="#f3f3f3">Ngừng cấp th&eacute;p cho XN</td>
                            <td rowspan="3" width="90" bgcolor="#f3f3f3">Tồn tối thiểu</td>
                            <td colspan="<?php echo count($receiver_locations); ?>" width="90" bgcolor="#f3f3f3" align="center">Xuất</td>
                            <td colspan="2" width="90" bgcolor="#f3f3f3" align="center">Tổng nhập</td>
                            <td colspan="2" width="90" bgcolor="#f3f3f3" align="center">Tổng xuất</td>
                            <td colspan="2" width="90" bgcolor="#f3f3f3" align="center">Tổng tồn</td>
                            <td rowspan="3" width="90" bgcolor="#f3f3f3">Chi tiết</td>
                        </tr>
                        <tr>
                            <td rowspan="2" width="86" bgcolor="#f3f3f3">Số lượng</td>
                            <td rowspan="2" width="91" bgcolor="#f3f3f3">Gi&aacute;&nbsp;</td>
                            <td rowspan="2" width="127" bgcolor="#f3f3f3">Th&agrave;nh tiền</td>
                            <?php if (!empty($receiver_locations)): ?>
                            <?php foreach ($receiver_locations as $receiver_location): ?>
                            <td rowspan="2" width="127" bgcolor="#f3f3f3"><?php echo get_data($receiver_location, 'name'); ?></td>
                            <?php endforeach; ?>
                            <?php endif; ?>
                            <td rowspan="2" width="127" bgcolor="#f3f3f3">Số lượng</td>
                            <td rowspan="2" width="127" bgcolor="#f3f3f3">Giá trị</td>
                            <td rowspan="2" width="127" bgcolor="#f3f3f3">Số lượng</td>
                            <td rowspan="2" width="127" bgcolor="#f3f3f3">Giá trị</td>
                            <td rowspan="2" width="127" bgcolor="#f3f3f3">Số lượng</td>
                            <td rowspan="2" width="127" bgcolor="#f3f3f3">Giá trị</td>
                        </tr>
                        <tr>
                            <td width="71" bgcolor="#f3f3f3">Số lượng</td>
                            <td style="white-space: nowrap" width="61" bgcolor="#f3f3f3">Ng&agrave;y cần</td>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($items)): ?>
                        <?php $index = 1; foreach ($items as $item): ?>
                        <?php
                        if (!empty($item->po_receive_date)) {
                            $receive_dates = explode(',', $item->po_receive_date);
                            $receive_dates = array_filter(array_unique($receive_dates));
                            sort($receive_dates);
                        }
                        ?>
                        <tr>
                            <td width="68"><?php echo $index; ?></td>
                            <td width="160px"><a target="_blank" href="<?php echo site_url('items/inventory/' . get_data($item, 'item_id') . '/2'); ?>"><?php echo get_data($item, 'item_name'); ?></a></td>
                            <td><?php echo get_data($item, 'unit_name'); ?></td>
                            <td width="140"><?php echo get_data($item, 'product_name'); ?></td>
                            <td width="89"><?php echo get_data($item, 'customer_name'); ?></td>
                            <td width="71"><?php echo get_data($item, 'po_quantity', 0); ?></td>
                            <td width="61" align="left">
                                <?php if (!empty($receive_dates)): ?>
                                <?php if (count($receive_dates) > 1): ?>
                                <?php foreach ($receive_dates as $receive_date): ?>
                                <div><?php echo date('d/m/Y', $receive_date); ?></div>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <?php foreach ($receive_dates as $receive_date): ?>
                                <?php echo date('d/m/Y', $receive_date); ?>
                                <?php endforeach; ?>
                                <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo get_data($item,  'quantity', 0); ?></td>
                            <td><?php echo to_currency(get_data($item, 'cost_price')); ?></td>
                            <td><?php echo to_currency(get_data($item,  'quantity', 0) * get_data($item, 'cost_price')); ?></td>
                            <td>&nbsp;THIẾU&nbsp;</td>
                            <td></td>
                            <td><?php echo get_data($item, 'limit'); ?></td>
                            <?php foreach ($receiver_locations as $receiver_location): ?>
                            <td>
                                <?php if (!empty($item->receiver_location_items)): ?>
                                <?php foreach ($item->receiver_location_items as $receiver_location_item): ?>
                                <?php if (get_data($receiver_location_item, 'receiver_location_id') == get_data($receiver_location, 'id')): ?>
                                <?php echo get_data($receiver_location_item, 'quantity'); ?>
                                <?php endif; ?>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                            <td><?php echo get_data($item, 'stock_in_quantity', 0); ?></td>
                            <td><?php echo to_currency(get_data($item,  'stock_in_quantity', 0) * get_data($item, 'cost_price')); ?></td>
                            <td><?php echo get_data($item, 'stock_out_quantity', 0); ?></td>
                            <td><?php echo to_currency(get_data($item,  'stock_out_quantity', 0) * get_data($item, 'cost_price')); ?></td>
                            <td><?php echo get_data($item, 'stock_summary_quantity', 0); ?></td>
                            <td><?php echo to_currency(get_data($item,  'stock_summary_quantity', 0) * get_data($item, 'cost_price')); ?></td>
                            <td><a href="<?php echo site_url('stock_report/view_by_month_detail/' . $selected_month . '?item_id=' . get_data($item, 'item_id')); ?>" class="btn btn-primary btn-detail">Chi tiết</a></td>
                        </tr>
                        <?php $index++; endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php if ($this->config->item('confirm_error_adding_item') && isset($error)):  ?>
    <script type="text/javascript">
        bootbox.confirm(<?php echo json_encode($error); ?>, function(result) {
            setTimeout(function() {$('#item').focus();}, 50);
        });
    </script>
<?php endif ?>
<script type="text/javascript">
    <?php
    if(isset($error) && !$this->config->item('confirm_error_adding_item')) {
        echo "show_feedback('error', ".json_encode($error).", ".json_encode(lang('common_error')).");";
    }
    if (isset($warning)) {
        echo "show_feedback('warning', ".json_encode($warning).", ".json_encode(lang('common_warning')).");";
    }
    if (isset($success)) {
        echo "show_feedback('success', ".json_encode($success).", ".json_encode(lang('common_success')).");";
    }
    ?>
</script>
<script type="text/javascript">
    $("#fixed-table").tableHeadFixer();
    $(".btn-detail").click(function() {
        var href = $(this).attr("href");
        $.ajax({
            url: href,
            type: 'POST',
            success: function(responses) {
                $("#stock-modal .modal-content").html(responses);
                $("#stock-modal").modal();
            }
        });
        return false;
    });
</script>
