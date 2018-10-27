<script type="text/x-template" id="vue_list_view">
<div class="vue-list-view">
    <div class="mask" v-show="loading">
        <div class="spinner">
			<div class="rect1"></div>
			<div class="rect2"></div>
		  <div class="rect3"></div>
		</div>
    </div>
    <table class="tablesorter table table-hover">
	<thead>
		<tr>
			<th v-if="show_check_column()">
				<input type="checkbox" @click="selectAllRows()" v-model="selected_all" id="select_all">
					<label for="select_all"><span></span></label>
			</th>
            <th v-for="(column, i) in columns" v-bind:class="[isSorting(i)]" @click="sort(column.field)" >
                {{ column.label }}
            </th>
		</tr>
	</thead>
	<tbody>
		<tr style="cursor: pointer;" v-for="(record, i) in data" v-bind:class="{ active: is_active(record) }">
			<td v-if="show_check_column()" class=""><input type="checkbox" @click="selectRow(record.id)" v-model="selected_rows" v-bind:id="generateElementId(record.id)" v-bind:value="record.id"><label v-bind:for="generateElementId(record.id)"><span></span></label></td>
			<td v-for="column in columns" @click="selectOnRow(record.id)" v-bind:class="column.data_type">
                <span v-if="column.data_type == 'text'">{{ record[column.field] }}</span>
                <a @click="clickOnLink(column, record)" v-if="column.data_type == 'link'">{{column.field}}</a>
            </td>
		</tr>
	</tbody>
    </table>
    
    <div class="panel-options custom" style="text-align: center;" v-show="data.length && (pagination.total_page > 1)">
	   <div class="pagination pagination-top hidden-print  text-center">
		  <a @click="paging(1)" rel="start">‹ First</a>
		  <a @click="paging(pagination.current_page - 1)" rel="prev">&lt;</a>
          <a v-for="n in pages_availables" @click="paging(n)" v-bind:class="{active: n == pagination.current_page }">{{n}}</a>
		  <a @click="paging(pagination.current_page + 1)" rel="next">&gt;</a>
          <a @click="paging(pagination.total_page)">Last ›</a>
	   </div>
    </div>
</div>
</script>

