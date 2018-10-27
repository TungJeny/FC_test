<?php $this->load->view("partial/header");?>
<div class="panel panel-piluku" style="margin-top: 30px;">
	<div class="panel-heading">
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#detail" aria-controls="home" role="tab" data-toggle="tab" @click="showDetail()">CHI TIẾT NĂM <?php echo date('Y');?></a></li>
			<li role="presentation"><a href="#summary" aria-controls="profile" role="tab" data-toggle="tab" @click="showSummaryOfMonth()">KẾ HOẠCH MUA VẬT TƯ</a></li>
		</ul>
	</div>
	
	<div class="panel-body form-horizontal" style="padding-left: 0px; padding-right: 0px;">
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="detail" v-show="!loading">
				<div style="padding-right: 0px;">
        			<div class="table-responsive">
        				<table class="table table-bordered">
        					<thead>
        						<tr>
        							<th><span style="float: left;">TÊN VẬT TƯ</span></th>
        							<th><span style="float: right; padding-right: 30px;">ĐVT</span></th>
        							<th style="" v-for="month in months">{{month}}</th>
        						</tr>
        					</thead>
        					<tbody id="fixedcolumnbody-detail" onscroll="fixscroll('detail')">
        						<tr v-for="material in materials">
        							<td v-bind:class="{material: is_material(material)}" @click="show_material_detail(material)">
        								<span class="name" style="float: left;">{{material.name}}</span>
        								
        							</td>
        							<td v-bind:class="{material: is_material(material)}" @click="show_material_detail(material)">
        							<span class="unit" style="float: right; padding-right: 15px;">{{material.unit}}</span>
        							</td>
        							<td v-for="month in months" class="customs_css">
                                       
                                        <input class="summary_material--input-data" v-if="material.type == 'qty_khsx'" v-model="materials_matrix[material.material_id][month][material.type]"/> 

                                        <!-- <span v-if="material.type == 'qty_booked'">{{materials_matrix[material.material_id][month][material.type]}}</span> -->
                                        <span class="small_column" v-else-if="material.type == 'qty_booked'">{{materials_matrix[material.material_id][month][material.type]}}</span>

        								<input class="summary_material--input-data" v-else-if="material.type == 'qty_esitmate'" v-model="materials_matrix[material.material_id][month][material.type]"/>

        								<!-- <input class="summary_material--input-data" v-else-if="material.type == 'qty_actual_income'" v-model="materials_matrix[material.material_id][month][material.type]"/> -->
                                        <span class="summary_material--input-data" v-else-if="material.type == 'qty_actual_income'">{{materials_matrix[material.material_id][month][material.type]}}</span>

                                        <input class="summary_material--input-data" v-else-if="material.type == 'qty_inventory'" v-model="materials_matrix[material.material_id][month][material.type]">

                                        <span v-else>
                                        {{get_material_matrix_value(month, material) | format_number}}
                                        </span>

        								<!-- <span v-else>{{get_material_matrix_value(month, material) | format_number}}</span> -->
        							</td>
        						</tr>
        					</tbody>
        				</table>
        			</div>
        		</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="summary" v-show="!loading">
				<div class="col-md-3 fixed-column" style="padding-right: 0px; margin-right: -5px;">
        			<div class="fixed-column-outter">
        				<table class="table">
        					<thead>
        						<tr>
        							<th rowspan="3">
        								<span style="float: left;">TÊN VẬT TƯ</span>
        								<span style="float: right; padding-right: 30px;">ĐVT</span>
        							</th>
        						</tr>
        					</thead>
        					<tbody id="fixedcolumnbody-summary" onscroll="fixscroll('summary')">
        						<tr v-for="summary_material in summary_materials">
        							<td class="material" style="cursor: pointer;" @click="show_material_detail(summary_material)">
        								<span class="name" style="float: left;">{{summary_material.name}}</span>
        								<span class="unit" style="float: right; padding-right: 15px;">{{summary_material.unit}}</span>
        							</td>
        						</tr>
        					</tbody>
        				</table>
        			</div>
        		</div>
        		<div class="col-md-9 materials-content" style="padding-left: 0px;">
        			<div class="summary_content_outter">
        				<table class="table">
        					<thead id="contenthead-summary">
        						<?php 
        						
        						$month = 'T' . date('m', strtotime(date($month)));
        						$last_month = 'T' . date('m', strtotime(date($last_month)));
        						
        						foreach ($est_months as &$est_month){
        						    $est_month = 'T' . date('m', strtotime(date($est_month)));
        						}
        						
        						?>
        						<tr>
        							<th colspan="8"><?php echo date('Y-m');?></th>
        							<th rowspan="3">Lượng tồn Tối Thiểu</th>
        							<th colspan="6">&nbsp;</th>
        							<th colspan="4">Ghi Chú</th>
        						</tr>
        						
        						<tr>
        							<th colspan="2">Vật tư, Nguyên liệu tồn kho <?php echo $last_month;?></th>
        							<th colspan="2">Vật tư, Nguyên liệu theo KHSX tháng <?php echo $month; echo '-'.date('Y');?></th>
        							<th colspan="2">Hàng đã đặt về <?php echo $month;?> ( Nhu cầu cho SX <?php echo $month;echo '/'.date('Y'); ?>)</th>
        							<th colspan="2">Tồn kho cuối <?php echo $month;?></th>
        							
        							<th colspan="2">KHSX <?php echo implode('+', $est_months); ?></th>
        							<th colspan="2">Hàng đã đặt về <?php echo implode('+', $est_months); ?></th>
        							<th colspan="2">Đặt hàng tháng <?php echo $month;?> cho SX <?php echo implode('+', $est_months); ?></th>
        							
        							<th rowspan="2">Thời gian cần về</th>
        							<th rowspan="2">Mục đích sử dụng</th>
        							<th rowspan="2">Nhà cung cấp</th>
        							<th rowspan="2">Hạng mục</th>
        						</tr>
        						
        						<tr>
        							<th>Số lượng</th>
        							<th>TT</th>
        							<th>Số lượng</th>
        							<th>TT</th>
        							<th>Số lượng</th>
        							<th>TT</th>
        							<th>Số lượng</th>
        							<th>TT</th>
        							
        							<th>Số lượng</th>
        							<th>TT</th>
        							<th>Số lượng</th>
        							<th>TT</th>
        							<th>Số lượng</th>
        							<th>TT</th>
        						</tr>
        					</thead>
        				</table>
        			</div>
        			<div class="table-outter">
        				<table class="table">
        					<tbody id="contentbody-summary" onscroll="contentscroll('summary')">
        						<tr v-for="summary_material in summary_materials">
        							<td ><span>{{summary_matrix[summary_material.item_id].inventory_previous_month_qty | format_number}}</span></td>
        							<td ><span>{{summary_matrix[summary_material.item_id].inventory_previous_month_price | format_number}}</span></td>
        							<td ><span>{{summary_matrix[summary_material.item_id].khsx_month_qty | format_number}}</span></td>
        							<td ><span>{{summary_matrix[summary_material.item_id].khsx_month_price | format_number }}</span></td>
        							<td ><span>{{summary_matrix[summary_material.item_id].booked_month_qty | format_number}}</span></td>
        							<td ><span>{{summary_matrix[summary_material.item_id].booked_month_price | format_number }}</span></td>
        							<td ><span>{{summary_matrix[summary_material.item_id].inventory_month_qty | format_number }}</span></td>
        							<td ><span>{{summary_matrix[summary_material.item_id].inventory_month_price | format_number }}</span></td>
        							<td ><span>{{summary_matrix[summary_material.item_id].limit_number | format_number }}</span></td>
        							
        							<td ><span>{{summary_matrix[summary_material.item_id].khsx_quater_qty | format_number}}</span></td>
        							<td ><span>{{summary_matrix[summary_material.item_id].khsx_quater_price | format_number}}</span></td>
        							
        							<td ><span>{{summary_matrix[summary_material.item_id].booked_quater_qty | format_number}}</span></td>
        							<td ><span>{{summary_matrix[summary_material.item_id].booked_quater_price | format_number}}</span></td>
        							
        							<td style="background-color: #ffe4c4">
        								<input v-model="summary_matrix[summary_material.material_id].target_month_qty"/>
        							</td>
        							<td><span>{{summary_matrix[summary_material.item_id].target_month_price | format_number}}</span></td>
        							<!-- <td><span>-</span></td>
        							<td><span>-</span></td>
        							<td><span>-</span></td>
        							<td><span>-</span></td> -->
                                    <td>
                                        <span>
                                            <input v-model="summary_matrix[summary_material.material_id].target_time_needed"/>
                                        </span>
                                    </td>
                                    <td>
                                        <span>
                                            <input v-model="summary_matrix[summary_material.material_id].target_uses
