<?php $this->load->view("partial/header");
$this->load->helper('demo');
?>
<div class="panel panel-piluku">
	<div class="panel-heading">
        <h3 class="panel-title">
            <i class="ion-edit"></i> 
            <?php echo lang("basic_information"); ?>
        </h3>
    </div>
	<div class="panel-body form-horizontal">
		<div class="form-group">
			<label for="commission_percent" class="col-sm-3 col-md-3 col-lg-2 control-label">Tên công thức:</label>
			<div class="col-sm-9 col-md-9 col-lg-10">
				<input type="text" class="form-control" v-validate="{required: true}" name="bom_name" v-model="bom.name">
				<span v-show="errors.has('bom_name')" class="help message">{{ errors.first('bom_name') }}</span>
			</div>
		</div>
		<div class="form-group">
			<label for="commission_percent" class="col-sm-3 col-md-3 col-lg-2 control-label">Mã công thức:</label>
			<div class="col-sm-9 col-md-9 col-lg-10">
				<input type="text" class="form-control" v-validate="{required: true}" name="bom_code" v-model="bom.code">
				<span v-show="errors.has('bom_code')" class="help message">{{ errors.first('bom_code') }}</span>
			</div>
		</div>
		<div class="form-actions pull-right">
			<input type="submit" value="Submit" class="submit_button floating-button btn btn-primary" @click="fnSave" >
		</div>
	</div>
</div>

