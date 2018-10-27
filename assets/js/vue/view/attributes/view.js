
const veeMessage = {
custom: {
	  'attribute-name': {
      required: 'Tên thuộc tính không được rỗng' 
    },
    'attribute-code': {
      required: () => 'Mã thuộc tính không được rỗng',
      exists:  'Mã thuộc tính đã tồn tại'
    }
  }
};
const  exists_rule = {
	    getMessage(field) {
	        'That ' + field + ' is already in use.';
	    },
	    validate(value) {
	    	var id = document.getElementsByName('attribute-code')[0].getAttribute('data-attr-id');
            if (!value) {
                return false;
            }
	   		 return axios.post('attributes/check_duplicate_code/'+id, convert_2_formdata({new_code: value}))
	         .then(function (response) {
	             return response.data.valid;
	         }).catch(function (error) {
	         })
	    }
	};
VeeValidate.Validator.extend('exists', exists_rule);
VeeValidate.Validator.localize('custom', veeMessage);
Vue.use(VeeValidate);
var app = new Vue({
    el: '#content',
    data: function() {
        return {
            attribute: {
            	id :'',
                name: '',
                code: '',
                description: ''
            },
        	err_code_duplicate:''
        }
    },
    created: function(){
        var vueObject = JSON.parse(VUE_OBJECT);
        if (typeof vueObject.attribute != 'undefined' && vueObject.attribute != null) {
            this.attribute = vueObject.attribute;
        }
        if (typeof vueObject.attribute != 'undefined' && vueObject.attribute != null) {
            this.err_code_duplicate = vueObject.err_code_duplicate;
        }
    },
    methods: {
        fnSave: function() {
        	const that = this;
        	this.$validator.validateAll().then((result) => {
        		if(!result){
        		return;
        		}
     		   axios.post(SITE_URL + 'attributes/save', convert_2_formdata({attribute: JSON.stringify(that.attribute)})).then(function(response){
                   if (response.data.type == 'attribute') {
                       show_feedback('success','Dữ liệu đã được cập nhật!', 'success');
                       window.location.href = SITE_URL + 'attributes';
                   } else {
                       show_feedback('error','Lỗi trong quá trình cập nhật dữ liệu', 'error');
                   }
               }).catch(function (error) {});
    		}).catch(() => {
    		});

		}
    }
})