"/>
                                        </span>
                                    </td>
                                    <td>
                                        <span>
                                            <input v-model="summary_matrix[summary_material.material_id].target_supplier"/>
                                        </span>
                                    </td>
                                    <td>
                                        <span>
                                            <input v-model="summary_matrix[summary_material.material_id].target_categories
"/>
                                        </span>
                                    </td>
        						</tr>
        					</tbody>
        				</table>
        			</div>
        		</div>
			</div>
			
			<div style="height: 100px; position: relative;" v-show="loading">
    			<div class="mask">
    				<div class="spinner">
    					<div class="rect1"></div>
    					<div class="rect2"></div>
    					<div class="rect3"></div>
    				</div>
    			</div>
    		</div>
		</div>
	</div>
	
	<div class="form-actions pull-right">
		<input type="submit" value="Submit" class="submit_button floating-button btn btn-primary" @click="fnSave" >
	</div>

	<div id="material_plan_detail" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
    				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    				<h4 class="modal-title" id="myModalLabel">PHÔI: {{material_detail.material.name}}</h4>
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

<style type="text/css">
    span.name {
        width: 225px;
    }

    input.input_inventory {
        width: 70px;
    }
    .table-bordered tbody tr td .small_column {
        width: 75px;
    }
    input.summary_material--input-data {
        width: 50px;
    }
    td.material {
        height: 45px;
    }
    span.name {
        overflow: hidden;
    }
</style>
