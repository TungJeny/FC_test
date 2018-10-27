<?php $this->load->view("partial/header"); ?>
	<style>
		@media print
		{
			.wrapper {
		  	 overflow: visible;
			 font-family: serif !important;
			}
		}

		.qrcode-label
		{
			-webkit-box-sizing: content-box;
			-moz-box-sizing: content-box;
			box-sizing: content-box;
			width: auto;
			height:auto;
			letter-spacing: normal;
			word-wrap: break-word;
			overflow: hidden;
			margin:0 auto;
			text-align:center;
			padding: 10px;
			font-size: 10pt;
			line-height: 1em;
			 font-family: serif !important;
		}
	</style>

<div class="hidden-print" style="text-align: center;margin-top: 20px;">
	<button class="btn btn-primary text-white hidden-print" id="print_button" onclick="window.print();"><?php echo lang('common_print'); ?></button>	
</div>
<!-- <?php echo $website ?>
<?php echo $company ?> -->
<?php 
for($k=0;$k<count($items);$k++)
{
	$item = $items[$k];
	
	$ciqrcode = $item['id'];
	$text = $item['name'];

	
	$page_break_after = ($k == count($items) -1) ? 'auto' : 'always';
	echo "<div class='qrcode-label' style='page-break-after: $page_break_after'>
	<img style='vertical-align:baseline;' class='img_qrcode' src='".site_url('ciqrcode').'?ciqrcode='.rawurlencode($ciqrcode).'&text='.rawurlencode($text).'&website='.rawurlencode($website).'&company='.rawurlencode($company)."'/></div>";

	// echo $qrcode."-".$text."<br>";
	//echo site_url('ciqrcode');
			
}




 ?>

<?php $this->load->view("partial/footer"); ?>