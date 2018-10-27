
import VueDraggable from '../../../vue/modules/vue-draggable/index.js';


const veeMessage = {
custom: {
	  'attr-group-name': {
      required: 'Tên nhóm thuộc tính không được rỗng' 
    },
    'attr-group-code': {
      required: () => 'Mã nhóm thuộc tính không được rỗng',
      exists:  'Mã nhóm thuộc tính đã tồn tại'
    }
  }
};
const  exists_rule = {
	    getMessage(field) {
	        'That ' + field + ' is already in use.';
	    },
	    validate(value) {
	    	var id = document.getElementsByName('attr-group-code')[0].getAttribute('data-attr-group-id');
            if (!value) {
                return false;
            }
	   		 return axios.post('attribute_groups/check_duplicate_code/'+id, convert_2_formdata({new_code: value}))
	         .then(function (response) {
	             return response.data.valid;
	         }).catch(function (error) {
	         })
	    }
	};
VeeValidate.Validator.extend('exists', exists_rule);
VeeValidate.Validator.localize('custom', veeMessage);
Vue.use(VueDraggable);
Vue.use(VeeValidate);

var app = new Vue({
    el: '#content',
    data: function() {
        return {
            attribute_group: {
            	id:'',
                name: '',
                code: '',
                description: '',
            },
        	err_code_duplicate:'',
        	related_object: [],
        	all_related_obj: [],
        	selected_attrs: [],
        	all_attrs:[],
        	options: {
        		dropzoneSelector: 'ul',
        		draggableSelector: 'li',
        		itemList: {
        			'target': [],
        			'owner': []
        		},
        		excludeOlderBrowsers: true,
        		multipleDropzonesItemsDraggingEnabled: true,
        		onDrop: this.onDrop,
        		onDragstart: this.onDragstart,
        		onDragend: this.onDragend,
        	}
        }
    },
    created: function(){
        var vueObject = JSON.parse(VUE_OBJECT);
        if (typeof vueObject.attribute_group != 'undefined' && vueObject.attribute_group != null) {
            this.attribute_group = vueObject.attribute_group;
        }
        if (typeof vueObject.all_related_obj != 'undefined' && vueObject.all_related_obj != null) {
            this.all_related_obj = vueObject.all_related_obj;
        }
        if (typeof vueObject.related_object != 'undefined' && vueObject.related_object != null) {
            this.related_object = vueObject.related_object;
        }
        if (typeof vueObject.selected_attrs != 'undefined' && vueObject.selected_attrs != null) {
            this.options.itemList.target = vueObject.selected_attrs;
        }
        if (typeof vueObject.attrs_without_selected != 'undefined' && vueObject.attrs_without_selected != null) {
            this.options.itemList.owner = vueObject.attrs_without_selected;
        }
    },
    methods: {
        fnSave: function() {
        	const that = this;
        	this.$validator.validateAll().then((result) => {
        		if(!result){
        		return;
        		}
                axios.post(SITE_URL + 'attribute_groups/save', convert_2_formdata({attribute_group: JSON.stringify(that.attribute_group),selected_attrs:JSON.stringify(that.selected_attrs),  related_object:JSON.stringify(that.related_object) })).then(function(response){
                    if (response.data.type == 'attribute_group') {
                        show_feedback('success','Dữ liệu đã được cập nhật!', 'success');
                        window.location.href = SITE_URL + 'attribute_groups';
                    } else {
                        show_feedback('error','Lỗi trong quá trình cập nhật dữ liệu', 'error');
                    }
                }).catch(function (error) {});
    		}).catch(() => {
    		});
        },
        onDrop: function(event) {
        	 this.selected_attrs = event.targetItems;
        },
        onDragstart: function(event) {
        	
        },
        onDragend: function(event) {
        	
        },
    }
})