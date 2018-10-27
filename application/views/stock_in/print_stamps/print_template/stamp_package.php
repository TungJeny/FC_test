
<?php $this->load->view("partial/header"); ?>
<div class="manage_buttons hidden-print pd-15" style="padding: 15px;">

		<input type="text" name="" id="number_pr" class="keyup" >

    <button class="btn btn-primary btn-lg hidden-print" id="print_button" onClick="do_print()" > <?php echo lang('common_print'); ?> </button>
    <!-- <a class="btn btn-primary btn-lg hidden-print" href="<?php echo site_url('stock_in/print_stamp/'); ?>" > <?php echo lang('common_back'); ?> </a> -->
 
</div>

<script type="text/javascript">
	$(document).ready(function(){

       	$("#number_pr").keyup(function() {
       		if ($(this).val()) {
       			var number_pr = $(this).val();
       		}else{
       			var number_pr = 1;
       		}
   			href = "<?php echo site_url('stock_in/view_print_barcode_ajax/').$get_item->item_id ; ?>/" + number_pr;
			//console.log(number_pr);
			$.ajax({
			   url:href,
			   type:'GET',
			   success: function(data){
			       $('.page').html(data);
			   }
			});
       	});
    });  
</script>
 <style type="text/css">
	 	table.page_table {
	        width: 9.8cm;
		    /*height: 4.2cm;*/
		    height: 273px;
		    float: left;
		    margin: 6px;
		    border: 1px solid;
		    margin-left: 12px;
		    margin-bottom: 12px;
		    margin-top: 15px;
		}

		.boder_page {
		    
		    margin: 3px;
		}
		img.img_barcode_print {
		    position: relative;
		    left: 15px;
		}
		.tbl_title_check{
			text-align: center;
		}

		td.print_td {
	    	border: 1px solid;
	    }
	    .tbl_company {
		    padding-top: 19px;
		    position: relative;
    		top: -10px;
    		text-align: center;
		}
		.tbl_logo {
		    width: 30%;
			float: left;
			box-sizing: border-box;
			margin: 2px;
			/*border-top: 1px solid;
    		border-left: 1px solid;
    		border-bottom: 1px solid;*/
		}
		.tbl_logo img {
			width: 90%;

		}
		strong.title_company {
		    font-size: 22px;
		}
	    .page {
		    width: 21cm;
		    min-height: 29.7cm;
		    margin: 1cm auto;
		}

		@page {
		  size: A4;
		  margin: 0;
		}

		@media print {
		.page {
		    margin: 0;
		    border: initial;
		    border-radius: initial;
		    width: initial;
		    min-height: initial;
		    box-shadow: initial;
		    background: initial;
		    /*page-break-after: always;*/
		}

	</style>

<?php if ($number_pr == 1): ?>

<div class="page">
	<div class="boder_page">
	<!-- end one table items -->
	<table class="page_table">
		<tbody>
			<tr>
				<td class="print_td" colspan="3" width="623">
					<div class="tbl_logo">
						<?php echo img(array('src' => $this->Appconfig->get_logo_image(), 'height' => 70)); ?>
					</div>

					<div class="tbl_company">
						<strong class="title_company text-center"><?php echo $company; ?></strong>
					</div>
					<div class="clearfix"></div>	
				</td>
			</tr>

			<tr>
				<td class="print_td" width="175px"><strong>Mã số sản phẩm</strong></td>
				<td class="print_td text-center" width="175px"><strong> <?php echo $get_item->product_id; ?> </strong></td>
				<td class="print_td" rowspan="3" width="210">

					<?php 						
						for($k=0;$k<count($items);$k++)
						{
							$item = $items[$k];
							$expire_key = (isset($from_recv) ? $from_recv : 0).'|'.ltrim($item['id'],0);
							$barcode = $item['id'];
							$text = $item['name'];
							
							if(isset($items_expire[$expire_key]) && $items_expire[$expire_key])
							{
								$text.= " (".lang('common_expire_date').' '.$items_expire[$expire_key].')';		
							}
							elseif (isset($from_recv))
							{
								$text.= " (RECV $from_recv)";
							}

							$page_break_after = ($k == count($items) -1) ? 'auto' : 'always';

							echo "<img style='vertical-align:baseline;'src='".site_url('barcode').'?barcode='.rawurlencode($barcode).'&text='.rawurlencode($barcode)."&scale=$scale' class='img_barcode_print'  />";
						}

					 ?>
					<!-- <img src=" <?php echo $items; ?> " width="100px" class="img_barcode_print" alt=""> -->
				</td>
			</tr>
			<tr>
				<td class="print_td" >Tên sản phẩm</td>
				<td class="print_td text-center" width="198"><?php echo $get_item->name; ?></td>
			</tr>
			<tr>
				<td class="print_td" ><strong>Số lượng/túi</strong></td>
				<td class="print_td text-center" width="188"><strong> <?php echo $amount_unit; ?> </strong></td>
			</tr>
			<tr>
				<td class="print_td" >Ngày đóng gói</td>
				<td class="print_td" width="188"></td>
				<td class="print_td tbl_title_check" width="228" >Kiểm tra </td>
			</tr>
			<tr>
				<td class="print_td" >Người đóng gói</td>
				<td class="print_td" width="188"></td>
				<td class="print_td" width="228"></td>
			</tr>
		</tbody>
	</table>
	<!-- end one table items --><!-- end one table items -->
	<div clearfix></div>
</div> <!-- end boder_page -->
</div>
<?php endif; ?>


<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript">
    function do_print() {
		window.print();	
	}
</script>



