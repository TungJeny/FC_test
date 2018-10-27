var app = new Vue({
    el: '#content',
    data : function() {
        return {
            sales: [],
            pagination: {
                total_row: 0,
                total_page: 0,
                per_page: 0,
                current_page: 0,
            },
            page: 1,
            options: {
            	view_title: '',
                prefix: 'LISTVIEW_ORDER_SALES',
                selected_rows: []
            },
            show_manage_buttons: false,
            s_term: '',
            displayCols: [
                {field: 'id', label: 'ID', sortable: false, data_type: 'text'},
                {field: 'employee', label: 'Nhân viên', sortable: false, data_type: 'text'},
                {field: 'customer', label: 'Khách hàng', sortable: false, data_type: 'text'},
                {field: 'order_for', label: 'Đơn hàng', sortable: false, data_type: 'text'},
                {field: 'month', label: 'Tháng', sortable: false, data_type: 'text'},
                {field: 'created_at', label: 'Ngày tạo', sortable: false, data_type: 'text'},
                {field: 'edit', label: 'Sửa', sortable: false, data_type: 'link'},
                {field: 'clone', label: 'Copy', sortable: false, data_type: 'link'},
                {field: 'exportExcel', label: 'Xuất Excel', sortable: false, data_type: 'link'},
                {field: 'IN', label: 'In', sortable: false, data_type: 'link'},
            ],
            loading: false
        }
    },
    created: function(){
        this._getList();
        this.checkShowManageButtons();
        this.options.selected_rows = listview_get_selected_ids(this.options.prefix);
    },
    watch: {
        page: function(page) {
            this.pagination.current_page = this.page = page;
            this._getList({page: this.page});
        }
    },
    methods: {
        _getList: function(params){
            if (typeof params == 'undefined') {
                params = {};
            }
            const that = this;
            that.loading = true;
            axios.get(SITE_URL + 'orders/get_list' + http_build_query(params)).then(function(response){
                that.loading = false;
                that.sales = response.data.data.list;
                that.pagination = response.data.data.pagination;
                for(var i = 0; i < that.sales.length; i++) {
                	that.sales[i]['exportExcel'] = SITE_URL + 'Export_excel_orders/export/full/' + that.sales[i]['id'];
                	that.sales[i]['IN'] = SITE_URL + 'Export_excel_orders/export/forPrint/' + that.sales[i]['id'];
                	if(that.sales[i]['order_for'] == 'sp' || that.sales[i]['order_for'] == 'samplep') {
                		that.sales[i]['IN'] = SITE_URL + 'Export_excel_orders/export/full/' + that.sales[i]['id'];
                	}
                	
                    if (that.sales[i]['customer_id'] == 10) {
                        that.sales[i]['edit'] = SITE_URL + 'orders/update/yamaha/' + that.sales[i]['id'];
                        that.sales[i]['clone'] = SITE_URL + 'orders/order_clone/yamaha/' + that.sales[i]['id'];
                    } else if (that.sales[i]['customer_id'] == 9) {
                    	var order_for = 'honda';
                    	if(that.sales[i]['order_for'] == 'sp' ) {
                    		order_for = 'honda_sp';
                    	} else if (that.sales[i]['order_for'] == 'sample') {
                    		order_for = 'honda_sample';
                    	}
                        that.sales[i]['edit'] = SITE_URL + 'orders/update/'+order_for+'/' + that.sales[i]['id'];
                        that.sales[i]['clone'] = SITE_URL + 'orders/order_clone/honda/' + that.sales[i]['id'];
                    } else {
                        that.sales[i]['edit'] = SITE_URL + 'orders/update/' + that.sales[i]['id'];
                    }
                    
                }
            });
        },
        checkShowManageButtons: function() {
            var selectedIds = listview_get_selected_ids(this.options.prefix);
            this.show_manage_buttons = (selectedIds.length > 0);
        },
        clearSelection: function() {
            listview_clear_all(this.options.prefix);
            var newOptions = Object.assign({}, this.options);
            newOptions.selected_rows = [];
            this.options= newOptions;
            this.show_manage_buttons = false;
        },
        selectingRow: function() {
            this.checkShowManageButtons();
        },
        deleteSelected: function() {
            const that = this;
            var selectedIds = listview_get_selected_ids(this.options.prefix);
            axios.post(SITE_URL + 'orders/delete_ignore_type', convert_2_formdata({ids: JSON.stringify(selectedIds)})).then(function(response){
                that._getList();
                that.clearSelection();
            });
        },
        click_on_link: function(column, record) {
            if (column.field == 'delete') {
                // TODO
            } else if (column.field == 'edit') {
                window.location.href = record.edit;
            } else if (column.field == 'clone') {
            	window.location.href = record.clone;
            } else if (column.field == 'exportExcel') {
            	window.location.href = record.exportExcel;
            } else if (column.field == 'IN') {
            	window.location.href = record.IN;
            }
        },

        sort: function(order) {
            const params = {};
            params.order_by = order.by;
            params.order_field = order.field;
            this._getList(params);
        },
    }
});