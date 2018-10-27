<?php if ($number_pr > 0): ?>
	<?php for ($i=0; $i < $number_pr; $i++) : ?>
		
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
	
		<!-- end one table items -->
	<?php endfor; ?>

<?php endif; ?>
<!--  -->