<div class="panel panel-piluku" v-for="(semi_item, i) in semi_items" style="margin-bottom: -2px;">
	<div class="panel-heading pointer" @click="toggle_panel(i)">
        <h3 class="panel-title" v-if="semi_item.semi_id == 'semi_main'">
            <i class="ion-edit"></i> 
            Định mức vật tư
        </h3>
        <h3 class="panel-title" v-else>
            <i class="ion-edit"></i> 
            Bán Thành Phẩm ({{semi_item.name}})
        </h3>
    </div>
	<div class="panel-body form-horizontal" v-show="semi_item.is_show">
		<div class="bs-example bs-example-tabs" data-example-id="togglable-tabs" style="margin-top: 20px;"> 
        	<ul class="nav nav-tabs" v-bind:id="'myTabs' + semi_item.semi_id" role="tablist"> 
            	<li role="presentation" class="active">
            		<a v-bind:href="'#vtc' + semi_item.semi_id" v-bind:id="'vtc_tab' + semi_item.semi_id" role="tab" data-toggle="tab" aria-expanded="true" @click="change_tab(semi_item.semi_id, 2)">Vật Tư Chính</a>
            	</li> 
            	<li role="presentation" class="">
        			<a v-bind:href="'#vtp' + semi_item.semi_id" role="tab" v-bind:id="'vtp_tab' + semi_item.semi_id" data-toggle="tab" aria-expanded="false" @click="change_tab(semi_item.semi_id, 3)">Vật Tư Phụ</a>
        		</li>
        		<li role="presentation" class="">
        			<a v-bind:href="'#phoi' + semi_item.semi_id" role="tab" v-bind:id="'phoi_tab' + semi_item.semi_id" data-toggle="tab" aria-expanded="false" @click="change_tab(semi_item.semi_id, 5)">Phôi</a>
        		</li>
        		<li style="float: right; width: 50%">
        			<autocomplete :resource="semi_item.resource" :refresh="true" :options="{parent_id: semi_item.semi_id}" v-on:onselected="on_selected"></autocomplete>
        		</li>
        	</ul>
        	<div class="tab-content"> 
        		<div class="tab-pane fade active in" role="tabpanel" v-bind:id="'vtc' + semi_item.semi_id">
        			<div class="row" style="margin-top:10px;">
        				<div class="col-md-12">
        					<table class="tablesorter table table-hover table-bordered">
                        	<thead>
                        		<tr>
                        			<th rowspan="2">STT {{materials[semi_item.semi_id].length}}</th>
                        			<th rowspan="2">Mã hiệu sản phẩm</th>
                        			<th colspan="4" style="text-align: center;">Vật tư</th>
                        			<th colspan="2" style="text-align: center;">Định mức</th>
                        			<th rowspan="2" width="50"></th>
                        		</tr>
                        		
                        		<tr>
                        			<th>Quy cách (tên gọi)</th>
                        			<th>Mã vật tư</th>
                        			<th>Nhà sản xuất</th>
                        			<th>Đơn vị tính</th>
                        			<th  width="10%">Khối lượng chi (ĐVT/1SP)</th>
                        			<th  width="10%">Định mức (SP/ĐVT)</th>
                        		</tr>
                        	</thead>
                        	<tbody>
                        		<tr style="cursor: pointer;" v-for="(record, i) in materials[semi_item.semi_id]" v-show="record.category_id == 2">
                        			<td>{{i + 1}}</td>
                        			<td>{{selected_item.name | displayValue}}</td>
                        			<td>{{record.name | displayValue}}</td>
                        			<td>{{record.product_id | displayValue}}</td>
                        			<td>{{record.manufacturer | displayValue}}</td>
                        			<td>{{record.unit | displayValue}}</td>
                        			<td>
                        				<input style="width: 100%;" type ="number" v-model="materials[semi_item.semi_id][i].rate_of_qty"  @change="update_rate_of_qty(semi_item.semi_id, i)"/>
                        			</td>
                        			<td>
                        				<input style="width: 100%;" type ="number" v-model="materials[semi_item.semi_id][i].rate_of_unit"  @change="update_rate_of_unit(semi_item.semi_id, i)"/>
                        			</td>
                        			<td>
                        				<button type="button" class="btn btn-default" @click="remove_item(semi_item.semi_id, record)">
  											<span class="icon ti-trash" aria-hidden="true"></span>
										</button>
                        			</td>
                        		</tr>
                        	</tbody>
                            </table>
        				</div>
    				</div> 
        		</div>
        		<div class="tab-pane fade in" role="tabpanel" v-bind:id="'vtp' + semi_item.semi_id">
        			<div class="row" style="margin-top:10px;">
        				<div class="col-md-12">
        					<table class="tablesorter table table-hover table-bordered">
                        	<thead>
                        		<tr>
                        			<th rowspan="2">STT</th>
                        			<th rowspan="2">Mã hiệu sản phẩm</th>
                        			<th colspan="4" style="text-align: center;">Vật tư</th>
                        			<th colspan="2" style="text-align: center;">Định mức</th>
                        			<th rowspan="2" width="50"></th>
                        		</tr>
                        		
                        		<tr>
                        			<th>Quy cách (tên gọi)</th>
                        			<th>Mã vật tư</th>
                        			<th>Nhà sản xuất</th>
                        			<th>Đơn vị tính</th>
                        			<th  width="10%">Khối lượng chi (ĐVT/1SP)</th>
                        			<th  width="10%">Định mức (SP/ĐVT)</th>
                        		</tr>
                        	</thead>
                        	<tbody>
                        		<tr style="cursor: pointer;" v-for="(record, i) in materials[semi_item.semi_id]" v-show="record.category_id == 3">
                        			<td>{{i + 1}}</td>
                        			<td>{{selected_item.name | displayValue}}</td>
                        			<td>{{record.name | displayValue}}</td>
                        			<td>{{record.product_id | displayValue}}</td>
                        			<td>{{record.manufacturer | displayValue}}</td>
                        			<td>{{record.unit | displayValue}}</td>
                        			<td>
                        				<input style="width: 100%;" v-model="materials[semi_item.semi_id][i].rate_of_qty"/>
                        			</td>
                        			<td>
                        				<input style="width: 100%;" v-model="materials[semi_item.semi_id][i].rate_of_unit"/>
                        			</td>
                        			<td>
                        				<button type="button" class="btn btn-default" @click="remove_item(semi_item.semi_id, record)">
  											<span class="icon ti-trash" aria-hidden="true"></span>
										</button>
                        			</td>
                        		</tr>
                        	</tbody>
                            </table>
        				</div>
    				</div> 
        		</div>
        		
        		<div class="tab-pane fade in" role="tabpanel" v-bind:id="'phoi' + semi_item.semi_id">
        			<div class="row" style="margin-top:10px;">
        				<div class="col-md-12">
        					<table class="tablesorter table table-hover table-bordered">
                        	<thead>
                        		<tr>
                        			<th rowspan="2">STT</th>
                        			<th rowspan="2">Mã hiệu sản phẩm</th>
                        			<th colspan="4" style="text-align: center;">Vật tư</th>
                        			<th colspan="2" style="text-align: center;">Định mức</th>
                        			<th rowspan="2" width="50"></th>
                        		</tr>
                        		
                        		<tr>
                        			<th>Quy cách (tên gọi)</th>
                        			<th>Mã vật tư</th>
                        			<th>Nhà sản xuất</th>
                        			<th>Đơn vị tính</th>
                        			<th  width="10%">Khối lượng chi (ĐVT/1SP)</th>
                        			<th  width="10%">Định mức (SP/ĐVT)</th>
                        		</tr>
                        	</thead>
                        	<tbody>
                        		<tr style="cursor: pointer;" v-for="(record, i) in materials[semi_item.semi_id]" v-show="record.category_id == 5">
                        			<td>{{i + 1}}</td>
                        			<td>{{selected_item.name | displayValue}}</td>
                        			<td>{{record.name | displayValue}}</td>
                        			<td>{{record.product_id | displayValue}}</td>
                        			<td>{{record.manufacturer | displayValue}}</td>
                        			<td>{{record.unit | displayValue}}</td>
                        			<td>
                        				<input style="width: 100%;" v-model="materials[semi_item.semi_id][i].rate_of_qty"/>
                        			</td>
                        			<td>
                        				<input style="width: 100%;" v-model="materials[semi_item.semi_id][i].rate_of_unit"/>
                        			</td>
                        			<td>
                        				<button type="button" class="btn btn-default" @click="remove_item(semi_item.semi_id, record)">
  											<span class="icon ti-trash" aria-hidden="true"></span>
										</button>
                        			</td>
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

