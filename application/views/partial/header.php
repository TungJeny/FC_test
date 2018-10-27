<!DOCTYPE html>

<html class="<?php echo $this->config->item('language');?>">
<head>
<meta charset="UTF-8" />
<title>
<?php
if (isset($vueObjects['item']->{"type_unit_formula"})) {
    $vueObjects['item']->{"type_unit_formula"} = json_decode($vueObjects['item']->{"type_unit_formula"});
}

$escapers = [
    "\\",
    "/",
    "\"",
    "\n",
    "\r",
    "\t",
    "\x08",
    "\x0c"
];

$replacements = [
    "\\\\",
    "\\/",
    "\\\"",
    "\\n",
    "\\r",
    "\\t",
    "\\f",
    "\\b"
];
$this->load->helper('demo');
$company = ($company = $this->Location->get_info_for_key('company')) ? $company : $this->config->item('company');
echo ! is_on_demo_host() ? $company . ' -- ' . lang('common_powered_by') . ' LifeTek' : 'Demo - LifeTek | Easy to use Online POS Software'?></title>
<link rel="icon" href="<?php echo base_url();?>favicon.ico"
	type="image/x-icon" />
<meta name="viewport"
	content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
<!--320-->
<base href="<?php echo base_url();?>" />
	<?php $controller = $this->uri->segment(1);?>
	<link rel="icon" href="<?php echo base_url();?>favicon.ico"
	type="image/x-icon" />
<script type="text/javascript">
		var SITE_URL= "<?php echo site_url(); ?>";
		var BASE_URL= "<?php echo base_url(); ?>";
		var ENABLE_SOUNDS = <?php echo $this->config->item('enable_sounds') ? 'true' : 'false'; ?>;
		var JS_DATE_FORMAT = <?php echo json_encode(get_js_date_format()); ?>;
		var JS_TIME_FORMAT = <?php echo json_encode(get_js_time_format()); ?>;
		var LOCALE =  <?php echo json_encode(get_js_locale()); ?>;
		var MONEY_NUM_DECIMALS = <?php echo $this->config->item('number_of_decimals') ? (int)$this->config->item('number_of_decimals') : 2; ?>;
		var IS_MOBILE = <?php echo $this->agent->is_mobile() ? 'true' : 'false'; ?>;
		var ENABLE_QUICK_EDIT = <?php echo $this->config->item('enable_quick_edit') ? 'true' : 'false'; ?>;
		var VUE_OBJECT = '<?php echo !empty($vueObjects) ? $result = str_replace($escapers, $replacements, json_encode($vueObjects, 1)) : '{}';?>';
		var CONST = JSON.parse('<?php echo !empty($const) ? json_encode($const, 1) : '{}';?>');
	</script>
	<?php
