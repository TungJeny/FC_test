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
            star :1, 
            materials: [],
            materials_item: [],
            months: [],
            month: [],
            totals: [],
            materials_matrix: [],
            factories: [],
            loading: false,
            summary_materials: [],
            summary_matrix: [],
            material_detail: {material: {id: '', name: ''}},
            planning_sub:[],
            data_bom_id:[],
            data_sub:[],
            data_planning_detail:[],
            planning_sub_temp:[],
            data_planning_full :{},
            item_id:[],
        }
    },
    watch: {
        materials_matrix: {

            handler: function (after, before) {
                this.update_matrix();
                if(this.star!=1){
                    this.totals_up();
                }
                 this.star++;
               
            },
            deep: true,
        }
    },
    created: function() {
        this.load_materials_matrix();
    }, 

    methods: {

        update_matrix: function() {
        	
            for(var id in this.materials_matrix) {
     
                var qty_month = 0;
                var price_month = 0;
                for(var property in this.materials_matrix[id]) {
                    if (property.indexOf('-') === -1) {
                        if(this.materials_matrix[id][property]>0) {
                        	if (property.endsWith('qty')) {
                        		var qty = this.materials_matrix[id][property];
                                var price = this.materials_matrix[id][property.replace("_qty", "_price")];
                        		if (qty > 0 && price >0) {
                                    qty_month = qty;
                                    price_month = price;
                        		}
                        	} else if (property.endsWith('price')) {
                                var price = this.materials_matrix[id][property];
                                var qty = this.materials_matrix[id][property.replace("_price", "_qty")];
                                if (qty > 0 && price >0) {
                                    qty_month = qty;
                                    price_month = price;
                                }
                            }       
                        }
                        
                    }

                }
            	this.materials_matrix[id]['xn_'+this.month+'_qty'] = qty_month;
            	this.materials_matrix[id]['xn_'+this.month+'_price'] = price_month;
            }
            

        },

        totals_up: function(){
           for(var tots in this.totals) {
            if (tots.indexOf('-') === -1)
             this.totals[tots] = 0;
           }
            for(var id in this.materials_matrix) {
                for(var property in this.materials_matrix[id]) {
                	
                    if (property.indexOf('-') !== -1) {
                        
                    } else {
                        if(this.totals['tol_'+property]>0)
                            this.totals['tol_'+property] = Number(this.totals['tol_'+property])+Number(this.materials_matrix[id][property]);
                        else
                            this.totals['tol_'+property] = Number(this.materials_matrix[id][property]);

                    }
                }
            }
        },
        load_data_sub:function(){
                var data_sub={};
                for(var x in this.planning_sub_temp){
                    var val_sub = {};
                    var toal=0;
                    var k = this.planning_sub_temp[x]['item_id'];
                    for(var bom in this.data_bom_id){
                        if(this.planning_sub_temp[x]['bom_id']===this.data_bom_id[bom]['id']){
                            for(var fa in this.factories){
                                if(this.materials_matrix[this.data_bom_id[bom]['item_id']]['xn_'+fa+'_qty']>0){
                                    val_sub[fa] = this.materials_matrix[this.data_bom_id[bom]['item_id']]['xn_'+fa+'_qty'];
                                    toal = Number(toal)+Number(val_sub[fa]);
                                }
                                else
                                    val_sub[fa]= 0;   
                            }
                           
                        }
                    }
                    val_sub['toal'] =  toal;
                    val_sub['key'] = k;
                    data_sub[x]=val_sub;
                   
                }
                var data ={};
                for(var k in data_sub){
                    if(data[data_sub[k]['key']]==undefined){
                      data[data_sub[k]['key']]=data_sub[k];   
                    }
                    else{
                        for(var x in data_sub[k]){
                            data[data_sub[k]['key']][x] = Number(data[data_sub[k]['key']][x])+Number(data_sub[k][x]);
                        }
                    }
                    
                }
                return data;
        },
        load_data_planning_full: function(){
            var data_planning_full_temp = [];
            var total = 0;
            var result = {};
            for(var i in this.item_id){
                for(var x in this.materials_item){
                    if(this.item_id[i]['item_id'] === x){
                    	data_planning_full_temp[i] = this.materials_item[x];
                    	data_planning_full_temp[i]['name_planning'] = this.item_id[i]['code'];
                        for(var property in this.materials_matrix[x]){
                            if (property.indexOf('-') !== -1){
                                if (property.endsWith('qty')) {
                                	data_planning_full_temp[i]['qty_planning'] = this.materials_matrix[x][property];
                                }
                                else {
                                	data_planning_full_temp[i]['price_planning'] = this.materials_matrix[x][property];
                                }  
                            }
                        }
                    }
                }
            }
            for(var idpf in data_planning_full_temp){
            	 total += parseInt(data_planning_full_temp[idpf]['price_planning']);
            }
           
            for(var idpf in data_planning_full_temp){
            	result[data_planning_full_temp[idpf]['name_planning']] ={};
            	result[data_planning_full_temp[idpf]['name_planning']]['name_planning'] = data_planning_full_temp[idpf]['name_planning'];
            	result[data_planning_full_temp[idpf]['name_planning']]['qty_planning'] = 0;
            	result[data_planning_full_temp[idpf]['name_planning']]['price_planning'] = 0;
            	result[data_planning_full_temp[idpf]['name_planning']]['unit'] = '';
            	result[data_planning_full_temp[idpf]['name_planning']]['percent_planning'] = 0;
            }
            console.log(data_planning_full_temp);
            for(var name_planning in result){
                for(var idpf in data_planning_full_temp){
                	if (name_planning == data_planning_full_temp[idpf]['name_planning']) {
                		result[name_planning]['qty_planning'] += parseInt(data_planning_full_temp[idpf]['qty_planning']);
                		result[name_planning]['price_planning'] += parseInt(data_planning_full_temp[idpf]['price_planning']);
                		result[name_planning]['percent_planning'] = Math.round(parseInt(result[name_planning]['price_planning'])/total*100) +''+'%';;
                	}
                }
            }
            return result;
        },
        load_materials_matrix: function() {
            const that = this;
            that.loading = true;
            axios.get(SITE_URL + 'production/materials',  {transformResponse: (req) => {
                // Do your own parsing here if needed ie JSON.parse(req);
                return req;
            },
            }).then(function(response){
                that.loading = false;
                that.materials = JSON.parse(response.data).data.materials;
                that.materials_item = JSON.parse(response.data).data.materials_item;
                that.totals = JSON.parse(response.data).data.totals;
                that.month = JSON.parse(response.data).data.month;
                that.materials_matrix = JSON.parse(response.data).data.matrix;
                that.data_bom_id = JSON.parse(response.data).data_bom_id;
                that.factories = JSON.parse(response.data).data.factories;
                that.data_planning_detail = JSON.parse(response.data).data_planning_detail;
                that.planning_sub = JSON.parse(response.data).planning_sub;
                that.planning_sub_temp = JSON.parse(response.data).planning_sub_temp;
                that.data_sub = that.load_data_sub();
                that.item_id = JSON.parse(response.data).item_id;
                that.data_planning_full = that.load_data_planning_full();
                console.log(that.materials_matrix);
                setTimeout(function(){
                    correct_fixed_table('detail');
                    $('#detail .table-outter').css({"top": ($('.detail_content_outter table').height() - getScrollbarWidth())});
                    $('#detail .fixed-column-outter th').css({"height": ($('.detail_content_outter table').height() - getScrollbarWidth() - 2)});
                }, 10);
                
            }).catch(function (error) {});
        },
        is_material: function(material) {
            return material.type == 'material';
        },
        get_material_matrix_value: function(material, id, type) {
        	
            return this.materials_matrix[material.item_id]['xn_' + id + '_' + type];
        },
        fnSave: function() {
            var active_tab = $('.nav-tabs li.active a').attr('href');
            const that = this;
             //alert(active_tab);
            that.loading = true;
            if (active_tab == '#detail') {
                axios.post(SITE_URL + 'production/materials_save', convert_2_formdata({
                    materials_matrix: JSON.stringify(that.materials_matrix), 
                    materials: JSON.stringify(that.materials), 
                    factories: JSON.stringify(that.factories),
                    type: JSON.stringify('detail'),
                    month: JSON.stringify(that.month),
                })).then(function(response){
                    that.loading = false;
                }).catch(function (error) {});
            } else {
                        var arr_pl = [];
                        var x=0;
                        var comn_sub = {};
                        for(var y in that.planning_sub){
                           arr_pl[x]=(y);
                           x++;
                        }
                        var x=0;
                        $('input[name="comn_sub[]"]');
                        $('input[name^="comn_sub"]').each(function() {
                            comn_sub[arr_pl[x]]= $(this).val();
                            x++;
                        });
                        axios.post(SITE_URL + 'production/planning_detail_sub_save', convert_2_formdata({
                            month: JSON.stringify(that.month),
                            data_sub_comn: JSON.stringify(comn_sub), 

                        })).then(function(response){
                        
                            that.loading = false;
                        }).catch(function (error) {});
                         that.loading = false;
            }
        },
        showDetail: function() {
            setTimeout(function(){
                correct_fixed_table('detail');
                $('#detail .table-outter').css({"top": ($('.detail_content_outter table').height() - getScrollbarWidth())});
                $('#detail .fixed-column-outter th').css({"height": ($('.detail_content_outter table').height() - getScrollbarWidth() - 2)});
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
        // showSummaryOfMonth_sub: function() {
        // const that = this;
        // that.loading = true;
        // CONST.MRP_CATEGORY_ID_VTC = 3;
        // axios.get(SITE_URL + 'materials_plan/matrix_summary/' +
		// CONST.MRP_CATEGORY_ID_VTC).then(function(response){
        // that.loading = false;
        // that.summary_materials = response.data.data.materials;
        // that.summary_matrix = response.data.data.summary_matrix;
        // setTimeout(function(){
        // correct_fixed_table('summary_sub');
        // }, 10);
        // }).catch(function (error) {});
        // },
        show_material_detail: function(material) {
            // return;
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
        }
    }
})