<div class="panel panel-piluku" v-show="false">
	<div class="panel-heading pointer" @click="toggle_panel_summary()">
        <h3 class="panel-title">
            <i class="ion-edit"></i> 
            Tổng hợp Nguyên Vật Liệu
        </h3>
    </div>
	<div class="panel-body form-horizontal" v-show="show_summary">
		<div class="bs-example bs-example-tabs" data-example-id="togglable-tabs" style="margin-top: 20px;"> 
        	<ul class="nav nav-tabs" id="myTabs" role="tablist"> 
            	<li role="presentation" class="active">
            		<a href="#vtcsummary" id="vtc-tab-summary" role="tab" data-toggle="tab" aria-controls="vtcsummary" aria-expanded="true" @click="change_tab(2)">Vật Tư Chính</a>
            	</li> 
            	<li role="presentation" class="">
        			<a href="#vtpsummary" role="tab" id="vtp-tab-summary" data-toggle="tab" aria-controls="ctpsummary" aria-expanded="false" @click="change_tab(3)">Vật Tư Phụ</a>
        		</li>
        		
        		<li role="presentation" class="">
        			<a href="#phoisummary" role="tab" id="phoi-tab-summary" data-toggle="tab" aria-controls="phoisummary" aria-expanded="false" @click="change_tab(5)">Vật Tư Phụ</a>
        		</li>
        	</ul>
        	<div class="tab-content"> 
        		<div class="tab-pane fade active in" role="tabpanel" id="vtcsummary" aria-labelledby="vtc-tab-summary">
        			<div class="row" style="margin-top:10px;">
        				<div class="col-md-12">
        					<table class="tablesorter table table-hover table-bordered">
                        	<thead>
                        		<tr>
                        			<th rowspan="2">STT</th>
                        			<th rowspan="2">Mã hiệu sản phẩm</th>
                        			<th colspan="4" style="text-align: center;">Vật tư</th>
                        			<th colspan="2" style="text-align: center;">Định mức</th>
                        		</tr>
                        		
                        		<tr>
                        			<th>Quy cách (tên gọi)</th>
                        			<th>Mã vật tư</th>
                        			<th>Nhà sản xuất</th>
                        			<th>Đơn vị tính</th>
                        			<th  width="10%">Khối lượng chi (ĐVT/1SP)</th>
                        			<th  width="10%">Định mức (SP/ĐVT)</th>
                        		</tr>
                        	</thead>
                        	<tbody>
                        		<tr style="cursor: pointer;" v-for="(record, i) in semi_all" v-show="record.category_id == 2">
                        			<td>{{i + 1}}</td>
                        			<td>{{selected_item.name | displayValue}}</td>
                        			<td>{{record.name | displayValue}}</td>
                        			<td>{{record.product_id | displayValue}}</td>
                        			<td>{{record.manufacturer | displayValue}}</td>
                        			<td>{{record.unit | displayValue}}</td>
                        			<td>
                        				<input style="width: 100%;" v-model="semi_all[i].rate_of_qty"/>
                        			</td>
                        			<td>
                        				<input style="width: 100%;" v-model="semi_all[i].rate_of_unit"/>
                        			</td>
                        		</tr>
                        	</tbody>
                            </table>
        				</div>
    				</div> 
        		</div>
        		<div class="tab-pane fade in" role="tabpanel" id="vtpsummary" aria-labelledby="vtp-tab-summary">
        			<div class="row" style="margin-top:10px;">
        				<div class="col-md-12">
        					<table class="tablesorter table table-hover table-bordered">
                        	<thead>
                        		<tr>
                        			<th rowspan="2">STT</th>
                        			<th rowspan="2">Mã hiệu sản phẩm</th>
                        			<th colspan="4" style="text-align: center;">Vật tư</th>
                        			<th colspan="2" style="text-align: center;">Định mức</th>
                        		</tr>
                        		
                        		<tr>
                        			<th>Quy cách (tên gọi)</th>
                        			<th>Mã vật tư</th>
                        			<th>Nhà sản xuất</th>
                        			<th>Đơn vị tính</th>
                        			<th  width="10%">Khối lượng chi (ĐVT/1SP)</th>
                        			<th  width="10%">Định mức (SP/ĐVT)</th>
                        		</tr>
                        	</thead>
                        	<tbody>
                        		<tr style="cursor: pointer;" v-for="(record, i) in semi_all" v-show="record.category_id == 3">
                        			<td>{{i + 1}}</td>
                        			<td>{{selected_item.name | displayValue}}</td>
                        			<td>{{record.name | displayValue}}</td>
                        			<td>{{record.product_id | displayValue}}</td>
                        			<td>{{record.manufacturer | displayValue}}</td>
                        			<td>{{record.unit | displayValue}}</td>
                        			<td>
                        				<input style="width: 100%;" v-model="semi_all[i].rate_of_qty"/>
                        			</td>
                        			<td>
                        				<input style="width: 100%;" v-model="semi_all[i].rate_of_unit"/>
                        			</td>
                        		</tr>
                        	</tbody>
                            </table>
        				</div>
    				</div> 
        		</div>
        		
        		<div class="tab-pane fade in" role="tabpanel" id="phoisummary" aria-labelledby="phoi-tab-summary">
        			<div class="row" style="margin-top:10px;">
        				<div class="col-md-12">
        					<table class="tablesorter table table-hover table-bordered">
                        	<thead>
                        		<tr>
                        			<th rowspan="2">STT</th>
                        			<th rowspan="2">Mã hiệu sản phẩm</th>
                        			<th colspan="4" style="text-align: center;">Vật tư</th>
                        			<th colspan="2" style="text-align: center;">Định mức</th>
                        		</tr>
                        		
                        		<tr>
                        			<th>Quy cách (tên gọi)</th>
                        			<th>Mã vật tư</th>
                        			<th>Nhà sản xuất</th>
                        			<th>Đơn vị tính</th>
                        			<th  width="10%">Khối lượng chi (ĐVT/1SP)</th>
                        			<th  width="10%">Định mức (SP/ĐVT)</th>
                        		</tr>
                        	</thead>
                        	<tbody>
                        		<tr style="cursor: pointer;" v-for="(record, i) in semi_all" v-show="record.category_id == 5">
                        			<td>{{i + 1}}</td>
                        			<td>{{selected_item.name | displayValue}}</td>
                        			<td>{{record.name | displayValue}}</td>
                        			<td>{{record.product_id | displayValue}}</td>
                        			<td>{{record.manufacturer | displayValue}}</td>
                        			<td>{{record.unit | displayValue}}</td>
                        			<td>
                        				<input style="width: 100%;" v-model="semi_all[i].rate_of_qty"/>
                        			</td>
                        			<td>
                        				<input style="width: 100%;" v-model="semi_all[i].rate_of_unit"/>
                        			</td>
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