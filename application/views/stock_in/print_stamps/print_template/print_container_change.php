


<?php $this->load->view("partial/header"); ?>

<div class="manage_buttons hidden-print pd-15" style="padding: 15px;">
	
		<input type="text" name="" id="number_pr" class="keyup" >

    <button class="btn btn-primary btn-lg hidden-print" id="print_button" onClick="do_print()" > <?php echo lang('common_print'); ?> </button>
 
</div>
<script type="text/javascript">
	$(document).ready(function(){
       //window.print();
       	$("#number_pr").keyup(function(){
       		console.log('dsds');
       		if ($(this).val()) {
       			var number_pr = $(this).val();
       		}else{
       			var number_pr = 1;
       		}
	   			href = "<?php echo site_url('stock_in/view_print_container_change_ajax/').$stock_id.'/'.$get_item[0]['item_id'] ; ?>/" + number_pr;
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
		    width: 20cm;
		    border: 1px solid;
		    margin-left: 15px;
		    margin-bottom: 17px;
		    margin-top: 17px;
		    border:1px solid;
		}
		.container_title {
		    width: 100%;
		    height: 110px;
		}
		.boder_page {
		    
		    margin: 3px;
		}
		img.img_barcode_print {
		    /*position: relative;
		    left: 26px;*/

		}
		td.print_td.text-center.qrcode_img {
		    height: 120px;
		}
		img.img_barcode_print {
		    width: 110px;
		}

		.tbl_title_check{
			text-align: center;
		}

		td.print_td {
	    	border: 1px solid;
	    	height:25px;
	    }
	    .tbl_company {
		    padding-top: 36px;
		    float: left;
		    width: 79%;
		    text-align: center;
		}
		.tbl_logo {
			width: 20%;
		    float: right;
		    height: auto;
		    /*height: 118px;*/
		    box-sizing: border-box;
		    /* margin: 2px; */
		}
		.tbl_logo img {
		    width: 75%;
		    margin-top: 12px;
		    float: right;
		    margin-right: 14px;
		}
		.number_pcs{
			margin-top: 5px;
			margin-left: 10px;
		}
		.titile_product {
			font-size: 18px;
		}	
	    .page {
		  width: 21cm;
		  /*min-height: 29.7cm;*/
		  /*padding: 2cm;*/
		  margin: 1cm auto;
		  border: 1px #D3D3D3 solid;
		  border-radius: 5px;
		  background: white;
		  box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
		}
		/*customs css*/
		td.print_td.title_company {
		    text-align: center;
		    font-weight: bold;
		    font-size: 21px;
		}
		td.print_td.img_logo {
		    text-align: center;
		}
		td.print_td.text_center {
		    text-align: center;
		}

		td.print_td.text-center.bar_code {
		    width: 220px;
		}
		td.print_td.alias_titel {
		    width: 165px;
		    height: 34px;
		} 

		/*end*/

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
			}
		}
		
	</style>

<?php if ($number_pr == 1): ?>
	<div class="page">

		<table class="page_table">
			<tbody>
				<tr>
					<td class="print_td img_logo">
						<?php echo img(array('src' => $this->Appconfig->get_logo_image(), 'height' => 65)); ?>
					</td>
					<td class="print_td title_company">
						<?php echo $company; ?>
					</td>
					<td class="print_td text-center bar_code" rowspan="3" colspan="1">
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
					</td>
				</tr>
				<tr>
					<td class="print_td alias_titel"><strong>Mã Sản Phẩm</strong></td>
					<td class="print_td text_center"><?php echo $get_item[0]['product_id']; ?></td>
				</tr>
				<tr>
					<td class="print_td alias_titel">Tên Sản Phẩm</td>
					<td class="print_td text_center"><?php echo $get_item[0]['name']; ?></td>
				</tr>
				<tr>
					<td class="print_td alias_titel"><strong>Số Lượng</strong></td>
					<td class="print_td text_center"><?php echo $amount_unit; ?></td>
					<td class="print_td text-center qrcode_img" rowspan="3" colspan="1">
						<!-- <img src="images/barcode.png" width="100px" class="img_barcode_print" alt=""> -->
						<?php 
							for($k=0;$k<count($items);$k++)
							{
								$item = $items[$k];
								
								$ciqrcode = $item['id'];
								$text = $item['name'];

								
								$page_break_after = ($k == count($items) -1) ? 'auto' : 'always';
								
								echo " <img src='".site_url('ciqrcode').'?ciqrcode='.rawurlencode($ciqrcode).'&text='.rawurlencode($text).'&lvt='.rawurlencode($lvt).'&ltp='.rawurlencode($ltp).'&website='.rawurlencode($website).'&company='.rawurlencode($company)."' width='80px' class='img_qrcode_print' alt=''> ";
							}
						?>
					</td>
				</tr>
				<tr>
					<td class="print_td alias_titel">Mã Lô Sản Phẩm</td>
					<td class="print_td text_center"><?php echo $ltp; ?></td>
				</tr>
				<tr>
					<td class="print_td alias_titel">Ngày Nhập Kho</td>
					<td class="print_td text_center">........./........./.......</td>
				</tr>
			</tbody>
		</table>
	</div>

<?php endif; ?>

<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript">

	function do_print() {
		window.print();	
	}

</script>