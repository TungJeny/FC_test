<?php $this->load->view("partial/header"); ?>
<div class="manage_buttons hidden-print pd-15" style="padding: 15px;">
	
		<input type="text" name="" id="number_pr" class="keyup" >

    <button class="btn btn-primary btn-lg hidden-print" id="print_button" onClick="do_print()" > <?php echo lang('common_print'); ?> </button>
 
</div>
<script type="text/javascript">
	$(document).ready(function(){
       //window.print();
       	$("#number_pr").keyup(function(){
       		if ($(this).val()) {
       			var number_pr = $(this).val();
       		}else{
       			var number_pr = 1;
       		}
	   			href = "<?php echo site_url('stock_in/view_print_container_ajax/').$stock_id.'/'.$get_item->item_id ; ?>/" + number_pr;
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
		    position: relative;
		    left: 26px;
		}
		.tbl_title_check{
			text-align: center;
		}

		td.print_td {
	    	border: 1px solid;
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
		
	</style>

<?php if ($number_pr == 1): ?>
<div class="page">
	<table class="page_table">
		<tbody>
			<tr>
				gfgfgf
				<td class="print_td" colspan="3" width="623">
					<div class="container_title">
						<div class="tbl_company">
						<span class="titile_product">  SẢN PHẨM: </span>
						<strong class="titile_product"> <?php echo $get_item->name; ?> </strong>
						</div>
							<div class="tbl_logo">
								<?php echo img(array('src' => $this->Appconfig->get_logo_image(), 'height' => 85)); ?>
							</div>
						<div class="clearfix"></div>
					</div>
						
				</td>
			</tr>

			<tr>
				<td class="" width="35%">
					<p class="number_pcs">Số Lượng .................................... pcs</p>
					<p class="number_pcs">Ngày Nhập ............./............/......................</p>
				</td>
				<td class="print_td text-center" width="65%">
					<h4>Mã Hiệu Lô</h4>
					<p> <?php echo $ltp; ?> </p>
				</td>
			</tr>

		</tbody>
	</table>
	<!-- end one table items -->
</div>


<?php endif; ?>

<?php $this->load->view("partial/footer"); ?>
<script type="text/javascript">

	function do_print() {
		window.print();	
	}

</script>