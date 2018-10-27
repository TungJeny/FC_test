var app = new Vue({
    el: '#content',
    data: function() {
        return {
            departments: [],
            pagination: {
                total_row: 0,
                total_page: 0,
                per_page: 0,
                current_page: 0,
            },
            page: 1,
            options: {
                view_title: '',
                prefix: 'LISTVIEW_DEPARTMENT',
                selected_rows: []
            },
            show_manage_buttons: false,
            s_term: '',
            displayCols: [
                {field: 'code', label: 'Mã phòng ban', sortable: true, data_type: 'text'},
                {field: 'name', label: 'Tên phòng ban', sortable: true, data_type: 'text'},
                {field: 'description', label: 'Mô tả', sortable: false, data_type: 'text'},
                {field: 'edit', label: '', sortable: false, data_type: 'link'},
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
            this._getList();
        }
    },
    methods: {
        _getList: function(params){
            const that = this;
            that.loading = true;
            var query = '?page=' + this.page;
            if (this.s_term.length) {
                query += ('&q=' + this.s_term);
            }
            
            if (typeof params != 'undefined') {
                query += ('&order_by=' + params.order_by);
                query += ('&order_field=' + params.order_field);
            }
            
            axios.get(SITE_URL + 'departments/getList' + query).then(function(response){
                if (response.data.type == 'departments') {
                    that.departments = response.data.data.list;
                    that.pagination = response.data.data.pagination;
                    for(var i = 0; i < that.departments.length; i++) {
                        that.departments[i]['edit'] = SITE_URL + 'departments/view/' + that.departments[i]['id'];
                    }
                    that.loading = false;
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
            axios.post(SITE_URL + 'departments/delete', convert_2_formdata({ids: JSON.stringify(selectedIds)})).then(function(response){
                that._getList();
                that.clearSelection();
            });
        },
        search: function() {
            this._getList();
        },
        sort: function(order) {
            const params = {};
            params.order_by = order.by;
            params.order_field = order.field;
            this._getList(params);
        },
        click_on_link: function(column, record) {
            if (column.field == 'delete') {
                // TODO
            } else if (column.field == 'edit') {
                window.location.href = record.edit;
            }
        },
    }
})

