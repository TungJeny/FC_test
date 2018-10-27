<?php $this->load->view("partial/header"); ?>
<style> 
/* Template-specific stuff
 *
 * Customizations just for the template; these are not necessary for anything
 * with disabling the responsiveness.
 */

/* Account for fixed navbar */
body {
  min-width: 970px;
}

/* Finesse the page header spacing */
.page-header {
  margin-bottom: 30px;
}
.page-header .lead {
  margin-bottom: 10px;
}


/* Non-responsive overrides
 *
 * Utilitze the following CSS to disable the responsive-ness of the container,
 * grid system, and navbar.
 */

/* Reset the container */
.container {
  width: 970px;
  max-width: none !important;
}

/* Demonstrate the grids */
.container .navbar-header,
.container .navbar-collapse {
  margin-right: 0;
  margin-left: 0;
}

/* Always float the navbar header */
.navbar-header {
  float: left;
}

/* Undo the collapsing navbar */
.navbar-collapse {
  display: block !important;
  height: auto !important;
  padding-bottom: 0;
  overflow: visible !important;
}

.navbar-toggle {
  display: none;
}
.navbar-collapse {
  border-top: 0;
}

.navbar-brand {
  margin-left: -15px;
}

/* Always apply the floated nav */
.navbar-nav {
  float: left;
  margin: 0;
}
.navbar-nav > li {
  float: left;
}
.navbar-nav > li > a {
  padding: 15px;
}

/* Redeclare since we override the float above */
.navbar-nav.navbar-right {
  float: right;
}

/* Undo custom dropdowns */
.navbar .navbar-nav .open .dropdown-menu {
  position: absolute;
  float: left;
  background-color: #fff;
  border: 1px solid #ccc;
  border: 1px solid rgba(0, 0, 0, .15);
  border-width: 0 1px 1px;
  border-radius: 0 0 4px 4px;
  -webkit-box-shadow: 0 6px 12px rgba(0, 0, 0, .175);
          box-shadow: 0 6px 12px rgba(0, 0, 0, .175);
}
.navbar-default .navbar-nav .open .dropdown-menu > li > a {
  color: #333;
}
.navbar .navbar-nav .open .dropdown-menu > li > a:hover,
.navbar .navbar-nav .open .dropdown-menu > li > a:focus,
.navbar .navbar-nav .open .dropdown-menu > .active > a,
.navbar .navbar-nav .open .dropdown-menu > .active > a:hover,
.navbar .navbar-nav .open .dropdown-menu > .active > a:focus {
  color: #fff !important;
  background-color: #428bca !important;
}
.navbar .navbar-nav .open .dropdown-menu > .disabled > a,
.navbar .navbar-nav .open .dropdown-menu > .disabled > a:hover,
.navbar .navbar-nav .open .dropdown-menu > .disabled > a:focus {
  color: #999 !important;
  background-color: transparent !important;
}
</style>
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
    <a class="btn btn-primary btn-lg hidden-print" href="<?php echo site_url('stock_in'); ?>" > <?php echo lang('common_back'); ?> </a>
