<?php $this->load->view("partial/header"); ?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-piluku">
            <div class="panel-body form-horizontal pd-15">
                <div class="row">
                    <?php echo form_open("stock_in_temp/search_item",array('id' => 'frm-search-item', 'autocomplete' => 'off')); ?>
                    <div class="col-md-4">
                        <label class="font-14">Nhà cung cấp</label>
                        <select id="supplier-id" name="supplier_id" class="select2 filter-params">
                            <option value="0">Chọn  nhà cung cấp</option>
                            <?php foreach ($list_supplier as $supplier): ?>
                            <option value="<?php echo $supplier['person_id']; ?>"><?php echo $supplier['company_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="font-14">Mã Po </label>
                        <select id="po_id" name="po_id" class="select2 filter-params">
                        	<option value="0">Chọn mã po</option>
                            <?php  foreach ($list_po as $po): ?>
                            <option value="<?php echo $po['stock_in_by_id']; ?>"><?php echo $po['po_code']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="font-14">Mã lô vật tư</label>
                        <select id="package_slug" name="package_slug" class="select2 filter-params">
                        	<option value="0">Chọn mã lô</option>
                            <?php  foreach ($list_package_code as $package): ?>
                            <option value="<?php echo $package['package_slug']; ?>"><?php echo $package['package_code']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <div class="row mt-15">
                    <div class="col-md-12 table-responsive" id="ajax-result">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>STT</th>
                            <th>Nhà cung cấp</th>
                            <th>Mã Po</th>
                            <th>Mã lô vật tư</th>
                            <th>Tên sp</th>
                            <th>Số lượng</th>
                            <th>ĐVT</th>
                            <th>Ghi chú</th>
                            <th>Trạng thái</th>
                        </tr>
                        </thead>
                        <tbody>
                       <?php if (empty($stock_temp_items)): ?>
                        <tr>
                            <th colspan="9"><p class="font-14 mt-10" align="center">Không có thông tin</p></th>
                        </tr>
                        <?php else: ?>
                        <?php $index = 1; foreach ($stock_temp_items as $item): ?>
                        <tr>
                            <td><?php echo $index; ?></td>
                            <td><?php echo $item['company_name']; ?></a></td>
                            <td>
                                <?php echo $item['po_code']; ?>
                            </td>
                            <td><?php echo $item['package_code']; ?></td>
                            <td><?php echo $item['name']; ?></td>
                            <td><?php echo $item['overflow_quantity']; ?></td>
                            <td></td>
                            <td><?php echo $item['note']; ?></td>
                            <td class ="text-center">
                               <input type ="checkbox" disabled <?php echo $item['status']? 'checked':''; ?> >
                               <label><span></span></label>
                            </td>
                        </tr>
                        <?php $index++; endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                    <div class="cl"></div>
                </div>
            </div>
        </div>
		<div class="row">
			<div class="col-md-12 text-right">
				<button class ="btn btn-primary" id="transfer">Chuyển kho</button>
				<button class ="btn btn-primary" id="print">In</button>
			</div>
		</div>
	</div>

</div>
<script type="text/javascript">
    $('.select2').select2();
    $('.filter-params').on('change', function(){
        $.post( $('#frm-search-item').attr("action"), $('#frm-search-item').serialize(), function(res){
            $('#ajax-result table tbody').html(JSON.parse(res));
        });
    });

    $('#transfer').on('click', function(){
        $.get( 'stock_in_temp/transfer_item', function(res){
            res = JSON.parse(res);
            if (res.status == 'success') {
                toastr.success(res.msg);
            }
            if (res.status == 'error') {
                toastr.error(res.msg);
            }
            location.reload();
        });
    });
</script>
<?php $this->load->view("partial/footer"); ?>