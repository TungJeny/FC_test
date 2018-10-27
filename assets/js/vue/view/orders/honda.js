var app = new Vue({
    el: '#content-honda',
    data : function() {
    	let that = this;
        return {
            upload_url: SITE_URL + 'orders/upload/honda',
            message: '',
            end_date:'',
            hot_table: {},
            root : "hot",
            hotSettings : {
                data : [[]],
                rowHeaders: true,
                colHeaders: true,
                minRows : 20,
                minCols : 13,
                width : '100%',
                colWidths : 100,
                renderAllRows: true,
                formulas: true,
                total_qty_row_num: '',
                fixedRowsTop: 4,
                fixedColumnsLeft: 1,
                mergeCells: [],
                cells: function(row, col){
                    var cell_properties = this;
                    if(row == 1 && col > 0){
                        cell_properties.className = 'htCenter htMiddle htBold';
                        cell_properties.readOnly = true;
                    }
                    if((row == 2 || row == 3) && col > 0){
                        cell_properties.className = 'htCenter htMiddle';
                        cell_properties.readOnly = true;
                    }
                    return cell_properties;
                 }
            }
        }
    },
    created: function(){
        let that = this;
        setTimeout(function(){
        	that.hot_table = that.$refs.myHotTable.table;
            that.hot_table.render();
        	that.hot_table.updateSettings({
       		   height: $(window).height() - $("#hot").offset().top,
        	})
            $(window).resize(function(){
            	that.hot_table.updateSettings({
            		 height: $(window).height() - $("#hot").offset().top
                })
            });
            
        }, 2000);
        if (DATA_VIEW_HONDA.sale_monthly_id) {
            show_page_loading();
            axios.get(SITE_URL + 'orders/load_view/honda/' + DATA_VIEW_HONDA.sale_monthly_id).then(function(response){
                setTimeout(function(){
                	if (DATA_VIEW_HONDA.is_clone == true ) DATA_VIEW_HONDA.sale_monthly_id = false;
                    that.hotSettings.data =JSON.parse(response.data.data.data);
                    that.hotSettings.mergeCells =JSON.parse(response.data.data.merge_cell);
                    that.hotSettings.total_qty_row_num = JSON.parse(response.data.data.end_row_body) +1;
                    that.end_date =  JSON.parse(response.data.data.data)[JSON.parse(response.data.data.end_row_body)][0];
                    var parts = that.end_date.split('/');
                    that.end_date = parts[2]+'/'+parts[1]+'/'+parts[0];
                    hide_page_loading();
                }, 2000);
            }).catch(function (error) {hide_page_loading();});
        }
    },
    
    methods: {
        upload_completed: function(response) {
                this.hotSettings.mergeCells = response.mergeCells;
                this.hotSettings.data = response.data;
                this.hotSettings.total_qty_row_num = response.total_qty_row_num;
                this.end_date = response.end_date;
        },
        get_saved_hot_setting: function(name) {
        	if (localStorage.getItem(name) == '' || localStorage.getItem(name) == null || typeof localStorage.getItem(name) == 'undefined') {
        		if(name === 'hot_settings_data_honda'){
        			return JSON.stringify([[]]);
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
        after_change_hot_table: function() {
        	var json_data_str = JSON.stringify(this.hotSettings.data);
        	var json_merge_cell_str = JSON.stringify(this.hotSettings.mergeCells);
        	var json_cells_styles_str = JSON.stringify(this.hotSettings.cell);
        	this.set_saved_hot_setting('hot_settings_data_honda',json_data_str);
        	this.set_saved_hot_setting('hot_settings_merger_cell_honda',json_merge_cell_str);
        	this.set_saved_hot_setting('hot_settings_cell_style_honda',json_cells_styles_str);
        },
        save_hot_data : function() {
            this.loading = true;
            let that = this;
            var month = (new Date(this.end_date).getFullYear()) + '-' + String("00" + (new Date(this.end_date).getMonth()+1)).slice(-2);
            axios.post(SITE_URL + 'orders/save/honda', convert_2_formdata({
                data: JSON.stringify(this.hotSettings.data), 
                merge_cell: JSON.stringify(this.hotSettings.mergeCells),
                month: month, 
                end_row_body:this.hotSettings.total_qty_row_num-1,
                sale_monthly_id: DATA_VIEW_HONDA.sale_monthly_id,
                order_for: DATA_VIEW_HONDA.order_for
                })).then(function(response){
                if (response.data.status == 'success') {
                    show_feedback('success','Dữ liệu đã được cập nhật!', 'success');
                } else {
                    show_feedback('error',response.data.msg, 'Lỗi');
                    that.loading = false;
                }
                
            }).catch(function (error) {});
        },
    }
});