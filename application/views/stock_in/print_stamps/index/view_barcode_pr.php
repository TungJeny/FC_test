<?php if ($number_pr > 0): ?>
	
<div class="page">

	<div class="boder_page">
	<!-- end one table items -->
	<?php for ($i=0; $i < $number_pr; $i++) : ?>
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
				<td class="print_td text-center" width="188"><strong> <?php echo $amount_unit; ?> </strong></td>
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
	<?php endfor; ?>
	<!-- end one table items --><!-- end one table items -->
	<div clearfix></div>
</div> <!-- end boder_page -->
</div>

<?php endif; ?> 
<!--  -->


	