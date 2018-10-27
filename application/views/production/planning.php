<?php $this->load->view("partial/header");?>
<div class="panel panel-piluku" style="margin-top: 30px;">
	<div class="panel-heading">
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#detail" aria-controls="home" role="tab" data-toggle="tab" @click="showDetail()">KẾ HOẠCH SẢN XUẤT THÁNG <?php echo date('m');?> NĂM <?php echo date('Y');?></a></li>
            <li role="presentation"><a href="#summary" aria-controls="profile" role="tab" data-toggle="tab" @click="showSummaryOfMonth()">KẾ HOẠCH SẢN XUẤT TỪNG LOẠI MẶT HÀNG THÁNG <?php echo date('m');?> NĂM <?php echo date('Y');?></a></li>
			<li role="presentation"><a href="#summary_sub" aria-controls="profile" role="tab" data-toggle="tab">DỰ TRÙ VẬT TƯ PHỤ HÀNG THÁNG <?php echo date('m');?> NĂM <?php echo date('Y');?></a></li>
		</ul>
	</div>
	
	<div class="panel-body form-horizontal" style="padding-left: 0px; padding-right: 0px;">
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="detail" v-show="!loading">
				<div class="col-md-3 fixed-column" style="padding-right: 0px;">
        			<div class="fixed-column-outter" ref="">
        				<table class="table">
        					<tbody id="fixedcolumnbody-detail" onscroll="fixscroll('detail')">
        						<tr style="height: 62px">
        							<td width="5%">STT</td>
        							<td>TÊN HÀNG</td>
        							<td>ĐVT</td>
        							<td>Giá KH</td>
        						</tr>
        						<tr style="height: 46px">
        							<td colspan ="4">
                                            <b>HÀNG CHI TIẾT XM</b>
        							</td>
        						</tr>
        						<tr style="height: 46px">
        							<td colspan ="4">
                                            <b>Hàng Honda</b>
        							</td>
        						</tr>
        						<tr v-for="material in materials" v-if="material.customer_id == 9" style="height: 46px">
	       							<td  v-bind:class="{material: is_material(material)}" @click="show_material_detail(material)">

        							</td>
        							<td  v-bind:class="{material: is_material(material)}" @click="show_material_detail(material)">
                                            <div class="col-md-5 name" v-bind:class="{italic: material.type_item == 'semi'}">{{material.name}}</div>

        							</td>
        							<td  v-bind:class="{material: is_material(material)}" @click="show_material_detail(material)">
  										 <div class="col-md-3 unit center">{{material.unit}}</div>
                                        </div>
        							</td>
        							<td  v-bind:class="{material: is_material(material)}" @click="show_material_detail(material)">
                                            <div class="col-md-2 ">{{material.price | format_number}}</div>
        							</td>
        						
        						</tr>
        						<tr style="height: 46px">
        							<td colspan ="4">
                                            <b>Hàng Yamaha</b>
        							</td>
        						</tr>
    						    <tr v-for="material in materials" v-if="material.customer_id == 10" style="height: 46px">
        							<td  v-bind:class="{material: is_material(material)}" @click="show_material_detail(material)">

        							</td>
        							<td  v-bind:class="{material: is_material(material)}" @click="show_material_detail(material)">
                                            <div class="col-md-5 name" v-bind:class="{italic: material.type_item == 'semi'}">{{material.name}}</div>

        							</td>
        							<td  v-bind:class="{material: is_material(material)}" @click="show_material_detail(material)">
  										 <div class="col-md-3 unit center">{{material.unit}}</div>
                                        </div>
        							</td>
        							<td  v-bind:class="{material: is_material(material)}" @click="show_material_detail(material)">
                                            <div class="col-md-2 ">{{material.price | format_number}}</div>
        							</td>
        						
        						</tr>
                                <tr>
                                    <td colspan ="4" style="background-color: #FFFFCC">
                                         <div class="row">
                                            <div class="col-md-12 " ><b>Tổng cộng</b></div>
                                        </div>
                                       
                                    </td>
                                </tr>
        					</tbody>
        				</table>
        			</div>
        		</div>
        		<div class="col-md-9 materials-content" style="padding-left: 0px;">
        			<div class="detail_content_outter">
            			<table class="table">
            				<thead id="contenthead-detail">
            					<tr>
            						<th colspan="2">Kế hoạch tháng <?php echo $month;?></th>
            						<?php foreach ($factories as $id => $factory) {?>
            							<th colspan="2"><?php echo $factory;?></th>
            						<?php } ?>
            					</tr>
            					
            					<tr>
            						<th>Số lượng</th>
