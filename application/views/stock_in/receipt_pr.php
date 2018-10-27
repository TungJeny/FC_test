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
<div class="manage_buttons hidden-print pd-15" style="padding: 15px;">
    <button class="btn btn-primary btn-lg hidden-print" id="print_button" onClick="do_print()" > <?php echo lang('common_print'); ?> </button>
    <a class="btn btn-primary btn-lg hidden-print" href="<?php echo site_url('stock_in/view/' . get_data($stock_request, 'stock_id')); ?>" > <?php echo lang('common_back'); ?> </a>
</div>
<div style="margin-top: 25px;" class="row receipt_<?php echo $this->config->item('receipt_text_size') ? $this->config->item('receipt_text_size') : 'small';?>" id="receipt_wrapper">
	<div class="col-md-12" id="receipt_wrapper_inner">
		<div class="panel panel-piluku">
			<div class="panel-body panel-pad font-arial">
				<div class="row">
					<div class="col-md-2 col-sm-2 col-xs-12" align="center" style="float: left; display: block; width: 16%">
                        <?php echo img(array('src' => $this->Appfile->get_url_for_file($company_logo))); ?>
					</div>
                    <div class="col-md-10 col-sm-10 col-xs-12" style="float: left; display: block; width: 84%">
                        <h3 class="font-18 bold"><?php echo $company; ?></h3>
                        <p><?php echo nl2br($this->Location->get_info_for_key('address',isset($override_location_id) ? $override_location_id : FALSE)); ?></p>
                        <p><?php echo $this->Location->get_info_for_key('phone',isset($override_location_id) ? $override_location_id : FALSE); ?></p>
                    </div>
                    <div class="cl" style="clear: both; display: block"></div>
				</div>
                <div class="mt-15 font-14">
                    <h3 class="uppercase font-18 bold" align="center"><?php echo $invoice_title; ?></h3>
                    <p class="mt-10" align="center">Số phiếu: <?php echo get_data($stock_request, 'updated_at'); ?></p>
                    <p class="mt-10" align="center">Ngày <?php echo date('d'); ?> tháng <?php echo date('m'); ?> năm <?php echo date('Y'); ?></p>
                    <p class="mt-10">Nhân viên nhập kho: <?php echo get_data($employee, 'first_name') . ' ' . get_data($employee, 'last_name'); ?></p>
                    <p class="mt-10">Địa chỉ: <?php echo get_data($location, 'name'); ?></p>
                    <div class="row mt-10">
                        <div class="col-md-6">
                            Diễn giải: <?php echo get_data($stock_request, 'comment'); ?>
                        </div>
                        <div class="col-md-6 pull-right">
                            Nhập tại kho: <?php echo get_data($location, 'name'); ?>
                        </div>
                        <div class="cl"></div>
                    </div>
                    <?php if (!empty($stock_request->items)): ?>
                    <div class="mt-15">
                        <table class="invoice-table table table-bordered font-14 font-arial">
                            <thead>
                                <th class="font-14">STT</th>
                                <th class="font-14">Tên phụ tùng</th>
                                <th class="font-14">ĐVT</th>
                                <th class="font-14">Số lượng</th>
                                <th class="font-14">Ghi chú</th>
                            </thead>
                            <tbody>
                            <?php $index = 1; foreach (array_reverse($stock_request->items) as $item): ?>
                            <tr>
                                <td><?php echo $index; ?></td>
                                <td><?php echo H(get_data($item, 'name')); ?></td>
                                <td>Chiếc</td>
                                <td><?php echo get_data($item, 'quantity'); ?></td>
                                <td><?php echo get_data($item, 'note'); ?></td>
                            </tr>
                            <?php $index++; endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                    <div class="mt-15" align="right">
                        Ngày: <span class="gray-color dot-slot">----</span> Tháng: <span class="gray-color dot-slot">----</span> Năm: <span class="gray-color dot-slot">------</span>
                    </div>
                    <div class="row mt-15 mb-15" align="center">
                        <div class="col-md-3" style="width:25%;float:left;display:block" align="center">
                            <div class="uppercase">PHỤ TRÁCH CUNG TIÊU</div>
                            <div class="mt-20"><br/><br/></div>
                            <div class="mt-10"><i>(Ký, họ tên)</i></div>
                        </div>
                        <div class="col-md-3" style="width:25%;float:left;display:block" align="center">
                            <div class="uppercase">NGƯỜI GIAO HÀNG</div>
                            <div class="mt-20"><br/><br/></div>
                            <div class="mt-10"><i>(Ký, họ tên)</i></div>
                        </div>
                        <div class="col-md-3" style="width:25%;float:left;display:block" align="center">
                            <div class="uppercase">THỦ KHO</div>
                            <div class="mt-20"><br/><br/></div>
                            <div class="mt-10"><i>(Ký, họ tên)</i></div>
                        </div>
                        <div class="col-md-3" style="width:25%;float:left;display:block" align="center">
                            <div class="uppercase">THỦ TRƯỞNG ĐƠN VỊ</div>
                            <div class="mt-20"><br/><br/></div>
                            <div class="mt-10"><i>(Ký, họ tên)</i></div>
                        </div>
                        <div class="cl" style="clear:both;display:block"></div>
                    </div>
                </div>
            </div>
		</div>
	</div>
</div>
<?php $this->load->view("partial/footer"); ?>
<script type="text/javascript">
<?php if ($this->config->item('print_after_stock_in') && $this->uri->segment(2) == 'complete'): ?>
$(window).load(function() {
	do_print();
});
<?php endif; ?>
function do_print() {
	window.print();
	<?php if ($this->config->item('redirect_to_sale_or_recv_screen_after_printing_receipt')): ?>
 	window.location = '<?php echo site_url('stock_in'); ?>';
	<?php endif; ?>
}
</script>
