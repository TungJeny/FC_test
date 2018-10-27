

<?php if ($number_pr > 0): ?>

<div class="page">

<?php for ($i=0; $i < $number_pr; $i++) : ?>
	<table class="page_table">
		<tbody>
			<tr height="60">
				<td class="print_td text-center" width="" colspan="2" >GSKHCL-HD-01/BM-04</td>
				<td class="print_td text-center text-center" colspan="2" width="35
				7"><strong>PHIẾU THEO DÕI LÔ</strong></td>
			</tr>
			<tr>
				<td class="print_td text-center" height="30" colspan="2" >Mã lô vật tư</td>
				<td class="print_td text-center " width="130" > <?php echo $lvt; ?> </td>
				<td class="print_td text-center tbl_logo_qr" rowspan="4" width="210" height="120" >
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
				<td class="print_td text-center" colspan="2" >Heat No.(Nhà sản xuất)</td>
				<td class="print_td text-center " > <?php echo $manufacturer; ?> </td>
			</tr>
			<tr>
				<td class="print_td text-center" height="30" colspan="2" >Mã lô sản phẩm</td>
				<td class="print_td text-center"" width="240px" ><?php echo $ltp; ?></td>
			</tr>
			<tr>
				<td class="print_td text-center" colspan="2" height="30" >Mã lô xử lý nhiệt(FMC)</td>
				<td class="print_td text-center" > </td>
			</tr>
			<tr>
				<td class="print_td text-center" width="114"><strong>STT</strong></td>
				<td class="print_td text-center" width="156"><strong>Tên công đoạn</strong></td>
				<td class="print_td text-center" ><strong>Mã SP: </strong></td>
				<td class="print_td text-center " width="164"> 
					<?php echo $id_product; ?>
				</td>
			</tr>
			<tr>
				<td class="print_td text-center" rowspan="3" width="114">1</td>
				<td class="print_td text-center" rowspan="3" width="156"></td>
				<td class="print_td text-center" height="25" >Người gia công</td>
				<td class="print_td text-center" width="164"></td>
			</tr>
			<tr>
				<td class="print_td text-center" >SL SP/1 thùng</td>
				<td class="print_td text-center" width="164"> <?php echo $amount_unit; ?> </td>
			</tr>
			<tr>
				<td class="print_td" colspan="2" height="20"  width="357">Ca…….ngày……tháng……năm…</td>
			</tr>
			<tr>
				<td class="print_td text-center" rowspan="3" width="114">2</td>
				<td class="print_td text-center" rowspan="3" width="156"></td>
				<td class="print_td text-center" height="25"  >Người gia công</td>
				<td class="print_td text-center" width="164"></td>
			</tr>
			<tr>
				<td class="print_td text-center" >SL SP/1 thùng</td>
				<td class="print_td text-center" width="164"> <?php echo $amount_unit; ?> </td>
			</tr>
			<tr>
				<td class="print_td" colspan="2" height="20"  width="357">Ca……. ngày …… tháng …… năm…</td>
			</tr>
		</tbody>
	</table>
	<!-- end table one item -->
	<?php endfor; ?>

</div>

<?php endif; ?>
<!--  -->