$this->load->helper('assets');
foreach (get_css_files() as $css_file) {
    ?>
		<link rel="stylesheet" type="text/css"
	href="<?php echo base_url().$css_file['path'].'?'.ASSET_TIMESTAMP;?>" />
	<?php } ?>
	
	<?php if(is_file(FCPATH . 'assets/css/modules/' . $controller .'.css')) {?>
	<link rel="stylesheet" type="text/css"
	href="<?php echo base_url() . 'assets/css/modules/' . $controller .'.css'.'?'.ASSET_TIMESTAMP;?>" />
	<?php }?>
	<?php foreach(get_js_files() as $js_file) { ?>
		<script
	src="<?php echo base_url().$js_file['path'].'?'.ASSET_TIMESTAMP;?>"
	type="text/javascript" charset="UTF-8"></script>
	<?php } ?>
	<script type="text/javascript">
		var SCREEN_WIDTH = $(window).width();
		var SCREEN_HEIGHT = $(window).height();
		COMMON_SUCCESS = <?php echo json_encode(lang('common_success')); ?>;
		COMMON_ERROR = <?php echo json_encode(lang('common_error')); ?>;
		
		bootbox.addLocale('ar', {
		    OK : 'حسنا',
		    CANCEL : 'إلغاء',
		    CONFIRM : 'تأكيد'			
		});
		
		bootbox.addLocale('km', {
		    OK :'យល់ព្រម',
		    CANCEL : 'បោះបង់',
		    CONFIRM : 'បញ្ជាក់ការ'			
		});
		bootbox.setLocale(LOCALE);
		$.ajaxSetup ({
			cache: false,
			headers: { "cache-control": "no-cache" }
		});
		
		$(document).on('show.bs.modal','.bootbox.modal', function (e) 
		{
			var isShown = ($(".bootbox.modal").data('bs.modal') || {}).isShown;
			//If we have a dialog already don't open another one
			if (isShown)
			{
				//Cleanup the dialog(s) that was added to dom
				$('.bootbox.modal:not(:first)').remove();
				
				//Prevent double modal from showing up
				return e.preventDefault();
			}
		});
		
		
		toastr.options = {
		  "closeButton": true,
		  "debug": false,
		  "newestOnTop": false,
		  "progressBar": false,
		  "positionClass": "toast-top-right",
		  "preventDuplicates": false,
		  "onclick": null,
		  "showDuration": "300",
		  "hideDuration": "1000",
		  "timeOut": "5000",
		  "extendedTimeOut": "1000",
		  "showEasing": "swing",
		  "hideEasing": "linear",
		  "showMethod": "fadeIn",
		  "hideMethod": "fadeOut"
		}
		
    $.fn.editableform.buttons = 
      '<button tabindex="-1" type="submit" class="btn btn-primary btn-sm editable-submit">'+
        '<i class="icon ti-check"></i>'+
      '</button>'+
      '<button tabindex="-1" type="button" class="btn btn-default btn-sm editable-cancel">'+
        '<i class="icon ti-close"></i>'+
      '</button>';
	  
 	  $.fn.editable.defaults.emptytext = <?php echo json_encode(lang('common_empty')); ?>;
		
		$(document).ready(function()
		{
			$(".wrapper.mini-bar .left-bar").hover(
			   function() {
			     $(this).parent().removeClass('mini-bar');
			   }, function() {
			     $(this).parent().addClass('mini-bar');
			   }
			 );

		    $('.menu-bar').click(function(e){                  
		    	e.preventDefault();
		        $(".wrapper").toggleClass('mini-bar');        
		    }); 
    					
			//Ajax submit current location
			$(".set_employee_current_location_id").on('click',function(e)
			{
				e.preventDefault();

				var location_id = $(this).data('location-id');
				$.ajax({
				    type: 'POST',
				    url: '<?php echo site_url('home/set_employee_current_location_id'); ?>',
				    data: { 
				        'employee_current_location_id': location_id, 
				    },
				    success: function(){
				    	window.location.reload(true);	
				    }
				});
				
			});
			
			$(".set_employee_language").on('click',function(e)
			{
				e.preventDefault();

				var language_id = $(this).data('language-id');
				$.ajax({
				    type: 'POST',
				    url: '<?php echo site_url('employees/set_language'); ?>',
				    data: { 
				        'employee_language_id': language_id, 
				    },
				    success: function(){
				    	window.location.reload(true);	
				    }
				});
				
			});
			
			<?php
$this->load->helper('update');
if (! is_on_phppos_host()) {
    // If we are using on browser close (NULL or ""; both false) then we want to keep session alive
    if ($this->db->table_exists('app_config') && ! $this->Appconfig->get_raw_phppos_session_expiration()) {
        ?>
					//Keep session alive by sending a request every 5 minutes
					setInterval(function(){$.get('<?php echo site_url('home/keep_alive'); ?>');}, 300000);
					<?php } ?>
			<?php } ?>

<?php for ($i = 0; $i <= 2; $i++) { ?>
	$('#level-0-<?php echo $i;?>').click(function(){
	    $('#level-0-<?php echo $i;?> .icon.icon-submenu').toggleClass('ti-angle-down');
		$('#level-0-<?php echo $i;?> .icon.icon-submenu').toggleClass('ti-angle-right');


		if ($('#level-0-<?php echo $i;?> .icon.icon-submenu').hasClass('ti-angle-right')) {
		    <?php for ($k = 0; $k <= 3; $k++) { ?>
		    <?php for ($j = 0; $j <= 3; $j++) { ?>
		    $('li[data-parent="level-<?php echo $j; ?>-<?php echo $k;?>"]').hide(300);
		    <?php }?>
		    <?php }?>
		}

		if ($('#level-0-<?php echo $i;?> .icon.icon-submenu').hasClass('ti-angle-down')) {
		    $('li[data-parent="level-0-<?php echo $i;?>"]').show(300);
		}
	});
<?php } ?>	


$('.has-submenu').each(function() {
	$(this).click(function(e) {
		var id = $(this).attr("id").toString().replace("level-1-", "");
		//alert(id);
		$('li[data-parent="level-1-'+id+'"]').toggle(300);
		$('#level-1-'+id+' .icon.icon-submenu').toggleClass('ti-angle-down');
		$('#level-1-'+id+' .icon.icon-submenu').toggleClass('ti-angle-right');
	});
});
$('.sm-has-submenu').each(function() {
	$(this).click(function(e) {
		var id = $(this).attr("id").toString().replace("sm-level-1-", "");
		//alert(id);
		$('li[data-parent="sm-level-1-'+id+'"]').toggle(300);
		$('#sm-level-1-'+id+' .icon.icon-submenu').toggleClass('ti-angle-down');
		$('#sm-level-1-'+id+' .icon.icon-submenu').toggleClass('ti-angle-right');
	});
});

<?php for ($i = 0; $i <= 2; $i++) { ?>
$('#sm-level-0-<?php echo $i;?>').click(function(){
    $('#sm-level-0-<?php echo $i;?> .icon.icon-submenu').toggleClass('ti-angle-down');
	$('#sm-level-0-<?php echo $i;?> .icon.icon-submenu').toggleClass('ti-angle-right');


	if ($('#sm-level-0-<?php echo $i;?> .icon.icon-submenu').hasClass('ti-angle-right')) {
	    <?php for ($k = 0; $k <= 3; $k++) { ?>
	    <?php for ($j = 0; $j <= 3; $j++) { ?>
	    $('li[data-parent="sm-level-<?php echo $j; ?>-<?php echo $k;?>"]').hide(300);
	    <?php }?>
	    <?php }?>
	}

	if ($('#sm-level-0-<?php echo $i;?> .icon.icon-submenu').hasClass('ti-angle-down')) {
	    $('li[data-parent="sm-level-0-<?php echo $i;?>"]').show(300);
	}
});

$('#sm-level-1-<?php echo $i;?>').click(function(){
	$('li[data-parent="sm-level-1-<?php echo $i;?>"]').toggle(300);

	$('#sm-level-1-<?php echo $i;?> .icon.icon-submenu').toggleClass('ti-angle-down');
	$('#sm-level-1-<?php echo $i;?> .icon.icon-submenu').toggleClass('ti-angle-right');
});
<?php } ?>


// jquery submenu items
	
<?php for ($i = 0; $i <= 2; $i++) { ?>
$('#im-level-0-<?php echo $i;?>').click(function(){
    $('#im-level-0-<?php echo $i;?> .icon.icon-submenu').toggleClass('ti-angle-down');
	$('#im-level-0-<?php echo $i;?> .icon.icon-submenu').toggleClass('ti-angle-right');


	if ($('#im-level-0-<?php echo $i;?> .icon.icon-submenu').hasClass('ti-angle-right')) {
	    <?php for ($k = 0; $k <= 3; $k++) { ?>
	    <?php for ($j = 0; $j <= 3; $j++) { ?>
	    $('li[data-parent="im-level-<?php echo $j; ?>-<?php echo $k;?>"]').hide(300);
	    <?php }?>
	    <?php }?>
	}

	if ($('#im-level-0-<?php echo $i;?> .icon.icon-submenu').hasClass('ti-angle-down')) {
	    $('li[data-parent="im-level-0-<?php echo $i;?>"]').show(300);
	}
});

$('#im-level-1-<?php echo $i;?>').click(function(){
	$('li[data-parent="im-level-1-<?php echo $i;?>"]').toggle(300);

	$('#im-level-1-<?php echo $i;?> .icon.icon-submenu').toggleClass('ti-angle-down');
	$('#im-level-1-<?php echo $i;?> .icon.icon-submenu').toggleClass('ti-angle-right');
});
<?php } ?>

// end jquery submenu items 

			$('#toggle-menu').unbind('click').bind('click', function(){
				if ( $('.left-bar').css('display') == 'none' ){
					$('.content').css({'margin-left': '250px'});
					$('.left-bar, .side-bar').show();
					Cookies.set('APP_MENU_SHOW', 1);
				} else {
					$('.content').css({'margin-left': '0px'});
					$('.left-bar, .side-bar').hide();
					Cookies.set('APP_MENU_SHOW', 0);
				}
				correct_fixed_table('detail');
				correct_fixed_table('summary');
			});

			var appMenuShow = Cookies.get('APP_MENU_SHOW');
			if (typeof appMenuShow == 'undefined') {
				appMenuShow = 1;
			}			
			if (appMenuShow == 0) {
				setTimeout(function(){
					$('.content').css({'margin-left': '0px'});
					$('.left-bar, .side-bar').hide();
					correct_fixed_table('detail');
					correct_fixed_table('summary');
				}, 100);
			} else {
				setTimeout(function(){
					$('.content').css({'margin-left': '250px'});
					$('.left-bar, .side-bar').show();
					correct_fixed_table('detail');
					correct_fixed_table('summary');
				}, 100);
			}
		});
	</script>

