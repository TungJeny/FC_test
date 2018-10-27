var app = new Vue({
    el : "#form",
    filters: {
        displayValue: function(unit) {
            if (unit === null || !unit.length) {
                return '-';
            }
            return unit;
        }
    },
    data : function() {
        return {
            resource: SITE_URL + 'items/suggest_autocomplete?category_id=' + CONST.MRP_CATEGORY_ID_BTP,
            selected_items: [],
            selected_attr_group: 0,
            attributes: []
        }
    },
    created: function(){
        var vueObject = JSON.parse(VUE_OBJECT);
        this.selected_items = vueObject.semi_items;
        this.attributes = vueObject.attr_values;
        this.selected_attr_group = vueObject.attr_group_id;
    },
    
    methods: {
        on_selected: function(selected) {
            if (!this.check_exist_item(selected)) {
                selected.qty = 1;
                this.selected_items.push(selected);
            }
        },
        check_exist_item: function(record) {
            if (this.selected_items.length > 0) {
                for(var i = 0; i <= this.selected_items.length - 1; i++) {
                    if (this.selected_items[i].item_id === record.item_id) {
                        return true;
                    }
                }
            }
            return false;
        },
        build_input_name: function(record) {
            return 'item_semis[' + record.item_id + ']';
        },
        remove_item: function(index) {
            var new_items = [];
            for(var i = 0; i <= this.selected_items.length - 1; i++) {
                if (i != index) {
                    new_items.push(this.selected_items[i]);
                }
            }
            this.selected_items = new_items;
        },
        select_attr_group: function() {
            const that = this;
            axios.get(SITE_URL + 'attribute_groups/get_detail/' + this.selected_attr_group).then(function(response){
                that.attributes = response.data.data;
            });
        },
        build_attr_input_name: function(attribute) {
            return 'attrs[' + attribute.id + ']';
        },
    },
});