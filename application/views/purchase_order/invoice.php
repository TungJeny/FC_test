<?php $this->load->view("partial/header"); ?>
<?php
$company = ($company = $this->Location->get_info_for_key('company', isset($override_location_id) ? $override_location_id : FALSE)) ? $company : $this->config->item('company');
$company_logo = ($company_logo = $this->Location->get_info_for_key('company_logo', isset($override_location_id) ? $override_location_id : FALSE)) ? $company_logo : $this->config->item('company_logo');
$company_address = ($company_address = $this->Location->get_info_for_key('address', isset($override_location_id) ? $override_location_id : FALSE)) ? $company_address : $this->config->item('address');
?>
<div class="row font-arial pd-15">
    <div class="btn-wp">
        <a class="btn btn-primary uppercase" href="<?php echo site_url('purchase_orders/view/' .  get_data($purchase_order, 'id')); ?>">Xem đơn hàng</a>
    </div>
    <div class="col-md-12">
        <div class="panel panel-piluku">
            <div class="panel-body form-horizontal pd-15">
                <div class="po-invoice">
                    <table width="100%" class="table table-bordered">
                        <tr>
                            <td align="center">
                                <?php if ($company_logo): ?>
                                    <img src="<?php echo $this->Appfile->get_url_for_file($company_logo); ?>" width="100px" height="auto" />
                                <?php endif; ?>
                            </td>
                            <td class="pdl-15">
                                <div class="company-title uppercase" align="center"><strong><?php echo $company; ?></strong></div>
                                <div>Địa chỉ: P.Bãi Bông, TX.Phổ yên, Thái Nguyên</div>
                                <div>Điện thoại: 0208.3863.693/3864.132/3863.083 Fax: 0208.3863.118</div>
                                <div>Tài khoản: 102010000442491 NHCT  Sông Công, Thái Nguyên</div>
                            </td>
                            <td align="center">
                                <div>Mã hiệu: SXKD-QT-02/BM.04</div>
                                <div>Ban hành/sửa đổi:  1/0</div>
                                <div>Ngày ban hành: 28/3/2014</div>
                            </td>
                        </tr>
                    </table>
                    <div align="right">
                        <i>Thái Nguyên, ngày <?php echo date('d', $purchase_order->created_at); ?> tháng <?php echo date('m',$purchase_order->created_at); ?> năm <?php echo date('Y', $purchase_order->created_at); ?></i>
                    </div>
                    <div>
                        <h2 align="center" class="uppercase font-16"><strong>Đơn đặt hàng</strong></h2>
                        <div class="bold center">Số: <?php echo get_data($purchase_order, 'po_code'); ?></div>
                        <div class="bold center">Kính gửi: <?php echo $purchase_order->supplier_name ?></div>
                        <div>Công ty CP Cơ khí Phổ yên đặt hàng:</div>
                    </div>
                    <table class="table table-bordered table-hover mt-15">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th width="120px">Tên hàng</th>
                                <th width="120px">ĐVT</th>
                                <th width="80px">Số lượng</th>
                                <th width="80px">Tiến độ</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($purchase_order->items)): ?>
                        <?php $index = 1; foreach ($purchase_order->items as $item): ?>
                        <tr>
                            <td><?php echo $index; ?></td>
                            <td><?php echo get_data($item, 'item_name'); ?></td>
                            <td><?php echo get_data($item, 'unit_name'); ?></td>
                            <td><?php echo get_data($item, 'quantity'); ?></td>
                            <td><?php echo get_data($item, 'progress', 'Chưa xác định'); ?></td>
                            <td><?php echo get_data($item, 'comment'); ?></td>
                        </tr>
                        <?php $index++; endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <div class="mt-15">
                        <div class="box">
                            <label><i>Ghi chú: </i></label>
                            <div><?php echo get_data($purchase_order, 'comment'); ?></div>
                        </div>
                        <p class="mt-15"><strong>Liên hệ: </strong> <?php echo get_data($purchase_order->person, 'first_name') . ' ' . get_data($purchase_order->person, 'last_name'); ?> - <?php echo get_data($purchase_order->person, 'phone_number'); ?></p>
                        <p>- Địa điểm giao hàng: <?php echo $company_address; ?></p>
                    </div>
                    <div class="mt-15 uppercase font-16" align="right">
                        <strong><?php echo $company; ?></strong>
                        <div><br/><br/><br/><br/><br/><br/></div>
                    </div>
                    <div class="cl"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="cl"></div>
    <div class="form-actions pull-right">
        <input onclick="admin_po.save();" type="button" value="In" class="submit_button floating-button btn btn-primary">
    </div>
</div>
<script type="text/javascript">
</script>
<?php $this->load->view("partial/footer"); ?>s