<?php $this->load->view("partial/header"); ?>
<?php
if (isset($error_message))
{
	echo '<h1 style="text-align: center;">'.$error_message.'</h1>';
	exit;
}

$company = ($company = $this->Location->get_info_for_key('company', isset($override_location_id) ? $override_location_id : FALSE)) ? $company : $this->config->item('company');
$company_logo = ($company_logo = $this->Location->get_info_for_key('company_logo', isset($override_location_id) ? $override_location_id : FALSE)) ? $company_logo : $this->config->item('company_logo');

?>
<div class="manage_buttons hidden-print pd-15" style="padding: 15px;">
    <button class="btn btn-primary btn-lg hidden-print" id="print_button" onClick="do_print()" > <?php echo lang('common_print'); ?> </button>
    <a class="btn btn-primary btn-lg hidden-print" href="<?php echo site_url('stock_out'); ?>" > <?php echo lang('common_back'); ?> </a>
</div>
<div style="margin-top: 25px;" class="row receipt_<?php echo $this->config->item('receipt_text_size') ? $this->config->item('receipt_text_size') : 'small';?>" id="receipt_wrapper">
	<div class="col-md-12" id="receipt_wrapper_inner">
		<div class="panel panel-piluku">
			<div class="panel-body panel-pad">
				<div class="row">
					<div class="col-md-4 col-sm-4 col-xs-12">
						<ul class="list-unstyled invoice-address">
							<?php if ($company_logo): ?>
								<li id="company_logo" class="invoice-logo">
									<?php echo img(array('src' => $this->Appfile->get_url_for_file($company_logo))); ?>
								</li>
							<?php endif; ?>
							<li id="company_name"  class="company-title"><?php echo $company; ?></li>
							<li id="company_address"><?php echo nl2br($this->Location->get_info_for_key('address',isset($override_location_id) ? $override_location_id : FALSE)); ?></li>
							<li id="company_phone"><?php echo $this->Location->get_info_for_key('phone',isset($override_location_id) ? $override_location_id : FALSE); ?></li>
							<li id="sale_time"><?php echo $transaction_time ?></li>
						</ul>
					</div>
					<!--  sales-->
			        <div class="col-md-4 col-sm-4 col-xs-12">
			            <ul class="list-unstyled invoice-detail">
							<li id="sale_id"><span><?php echo $is_po ? lang('stock_out_purchase_order') : lang('stock_out_id').": "; ?></span><?php echo $stock_out_id; ?></li>
							<li id="employee"><span><?php echo lang('common_employee').": "; ?></span><?php echo $employee; ?></li>
							<li id="receiver_location"><span>Đơn vị nhận :</span><?php echo $receiver_location_name; ?></li>
			            </ul>
			        </div>
			        <?php if(isset($supplier) || isset($transfer_to_location)): ?>
			        <div class="col-md-4 col-sm-4 col-xs-12">
						<ul class="list-unstyled invoice-address invoiceto">
							<?php if(isset($supplier)): ?>
                            <li id="supplier"><?php echo lang('common_supplier').": ".$supplier; ?></li>
                            <?php if(!empty($supplier_address_1)): ?><li><?php echo lang('common_address'); ?> : <?php echo $supplier_address_1. ' '.$supplier_address_2; ?></li><?php endif; ?>
                            <?php if (!empty($supplier_city)) { echo '<li>'.$supplier_city.' '.$supplier_state.', '.$supplier_zip.'</li>';} ?>
                            <?php if (!empty($supplier_country)) { echo '<li>'.$supplier_country.'</li>';} ?>
                            <?php if(!empty($supplier_phone)){ ?><li><?php echo lang('common_phone_number'); ?> : <?php echo $supplier_phone; ?></li><?php } ?>
                            <?php if(!empty($supplier_email)){ ?><li><?php echo lang('common_email'); ?> : <?php echo $supplier_email; ?></li><?php } ?>
							<?php else: ?>
							<?php if(isset($transfer_to_location)): ?>
                            <li id="transfer_from"><span><?php echo lang('stock_out_transfer_from').': ' ?></span><?php echo $transfer_from_location ?></li>
                            <li id="transfer_to"><span><?php echo lang('stock_out_transfer_to').': ' ?></span><?php echo $transfer_to_location ?></li>
							<?php endif; ?>
                            <?php endif; ?>
						</ul>
			        </div>
			        <?php endif; ?>
				</div>
				<!-- invoice heading-->
			    <div class="invoice-table">
			        <div class="row">
			            <div class="col-md-4 col-sm-4 col-xs-12">
			                <div class="invoice-head invoice-heading"><?php echo lang('common_item_name'); ?></div>
			            </div>
			            <div class="col-md-2 col-sm-2 col-xs-3">
			                <div class="invoice-head"><?php echo lang('common_price'); ?></div>
			            </div>
			            <div class="col-md-2 col-sm-2 col-xs-3">
			                <div class="invoice-head"><?php echo lang('common_quantity'); ?></div>
			            </div>
			            <div class="col-md-2 col-sm-2 col-xs-3">
			                <div class="invoice-head pull-right"><?php echo lang('common_total'); ?></div>
			            </div>
			        </div>
			    </div>
			    <?php foreach(array_reverse($cart, true) as $line => $item): ?>
                <?php
                    $item_number_for_receipt = false;
                    if ($this->config->item('show_item_id_on_receipt')) {
                        switch($this->config->item('id_to_show_on_sale_interface')) {
                            case 'number':
                                $item_number_for_receipt = array_key_exists('item_number', $item) ? H($item['item_number']) : '';
                                break;
                            case 'product_id':
                                $item_number_for_receipt = array_key_exists('product_id', $item) ? H($item['product_id']) : '';
                                break;
                            case 'id':
                                $item_number_for_receipt = array_key_exists('item_id', $item) ? H($item['item_id']) : '';
                                break;
                            default:
                                $item_number_for_receipt = array_key_exists('item_number', $item) ? H($item['item_number']) : '';
                                break;
                        }
                    }
                ?>
                <!-- invoice items-->
                <div class="invoice-table-content">
                    <div class="row">
                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <div class="invoice-content invoice-con">
                                <div class="invoice-content-heading"><?php echo $item['name']; ?><?php if ($item_number_for_receipt){ ?> - <?php echo $item_number_for_receipt; ?><?php } ?><?php if ($item['size']){ ?> (<?php echo $item['size']; ?>)<?php } ?></div>
                                <?php if (!$this->config->item('hide_desc_on_receipt') && !$item['description'] == "" ): ?>
                                <div class="invoice-desc"><?php echo H($item['description']); ?></div>
                                <?php endif; ?>
                                <?php if (isset($item['serialnumber']) && $item['serialnumber'] !=""): ?>
                                <div class="invoice-desc"><?php echo $item['serialnumber']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-2 col-xs-3">
                            <div class="invoice-content"><?php echo to_currency($item['price']); ?></div>
                        </div>
                        <div class="col-md-2 col-sm-2 col-xs-3">
                            <div class="invoice-content"><?php echo to_quantity($item['quantity']); ?></div>
                        </div>
                        <div class="col-md-2 col-sm-2 col-xs-3">
                            <div class="invoice-content pull-right"><?php echo to_currency($item['price']*$item['quantity']-$item['price']*$item['quantity']*$item['discount']/100); ?></div>
                        </div>
                    </div>
                </div>
			    <?php endforeach; ?>
				 
					<div class="row">
			            <div class="col-md-12 col-sm-12 col-xs-12">
			                <div class="text-center"><?php echo $comment; ?></div>
			            </div>
			        </div>
			    </div>
				 
			    <div class="invoice-footer panel-pad">
			    	<?php if ($this->config->item('charge_tax_on_recv')): ?>
				        <div class="row">
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-heading"><?php echo lang('common_sub_total'); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value"><?php echo to_currency($subtotal); ?></div>
				            </div>
				        </div>
				        <?php if ($this->config->item('group_all_taxes_on_receipt')): ?>
                        <?php
                            $total_tax = 0;
                            foreach($taxes as $name=>$value) {
                                $total_tax+=$value;
                            }
                        ?>
                        <div class="row">
                            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-4">
                                <div class="invoice-footer-heading"><?php echo lang('common_tax'); ?></div>
                            </div>
                            <div class="col-md-2 col-sm-2 col-xs-4">
                                <div class="invoice-footer-value"><?php echo to_currency($total_tax); ?></div>
                            </div>
                        </div>
						<?php else: ?>
                        <?php foreach($taxes as $name=>$value): ?>
                            <div class="row">
                                <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-4">
                                    <div class="invoice-footer-heading"><?php echo $name; ?></div>
                                </div>
                                <div class="col-md-2 col-sm-2 col-xs-4">
                                    <div class="invoice-footer-value"><?php echo to_currency($value); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
						<?php endif; ?>
				    <?php endif; ?>

				    <div class="row">
			            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-4">
			                <div class="invoice-footer-heading"><?php echo lang('common_total'); ?></div>
			            </div>
			            <div class="col-md-2 col-sm-2 col-xs-4">
			                <div class="invoice-footer-value"><?php echo to_currency($total); ?></div>
			            </div>
			        </div>
					
			        <?php foreach ($payments as $payment_id => $payment): ?>
                    <div class="row">
                        <div class="col-md-offset-6 col-sm-offset-6 col-md-2 col-sm-2 col-xs-4">
                            <div class="invoice-footer-heading"><?php echo (isset($show_payment_times) && $show_payment_times) ?  date(get_date_format().' '.get_time_format(), strtotime($payment['payment_date'])) : lang('common_payment'); ?></div>
                        </div>
                        <div class="col-md-2 col-sm-4 col-xs-4">
                            <div class="invoice-footer-value"><?php $splitpayment=explode(':',$payment['payment_type']); echo $splitpayment[0]; ?></div>
                        </div>

                        <div class="col-md-2 col-sm-2 col-xs-4">
                            <div class="invoice-footer-value invoice-payment"><?php echo to_currency($payment['payment_amount']); ?></div>
                        </div>
                    </div>
					<?php endforeach; ?>
										
			        <?php if (isset($amount_change)): ?>
                    <div class="row">
                        <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-4">
                            <div class="invoice-footer-heading"><?php echo lang('common_amount_tendered'); ?></div>
                        </div>
                        <div class="col-md-2 col-sm-2 col-xs-4">
                            <div class="invoice-footer-value"><?php echo to_currency($amount_tendered); ?></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-4">
                            <div class="invoice-footer-heading"><?php echo lang('common_change_due'); ?></div>
                        </div>
                        <div class="col-md-2 col-sm-2 col-xs-4">
                            <div class="invoice-footer-value"><?php echo $amount_change; ?></div>
                        </div>
                    </div>
					<?php endif; ?>
					
					<?php if (isset($supplier_balance_for_sale) && $supplier_balance_for_sale !== FALSE && !$this->config->item('hide_store_account_balance_on_receipt')): ?>
                    <div class="row">
                        <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-48">
                            <div class="invoice-footer-value"><?php echo lang('stock_out_supplier_account_balance'); ?></div>
                        </div>
                        <div class="col-md-2 col-sm-2 col-xs-4">
                            <div class="invoice-footer-value invoice-payment"><?php echo to_currency($supplier_balance_for_sale); ?></div>
                        </div>
                    </div>
					<?php endif; ?>
			    </div>
				
			    <!-- invoice footer -->
			    <div class="row">
			        <div class="col-md-12 col-sm-12">
			            <?php if (!$this->config->item('hide_barcode_on_sales_and_recv_receipt')): ?>
                        <div class="invoice-policy" id="barcode">
                            <?php echo "<img src='".site_url('barcode')."?barcode=$stock_out_id&text=$stock_out_id' />"; ?>
                        </div>
				        <?php endif ?>
			        </div>
			    </div>
			</div>
		</div>
	</div>
</div>
<?php $this->load->view("partial/footer"); ?>
<script type="text/javascript">

$("#edit_recv").click(function(e)
{
	e.preventDefault();
	bootbox.confirm(<?php echo json_encode(lang('stock_out_edit_confirm')); ?>, function(result)
	{
		if (result)
		{
			$("#stock_out_change_form").submit();
		}
	});
});

$("#email_receipt").click(function()
{
	$.get($(this).attr('href'), function()
	{
		show_feedback('success', <?php echo json_encode(lang('common_receipt_sent')); ?>, <?php echo json_encode(lang('common_success')); ?>);
		
	});
	
	return false;
});

<?php if ($this->config->item('print_after_stock_out') && $this->uri->segment(2) == 'complete')
{
?>
$(window).load(function()
{
	do_print();
});
<?php
}
?>
function do_print()
{
	window.print();
	<?php
	if ($this->config->item('redirect_to_sale_or_recv_screen_after_printing_receipt'))
	{
	?>
 	window.location = '<?php echo site_url('stock_out'); ?>';
	<?php
	}
	?>
}
</script>
