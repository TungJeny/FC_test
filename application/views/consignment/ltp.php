<?php $this->load->view("partial/header");?>
<div  class="panel panel-piluku" style="margin-bottom: -20px;padding-bottom: 10px;">
	<div class="panel-heading">
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#detail" aria-controls="home" role="tab" data-toggle="tab" style="font-weight:bold;" >LÔ THÀNH PHẨM</a></li>
		</ul>
	</div>
	<!-- search -->
	<div class="panel-body form-horizontal">
		<div class="search search-items no-left-border" >
			<div class="row">
				<div class="col-md-2" style="display: table;">
					<input type="search" class="form-control search fitler" name='search_ltp' id='search' placeholder="<?php echo lang('common_search'); ?> theo mã hiệu lô thành phẩm"/>
				</div>

				<div class="col-md-2" style="display: table;">
					<div style="display: table-cell;padding-right: 5px">
						<?php echo lang('common_category'); ?>
					</div>
					<div style="display: table-cell;">
						<select class="form-control fitler" id="categories" style="width:150px" name="type">
							<option value="">Chọn thành phẩm</option>
							<?php foreach($categories as $value){ ?>
								<option  value="<?php echo $value['id']  ?>"><?php echo $value['name']  ?></option>
							<?php } ?>
						</select>
					</div>
				</div>

				<div class="col-md-3">
					<div class="input-group input-daterange" id="reportrange">
						<div style="display: table-cell;padding: 0px 3px;vertical-align: middle; ">
							<?php echo lang('common_from');?> <?php echo lang('common_date1');?> 
						</div>
						<input type="date" class="form-control start_date fitler" name="start_date" id="start_date">
					</div>
				</div>

				<div class="col-md-3">
					<div class="input-group input-daterange" id="reportrange1">
						<div style="display: table-cell;padding: 0px 3px;vertical-align: middle; ">
							<?php echo lang('common_to');?> <?php echo lang('common_date1');?> 
						</div>
						<input type="date" class="form-control end_date fitler" name="end_date" id="end_date">
					</div>	
				</div>
					
				<div><?php echo form_submit('submitf', lang('common_search'), 'class="search-ltp btn btn-primary btn-lg"'); ?></div>
			</div>	
		</div>
	</div>
</div>


<!-- foreach data -->
<div class="panel panel-piluku sorting-ltp">
	<h4 style=" margin-right:10px" >
		<div align="right" style="opacity: 0;" ><a class="ti-close" href="<?php echo base_url('Manage_consignment/ltp')?>"></a></div>
	</h4>
	<table class="table table-hover table-search">
		<thead>
			<tr>
				<th width="30x">STT</th>
				<th class="pointer" title="" dir="ASC" width="140px" order = "package_code">Mã hiệu lô thành phẩm</th>
				<th title="" dir="ASC" width="150px">Mã hiệu lô vật tư</th>
				<th class="pointer" title="" dir="ASC" width="150px" order = "name">Tên phụ tùng</th>
				<th class="pointer" title="" dir="ASC" width="90px" order = "name2">Loại</th>
				<th class="pointer" title="" dir="ASC" width="70px" order = "quantity">Số lượng</th>
				<th class="pointer" title="" dir="ASC" width="90px" order = "created_at">Ngày nhập lô</th>
				<th class="pointer" title="" dir="ASC" width="140px" order = "full_name">Nhân viên nhập kho</th>
				<th class="pointer" title="" dir="ASC" width="80px" order = "note">Ghi chú</th>
				<th width="200px"><center>IN</center></th>
			</tr>
		</thead>
		
		<tbody>
			<?php $i = 1; foreach ($collection as $row): ?>
			<tr >
				<td><?php echo $i; ?></td>
				<td colspan="" style="font-weight: 500;"><?php echo $row['package_code']; ?></td>
				<td><?php echo $row['package_id']; ?></td>
			</tr>
			<?php $j =0; if(!empty($row['items'])) {foreach ($row['items'] as $item): ?>	
				<tr>
					<td></td>			
					<td></td>
					<td></td>				
					<td><a class="ltp-click" href="<?php echo site_url()."home/view_item_modal/".$row['items'][$j]['item_id']; ?>" data-toggle="modal" data-target="#myModal" ><?php echo $item['name'] ? $item['name'] : ''; ?></a></td>
					<td><?php echo $item['name2'] ? $item['name2'] : '' ; ?></td>
					<td><?php echo $item['quantity'] ? $item['quantity'] : ''; ?></td>
					<td><?php echo date('d/m/y', $item['created_at']) ?></td>
					<td><?php echo $item['full_name'] ? $item['full_name'] : ''; ?></td>
					<td><?php echo $item['note']? $item['note'] : '';  ?></td>
					<td>
						<a href=" <?php echo site_url('stock_in/view_print_container/').$item['stock_id'].'/'.$item['item_id']; ?> " class="btn btn-primary uppercase">Lô</a>
						<a href=" <?php echo site_url('stock_in/view_print_barcode/').$item['item_id']; ?> " class="btn btn-primary uppercase">Barcode</a>
						<a href=" <?php echo site_url('stock_in/view_print_qrcode/').$item['stock_id'].'/'.$item['item_id']; ?> " class="btn btn-primary uppercase">QRcode</a>	
					</td>
				</tr>	
			<?php $j++; endforeach; } ?>
		</tbody>
		<?php $i++; endforeach; ?> 

	</table>
	<div class="paginatin"  align="center"><?php echo $pagination; ?></div>
