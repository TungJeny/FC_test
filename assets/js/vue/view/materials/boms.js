var app = new Vue({
    el: '#content',
    data: function() {
        return {
            items: [],
            pagination: {
                total_row: 0,
                total_page: 0,
                per_page: 0,
                current_page: 0,
            },
            page: 1,
            options: {
                prefix: 'LISTVIEW_MATERIALS_ITEMS',
                toggle_click: false,
                show_active_row: true,
                row_selection: 'single',
                show_check_colmun: false,
            },
            s_term: '',
            displayCols: [
                {field: 'item_id', label: 'ID', sortable: false, data_type: 'text'},
                {field: 'name', label: 'Name', sortable: false, data_type: 'text'},
                {field: 'category', label: 'Category', sortable: false, data_type: 'text'},
            ],
            loading: false,
            selected_item: {item_id: -1},
            
            boms: [],
            bom_pagination: {
                total_row: 0,
                total_page: 0,
                per_page: 0,
                current_page: 0,
            },
            bom_page: 1,
            bom_options: {
                view_title: '',
                prefix: 'LISTVIEW_ITEMS_BOMS',
                selected_rows: [],
                show_check_colmun: false,
            },
            bom_displayCols: [
                {field: 'id', label: 'ID', sortable: false, data_type: 'text'},
                {field: 'code', label: 'Code', sortable: false, data_type: 'text'},
                {field: 'name', label: 'Name', sortable: false, data_type: 'text'},
                {field: 'edit', label: '', sortable: false, data_type: 'link'},
                {field: 'delete', label: '', sortable: false, data_type: 'link'},
            ],
            bom_loading: false,
            bom_selected_item: null,
            href_new_bom: SITE_URL + 'materials/view/-1/-1'
        }
    },
    created: function(){
        this._getList({categories: CONST.MRP_CATEGORY_ID_TP});
    },
    watch: {
        page: function(page) {
            this.pagination.current_page = this.page = page;
            this._getList();
        }
    },
    methods: {
        on_enter: function() {
            this._getList({categories: CONST.MRP_CATEGORY_ID_TP, q: this.s_term});
        },
        _getList: function(params){
            const that = this;
            that.loading = true;
            
            if (typeof params == 'undefined') {
                var params = {};
                params.categories = 1;
                params.q = this.s_term;
                params.page = this.page;
            }
            axios.get(SITE_URL + 'items/get_list' + http_build_query(params)).then(function(response){
                if (response.data.type == 'items') {
                    that.items = response.data.data.list;
                    for(var i = 0; i < that.items.length; i++) {
                        that.items[i]['id'] = that.items[i]['item_id']
                    }
                    that.pagination = response.data.data.pagination;
                    that.loading = false;
                }
            });
        },
        _getListBOM: function(){
            const that = this;
            that.bom_loading = true;
            axios.get(SITE_URL + 'materials/get_list_boms/' + this.selected_item.id).then(function(response){
                if (response.data.type == 'boms') {
                    that.boms = response.data.data.list;
                    that.bom_pagination = response.data.data.pagination;
                    that.bom_loading = false;
                    
                    for(var i = 0; i < that.boms.length; i++) {
                        that.boms[i]['edit'] = SITE_URL + 'materials/view/' + that.boms[i]['item_id'] + '/' + that.boms[i]['id'];
                        that.boms[i]['delete'] = SITE_URL + 'materials/delete/' + that.boms[i]['item_id'] + '/' + that.boms[i]['id'];
                    }
                }
            });
        },
        selectingRow: function(id) {
            for(var i = 0; i < this.items.length; i++) {
                if (this.items[i]['id'] == id) {
                    this.selected_item = this.items[i];
                }
            }
            this._getListBOM();
            this.href_new_bom = SITE_URL + 'materials/view/'+ this.selected_item.item_id +'/-1';
        },
        search: function() {
            this._getList({categories: CONST.MRP_CATEGORY_ID_TP, q: this.s_term});
        },
        sort: function(order) {
            const params = {};
            params.order_by = order.by;
            params.order_field = order.field;
            params.categories = 1;
            this._getList(params);
        },
        click_on_link: function(column, record) {
            if (column.field == 'delete') {
                this.remove_bom(record.id);
            } else if (column.field == 'edit') {
                window.location.href = record.edit;
            }
        },
        
        remove_bom: function(bom_id) {
            const that = this;
            that.bom_loading = true;
            axios.get(SITE_URL + 'materials/delete_bom/' + bom_id).then(function(response){
                if (response.data.type == 'boms') {
                    that.boms = response.data.data.list;
                    that.bom_pagination = response.data.data.pagination;
                    for(var i = 0; i < that.boms.length; i++) {
                        that.boms[i]['edit'] = SITE_URL + 'materials/view/' + that.boms[i]['item_id'] + '/' + that.boms[i]['id'];
                        that.boms[i]['delete'] = SITE_URL + 'materials/delete/' + that.boms[i]['item_id'] + '/' + that.boms[i]['id'];
                    }
                    that.bom_loading = false;
                }
            });
        }
    }
})

