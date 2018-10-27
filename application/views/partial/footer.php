
			<div id="footers" class="col-md-12 hidden-print text-center">
				<?php echo lang('common_please_visit_my'); ?> 
					<a tabindex="-1" href="http://phppointofsale.com" target="_blank"><?php echo lang('common_website'); ?></a> <?php echo lang('common_learn_about_project'); ?>.
					<span class="text-info"><?php echo lang('common_you_are_using_phppos')?> <span class="badge bg-primary"> <?php echo APPLICATION_VERSION; ?></span></span> <?php echo lang('common_built_on'). ' '.BUILT_ON_DATE;?>
			</div>
		</div>
		<!---content -->
	</div>
<?php 
$vueModules = array_keys($vue_modules);
$module = $this->uri->segment(1);
$action = empty($this->uri->segment(2)) ? 'manage' : $this->uri->segment(2);
if ($module == 'orders') {
    $action = empty($this->uri->segment(3)) ? 'other' : $this->uri->segment(3);
    if ($this->uri->segment(2) == 'manage') {
        $action = 'manage';
    }
}

if (in_array(strtolower($module), $vueModules) && !empty($module)) {
?>
<script src="<?php echo base_url().'assets/js/vue/vue.js?'.ASSET_TIMESTAMP;?>" type="text/javascript" charset="UTF-8"></script>
<script src="<?php echo base_url().'assets/js/vue/modules/axios.js?'.ASSET_TIMESTAMP;?>" type="text/javascript" charset="UTF-8"></script>
<script src="<?php echo base_url().'assets/js/vue/modules/vee-validate.js?'.ASSET_TIMESTAMP;?>" type="text/javascript" charset="UTF-8"></script>
<script src="<?php echo base_url().'assets/js/vue/modules/vuejs-datepicker.js?'.ASSET_TIMESTAMP;?>"></script>
<script src="<?php echo base_url().'assets/js/vue/filters.js?'.ASSET_TIMESTAMP;?>" type="text/javascript" charset="UTF-8"></script>
<?php 
if (!empty($vue_modules)) {
    foreach ($vue_modules as $moduleId => $actions) {
        if ($moduleId == $module) {
            foreach ($actions as $components) {
                foreach ($components as $component) {
                    $this->load->view("partial/vue_components/" . $component);
                }
            }
        }
    }
} ?>
<script src="<?php echo base_url().'assets/js/vue/view/'. $module .'/'. $action .'.js?'.ASSET_TIMESTAMP;?>" type="module" charset="UTF-8"></script>
<?php } ?>
</body>
<div id="page_loader" class="hide">
    <div style="height: 100px; position: relative; top: 30%">
        <div class="mask">
            <div class="spinner">
                <div class="rect1"></div>
                <div class="rect2"></div>
                <div class="rect3"></div>
            </div>
        </div>
    </div>
</div>
</html>