<!--             						<th>Kế hoạch</th>
 -->                					<th>Thành tiền</th>
            						<?php foreach ($factories as $id => $factory) {?>
                						<th>Số lượng</th>
                						<th>Thành tiền</th>
            						<?php } ?>
            					</tr>
            				</thead>

            			</table>
            		</div>
        			<div class="table-outter">
        				<table class="table">
        					<tbody id="contentbody-detail" onscroll="contentscroll('detail')">
        						<tr style="height: 46px" ><td colspan = "16"></td></tr>
        						<tr style="height: 46px" > <td colspan = "16"></td></tr>
        						<tr v-for="material in materials" style="height: 46px" v-if="material.customer_id == 9">
        							<td>{{get_material_matrix_value(material, month, 'qty') | format_number}}</td>
        							<!-- <td>
        								<input v-model="materials_matrix[material.item_id]['xn_' + month + '_qty_actual']"/>
        							</td> -->
        							<td style="background-color: #fff0f5">{{get_material_matrix_value(material, month, 'price') | format_number}}</td>
        							
        							<template v-for="(factory, id) in factories">
        								<td>
        									<input v-model="materials_matrix[material.item_id]['xn_' + id + '_qty']"/>
        								</td>
                                       
                                        <!-- <td style="background-color: #fff0f5"><input></td> -->
                                        <!-- {{get_material_matrix_value(material, id, 'price') | format_number}} -->

        								<td style="background-color: #fff0f5"><input v-model="materials_matrix[material.item_id]['xn_' + id + '_price']"  /></td>
        							</template>
        						</tr>
        						<tr style="height: 46px" >
        						<td colspan = "16"></td>
        						</tr>
        						<tr v-for="material in materials" style="height: 46px" v-if="material.customer_id == 10">
        							<td>{{get_material_matrix_value(material, month, 'qty') | format_number}}</td>
        							<!-- <td>
        								<input v-model="materials_matrix[material.item_id]['xn_' + month + '_qty_actual']"/>
        							</td> -->
        							<td style="background-color: #fff0f5">{{get_material_matrix_value(material, month, 'price') | format_number}}</td>
        							
        							<template v-for="(factory, id) in factories">
        								<td>
        									<input v-model="materials_matrix[material.item_id]['xn_' + id + '_qty']"/>
        								</td>
                                       
                                        <!-- <td style="background-color: #fff0f5"><input></td> -->
                                        <!-- {{get_material_matrix_value(material, id, 'price') | format_number}} -->

        								<td style="background-color: #fff0f5"><input v-model="materials_matrix[material.item_id]['xn_' + id + '_price']"  /></td>
        							</template>
        						</tr>
                                <tr style="height: 40px">  
                                    <template v-for="total in totals">
                                        <td style="background-color: #FFFFCC">{{total | format_number}}</td>
                                    </template>
                                </tr>
        					</tbody>
        				</table>
        			</div>
        		</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="summary" v-show="!loading">
				
        		<div class="col-md-9 materials-content-summary" style="padding-left: 0px;">
        			<div class="table-responsive">
        				<table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="center" rowspan="2">STT</th>
                                    <th class="center" rowspan="2">Tên mặt hàng</th>
                                    <th class="center" rowspan="2">ĐVT</th>
                                    <th class="center" colspan="2">Kế hoạch tháng </th>
                                    <th class="center" rowspan="2">Tỷ trọng</th>
                                </tr>
                                <tr>
                                    <th>Số lượng</th>
                                    <th>Thành tiền(đ)</th>
                                </tr>
                            </thead>
        					<tbody>
        						<tr v-for="(summary_material, key, index) in data_planning_full">
        							<td><span>{{index + 1}}</span></td>
        							<td><span>{{summary_material.name_planning}}</span></td>
        							<td><span>{{summary_material.unit}}</span></td>
        							<td><span>{{summary_material.qty_planning | format_number}}</span></td>
                                    <td><span>{{summary_material.price_planning | format_number}}</span></td>
        							<td><span>{{summary_material.percent_planning | format_number}}</span></td>
        						</tr>
        					</tbody>
        				</table>
        			</div>
        		</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="summary_sub" v-show="!loading">
                <div class="col-md-3 fixed-column" style="padding-right: 0px;">
                    <div class="fixed-column-outter" ref="">
                        <table class="table">
                            <thead>
                                <tr class="center" style="height: 69px;">
                                    <th class="center">STT</th>
                                    <th class="center" width="280px">Danh mục vật tư</th>
                                    <th class="center" width="68px">ĐVT</th>
                                </tr>
                            </thead>
                            <tbody id="fixedcolumnbody-detail" onscroll="fixscroll('detail')">
                                <tr v-for="(plan_sub,index) in planning_sub" style="height: 46px">
                                    <template>
                                        <td>{{index}}</td>
                                        <td class="center" width="280px">{{plan_sub.name}}</td>
                                        <td class="center" width="68px">{{plan_sub.unit}}</td> 
                                    </template>
                                    
                                </tr>
                                
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-9 materials-content" style="padding-left: 0px;">
                   
                    <div class="table-outter">
                        <table class="table">
                            
                            <tbody id="contentbody-detail" onscroll="contentscroll('detail')">
                                <tr>
                                    <th colspan="7" class="center">Như cầu sản xuất</th>
                                    <th rowspan="2"> Ghi chú </th>
                                </tr>
                                <tr>

                                    <?php foreach ($factories as $id => $factory) {?>
                                        <th class="center" width="86"><?php echo $factory;?></th>
                                    <?php } ?>
                                    <th class="center">Tổng</th>

                                </tr>
                                <tr v-for="(material,index) in planning_sub" style="height: 47px;">
                                    <template v-for="(factory, id) in factories">
                                        <td class="center" width="68px">{{data_sub[material['item_id']][id] | format_number}}</td> 
                                    </template>
                                        <td class="center" width="68px">{{data_sub[material['item_id']]['toal'] | format_number}}</td> 
                                        <td class="center" width="68px"><input name="comn_sub[]" value="" v-model="data_planning_detail['detail'][index]"></td>
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
		<input type="submit" value="Lưu" class="submit_button floating-button btn btn-primary" @click="fnSave" >
	</div>

	<div id="material_plan_detail" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
    				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    				<h4 class="modal-title" id="myModalLabel">VẬT TƯ CHÍNH: {{material_detail.material.name}}</h4>
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