<script type="text/javascript">
Vue.component('list-view', {
    data: function() {
        return {
            page: 1,
            pages_availables: [],
            selected_rows: [],
            selected_all: false,
            is_loading: false,
            last_row_click: 0,
        }
    },
    props: {
        data: {
            type: Array,
            required: true
        },
        columns: {
            type: Array,
            required: false
        },
        pagination: {
            required: false,
            type: Object,
            default: null
        },
        options: {
            required: false,
            type: Object,
            default: function(){
                return {}
            }
        },
        order: {
            required: false,
            type: Object,
            default: function(){
              	return {field: 0, by: 'desc'}
            }
        },
        loading: {
            required: false,
            type: Boolean,
            default: false
        },
    },
    watch: {
        data: function(newData) {
              this.data = newData;
              this.checkSelectedAllRow();
              this.selected_rows = listview_get_selected_ids(this.options.prefix);
        },
        pagination: function(newData) {
            this.pages_availables = [];
            if (newData.total_page > 5) {
                if (newData.current_page == 1 || newData.current_page ==  2 || newData.current_page == 3) {
                        this.pages_availables = [1,2,3,4,5];
                } else if (newData.current_page == newData.total_page || newData.current_page ==  newData.total_page - 1 || newData.current_page == newData.total_page - 2) {
                    this.pages_availables = [newData.total_page - 4, newData.total_page - 3, newData.total_page - 2, newData.total_page - 1, newData.total_page];
                } else if(newData.current_page <= newData.total_page) {
                    this.pages_availables = [newData.current_page - 2, newData.current_page - 1, newData.current_page, newData.current_page + 1, newData.current_page + 2];
                }
            } else {
                for(var i = 1; i <= newData.total_page; i++) {
                    this.pages_availables.push(i);
                }
            }
        },
        options: function(options) {
            this.selected_rows = this.options.selected_rows;
            this.checkSelectedAllRow();
        }
    },
    created: function(){
        if (typeof this.options.view_title == 'undefined') {
            this.options.view_title = 'List View';
        }

        if (typeof this.options.prefix == 'undefined') {
            this.options.prefix = 'LISTVIEW_PREFIX';
        }

        if (typeof this.options.selected_rows == 'undefined') {
            this.options.selected_rows = null;
        }

        if (typeof this.options.show_active_row == 'undefined') {
            this.options.show_active_row = false;
        }

        if (typeof this.options.row_selection == 'undefined') {
            this.options.row_selection = 'single';
        }

        if (typeof this.options.toggle_click == 'undefined') {
            this.options.toggle_click = true;
        }

        if (typeof this.options.show_check_colmun == 'undefined') {
            this.options.show_check_colmun = true;
        }
        this.selected_rows = this.options.selected_rows;
    },
    methods: {
        paging: function(page) {
            if (page <= this.pagination.total_page) {
                this.$parent.page = page;
            }
        },
        generateElementId: function(id) {
            return 'row_' + id;
        },
        selectRow: function(id) {
            this.last_row_click = id;
            
            var selectedIds = listview_get_selected_ids(this.options.prefix);
            if (selectedIds.indexOf(id) != -1) {
                if (this.options.toggle_click) {
                	listview_remove_ids_listview(this.options.prefix, [id]);
                }
            } else {
                listview_add_selected_id(this.options.prefix, id);
            }
            this.$emit('selectingrow', id);
        },
        selectOnRow: function(id) {
            if(this.options.show_check_colmun)
            {
                return false;
            }
            
            this.last_row_click = id;
            
            var selectedIds = listview_get_selected_ids(this.options.prefix);
            if (selectedIds.indexOf(id) != -1) {
                if (this.options.toggle_click) {
                	listview_remove_ids_listview(this.options.prefix, [id]);
                }
            } else {
                listview_add_selected_id(this.options.prefix, id);
            }
            this.$emit('selectingrow', id);
        },
        selectAllRows: function() {
            this.selected_all = !this.selected_all;
            if (this.selected_all) {
                for(var i = 0; i < this.data.length; i++) {
                    listview_add_selected_id(this.options.prefix, this.data[i].id);
                }
            } else {
                var removeIds = [];
                for(var i = 0; i < this.data.length; i++) {
                    removeIds.push(this.data[i].id);
                }
                listview_remove_ids_listview(this.options.prefix, removeIds);
           }
           this.selected_rows = listview_get_selected_ids(this.options.prefix);

           this.$emit('selectingrow', 'all');
        },
        isChecked: function(id) {
            var selectedIds = listview_get_selected_ids(this.options.prefix);;
            return (selectedIds.indexOf(id) != -1);
        },
        checkSelectedAllRow: function() {
            if (!this.data.length) {
                return;
            }
            var ids = [];
            for(var i = 0; i < this.data.length; i++) {
                ids.push(this.data[i].id);
            }
            var selectedIds = listview_get_selected_ids(this.options.prefix);
            this.selected_all = array_contains_another_array(ids, selectedIds);
        },
        isSorting: function(byFieldIndex) {
            if (!this.data.length) {
                return '';
            }
            
            var byField = this.columns[byFieldIndex].field;
            if (this._isSortable(this.columns[byFieldIndex].field)) {
                if (this.order.field == byField && this.order.by == 'desc') {
                	return 'sorting_desc';
            	}
            	if (this.order.field == byField && this.order.by == 'asc') {
                	return 'sorting_asc';
            	}
              	return 'sorting';
            }
            return '';
        },
        sort: function(byField) {
            if (!this.data.length) {
                return;
            }
            
            if (this._isSortable(byField)) {
                this.order.field = byField;
                if (this.order.by == 'desc') {
                    this.order.by = 'asc';
                } else {
                    this.order.by = 'desc';
                }

                this.$emit('sorting', this.order);
            }
        },
        _isSortable: function(byField) {
            var sortable = false;
            this.columns.forEach(function(column){
              	if (column.field == byField && typeof column.sortable != 'undefined' && column.sortable) {
                	sortable = true;
              	}
            });
            return sortable;
        },
        show_check_column: function() {
            return this.options.show_check_colmun;
        },
        is_active: function(record) {
            if (!this.options.show_active_row) {
                return false;
            }
            return record.item_id == this.last_row_click;
        },
        clickOnLink: function(column, record) {
            this.$emit('clickonlink', column, record);
        }
    },
    template: '#vue_list_view'
});
</script>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.vue-list-view td.link {width: 50px;}
</style>