<?php if ($number_pr > 0): ?>


	<?php for ($i=0; $i < $number_pr; $i++) : ?>
		<table class="page_table">
			<tbody>
				<tr>
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
	<?php endfor; ?>

<?php endif; ?>
<!--  -->