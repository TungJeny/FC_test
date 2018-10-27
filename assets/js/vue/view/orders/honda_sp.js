
Vue.use(VeeValidate);
var LOCAL_STORAGE_NAME = 'hot_settings_params_honda_sp';
var LOCAL_STORAGE_DATA = 'hot_settings_data_honda_sp';
var app = new Vue({
    el : "#content-yamaha",
    data : function() {
        let that = this;
        return {
            loading: false,
            hot_table: {},
            date_array:JSON.parse(that.get_saved_hot_setting(LOCAL_STORAGE_NAME)).date_array?JSON.parse(that.get_saved_hot_setting(LOCAL_STORAGE_NAME)).date_array: [],
            start_date:JSON.parse(that.get_saved_hot_setting(LOCAL_STORAGE_NAME)).start_date?JSON.parse(that.get_saved_hot_setting(LOCAL_STORAGE_NAME)).start_date: '',
            end_date:JSON.parse(that.get_saved_hot_setting(LOCAL_STORAGE_NAME)).end_date?JSON.parse(that.get_saved_hot_setting(LOCAL_STORAGE_NAME)).end_date: '',
            root : "hot",
            hotSettings : {
                data : JSON.parse(that.get_saved_hot_setting(LOCAL_STORAGE_DATA))?JSON.parse(that.get_saved_hot_setting(LOCAL_STORAGE_DATA)): [[]],
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
                fixedRowsTop: 4,
                fixedColumnsLeft: 1,
                total_row: JSON.parse(that.get_saved_hot_setting(LOCAL_STORAGE_NAME)).total_row?JSON.parse(that.get_saved_hot_setting(LOCAL_STORAGE_NAME)).total_row: 0,
    			total_money: JSON.parse(that.get_saved_hot_setting(LOCAL_STORAGE_NAME)).total_money?JSON.parse(that.get_saved_hot_setting(LOCAL_STORAGE_NAME)).total_money: 0,
                cost: JSON.parse(that.get_saved_hot_setting(LOCAL_STORAGE_NAME)).cost?JSON.parse(that.get_saved_hot_setting(LOCAL_STORAGE_NAME)).cost: 0,
	            mergeCells: JSON.parse(that.get_saved_hot_setting('hot_settings_merger_cell'))?JSON.parse(that.get_saved_hot_setting('hot_settings_merger_cell')): [],
                cell:	JSON.parse(that.get_saved_hot_setting('hot_settings_cell_style'))?JSON.parse(that.get_saved_hot_setting('hot_settings_cell_style')):[],
                cells: function(row, col){
                    var cell_properties = this;
                    if(row == 2 && col > 0){
                        cell_properties.type = 'autocomplete';
                        cell_properties.source = function (query, process) {
                            axios.get(SITE_URL + 'items/suggest_autocomplete?category_id='+ CONST.MRP_CATEGORY_ID_HONDA +'&term=' + query).then(function(response){
                                const labels = [];
                                for(var i = 0; i < response.data.length; i++) {
                                    labels.push(response.data[i].label);
                                }
                                process(labels);
                            });
                        }
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
        	that.hot_table.updateSettings({
       		height: $(window).height() - $("#hot").offset().top,
        	})
            $(window).resize(function(){
            	that.hot_table.updateSettings({
            		 height: $(window).height() - $("#hot").offset().top
                })
            });
        }, 2000);
        if (DATA_VIEW_HONDA_SP.sale_monthly_id) {
            show_page_loading();
            axios.get(SITE_URL + 'orders/load_view/honda/' + DATA_VIEW_HONDA_SP.sale_monthly_id).then(function(response){
                setTimeout(function(){
                	if (DATA_VIEW_HONDA_SP.is_clone == true ) DATA_VIEW_HONDA_SP.sale_monthly_id = false;
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
            this.date_array = new Array();
            var extra_row_header = new Array('','Total','Cost', 'Thành Tiền');
            var current = new Date(this.start_date);
            while (current <= new Date(this.end_date)) {
                this.date_array.push(new Date (current));
                current = this.add_days(current,1);
            }
            var row = 0;
            
            this.hotSettings.cell = [];
            this.hotSettings.cell.push({row:row, col:1,className:'htCenter htMiddle htBold'});
            this.hotSettings.mergeCells.push({row: row, col:1 , rowspan: 1, colspan: 9});
            this.hotSettings.data[row++][1] = 'KẾ HOẠCH GIAO HÀNG SP T' +(new Date(this.end_date).getMonth()+1);
            row++
            this.hotSettings.data[row++][0] = 'Ngày tháng';
            this.hotSettings.data[row++][0]= '';
            for(row = 4; row < this.date_array.length+4; row++) {
                if (typeof this.hotSettings.data[row]=='undefined') {
                    this.hotSettings.data[row] = new Array();
                }
                this.hotSettings.data[row].fill(null);
                this.hotSettings.data[row][0] = this.format_date(this.date_array[row-4]);
                
            }
            for(var i = 0; i < extra_row_header.length; i++) {
                if (typeof this.hotSettings.data[row]=='undefined') {
                    this.hotSettings.data[row] = new Array();
                }
                this.hotSettings.data[row].fill(null);
                this.hotSettings.data[row][0] = extra_row_header[i];
                row++;
            }
            for(var i = row; i < this.hotSettings.data.length; i++) {
                this.hotSettings.data[i].fill(null);
            }
            if (typeof this.hotSettings.data[row]=='undefined') {
                this.hotSettings.data[row] = new Array();
                this.hotSettings.data[row+1] = new Array();
                this.hotSettings.data[row+2] = new Array();
            }
            this.hotSettings.total_row = this.date_array.length+5;
            this.hotSettings.cost =  this.date_array.length+6;
            this.hotSettings.total_money =  this.date_array.length+7;
            this.loading = false;
        },
        add_days: function(date, days) {
              var result = new Date(date);
              result.setDate(result.getDate() + days);
              return result;
        },
        on_selected: function(selected) {
        	this.loading = true;
            this.selected_item = selected;
            var startCol = 1;
            var start_row = 2;
            if (this.hotSettings.data[start_row].indexOf(selected.label) != -1) {
            	toastr.error('Bạn đã chọn mã sản phẩm này!');
            	this.loading = false;
            	return;
            }
            
            for(var i = 0; i < this.hotSettings.data[start_row].length; i++) {
                if (this.hotSettings.data[start_row][i] != null && this.hotSettings.data[start_row][i]!='') {
                	startCol = i+1;
                }
            }
            if (typeof this.hotSettings.data[start_row][startCol] == 'undefined') {
                this.hotSettings.data[0].push(null);
            } 
            this.hotSettings.data[start_row][startCol] = selected.label;
            
            var col1 = this.hot_table.getColHeader(startCol);
            var sum1 = '= SUM(';
            if (this.date_array.length == 0) {
                var current = new Date(this.start_date);
                while (current <= new Date(this.end_date)) {
                    this.date_array.push(new Date (current));
                    current = this.add_days(current,1);
                }
            }
            for (var i = 5; i<this.date_array.length+5; i++) {
                sum1 += col1+''+i+',';
            }
            this.hotSettings.data[this.hotSettings.total_row][startCol] =  sum1.replace(/,+$/,'')+')';
            this.hotSettings.data[this.hotSettings.total_money][startCol] = '= '+col1+''+(this.hotSettings.total_row+1)+'*'+col1+''+(this.hotSettings.cost+1);
            this.hot_table.updateSettings({
                data:  this.hotSettings.data,
           });
            
            this.loading = false;
        },
       
        get_saved_hot_setting: function(name) {
        	if (localStorage.getItem(name) == '' || localStorage.getItem(name) == null || typeof localStorage.getItem(name) == 'undefined') {
        		if(name === LOCAL_STORAGE_DATA){
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
        save_change_hot_table: function() {
        	var json_data_str = JSON.stringify(this.hotSettings.data);
        	var json_merge_cell_str = JSON.stringify(this.hotSettings.mergeCells);
        	var json_cells_styles_str = JSON.stringify(this.hotSettings.cell);
        	var json_params = JSON.stringify({
        		total_row :this.hotSettings.total_row, 
                cost :this.hotSettings.cost, 
                total_money :this.hotSettings.total_money, 
        		start_date: this.start_date,
        		end_date: this.end_date
        		});
        	this.set_saved_hot_setting(LOCAL_STORAGE_DATA,json_data_str);
        	this.set_saved_hot_setting('hot_settings_merger_cell',json_merge_cell_str);
        	this.set_saved_hot_setting('hot_settings_cell_style',json_cells_styles_str);
        	this.set_saved_hot_setting(LOCAL_STORAGE_NAME,json_params);
        	toastr.success('Bạn đã lưu thành công, làm mới lại trang sẽ không mất dữ liệu!');
        },
        save_hot_data : function() {
            this.loading = true;
            let that = this;
            var month = (new Date(this.end_date).getFullYear()) + '-' + String("00" + (new Date(this.end_date).getMonth()+1)).slice(-2);
            axios.post(SITE_URL + 'orders/save/honda_sp', convert_2_formdata({
                data: JSON.stringify(this.hotSettings.data), 
                merge_cell: JSON.stringify(this.hotSettings.mergeCells), 
                cell: JSON.stringify(this.hotSettings.cell), 
                month: month, 
                end_row_body:this.hotSettings.total_row-2,
                sale_monthly_id: DATA_VIEW_HONDA_SP.sale_monthly_id
                })).then(function(response){
                if (response.data.status == 'success') {
                    show_feedback('success','Dữ liệu đã được cập nhật!', 'success');
                    that.clear_hot_data();
                    that.loading = false;
                } else if(response.data.status == 'error') {
                    show_feedback('error',response.data.msg, 'Lỗi');
                    that.loading = false;
                } else{
                    show_feedback('error','Bạn hãy giũ nguyên trạng thái/ các qui trình làm và gửi tới người hỗ trợ.', 'Lỗi không xác định.');
                    that.loading = false;
                }
                that.loading = false;
            }).catch(function (error) {});
        },
        clear_hot_data: function() {
            this.loading = true;
            this.hotSettings.data = [[]];
            this.hotSettings.cell = [];
            this.hotSettings.mergeCells =[];
            this.hotSettings.total_row =2;
            this.hotSettings.cost =0;
            this.hotSettings.total_money =0;
            this.start_date ='',
            this.end_date ='',
            this.remove_saved_hot_setting(LOCAL_STORAGE_DATA);
            this.remove_saved_hot_setting('hot_settings_merger_cell');
            this.remove_saved_hot_setting('hot_settings_cell_style');
            this.remove_saved_hot_setting(LOCAL_STORAGE_NAME);
            this.loading = false;
        }, 
        format_date: function(date){
        	var mm = date.getMonth() + 1;
        	var dd = date.getDate();
        	var yy = date.getFullYear();
        	return dd + '/' + mm + '/' + yy;
        }
        
        
    },
    components: {
        vuejsDatepicker
    }
});