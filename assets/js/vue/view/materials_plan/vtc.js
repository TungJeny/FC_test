Vue.use(VeeValidate);
var app = new Vue({
    el: '#content',
    components: {
        vuejsDatepicker
    },
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
            materials: [],
            months: [],
            materials_matrix: {},
            loading: false,
            summary_materials: [],
            summary_matrix: [],
            material_detail: {material: {id: '', name: ''}}
        }
    },
    created: function() {
        this.load_materials_matrix();
    },
    methods: {
        load_materials_matrix: function() {
            const that = this;
            that.loading = true;
            axios.get(SITE_URL + 'materials_plan/matrix_detail/' + CONST.MRP_CATEGORY_ID_VTC).then(function(response){
                that.materials = response.data.data.materials;
                that.months = response.data.data.months;
                that.materials_matrix = response.data.data.materials_matrix;
                that.loading = false;
                setTimeout(function(){
                    correct_fixed_table('detail');

                    var d = new Date();
                    var month = d.getMonth();
                    $('#detail #contenthead-detail').scrollLeft((month * 150));
                    $('#detail #contentbody-detail').scrollLeft((month * 150));
                }, 10);
                
            }).catch(function (error) {});
        },
        is_material: function(material) {
            return material.type == 'material';
        },
        get_material_matrix_value: function(month, material) {
            return this.materials_matrix[material.material_id][month][material.type];
        },
        fnSave: function() {
            var active_tab = $('.nav-tabs li.active a').attr('href');
            const that = this;
            that.loading = true;
            if (active_tab == '#detail') {
                axios.post(SITE_URL + 'materials_plan/save', convert_2_formdata({
                        materials_matrix: JSON.stringify(that.materials_matrix), 
                        materials: JSON.stringify(that.materials), 
                        months: JSON.stringify(that.months),
                        type: JSON.stringify('detail'),
                        category_id: CONST.MRP_CATEGORY_ID_VTC
                })).then(function(response){
                    that.loading = false;
                }).catch(function (error) {});
            } else {
                axios.post(SITE_URL + 'materials_plan/save', convert_2_formdata({
                    materials_matrix: JSON.stringify(that.summary_matrix), 
                    materials: JSON.stringify(that.summary_materials),
                    type: JSON.stringify('summary'),
                    category_id: CONST.MRP_CATEGORY_ID_VTC
            })).then(function(response){
                that.loading = false;
            }).catch(function (error) {});
            }
        },
        showDetail: function() {
            setTimeout(function(){
                correct_fixed_table('detail');

                var d = new Date();
                var month = d.getMonth();
                $('#detail #contenthead-detail').scrollLeft((month * 150));
                $('#detail #contentbody-detail').scrollLeft((month * 150));
            }, 10);
        },
        showSummaryOfMonth: function() {
            const that = this;
            that.loading = true;
            axios.get(SITE_URL + 'materials_plan/matrix_summary/' + CONST.MRP_CATEGORY_ID_VTC).then(function(response){
                that.loading = false;
                that.summary_materials = response.data.data.materials;
                that.summary_matrix = response.data.data.summary_matrix;
                setTimeout(function(){
                    correct_fixed_table('summary');
                }, 10);
            }).catch(function (error) {});
        },
        show_material_detail: function(material) {
            const that = this;
            var active_tab = $('.nav-tabs li.active a').attr('href');
            if (material.type == 'material' || active_tab == '#summary') {
                show_page_loading();
                axios.get(SITE_URL + 'materials_plan/detail/' + material.material_id).then(function(response){
                    that.material_detail = response.data.data;
                    $('#material_plan_detail').modal('show');
                    hide_page_loading();
                }).catch(function (error) {});
            } else {
                return;
            }
        },
        update_stock_report: function() {
           const that = this;
           that.loading = true;
           var material_ids = [];
           for (var index = 0; index < this.materials.length; index++) {
               if (material_ids.indexOf(this.materials[index].material_id) < 0) {
                   material_ids.push(this.materials[index].material_id);
               }
           }
           axios.post(
               SITE_URL + 'stock_report/update', convert_2_formdata({'material_ids': material_ids})).then(function(response){
               that.loading = false;
               that.load_materials_matrix();
           }).catch(function (error) {
               that.loading = false;
               alert(error);
           });
        }
    }
})