<!-- hide 3 cột  -->
<script type="text/javascript">
	$(document).ready(function(){ 	
        $("#mainMenu .items").hide();
        $("#mainMenu .stock_in").hide();
        $("#mainMenu .stock_out").hide();
	});
</script>



<?php
$this->load->helper('demo');
$this->load->helper('view');
?>		
</head>
<body>
	<style type="text/css">
#mainMenu li.im-level-1 a {
	padding-left: 61px;
}
</style>
	<div class="modal fade hidden-print" id="myModal" tabindex="-1"
		role="dialog" aria-hidden="true"></div>
	<div class="modal fade hidden-print" id="myModalDisableClose"
		tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false"
		data-backdrop="static"></div>
	<div
		class="wrapper <?php echo $this->uri->segment(1)=='sales' || $this->uri->segment(1)=='receivings'  ? 'mini-bar sales-bar' : ''; ?>">
		<div class="left-bar hidden-print">
			<div class="admin-logo" style="<?php echo isset($location_color) && $location_color ? 'background-color: '.$location_color.' !important': ''; ?>">
				<div class="logo-holder pull-left" style="padding-top: 0px;">
					<?php echo img(array('src' => $this->Appconfig->get_logo_image(), 'height' => 60)); ?>
				</div>
				<!-- logo-holder -->
				<?php
    ?>			
			</div>
			<!-- admin-logo -->
			<ul class="list-unstyled menu-parent" id="mainMenu">
				<li
					<?php echo $this->uri->segment(1)=='home'  ? 'class="active home"' : 'class="home"'; ?>>
					<a tabindex="-1" href="<?php echo site_url('home'); ?>"
					class="waves-effect waves-light"> <i class="icon ti-dashboard"></i>
						<span class="text"><?php echo lang('common_dashboard'); ?></span>
				</a>
				</li>

				<?php
    if (! empty($mrp_menus)) {
        foreach ($mrp_menus as $level => $parentMenu) {
            $parentId = 'level-0-' . $level;
        }
        ?>
    				<li id="<?php echo $parentId;?>" class="has-submenu"><a
					tabindex="-1" style="cursor: pointer;"> <i class="icon ti-package"></i>
						<span class="text"><?php echo $parentMenu['label'];?></span> <i
						class="icon <?php if (empty($parentMenu['is_openning'])) echo "ti-angle-right"; else echo "ti-angle-down";?> icon-submenu"></i>
				</a></li>
    				
    				<?php if (!empty($parentMenu['childs'])) { ?>
    					<?php
            
            foreach ($parentMenu['childs'] as $childLevel => $childOfParent) {
                $childId = 'level-1-' . $childLevel;
                ?>
    						
    			<li id="<?php echo $childId; ?>" data-parent="<?php echo $parentId;?>" class="has-submenu level-1 <?php if (!empty($childOfParent['is_openning']) && empty($childOfParent['childs'])) echo "active";?>" style="clear: both; 
    			<?php if (empty($parentMenu['is_openning'])) echo "display: none;";?>"> 
    			<?php if (!empty($childOfParent['childs'])) { ?>
				<a tabindex="-1" style="cursor: pointer;"> <span class="text"><?php echo $childOfParent['label'];?></span> <i
						class="icon <?php if (empty($childOfParent['is_openning'])) echo "ti-angle-right"; else echo "ti-angle-down";?> icon-submenu"></i>
				</a>
    							<?php } else { ?>
    								<a tabindex="-1" href="<?php echo $childOfParent['href'];?>"
					style="cursor: pointer;"> <span class="text"><?php echo $childOfParent['label'];?></span>
				</a>
    							<?php } ?>
    						</li>

				<!--manage child of child-->
    						<?php if (!empty($childOfParent['childs'])) { ?>
    							<?php foreach ($childOfParent['childs'] as $childOfChild) { ?>
    								<li data-parent="<?php echo $childId;?>" class="level-2 <?php if (!empty($childOfChild['is_openning'])) echo "active";?>" style="clear: both;  <?php if (empty($childOfParent['is_openning']) && empty($childOfChild['is_openning'])) echo "display: none;";?>">
					<a tabindex="-1" href="<?php echo $childOfChild['href'];?>"> <span
						class="text"><?php echo $childOfChild['label']; ?></span>
				</a>
				</li>
    							<?php }?>
    						<?php }?>
    					
    					<?php }?>
    				<?php }?>
				<?php }?>
				
				<?php
    
    if (! empty($order_menus)) {
        foreach ($order_menus as $om_level => $om_parentMenu) {
            $om_parentId = 'sm-level-0-' . $om_level;
        }
        ?>
    				<li id="<?php echo $om_parentId;?>" class="has-submenu"><a
					tabindex="-1" style="cursor: pointer;"> <i class="icon ti-package"></i>
						<span class="text"><?php echo $om_parentMenu['label'];?></span> <i
						class="icon ti-angle-right icon-submenu"></i>
				</a></li>
    				
    				<?php if (!empty($om_parentMenu['childs'])) { ?>
    					<?php
            
            foreach ($om_parentMenu['childs'] as $om_childLevel => $om_childOfParent) {
                $om_childId = 'sm-level-1-' . $om_childLevel;
                ?>
				<li id="<?php echo $om_childId; ?>" data-parent="<?php echo $om_parentId;?>" class="has-submenu sm-level-1 <?php if (!empty($om_childOfParent['is_openning']) && empty($om_childOfParent['childs'])) echo "active";?>" style="clear: both; 
				<?php if (empty($om_parentMenu['is_openning'])) echo "display: none;";?>"> 
    			<?php if (!empty($om_childOfParent['childs'])) { ?>
				<a tabindex="-1" style="cursor: pointer;"> 
					<span class="text"><?php echo $om_childOfParent['label'];?></span> 
					<i class="icon <?php if (empty($om_childOfParent['is_openning'])) echo "ti-angle-right"; else echo "ti-angle-down";?> icon-submenu"></i>
				</a>
				<?php } else { ?>
				<a tabindex="-1" href="<?php echo $om_childOfParent['href'];?>" style="cursor: pointer;"> 
					<span class="text"><?php echo $om_childOfParent['label'];?></span>
				</a>
				<?php } ?>
				</li>
				<?php if (!empty($om_childOfParent['childs'])) { ?>
				<?php foreach ($om_childOfParent['childs'] as $childOfChild) { ?>
				<li data-parent="<?php echo $om_childId;?>" class="sm-level-2 <?php if (!empty($childOfChild['is_openning'])) echo "active";?>" style="clear: both;  <?php if (empty($om_childOfParent['is_openning'])) echo "display: none;";?>">
				<a tabindex="-1" href="<?php echo $childOfChild['href'];?>"> <span class="text"><?php echo $childOfChild['label']; ?></span>
				</a>
				</li>
    							<?php }?>
    						<?php }?>
    					<?php }?>
    				<?php }?>
				<?php }?>


				<!-- add menu Kho -->

				<?php
    
    if (! empty($item_menus)) {
        foreach ($item_menus as $om_level => $om_parentMenu) {
            $om_parentId = 'im-level-0-' . $om_level;
        }
        ?>
    				
    				<li id="<?php echo $om_parentId;?>" class="has-submenu"><a
					tabindex="-1" style="cursor: pointer;"> <i class="icon ti-package"></i>
						<span class="text"><?php echo $om_parentMenu['label'];?></span> <i
						class="icon ti-angle-right icon-submenu"></i>
				</a></li>
    				
    				<?php if (!empty($om_parentMenu['childs'])) { ?>
    					<?php
            
            foreach ($om_parentMenu['childs'] as $om_childLevel => $om_childOfParent) {
                $om_childId = 'im-level-1-' . $om_childLevel;
                ?>
    						<li id="<?php echo $om_childId; ?>" data-parent="<?php echo $om_parentId;?>" class="has-submenu im-level-1 <?php if (!empty($om_childOfParent['is_openning'])) echo "active";?>" style="clear: both; <?php if (empty($om_parentMenu['is_openning'])) echo "display: none;";?>">
					<a tabindex="-1" href="<?php echo $om_childOfParent['href'];?>"
					style="cursor: pointer;"> <span class="text"><?php echo $om_childOfParent['label'];?></span>
				</a>
				</li>
            				
    					<?php }?>
    				<?php }?>
				<?php }?>

				<!-- end add kho -->

				<?php foreach($allowed_modules->result() as $module) { ?>
					<li
					<?php echo isActiveMenu($module->module_id, $this->uri->segment(1))  ? 'class="active ' . $module->module_id . '"' : 'class="' . $module->module_id . '"'; ?>>
					<a tabindex="-1"
					href="<?php echo site_url("$module->module_id");?>"
					class="waves-effect waves-light"> <i
						class="<?php echo $module->icon; ?>"></i> <span class="text"><?php echo lang("module_".$module->module_id) ?></span>
				</a>
				</li> 

				<?php } ?>
				
				<?php
    if ($this->config->item('timeclock')) {
        ?>
					<li
					<?php echo 'timeclocks'==$this->uri->segment(1)  ? 'class="active"' : ''; ?>>
					<a tabindex="-1" href="<?php echo site_url("timeclocks");?>"> <i
						class="icon ti-alarm-clock"></i> <span class="text"><?php echo lang("employees_timeclock") ?></span>
				</a>
				</li>
				<?php
    }
    ?>
				
                <li>
					<?php
    if ($this->config->item('track_cash') && $this->Register->is_register_log_open()) {
        $continue = $this->config->item('timeclock') ? 'timeclocks' : 'logout';
        echo anchor("sales/closeregister?continue=$continue", '<i class="icon ti-power-off"></i><span class="text">' . lang("common_logout") . '</span>', array(
            'tabindex' => '-1'
        ));
    } else {
        
        if ($this->config->item('timeclock') && $this->Employee->is_clocked_in()) {
            echo anchor("timeclocks", '<i class="icon ti-power-off"></i><span class="text">' . lang("common_logout") . '</span>', array(
                'tabindex' => '-1'
            ));
        } else {
            echo anchor("home/logout", '<i class="icon ti-power-off"></i><span class="text">' . lang("common_logout") . '</span>', array(
                'tabindex' => '-1'
            ));
        }
    }
    ?>

                </li>
			</ul>
		</div>
		<!-- left-bar -->
		<div class="content <?php echo $controller; ?>" id="content">
			<div class="overlay hidden-print"></div>
			<div class="top-bar hidden-print">
				<nav class="navbar navbar-default top-bar">
					<div id="toggle-menu">
						<i class="ti-menu"></i>
					</div>
					<div class="menu-bar-mobile" id="open-left">
						<i class="ti-menu"></i>
					</div>
					<div
						class="nav navbar-nav top-elements navbar-breadcrumb hidden-xs">
						 <?php
    $this->load->helper('breadcrumb');
    echo create_breadcrumb();
    ?>
					</div>

					<ul class="nav navbar-nav navbar-right top-elements">															
					<?php if ($this->config->item('show_clock_on_header')) { ?>
					<li>
						
						<?php
        $url = 'javascript:void(0);';
        
        if ($this->config->item('timeclock')) {
            $url = site_url('timeclocks');
        }
        
        ?>
						<a href="<?php echo $url;?>" class="visible-lg">
							<?php echo date(get_time_format()); ?>
							<?php echo date(get_date_format()) ?>
						</a>
						</li>
					<?php } ?>
					<?php if(($this->uri->segment(1)=='sales' && $this->uri->segment(2) != 'receipt' && $this->uri->segment(2) != 'complete') || ($this->uri->segment(1)=='receivings' && $this->uri->segment(2) != 'receipt' && $this->uri->segment(2) != 'complete')) { ?>
						<li class="dropdown"><a tabindex="-1" href="#" class="fullscreen"
							data-toggle="" role="button" aria-expanded="false"><i
								class="ion-arrow-expand  icon-notification"></i></a></li>
						<li class="dropdown"><a tabindex="-1" data-target="#" class=""
							data-toggle="" role="button" aria-expanded="false"><i
								class="ion-bag  icon-notification"></i><span
								class="badge info-number cart cart-number count">0</span></a></li>

					<?php } ?>
					<?php if ($this->Employee->has_module_permission('messages', $user_info->person_id)) {?>
						
						<li class="dropdown"><a href="#" class="dropdown-toggle"
							data-toggle="dropdown" role="button" aria-expanded="false"><i
								class="ion-ios-bell-outline  icon-notification"></i><span
								class="badge info-number count <?php echo $new_message_count > 0 ? 'bell': '';?>"
								id="unread_message_count"><?php echo $new_message_count; ?></span></a>
							<ul
								class="dropdown-menu animated fadeInUp wow message_drop neat_drop"
								data-wow-duration="1500ms" role="menu">
								<?php foreach ($this->Employee->get_messages(4) as $key => $value) { ?>
									<li><a
									href="<?php echo site_url('messages/view/'.$value['message_id']); ?>">
										<span class="avatar_left"><img
											src="<?php echo base_url(); ?>assets/assets/images/avatar-default.jpg"
											alt=""></span> <span class="text_info"><?php echo $value['message']; ?></span>
										<span class="time_info"><?php echo date(get_date_format().' '.get_time_format(), strtotime($value['created_at'])) ?> <i
											class="ion-record <?php echo !$value['message_read'] ? 'online' : ''?>"></i></span>
								</a></li>	
							 	<?php	} ?>
									<li class="bottom-links"><a
									href="<?php echo site_url('messages') ?>" class="last_info"><?php echo lang('common_see_all_notifications');?></a>
								</li>
									<?php if ($this->Employee->has_module_action_permission('messages','send_message',$this->Employee->get_logged_in_employee_info()->person_id)) {  ?>									
									
										<li class="bottom-links"><a
									href="<?php echo site_url('messages/sent_messages'); ?>"
									class="last_info"><?php echo lang('common_view_sent_message') ?></a>
								</li>

								<li class="bottom-links"><a
									href="<?php echo site_url('messages/send_message') ?>"
									class="last_info"><?php echo lang('employees_send_message');?></a>
								</li>
									<?php } ?>
								</ul></li>
						<?php } ?>
					<?php if (count($authenticated_locations) > 1) { ?>
						<li class="dropdown"><a href="#" class="dropdown-toggle"
							data-toggle="dropdown" role="button" aria-expanded="false"> <?php echo $authenticated_locations[$current_logged_in_location_id]; ?> <span
								class="drop-icon"><i class="ion ion-chevron-down"></i></span></a>
							<ul
								class="dropdown-menu animated fadeInUp wow locations-drop locations-drop neat_drop"
								data-wow-duration="1500ms" role="menu">
								<?php foreach ($authenticated_locations as $key => $value) { ?>
									<li><a class="set_employee_current_location_id"
									data-location-id="<?php echo $key; ?>"
									href="<?php echo site_url('home/set_employee_current_location_id/'.$key) ?>"><span class="badge" style="background-color:<?php echo $this->Location->get_info($key)->color; ?>">&nbsp;</span> <?php echo $value; ?> </a></li>
								<?php } ?>
								</ul></li>	

					<?php } ?>
						<?php if (is_on_demo_host() || $this->config->item('show_language_switcher')) { ?>
						<?php
        $languages = array(
            'english' => 'English',
            'indonesia' => 'Indonesia',
            'spanish' => 'Español',
            'french' => 'Fançais',
            'italian' => 'Italiano',
            'german' => 'Deutsch',
            'dutch' => 'Nederlands',
            'portugues' => 'Portugues',
            'arabic' => 'العَرَبِيةُ‎‎',
            'khmer' => 'Khmer',
            'vietnam' => 'Tiếng Việt'
        );
        
        ?>	
						<!-- redirect($_SERVER['HTTP_REFERER']);	 -->
						<li class="dropdown"><a tabindex="-1" href="#"
							class="dropdown-toggle language-dropdown" data-toggle="dropdown"
							role="button" aria-expanded="false"><img class="flag_img"
								src="<?php echo base_url(); ?>assets/assets/images/flags/<?php echo $user_info->language ? $user_info->language : "english";  ?>.png"
								alt=""> <span class="hidden-sm"> <?php echo $user_info->language ? $languages[$user_info->language] : $languages["english"];  ?></span><span
								class="drop-icon"><i class="ion ion-chevron-down"></i></span></a>
							<ul
								class="dropdown-menu animated fadeInUp wow language-drop neat_drop"
								data-wow-duration="1500ms" role="menu">
							<?php
        
        foreach ($languages as $key => $value) {
            if ($user_info->language != $key) {
                ?>
								<li><a tabindex="-1"
									href="<?php echo site_url('employees/set_language/') ?>"
									data-language-id="<?php echo $key; ?>"
									class="set_employee_language"><img class="flag_img"
										src="<?php echo base_url(); ?>assets/assets/images/flags/<?php echo $key; ?>.png"
										alt="flags"><?php echo $value; ?></a></li>
							<?php } } ?>
							</ul></li>	
						<?php } ?>
							
						

						
						<li class="dropdown"><a tabindex="-1" href="#"
							class="dropdown-toggle avatar_width" data-toggle="dropdown"
							role="button" aria-expanded="false"><span class="avatar-holder">

							<?php echo $user_info->image_id ? img(array('src' => app_file_url($user_info->image_id))) : img(array('src' => base_url('assets/assets/images/avatar-default.jpg'))); ?></span>

								<span class="avatar_info visible-md visible-lg"><?php echo $user_info->first_name." ".$user_info->last_name; ?></span></a>
							<ul
								class="dropdown-menu user-dropdown animated fadeInUp wow avatar_drop neat_drop"
								data-wow-duration="1500ms" role="menu">
								<li><a tabindex="-1" id="support_link" target="_blank"
									href="http://support.phppointofsale.com/"><i
										class="ion-help-buoy"></i><span class="text"><?php echo lang('common_support'); ?></span></a>
								</li>
							
								<?php if ($this->Employee->has_module_permission('config', $user_info->person_id)) {?>
								
									<li><?php echo anchor("config",'<i class="ion-android-settings"></i><span class="text">'.lang("common_settings").'</span>', array('tabindex' => '-1')); ?></li>
								<?php } ?>
								
								<?php
        $this->load->helper('update');
        if (is_on_phppos_host() && ! is_on_demo_host() && ! empty($cloud_customer_info)) {
            ?>
								<li><a tabindex="-1" id="update_billing_link" target="_blank"
									href="https://phppointofsale.com/update_billing.php?store_username=<?php echo $cloud_customer_info['username'];?>&username=<?php echo $this->Employee->get_logged_in_employee_info()->username; ?>&password=<?php echo $this->Employee->get_logged_in_employee_info()->password; ?>"><i
										class="ion-card"></i><span class="text"><?php echo lang('common_update_billing_info'); ?></span></a>
								</li>
								
								<?php } ?>
								<li><a tabindex="-1" id="switch_user"
									href="<?php echo site_url('login/switch_user/'.($this->uri->segment(1) == 'sales' ? '0' : '1'));  ?>"
									data-toggle="modal" data-target="#myModalDisableClose"><i
										class="ion-ios-toggle-outline"></i><span class="text"><?php echo lang('common_switch_user'); ?></span></a>
								</li>
								<?php if ($this->Employee->has_module_action_permission('employees','edit_profile',$this->Employee->get_logged_in_employee_info()->person_id)) {  ?>									
								
								<li><a tabindex="-1" title=""
									href="<?php echo site_url('home/edit_profile')?>"
									data-toggle="modal" data-target="#myModal"><i class="ion-edit"></i><span
										class="text"><?php echo lang('common_edit_profile'); ?></span></a>
								</li>
								<?php } ?>
								<?php
        if ($this->config->item('timeclock')) {
            ?>
					         		<li>
									<?php
            echo anchor("timeclocks", '<i class="ion-clock"></i>' . lang("employees_timeclock"), array(
                'tabindex' => '-1'
            ));
            ?>
									</li>
								<?php
        }
        ?>								
								<li>
								<?php
        if ($this->config->item('track_cash') && $this->Register->is_register_log_open()) {
            $continue = $this->config->item('timeclock') ? 'timeclocks' : 'logout';
            echo anchor("sales/closeregister?continue=$continue", '<i class="ion-power"></i><span class="text">' . lang("common_logout") . '</span>', array(
                'class' => 'logout_button',
                'tabindex' => '-1'
            ));
        } else {
            
            if ($this->config->item('timeclock') && $this->Employee->is_clocked_in()) {
                echo anchor("timeclocks", '<i class="ion-power"></i><span class="text">' . lang("common_logout") . '</span>', array(
                    'class' => 'logout_button',
                    'tabindex' => '-1'
                ));
            } else {
                echo anchor("home/logout", '<i class="ion-power"></i><span class="text">' . lang("common_logout") . '</span>', array(
                    'class' => 'logout_button',
                    'tabindex' => '-1'
                ));
            }
        }
        ?>
								</li>
							</ul></li>
					</ul>
				</nav>
			</div>
			<!-- top-bar -->
			<div class="main-content">