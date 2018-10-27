<?php $this->load->view("partial/header");?>
<div class="panel panel-piluku khsx-vtp" style="margin-top: 30px;">
	<div class="panel-heading">
		<h3 class="panel-title">
			KẾ HOẠCH MUA HÀNG VẬT TƯ PHỤ
		</h3>
	</div>
	<div class="panel-body form-horizontal" style="padding-left: 0px; padding-right: 0px;">
		<div role="tabpanel" class="tab-pane" id="summary" v-show="!loading">
        		<div class="col-md-12 materials-content" style="padding-left: 0px;">
        			<div class="col-md-12 table-responsive">
        				<table class="table table-bordered">
        					<thead>
        						<tr>
        							<th rowspan="2">Tên sản phẩm</th>
        							<th rowspan="2">ĐVT</th>
        							<th rowspan="2">Mã VT</th>
        							<th rowspan="2">Tên NCC</th>
        							<th rowspan ="2">Đơn giá</th>
        							<th colspan="2">Tồn tháng 8</th>
        							<th colspan="2">Nhu cầu KH</th>
        							<th colspan="2">Nhu cầu mua thực tế</th>
        						</tr>
        						<tr>
        							<th>Số lượng</th>
        							<th>TT</th>
        							<th>Số lượng</th>
        							<th>TT</th>
        							<th>Số lượng</th>
        							<th>TT</th>
        						</tr>
        					</thead>
        					<tbody>
        						<tr v-for="summary_material in summary_materials">
        							<td @click="show_material_detail(summary_material)" style="cursor: pointer;"><span>{{summary_material.name}}</span></td>
        							<td><span>{{summary_material.unit}}</span></td>
        							<td><span>{{summary_material.product_id}}</span></td>
        							<td><span></span></td>
        							<td><span>{{summary_material.cost_price | format_number}}</span></td>
        							<td><span>{{summary_material.inventory_month_qty | format_number}}</span></td>
        							<td><span>{{summary_material.inventory_month_price | format_number}}</span></td>
        							<td><span><input class ="summary_material--input-data" type="" name=""  v-model="summary_material.calculated_by_unit" @change="todata"></span></td>
        							<td><span>{{summary_material.price_calculated | format_number}}</span></td>
        							<td><span>{{summary_material.target_month_qty | format_number}}</span></td>
        							<td><span>{{summary_material.target_month_price | format_number}}</span></td>
        						</tr>
        					</tbody>
        				</table>
        			</div>
        		</div>
			</div>
	</div>
	<div class="form-actions pull-right">
		<input type="submit" value="Cập nhật" class="submit_button floating-button btn btn-primary" style="margin-right: 80px;" @click="fnLoad" >
		<input type="submit" value="Submit" class="submit_button floating-button btn btn-primary" @click="fnSave" >
	</div>

	<div id="material_plan_detail" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
    				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    				<h4 class="modal-title" id="myModalLabel">VẬT TƯ PHỤ: {{material_detail.material.name}}</h4>
    			</div>

				<div class="modal-body">
					<div class="row" style="margin-bottom: 15px;">
						<div class="col-md-4">
							<span>Đơn giá: <span>{{material_detail.material.cost_price | format_number}}</span></span>
						</div>
						
						<div class="col-md-4">
							<span>Lượng tồn tối thiểu: <span>{{material_detail.material.limit}}</span></span>
						</div>
						
						<div class="col-md-4">
							<span>Tỉ lệ hao hụt: <span>{{material_detail.material.buffer_rate}}</span></span>
						</div>
					</div>
					<div class="panel panel-piluku detail_boms">
						<div class="panel-heading">
							<h3 class="panel-title">
								Định mức vật tư
							</h3>
						</div>
						<div class="panel-body">
							<table class="tablesorter table table-hover table-bordered">
								<thead>
									<tr>
										<th>Mã hiệu sản phẩm</th>
										<th>Quy cách (tên gọi)</th>
										<th>Nhà sản xuất</th>
										<th>Đơn vị tính</th>
										<th>Khối lượng chi (ĐVT/1SP)</th>
										<th>Định mức (SP/ĐVT)</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(bom, i) in material_detail.boms">
										<td>{{bom.product_id | display_value}}</td>
										<td>{{bom.name | display_value}}</td>
										<td>{{bom.manufacturer | display_value}}</td>
										<td>{{bom.unit | display_value}}</td>
										<td>{{bom.rate_of_qty | display_value}}</td>
										<td>{{bom.rate_of_unit | display_value}}</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>

					<div class="panel panel-piluku detail_plan">
						<div class="panel-heading">
							<h3 class="panel-title">
								Kế hoạch sản xuất
							</h3>
						</div>
						<div class="panel-body">
							<table class="tablesorter table table-hover table-bordered">
								<thead>
									<tr>
										<th>Mã hiệu sản phẩm</th>
										<th>Số lượng</th>
										<th>Tổng</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(sale_item, i) in material_detail.sale_items">
										<td style="width: 10%">{{sale_item.item_name | display_value}}</td>
										<td style="width: 80%">
											<span style="margin-right: 3px; margin-bottom: 3px;" class="btn btn-primary btn-sm" v-for="(date, i) in sale_item.dates">
  												{{date.date}} <span class="badge">{{date.qty | format_number}}</span>
											</span>
										</td>
										<td style="width: 10%">{{sale_item.total | format_number}}</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					
					<div class="panel panel-piluku detail_plan">
						<div class="panel-heading">
							<h3 class="panel-title">
								Forecast
							</h3>
						</div>
						<div class="panel-body">
							<table class="tablesorter table table-hover table-bordered">
								<thead>
									<tr>
										<th>Mã hiệu sản phẩm</th>
										<th>Số lượng</th>
										<th>Tổng</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(forecast_item, i) in material_detail.forecast_items">
										<td style="width: 10%">{{forecast_item.item_name | display_value}}</td>
										<td style="width: 80%">
											<span style="margin-right: 3px; margin-bottom: 3px;" class="btn btn-primary btn-sm" v-for="(month, i) in forecast_item.months">
  												{{month.month}} <span class="badge">{{month.qty | format_number}}</span>
											</span>
										</td>
										<td style="width: 10%">{{forecast_item.total | format_number}}</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php $this->load->view("partial/footer"); ?>