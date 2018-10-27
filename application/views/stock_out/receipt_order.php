<?php $this->load->view("partial/header"); ?>
<?php
if (isset($error_message))
{
    echo '<h1 style="text-align: center;">'.$error_message.'</h1>';
    exit;
}

$company = ($company = $this->Location->get_info_for_key('company', isset($override_location_id) ? $override_location_id : FALSE)) ? $company : $this->config->item('company');
$company_logo = ($company_logo = $this->Location->get_info_for_key('company_logo', isset($override_location_id) ? $override_location_id : FALSE)) ? $company_logo : $this->config->item('company_logo');

?>
<style>
    .table td p {margin-bottom: 0px;}
    .table td {white-space: nowrap}
</style>
<div class="manage_buttons hidden-print pd-15" style="padding: 15px;">
    <button class="btn btn-primary btn-lg hidden-print" id="print_button" onClick="do_print()" > <?php echo lang('common_print'); ?> </button>
    <a class="btn btn-primary btn-lg hidden-print" href="<?php echo site_url('stock_out'); ?>" > <?php echo lang('common_back'); ?> </a>
</div>
<div style="margin-top: 25px;" class="row receipt_<?php echo $this->config->item('receipt_text_size') ? $this->config->item('receipt_text_size') : 'small';?>" id="receipt_wrapper">
    <div class="col-md-12" id="receipt_wrapper_inner">
        <div class="panel panel-piluku">
            <div class="panel-body panel-pad">
                <table style="width: 668px" width="668px" class="table table-bordered font-14 font-arial">
                    <tbody>
                    <tr>
                        <td colspan="8" width="576" align="center">
                            <p class="uppercase bold">Bảng kê xuất hàng <?php echo get_data($customer, 'first_name') . ' ' . get_data($customer, 'last_name'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" width="303">
                            <p><strong>Ngày <?php echo date('d', get_data($stock_request, 'received_at')); ?> tháng <?php echo date('m', get_data($stock_request, 'received_at')); ?> năm <?php echo date('Y', get_data($stock_request, 'received_at')); ?></strong></p>
                        </td>
                        <td class="uppercase" colspan="5">
                            <div class="row">
                                <div class="col-md-4" style="float: left; width: 33.3%; text-align: center">
                                    Thủ kho
                                </div>
                                <div class="col-md-4" style="float: left; width: 33.3%; text-align: center">
                                    Dán tem
                                </div>
                                <div class="col-md-4" style="float: left; width: 33.3%; text-align: center">
                                    Đóng hàng
                                </div>
                                <div class="cl" style="clear: both; display: block;"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" width="175">
                            <p>Số xe: <strong><?php echo get_data($stock_request, 'license_plate'); ?></strong></p>
                        </td>
                        <td colspan="5" rowspan="3">
                            <div class="row">
                                <div class="col-md-4" style="float: left; width: 33.3%; text-align: center">

                                </div>
                                <div class="col-md-4" style="float: left; width: 33.3%; text-align: center">

                                </div>
                                <div class="col-md-4" style="float: left; width: 33.3%; text-align: center">

                                </div>
                                <div class="cl" style="clear: both; display: block"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" width="173">
                            <p>Người giao hàng: <strong><?php echo get_data($employee, 'first_name') . ' ' . get_data($employee, 'last_name'); ?><strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" width="303">
                            <p>Thời gian xuất hàng : <strong></strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" rowspan="2" width="175">
                            <p>Tên phụ tùng</p>
                        </td>
                        <td colspan="6" width="401">
                            <p style="width: 500px; white-space: normal; overflow-x: scroll"><strong>PS-CD: <?php echo get_data($stock_request, 'ports'); ?></strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td width="128">
                            <p>Số lượng (PCS)</p>
                        </td>
                        <td colspan="2" width="55">
                            <p>Quy cách</p>
                        </td>
                        <td colspan="2" width="42">
                            <p>Thực xuất</p>
                        </td>
                        <td width="176">
                            <p>Ghi chú</p>
                        </td>
                    </tr>
                    <?php $index = 0; foreach ($stock_request->items as $item): ?>
                    <tr>
                        <td colspan="2">
                            <strong><?php echo get_data($item, 'name'); ?></strong>
                        </td>
                        <td colspan="1" width="55">
                            <?php echo get_data($item, 'quantity'); ?>
                        </td>
                        <td colspan="2" width="55">
                            <?php echo get_unit_text(get_data($item, 'item_id'), get_data($item, 'quantity_received')); ?>
                        </td>
                        <td colspan="2" width="55">
                            <?php echo get_data($item, 'quantity_received', get_data($item, 'quantity')); ?>
                        </td>
                        <?php if ($index == 0): ?>
                        <td rowspan="<?php echo count($stock_request->items); ?>" width="176">
                            <?php echo get_data($stock_request, 'comment'); ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php $index++; endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view("partial/footer"); ?>
<script type="text/javascript">
    <?php if ($this->config->item('print_after_stock_out') && $this->uri->segment(2) == 'complete')
    {
    ?>
    $(window).load(function()
    {
        do_print();
    });
    <?php
    }
    ?>
    function do_print()
    {
        window.print();
        <?php
        if ($this->config->item('redirect_to_sale_or_recv_screen_after_printing_receipt'))
        {
        ?>
        window.location = '<?php echo site_url('stock_out'); ?>';
        <?php
        }
        ?>
    }
</script>