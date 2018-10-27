<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/handsontable.css?'.ASSET_TIMESTAMP;?>" />
<script src="<?php echo base_url().'assets/js/handsontable.js?'.ASSET_TIMESTAMP;?>" type="text/javascript" charset="UTF-8"></script>
<script type="text/x-template" id="vue_hot_table">
  <div :id="this.root" class ="hottable-template"></div>
</script>
<script type="module">
import {
    hotInit,
    hotDestroy,
    propFactory,
    propWatchFactory,
    updateHotSettings,
    updateBulkHotSettings
  } from '<?php echo site_url(); ?>assets/js/vue/modules/vue-hands-on-table/helpers.js';

Vue.component('hot-table', {
	    props: propFactory.call(this),
	    watch: propWatchFactory.call(this, updateHotSettings, updateBulkHotSettings),
	    mounted: function() { return hotInit.call(this); },
	    beforeDestroy: function() { return hotDestroy.call(this); },
	    template: '#vue_hot_table'
  });
</script>