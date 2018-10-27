
Vue.use(VeeValidate);
var app = new Vue({
    el : "#content-yamaha",
    data : function() {
        let that = this;
        return {
            loading: false,
            hot_table: {},
            date_array:JSON.parse(that.get_saved_hot_setting('hot_settings_params')).date_array?JSON.parse(that.get_saved_hot_setting('hot_settings_params')).date_array: [],
            start_date:JSON.parse(that.get_saved_hot_setting('hot_settings_params')).start_date?JSON.parse(that.get_saved_hot_setting('hot_settings_params')).start_date: '',
            end_date:JSON.parse(that.get_saved_hot_setting('hot_settings_params')).end_date?JSON.parse(that.get_saved_hot_setting('hot_settings_params')).end_date: '',
            root : "hot",
            hotSettings : {
                data : JSON.parse(that.get_saved_hot_setting('hot_settings_data'))?JSON.parse(that.get_saved_hot_setting('hot_settings_data')): [[]],
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
                total_row: JSON.parse(that.get_saved_hot_setting('hot_settings_params')).total_row?JSON.parse(that.get_saved_hot_setting('hot_settings_params')).total_row: 0,
            	revise_row: JSON.parse(that.get_saved_hot_setting('hot_settings_params')).revise_row?JSON.parse(that.get_saved_hot_setting('hot_settings_params')).revise_row: 0,
            	difference_row:JSON.parse(that.get_saved_hot_setting('hot_settings_params')).difference_row?JSON.parse(that.get_saved_hot_setting('hot_settings_params')).difference_row: 0,
                total_money: JSON.parse(that.get_saved_hot_setting('hot_settings_params')).total_money?JSON.parse(that.get_saved_hot_setting('hot_settings_params')).total_money: 0,
                cost: JSON.parse(that.get_saved_hot_setting('hot_settings_params')).cost?JSON.parse(that.get_saved_hot_setting('hot_settings_params')).cost: 0,
	            mergeCells: JSON.parse(that.get_saved_hot_setting('hot_settings_merge_cell'))?JSON.parse(that.get_saved_hot_setting('hot_settings_merge_cell')): [],
                cell:	JSON.parse(that.get_saved_hot_setting('hot_settings_cell_style'))?JSON.parse(that.get_saved_hot_setting('hot_settings_cell_style')):[],
                cells: function(row, col){
                    var cell_properties = this;
                    if(row == 2 && col > 0){
                        cell_properties.type = 'autocomplete';
                        cell_properties.className = 'htCenter htMiddle htBold';
                        cell_properties.source = function (query, process) {
                            axios.get(SITE_URL + 'items/suggest_autocomplete?category_id='+ CONST.MRP_CATEGORY_ID_YAMAHA +'&term=' + query).then(function(response){
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
                beforeUnmergeCells: function(cell_range) {
                	var cells = {};
                	var index = 0;
					for (var col = cell_range.from.col; col<=cell_range.to.col; col++) {
						for (var row = cell_range.from.row; row<=cell_range.to.row; row++) {
							cells[index] = {row:row, col: col};
							index++;
						}
					}
					for (var key in that.hotSettings.mergeCells) {
						for (var cell in cells) {
							if (cells[cell].row === that.hotSettings.mergeCells[key].row && cells[cell].col === that.hotSettings.mergeCells[key].col) {
							    that.hotSettings.mergeCells.splice(key,1);
								break;
							}
						}
					}
                },
                afterMergeCells: function(cell_range, merged_parent) {
                    for (var key in that.hotSettings.mergeCells) {
                        if (merged_parent.row === that.hotSettings.mergeCells[key].row && merged_parent.col === that.hotSettings.mergeCells[key].col && merged_parent.colspan ===  that.hotSettings.mergeCells[key].colspan && merged_parent.rowspan ===  that.hotSettings.mergeCells[key].rowspan) {
                            return;
                        }
                        if (merged_parent.row === that.hotSettings.mergeCells[key].row && merged_parent.col === that.hotSettings.mergeCells[key].col) {
                            that.hotSettings.mergeCells.splice(key,1);
                        }
                    }
                    that.hotSettings.mergeCells.push(merged_parent);
                }
            },

            selected_item: {},
            resource: SITE_URL + 'items/suggest_autocomplete?category_id=' + CONST.MRP_CATEGORY_ID_YAMAHA,
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
            var next_month_number = new Date(this.end_date).getMonth()+2;
            var next_2month_number = next_month_number+1;
            var next_3month_number = next_2month_number+1;

            var current_year = new Date(this.end_date).getFullYear(),
            	current_year_next_month = new Date(this.end_date).getFullYear(),
            	current_year_next_2month = new Date(this.end_date).getFullYear(),
    			current_year_next_3month  = new Date(this.end_date).getFullYear();
            if (next_month_number > 12) {
            	next_month_number = 1;
            	current_year_next_month += 1;
            	current_year_next_2month += 1;
            	current_year_next_3month += 1;
            	next_2month_number = next_month_number+1;
                next_3month_number = next_2month_number+1;
            }
            if (next_2month_number > 12) {
            	next_2month_number = 1;
            	current_year_next_2month += 1;
            	current_year_next_3month += 1;
            	next_3month_number = next_2month_number+1;
            }
            if (next_3month_number > 12) {
            	next_3month_number = 1;
            	current_year_next_3month += 1;
            }
            var next_month = 'T'+ next_month_number+'/'+ current_year_next_month;
            var next_two_month = 'T'+ next_2month_number+'/'+ current_year_next_2month;
            var next_three_month = 'T'+ next_3month_number+'/'+ current_year_next_3month;
            var extra_row_header = new Array('','Total','Cost', 'Thành Tiền','Rev','Chênh lệch',next_month, next_two_month, next_three_month);
            var current = new Date(this.start_date);
            while (current <= new Date(this.end_date)) {
                this.date_array.push(new Date (current));
                current = this.add_days(current,1);
            }
            var row = 0;
            
            this.hotSettings.cell = [];
            this.hotSettings.cell.push({row:row, col:1,className:'htCenter htMiddle htBold'});
            this.hotSettings.mergeCells.push({row: row, col:1 , rowspan: 1, colspan: 9});
            this.hotSettings.data[row++][1] = 'Hóa đơn đặt hàng yamaha T' +(new Date(this.end_date).getMonth()+1);
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
            this.hotSettings.revise_row =  this.date_array.length+8;
            this.hotSettings.difference_row =  this.date_array.length+9;
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
            var item_merge_cell = this.get_item_merge_cell(this.hotSettings.mergeCells, start_row);
            if (this.hotSettings.data[start_row].indexOf(selected.label) != -1) {
            	toastr.error('Bạn đã chọn mã sản phẩm này!');
            	this.loading = false;
            	return;
            }
            
            for(var i = 0; i < this.hotSettings.data[start_row].length; i++) {
                if (this.hotSettings.data[start_row][i] != null && this.hotSettings.data[start_row][i]!='') {
                    if (this.is_first(this.hotSettings.data[start_row])) {
                        startCol = i+1;
                    } else if (i == item_merge_cell[item_merge_cell.length-1].col){
                        startCol = i + item_merge_cell[item_merge_cell.length-1].colspan;
                    }
                }
            }
            if (typeof this.hotSettings.data[start_row][startCol] == 'undefined') {
                this.hotSettings.data[0].push(null);
                this.hotSettings.data[0].push(null);
                this.hotSettings.data[0].push(null);
                this.hotSettings.data[0].push(null);
                this.hotSettings.data[0].push(null);
            } 
            this.hotSettings.data[start_row++][startCol] = selected.label;
            this.hotSettings.data[start_row][startCol] = 'MP';
            this.hotSettings.data[start_row][startCol+1] = 'SP';
            this.hotSettings.data[start_row][startCol+2] = 'KYP';
            this.hotSettings.data[start_row][startCol+3] = 'xuất khẩu';
            this.hotSettings.data[start_row][startCol+4] = 'Tổng';
            
            var col1 = this.hot_table.getColHeader(startCol);
            var col2 = this.hot_table.getColHeader(startCol+1);
            var col3 = this.hot_table.getColHeader(startCol+2);
            var col4 = this.hot_table.getColHeader(startCol+3);
            var col5 = this.hot_table.getColHeader(startCol+4);
            var sum1 = '= SUM(';
            var sum2 = '= SUM(';
            var sum3 = '= SUM(';
            var sum4 = '= SUM(';
            var sum5 = '= SUM(';
            if (this.date_array.length == 0) {
                var current = new Date(this.start_date);
                while (current <= new Date(this.end_date)) {
                    this.date_array.push(new Date (current));
                    current = this.add_days(current,1);
                }
            }
            for (var i = 5; i<this.date_array.length+5; i++) {
                sum1 += col1+''+i+',';
                sum2 += col2+''+i+',';
                sum3 += col3+''+i+',';
                sum4 += col4+''+i+',';
                sum5 += col5+''+i+',';
            }
            this.hotSettings.data[this.hotSettings.total_row][startCol] =  sum1.replace(/,+$/,'')+')';
            this.hotSettings.data[this.hotSettings.total_row][startCol+1]= sum2.replace(/,+$/,'')+')';
            this.hotSettings.data[this.hotSettings.total_row][startCol+2]=  sum3.replace(/,+$/,'')+')';
            this.hotSettings.data[this.hotSettings.total_row][startCol+3] =sum4.replace(/,+$/,'')+')';
            this.hotSettings.data[this.hotSettings.total_row][startCol+4] =sum5.replace(/,+$/,'')+')';
            this.hotSettings.data[this.hotSettings.difference_row][startCol] = '= '+col1+''+(this.hotSettings.total_row+1)+'-'+col1+''+(this.hotSettings.total_row+4);
            this.hotSettings.data[this.hotSettings.difference_row][startCol+1]= '= '+col2+''+(this.hotSettings.total_row+1)+'-'+col2+''+(this.hotSettings.total_row+4);
            this.hotSettings.data[this.hotSettings.difference_row][startCol+2]=  '= '+col3+''+(this.hotSettings.total_row+1)+'-'+col3+''+(this.hotSettings.total_row+4);
            this.hotSettings.data[this.hotSettings.difference_row][startCol+3] ='= '+col4+''+(this.hotSettings.total_row+1)+'-'+col4+''+(this.hotSettings.total_row+4);
            this.hotSettings.data[this.hotSettings.difference_row][startCol+4] ='= '+col5+''+(this.hotSettings.total_row+1)+'-'+col5+''+(this.hotSettings.total_row+4);
            this.hotSettings.data[this.hotSettings.total_money][startCol] = '= '+col1+''+(this.hotSettings.total_row+1)+'*'+col1+''+(this.hotSettings.cost+1);
            this.hotSettings.data[this.hotSettings.total_money][startCol+1]= '= '+col2+''+(this.hotSettings.total_row+1)+'*'+col2+''+(this.hotSettings.cost+1);
            this.hotSettings.data[this.hotSettings.total_money][startCol+2]=  '= '+col3+''+(this.hotSettings.total_row+1)+'*'+col3+''+(this.hotSettings.cost+1);
            this.hotSettings.data[this.hotSettings.total_money][startCol+3] ='= '+col4+''+(this.hotSettings.total_row+1)+'*'+col4+''+(this.hotSettings.cost+1);
            this.hotSettings.data[this.hotSettings.total_money][startCol+4] ='= '+col5+''+(this.hotSettings.total_row+1)+'*'+col5+''+(this.hotSettings.cost+1);
            this.hotSettings.mergeCells.push({row: --start_row, col:startCol , rowspan: 1, colspan: 5});
            for (var total_col_start_row = start_row+2; total_col_start_row<this.hotSettings.total_row-1; total_col_start_row++) {
                var corrdinate_row = total_col_start_row+1;
                this.hotSettings.data[total_col_start_row][startCol+4] = '= SUM('+col1+''+corrdinate_row+':'+''+col4+''+corrdinate_row+')';
            }
            this.hot_table.updateSettings({
                data:  this.hotSettings.data,
           });
            
            this.loading = false;
        },
        get_item_merge_cell: function (merge_cells, item_row) 
        {
            var item_merge_cell = [];
            for (var hot_mc = 0; hot_mc< merge_cells.length; hot_mc++) {
                if (merge_cells[hot_mc].row == item_row) {
                    item_merge_cell.push(this.hotSettings.mergeCells[hot_mc]);
                }
            }
            return item_merge_cell;
        },
        is_first: function(cols)
        {
            for(var i = 1; i < cols.length; i++) {
                if (cols[i] != null && cols[i] != '') {
                    return false
                }
            }
            
            return true;
        },
        get_saved_hot_setting: function(name) {
        	if (localStorage.getItem(name) == '' || localStorage.getItem(name) == null || typeof localStorage.getItem(name) == 'undefined') {
        		if(name === 'hot_settings_data'){
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
        		revise_row :this.hotSettings.revise_row, 
        		difference_row :this.hotSettings.difference_row, 
                cost :this.hotSettings.cost, 
                total_money :this.hotSettings.total_money, 
        		start_date: this.start_date,
        		end_date: this.end_date
        		});
        	this.set_saved_hot_setting('hot_settings_data',json_data_str);
        	this.set_saved_hot_setting('hot_settings_merge_cell',json_merge_cell_str);
        	this.set_saved_hot_setting('hot_settings_cell_style',json_cells_styles_str);
        	this.set_saved_hot_setting('hot_settings_params',json_params);
        	toastr.success('Bạn đã lưu thành công, làm mới lại trang sẽ không mất dữ liệu!');
        },
        save_hot_data : function() {
            this.loading = true;
            let that = this;
            var month = (new Date(this.end_date).getFullYear()) + '-' + String("00" + (new Date(this.end_date).getMonth()+1)).slice(-2);
            axios.post(SITE_URL + 'orders/save/yamaha', convert_2_formdata({
                data: JSON.stringify(this.hotSettings.data), 
                merge_cell: JSON.stringify(this.hotSettings.mergeCells), 
                cell: JSON.stringify(this.hotSettings.cell), 
                month: month, 
                end_row_body:this.hotSettings.total_row-2,
                sale_monthly_id: DATA_VIEW.sale_monthly_id
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
                
            }).catch(function (error) {});
        },
        clear_hot_data: function() {
            this.loading = true;
            this.hotSettings.data = [[]];
            this.hotSettings.cell = [];
            this.hotSettings.mergeCells =[];
            this.hotSettings.cell =[];
            this.hotSettings.total_row =2;
            this.hotSettings.revise_row =3;
            this.hotSettings.difference_row =4;
            this.hotSettings.cost =0;
            this.hotSettings.total_money =0;
            this.start_date ='',
            this.end_date ='',
            this.remove_saved_hot_setting('hot_settings_data');
            this.remove_saved_hot_setting('hot_settings_merge_cell');
            this.remove_saved_hot_setting('hot_settings_cell_style');
            this.remove_saved_hot_setting('hot_settings_params');
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