</div>

<?php $this->load->view('partial/footer')?>

<script>
//button search
 $(document).ready(function(){
var order = "package_by_id", order_by = "desc";
  	$('.search-ltp').click(function(){
	   $.ajax({
			    type:'post',
			    url:"<?php echo base_url('Manage_consignment/search_categories') ?>",
			    dataType:'text',
			    data:{
				    start_date: $('#start_date').val(),
				    end_date: $('#end_date').val(),
					categories : $('#categories').val(),
					search: $('#search').val(), 
				    order:order,
				    order_by:order_by
	    		},
   				success: function(result){
     				$('.sorting-ltp').html(result);
    			}
   		});
  	});

$(document).keypress(function(){
  var order = (event.order ? event.order : event.which);
  if (order == '13') {
     // alert('Bạn vừa nhấn phím "enter" trên trang web');
      $.ajax({
			    type:'post',
			    url:"<?php echo base_url('Manage_consignment/search_categories') ?>",
			    dataType:'text',
			    data:{
				    start_date: $('#start_date').val(),
				    end_date: $('#end_date').val(),
					categories : $('#categories').val(),
					search: $('#search').val(), 
				    order:order,
	    		},
   				success: function(result){
     				$('.sorting-ltp').html(result);
    			}
   		});

  }
});
//sent sorting-ltp and pagination
 $('.sorting-ltp').on('click','.pagi a',function(){
	var page = $(this).attr('data-ci-pagination-page');
	href = '<?php echo base_url('Manage_consignment/sorting_ltp')?>/' + page;
	   $.ajax({
    type:'post',
    url:href,
    dataType:'text',
    data:{
    start_date: $('#start_date').val(),
    end_date: $('#end_date').val(),
	categories : $('#categories').val(),
	search: $('#search').val(), 
    order:order,
    order_by:order_by
    },
    success: function(result){
     $('.sorting-ltp').html(result);
    }
   });

return false;

});


// sorting fiel
$('.sorting-ltp').on('click','.pointer',function(){
	order = $(this).attr('order');
	order_by = (order_by ==  "desc") ? "asc" : "desc";

		   $.ajax({
		    type:'post',
		    url:"<?php echo base_url('Manage_consignment/search_categories') ?>",
		    dataType:'text',
		    data:{
		    start_date: $('#start_date').val(),
		    end_date: $('#end_date').val(),
			categories : $('#categories').val(),
			search: $('#search').val(), 
		    order:order,
		    order_by:order_by
	    },
   		 success: function(result){
     	$('.sorting-ltp').html(result);
    	}
   	});

});

});
</script>