
Vue.use(VeeValidate);
var LOCAL_STORAGE_NAME = 'hot_settings_params_honda_sample';
var LOCAL_STORAGE_DATA = 'hot_settings_data_honda_sample';
var init_data = [[], ['STT','Mã phụ tùng', 'Tên phụ tùng', 'Model áp dụng', 'Đơn giá', 'Số lượng','VĨNH PHÚC',null,null,null, null,'HÀ NAM' ]];
var mergeCells = [
	{row: 1, col: 0, rowspan: 2, colspan: 1, removed: false},
	{row: 1, col: 1, rowspan: 2, colspan: 1, removed: false},
	{row: 1, col: 2, rowspan: 2, colspan: 1, removed: false},
	{row: 1, col: 3, rowspan: 2, colspan: 1, removed: false},
	{row: 1, col: 4, rowspan: 2, colspan: 1, removed: false},
	{row: 1, col: 5, rowspan: 2, colspan: 1, removed: false},
	{row: 1, col: 6, rowspan: 1, colspan: 5, removed: false},
	{row: 1, col: 11, rowspan: 1, colspan: 5, removed: false},
	];

var app = new Vue({
    el : "#content-honda",
    data : function() {
        let that = this;
        return {
            loading: false,
            hot_table: {},
            root : "hot",
            month:JSON.parse(that.get_saved_hot_setting(LOCAL_STORAGE_NAME)).month? JSON.parse(that.get_saved_hot_setting(LOCAL_STORAGE_NAME)).month: '',
            is_show :false,
            hotSettings : {
                data : JSON.parse(that.get_saved_hot_setting(LOCAL_STORAGE_DATA)),
                minRows : 30,
                minCols : 16,
                stretchH : 'all',
                rowHeaders: true,
                colHeaders: true,
                headerTooltips : true,
                width : '100%',
                colWidths : 100,
                dropdownMenu : true,
                contextMenu: ['undo', 'redo', 'alignment', 'row_above', 'row_below', 'remove_row','mergeCells'],
                manualColumnResize: true ,
                manualRowResize: true,
                renderAllRows: true,
                formulas: true,
                fixedRowsTop: 3,
                fixedColumnsLeft: 1,
                mergeCells: JSON.parse(that.get_saved_hot_setting('hot_settings_merge_cell_sample'))?JSON.parse(that.get_saved_hot_setting('hot_settings_merge_cell_sample')): mergeCells,
                cell:	that.get_saved_hot_setting('hot_settings_cell_style_sample')?that.get_saved_hot_setting('hot_settings_cell_style_sample'):[],
                cells: function(row, col){
                    var cell_properties = this;
                    if(row == 1 && col >= 0){
                        cell_properties.className = 'htCenter htMiddle htBold';
                    }
                    if(row == 0 && col >= 0){
                        cell_properties.className = 'htCenter htMiddle htBold';
                    }
                    return cell_properties;
                 },
            },

            selected_item: {},
            resource: SITE_URL + 'items/suggest_autocomplete?category_id=' + CONST.MRP_CATEGORY_ID_HONDA,
        }        
    },
    created: function(){
        let that = this;
	  	 setTimeout(function(){
	  		that.hot_table = that.$refs.myHotTable.table;
	        that.hot_table.render();
	         if (DATA_VIEW_HONDA_SAMPLE.sale_monthly_id) {
	             that.is_show = true;
	             axios.get(SITE_URL + 'orders/load_view/honda/' + DATA_VIEW_HONDA_SAMPLE.sale_monthly_id).then(function(response){
	             	if (DATA_VIEW_HONDA_SAMPLE.is_clone == true ) DATA_VIEW_HONDA_SAMPLE.sale_monthly_id = false;
	                 that.hotSettings.data =JSON.parse(response.data.data.data);
	                 that.hotSettings.mergeCells = JSON.parse(response.data.data.merge_cell);
	                 that.month = response.data.data.month;
	                 that.hotSettings.height = $(window).height() - $("#hot").offset().top;
	             }).catch(function (error) {hide_page_loading();});
	         } else{
	        	 that.hot_table.updateSettings({
	 	      		height: $(window).height() - $("#hot").offset().top,
	 	      		data: init_data,
	 	      		mergeCells: mergeCells,
	 	      	})
	         }
	          $(window).resize(function(){
	          	that.hot_table.updateSettings({
	          		 height: $(window).height() - $("#hot").offset().top,
	              })
	          });
	      }, 0);

        if (this.is_show) {
        	this.generate_table();
        }
    	if (this.month != '') {
    		this.is_show = true;
    	}

    },
    computed:{
        show_button: function(){
            if(typeof this.hotSettings.data[0] != 'undefined') {
                if(typeof this.hotSettings.data[0][1] != null ){
                    return true;
                }
            }
            return false;
        }
    },
    methods: {
        create_table: function(){
            this.loading = true;
            this.is_show = true;
            this.generate_table();
            this.loading = false;
           
        },
        generate_table: function(){
	    	 this.hotSettings.mergeCells.push({row: 0, col:1 , rowspan: 1, colspan: 9});
	         this.hotSettings.data[0][1] = 'Bảng Tổng hợp giao TTN honda ' + (new Date(this.month).getMonth()+1)+'/'+(new Date(this.month)).getFullYear();

        },
        on_selected: function(selected) {
        	this.loading = true;
            this.selected_item = selected;
            var startRow = 3;
            var count = 1;
            for(var i = startRow; i < this.hotSettings.data.length; i++) {
            	 if (this.hotSettings.data[i][1] == selected.label) {
                 	toastr.error('Bạn đã chọn mã sản phẩm này!');
                 	this.loading = false;
                 	return;
                 }
                if (this.hotSettings.data[i][1] != null && this.hotSettings.data[i][1]!='') {
                	startRow = i+1;
                	count++;
                }
            }
            if (typeof this.hotSettings.data[startRow][1] == 'undefined') {
                this.hotSettings.data[0].push(null);
                this.hotSettings.data[0].push(null);
            } 
            this.hotSettings.data[startRow][1] = selected.label;
            this.hotSettings.data[startRow][2] = selected.name;
            this.hotSettings.data[startRow][0] = count;
            this.hot_table.updateSettings({
                data:  this.hotSettings.data,
           });
            this.loading = false;
        },
        get_saved_hot_setting: function(name) {
        	if (localStorage.getItem(name) == '' || localStorage.getItem(name) == null || typeof localStorage.getItem(name) == 'undefined') {
        		if(name === LOCAL_STORAGE_DATA){
        			return JSON.stringify(init_data);
        		} else if(name === 'hot_settings_merge_cell_sample') {
        			return JSON.stringify(mergeCells);
        		}
        		return JSON.stringify([]);
        	}
        	return localStorage.getItem(name);
        },
        set_saved_hot_setting : function (name, value) {
        	localStorage.setItem(name, value);
        },
        remove_saved_hot_setting(name) {
        	localStorage.removeItem(name);
        },
        save_change_hot_table: function() {
        	var json_data_str = JSON.stringify(this.hotSettings.data);
        	var json_merge_cell_str = JSON.stringify(this.hot_table.getPlugin('mergeCells').mergedCellsCollection.mergedCells);
        	var json_cells_styles_str = this.hot_table.getCellsMeta();
        	var json_params = JSON.stringify({
        			month: this.month
        		});
        	this.set_saved_hot_setting(LOCAL_STORAGE_DATA,json_data_str);
        	this.set_saved_hot_setting('hot_settings_merge_cell_sample',json_merge_cell_str);
        	this.set_saved_hot_setting('hot_settings_cell_style_sample',json_cells_styles_str);
        	this.set_saved_hot_setting(LOCAL_STORAGE_NAME,json_params);
        	toastr.success('Bạn đã lưu thành công, làm mới lại trang sẽ không mất dữ liệu!');
        },
        save_hot_data : function() {
			 this.loading = true;
			 let that = this;
			 var date =new Date(this.month);
			 axios.post(SITE_URL + 'orders/save/honda_sample', convert_2_formdata({
				 data: JSON.stringify(this.hotSettings.data),
				 merge_cell: JSON.stringify(that.hot_table.getPlugin('mergeCells').mergedCellsCollection.mergedCells),
				 cell: this.hot_table.getCellsMeta(),
				 month: date.getFullYear() + '-' +( date.getMonth() +1),
                 end_row_body:this.hotSettings.total_row-2,
                 sale_monthly_id: DATA_VIEW_HONDA_SAMPLE.sale_monthly_id
			 })).then(function(response){
			 if (response.data.status == 'success') {
				 show_feedback('success','Dữ liệu đã được cập nhật!', 'success');
				 that.clear_hot_data();
				 location.reload();
				 that.loading = false;
			 } else if(response.data.status == 'error') {
				 show_feedback('error',response.data.msg, 'Lỗi');
				 that.loading = false;
			 } else{
				 show_feedback('error','Bạn hãy giữ nguyên trạng thái/ các qui trình làm và gửi tới người hỗ trợ.', 'Lỗi không xác định.');
				 that.loading = false;
			 }
			                
			 }).catch(function (error) {});
        },
        clear_hot_data: function() {
            this.loading = true;
            this.hotSettings.data = [[]];
            this.hotSettings.cell = [];
            this.hotSettings.mergeCells =[];
            this.hotSettings.cell =[];
            this.month ='',
            this.remove_saved_hot_setting(LOCAL_STORAGE_DATA);
            this.remove_saved_hot_setting('hot_settings_merge_cell_sample');
            this.remove_saved_hot_setting('hot_settings_cell_style_sample');
            this.remove_saved_hot_setting(LOCAL_STORAGE_NAME);
            this.is_show = false;
            this.loading = false;
        }, 

        
        
    },
    components: {
        vuejsDatepicker
    }
});