</div>
<div style="margin-top: 25px;" class="row receipt_<?php echo $this->config->item('receipt_text_size') ? $this->config->item('receipt_text_size') : 'small';?>" id="receipt_wrapper">
	<div class="col-md-12" id="receipt_wrapper_inner">
		<div class="panel panel-piluku">
			<div class="panel-body panel-pad">
				<div class="row">
					<div class="col-md-4 col-sm-4 col-xs-4">
						<ul class="list-unstyled">
							<?php if ($company_logo): ?>
								<li id="company_logo" class="invoice-logo">
									<?php echo img(array('src' => $this->Appfile->get_url_for_file($company_logo))); ?>
								</li>
							<?php endif; ?>
							<li><?php echo $company; ?></li>
							<li><?php echo nl2br($this->Location->get_info_for_key('address',isset($override_location_id) ? $override_location_id : FALSE)); ?></li>
							<li><?php echo $this->Location->get_info_for_key('phone',isset($override_location_id) ? $override_location_id : FALSE); ?></li>
							

						</ul>
					</div>
					<!--  sales-->
			        <div class="col-md-4 col-sm-4 col-xs-4">
			            <ul class="list-unstyled invoice-detail">
							<li id="sale_id"><span>PHIẾU NHẬP KHO</span></li>
							<li id="employee"><span>Ngày nhập kho : </span><?php echo date('d-m-Y', $created_at); ?></li>
			            </ul>
			            
			        </div>
			        <div class="col-md-4 col-sm-4 col-xs-4">
			        	<ul class="list-unstyled invoice-detail">
							<li id="sale_id"><span></span></li>
							<li id="employee"><span></span></li>
							<li id="employee"><span>Số:</span></li>
			            </ul>
			        </div>

				</div>
				<div class = "row">
			        <div class="col-md-4 col-sm-4 col-xs-4">
						<ul class="list-unstyled">
                            <li class ="text-left">Người giao hàng:</li>
                            <li class ="text-left">Đơn vi:</li>
                            <li class ="text-left">Địa chỉ: </li>
                            <li class ="text-left">Số hóa đơn:      </li>
                            <li class ="text-left">Nội dung:        </li>
                            <li class ="text-left">Tài khoản có:     </li>
						</ul>
			        </div>
			        <div class="col-md-4 col-sm-4 col-xs-4">
						<ul class="list-unstyled invoice-detail">
							<li class ="text-left"><span><?php echo isset($employee)? $employee : '' ?></span></li>
							<li class ="text-left"><span><?php echo isset($supplier_name)?  $supplier_name: '';?></span></li>
							<li class ="text-left"><span><?php if(!empty($supplier_address_1))  echo $supplier_address_1; ?></span></li>
							<li class ="text-left"><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Seri:</span></li>
						</ul></div>
			        <div class="col-md-4 col-sm-4 col-xs-4">
						<ul class="list-unstyled invoice-detail">
						 <li class ="text-left"></li>
                            <li class ="text-left"></li>
                            <li class ="text-left"></li>
                            <li class ="text-left">Ngày tạo đơn hàng: <?php echo $po_created_time ?></li>
                            <li class ="text-left">Nhập tại kho: </li>
						</ul>
			        </div>
		        </div>
				<!-- invoice heading-->
			    <div class="invoice-table">
			        <div class="row">
		            	<div class="col-md-1 col-sm-1 col-xs-1">
			                <div class="invoice-head invoice-heading">STT</div>
			            </div>
			            <div class="col-md-4 col-sm-4 col-xs-4">
			                <div class="invoice-head invoice-heading">Tên vật tư</div>
			            </div>
			            <div class="col-md-1 col-sm-1 col-xs-1">
			                <div class="invoice-head invoice-heading">ĐVT</div>
			            </div>
			            <div class="col-md-1 col-sm-1 col-xs-1">
			                <div class="invoice-head">Số lượng</div>
			            </div>
			            <div class="col-md-1 col-sm-1 col-xs-1">
			                <div class="invoice-head">Thực nhập</div>
			            </div>
			            <div class="col-md-2 col-sm-2 col-xs-2">
			                <div class="invoice-head">Đơn giá</div>
			            </div>
			            <div class="col-md-2 col-sm-2 col-xs-2">
			                <div class="invoice-head ">Thành tiền</div>
			            </div>
			        </div>
			    </div>
			    <?php
                 $stt = 1;
                 foreach (array_reverse($cart, true) as $line => $item) :
                 ?>
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
                        <div class="col-md-1 col-sm-1 col-xs-12">
                            <div class="invoice-content invoice-con">
                                <div class="invoice-content-heading"><?php echo $stt++?></div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4 col-xs-4">
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
                        <div class="col-md-1 col-sm-1 col-xs-1">
                            <div class="invoice-content invoice-con">
                            <?php echo $item['unit_name'] ?>
                            </div>
                        </div>
                        <div class="col-md-1 col-sm-1 col-xs-1">
                            <div class="invoice-content"><?php echo to_quantity($quantity_total[$item['item_id']]); ?></div>
                        </div>
                        <div class="col-md-1 col-sm-1 col-xs-1">
                            <div class="invoice-content"><?php echo to_quantity($item['quantity']); ?></div>
                        </div>
                        <div class="col-md-2 col-sm-2 col-xs-2">
                            <div class="invoice-content"><?php echo to_currency($item['price']); ?></div>
                        </div>
                        <div class="col-md-2 col-sm-2 col-xs-2">
                            <div class="invoice-content"><?php echo to_currency($item['price']*$item['quantity']-$item['price']*$item['quantity']*$item['discount']/100); ?></div>
                        </div>
                    </div>
                </div>
			    <?php endforeach; ?>
		    <div class="row form-group">
	            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-2">
	                <div class="invoice-footer-heading">Tổng cộng tiền hàng:</div>
	            </div>
	            <div class="col-md-2 col-sm-2 col-xs-4">
	                <div class="invoice-footer-value"><?php echo to_currency($total); ?></div>
	            </div>
	            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-2">
	                <div class="invoice-footer-heading">Chi phí:</div>
	            </div>
	            <div class="col-md-2 col-sm-2 col-xs-4">
	                <div class="invoice-footer-value">0</div>
	            </div>
	            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-2">
	                <div class="invoice-footer-heading">Thuế giá trị gia tăng:</div>
	            </div>
	            <div class="col-md-2 col-sm-2 col-xs-4">
	                <div class="invoice-footer-value">0</div>
	            </div>
	            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-2">
	                <div class="invoice-footer-heading">TỔNG CỘNG</div>
	            </div>
	            <div class="col-md-2 col-sm-2 col-xs-4">
	                <div class="invoice-footer-value"><?php echo to_currency($total); ?></div>
	            </div>
	        </div>
					
			<div class="row form-group">
				<div class="col-md-12 col-sm-12 text-right">Ngày...Tháng...Năm...</div>
			</div>
			<div class="row form-group">
			        <div class="col-xs-3 col-md-3 col-sm-3 text-center">
			           PHỤ TRÁCH CUNG TIÊU<br>(Ký, họ tên)	
			           
			        </div>
			        <div class="col-xs-3 col-md-3 col-sm-3 text-center">
			           NGƯỜI GIAO HÀNG<br>(Ký, họ tên)
			           
			        </div>
			        <div class="col-xs-3 col-md-3 col-sm-3 text-center">
			           THỦ TRƯỞNG ĐƠN VỊ<br>(Ký, họ tên)
			        </div>
			        <div class="col-xs-3 col-md-3 col-sm-3 text-center">
			          THỦ KHO<br>(Ký, họ tên)
			        </div>
			    </div>
			</div>
			    </div>


		</div>
	</div>
<style>
.invoice-heading {
    font-size: 13px !important;
}
</style>
<?php $this->load->view("partial/footer"); ?>
<script type="text/javascript">

$("#edit_recv").click(function(e)
{
	e.preventDefault();
	bootbox.confirm(<?php echo json_encode(lang('stock_in_edit_confirm')); ?>, function(result)
	{
		if (result)
		{
			$("#stock_in_change_form").submit();
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

<?php if ($this->config->item('print_after_stock_in') && $this->uri->segment(2) == 'complete')
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
 	window.location = '<?php echo site_url('stock_in'); ?>';
	<?php
	}
	?>
}
</script>
