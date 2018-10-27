Vue.use(VeeValidate);
var app = new Vue({
    el: '#content',
    filters: {
        displayValue: function(unit) {
            if (unit === null || !unit.length) {
                return '-';
            }
            return unit;
        }
    },
    data: function() {
        return {
            bom: {
                id:'',
                item_id: '',
                name: '',
                code: ''
            },
            selected_item: {},
            resource: SITE_URL + 'items/suggest_autocomplete?category_id=' + CONST.MRP_CATEGORY_ID_VTC,
            materials: {},
            semi_items: [],
            semi_all: [],
            show_summary: false
        }
    },
    created: function(){
        var vueObject = JSON.parse(VUE_OBJECT);
        this.selected_item = vueObject.item;
        if (vueObject.bom !== null) {
            this.bom = vueObject.bom;
        }
        if (vueObject.semi_items !== null) {
            this.semi_items = vueObject.semi_items;
        }
        this.semi_items.unshift({
            item_id: 'main',
            name: '',
        });
        this.bom.item_id = this.selected_item.item_id;
        
        for(var i = 0; i <= this.semi_items.length - 1; i++) {
            this.semi_items[i].semi_id = 'semi_' + this.semi_items[i].item_id;
            this.materials[this.semi_items[i].semi_id] = [];
            if (vueObject.bom_raw !== null && typeof vueObject.bom_raw[this.semi_items[i].semi_id] != 'undefined') {
                this.materials[this.semi_items[i].semi_id] = vueObject.bom_raw[this.semi_items[i].semi_id];
            }
        }
        // console.log(this.materials);
        var semi_items = [];
        for(var i = 0; i <= this.semi_items.length - 1; i++) {
            var semi_item = {};
            semi_item.item_id = this.semi_items[i].item_id;
            semi_item.semi_id = 'semi_' + this.semi_items[i].item_id;
            semi_item.name = this.semi_items[i].name;
            semi_item.is_show = this.semi_items[i].item_id == 'main' ? true : false;
            semi_item.active_tab = 'vtc';
            semi_item.resource = SITE_URL + 'items/suggest_autocomplete?category_id=' + CONST.MRP_CATEGORY_ID_VTC;
            semi_items.push(semi_item);
        }
        this.semi_items = semi_items;
    },
    methods: {
        fnSave: function() {
            
            const that = this;
            this.$validator.validateAll().then((result) => {
                if(!result) {
                    return;
                }
                axios.post(SITE_URL + 'materials/save', convert_2_formdata({bom: JSON.stringify(that.bom), materials: JSON.stringify(that.materials)})).then(function(response){
                    if (response.data.type == 'mrp_item_bom') {
                        show_feedback('success','Dữ liệu đã được cập nhật!', 'success');
                        window.location = BASE_URL + 'materials/boms';
                    } else {
                        show_feedback('error','Lỗi trong quá trình cập nhật dữ liệu', 'error');
                    }
                }).catch(function (error) {});
            }).catch(() => {});
        },
        on_selected: function(selected) {
            if (!this.check_exist_item(selected.el_parent_id, selected)) {
                selected.rate_of_qty = '';
                selected.rate_of_unit = '';
                this.materials[selected.el_parent_id].push(selected);
            }
            this.semi_all.push(selected);
        },
        change_tab: function(semi_id, category_id) {
            for(var i =0; i <= this.semi_items.length -1; i++) {
                if (semi_id == this.semi_items[i].semi_id) {
                    this.semi_items[i].resource = SITE_URL + 'items/suggest_autocomplete?category_id=' + category_id;
                }
            }
        },
        remove_item: function(key, record) {
            var new_materials = {};
            for(var semi in this.materials) {
                if (semi == key) {
                    var newList = [];
                    for(var i = 0; i <= this.materials[key].length - 1; i++) {
                        if (this.materials[key][i].item_id !== record.item_id) {
                            newList.push(this.materials[key][i]);
                        }
                    }
                    new_materials[key] = newList;
                } else {
                    new_materials[semi] = this.materials[semi];
                }
            }
            this.materials = new_materials;
        },
        check_exist_item: function(key, record) {
            if (this.materials[key].length > 0) {
                for(var i = 0; i <= this.materials[key].length - 1; i++) {
                    if (this.materials[key][i].item_id === record.item_id) {
                        return true;
                    }
                }
            }
            return false;
        },
        toggle_panel: function(i) {
            this.semi_items[i].is_show = !this.semi_items[i].is_show; 
        },
        toggle_panel_summary: function(i) {
            this.show_summary = !this.show_summary; 
        },
        update_rate_of_qty: function(semi_id, i) {
        	if (parseFloat(this.materials[semi_id][i].rate_of_qty) > 0) {
        		this.materials[semi_id][i].rate_of_unit = null;
        	}
        	this.$forceUpdate();
        },
        update_rate_of_unit: function(semi_id, i) {
        	if (parseFloat(this.materials[semi_id][i].rate_of_unit) > 0) {
        		this.materials[semi_id][i].rate_of_qty = null;
        	}
        	this.$forceUpdate();
        }
        
    }
})