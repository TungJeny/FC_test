<?php $this->load->view("partial/header");?>
<div  class="panel panel-piluku" style="margin-bottom: -20px;padding-bottom: 10px;">
	<div class="panel-heading">
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#detail" aria-controls="home" role="tab" data-toggle="tab" style="font-weight:bold;" >LÔ VẬT TƯ</a></li>
		</ul>
	</div>
	<!-- search -->
	<div class="panel-body form-horizontal">
		<div class="search search-items no-left-border" >
			<div class="row">
				<div class="col-md-2" style="display: table;">
					<input type="search" class="form-control search fitler" name='search_lvt' id='search' placeholder="<?php echo lang('common_search'); ?> theo mã hiệu lô vật tư"/>
				</div>
				
				<div class="col-md-2" style="display: table;display: none;">
					<div style="display: table-cell;padding-right: 5px">
						<?php echo lang('common_category'); ?>
					</div>
					<div style="display: table-cell;">
						<select class="form-control fitler" id="categories" style="width:150px" name="type">
							<option value="">Chọn nguyên vật liệu</option>
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

				<div><?php echo form_submit('submitf', lang('common_search'), 'class="search-lvt btn btn-primary btn-lg"'); ?></div>
			</div>
		</div>
	</div>
</div>


<!-- foreach data -->
<div class="panel panel-piluku sorting-lvt">
	<h4 style=" margin-left: 10px" >
	</h4>
	<table class="table table-hover table-search">
		<thead>
			<tr>
				<th width="30x">STT</th>
				<th class="pointer" title="" dir="ASC" width="250px"  order = "package_code">Mã hiệu lô vật tư</th>
				<th class="pointer" title="" dir="ASC" width="250px"   order = "name">Tên vật tư</th>
				<th class="pointer" title="" dir="ASC" width="80px"  order = "quantity">Số lượng</th>
				<th class="pointer" title="" dir="ASC" width="120px"  order = "company_name">Nhà CC</th>
				<th class="pointer" title="" dir="ASC" width="100px"  order = "created_at">Ngày nhập lô</th>
				<th class="pointer" title="" dir="ASC" width="180px"  order = "full_name">Nhân viên nhập kho</th>
				<th class="pointer" title="" dir="ASC" width="200px"  order = "note">Ghi chú</th>
			</tr>
		</thead>

		<tbody>
			<?php $i = $offset+1; foreach ($temp as $row): ?>
				<tr >
					<td><?php echo $i; ?></td>
					<td style=" font-weight: 500;"><?php echo $row['package_code']; ?></td>
				 	<td><a class="lvt-click" href="<?php echo site_url()."home/view_item_modal/".$row['item_id']; ?>" data-toggle="modal" data-target="#myModal" ><?php echo $row['name']; ?></a></td> 
					<td><?php echo $row['quantity']; ?></td>
					<td><?php echo $row['company_name'] ?></td>
					<td><CENTER><?php echo date('d/m/y', $row['created_at']) ?></CENTER></td>
					<td><?php echo $row['full_name'] ?></td>
					<td><?php echo $row['note'] ?></td>
				</tr>	
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
  	$('.search-lvt').click(function(){
	   $.ajax({
			    type:'post',
			    url:"<?php echo base_url('Manage_consignment/sorting') ?>",
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
     				$('.sorting-lvt').html(result);
    			}
   		});
  	});

$(document).keypress(function(){
  var order = (event.order ? event.order : event.which);
  if (order == '13') {
     // alert('Bạn vừa nhấn phím "enter" trên trang web');
      $.ajax({
			    type:'post',
			    url:"<?php echo base_url('Manage_consignment/sorting') ?>",
			    dataType:'text',
			    data:{
				    start_date: $('#start_date').val(),
				    end_date: $('#end_date').val(),
					categories : $('#categories').val(),
					search: $('#search').val(), 
				    order:order,
	    		},
   				success: function(result){
     				$('.sorting-lvt').html(result);
    			}
   		});

  }
});
//sent sorting-lvt and pagination
 $('.sorting-lvt').on('click','.pagi a',function(){
	var page = $(this).attr('data-ci-pagination-page');
	href = '<?php echo base_url('Manage_consignment/sorting')?>/' + page;
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
     $('.sorting-lvt').html(result);
    }
   });

return false;

});


// sorting fiel
$('.sorting-lvt').on('click','.pointer',function(){
	order = $(this).attr('order');
	order_by = (order_by ==  "desc") ? "asc" : "desc";

		   $.ajax({
		    type:'post',
		    url:"<?php echo base_url('Manage_consignment/sorting') ?>",
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
     	$('.sorting-lvt').html(result);
    	}
   	});

});

});
</script>
  


