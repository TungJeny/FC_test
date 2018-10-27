Vue.use(VeeValidate);
var app = new Vue({
    el: '#content',
    data: function() {
        return {
            department: {
                id: '',
                name: '',
                code: '',
                description: ''
            },
            is_valid: false
        }
    },
    watch: {
        department: {
            deep: true,
            handler(val, oldVal){
                if (val.name.length && val.code.length) {
                    this.is_valid = true;
                }
            },
        }
    },
    created: function(){
        var vueObject = JSON.parse(VUE_OBJECT);
        if (typeof vueObject.department != 'undefined' && vueObject.department != null) {
            this.department = vueObject.department;
        }
    },
    methods: {
        fnSave: function() {
            const that = this;
            if (this.is_valid) {
                axios.post(SITE_URL + 'departments/save', convert_2_formdata({department: JSON.stringify(this.department)})).then(function(response){
                    if (response.data.type == 'department') {
                        show_feedback('success','Dữ liệu đã được cập nhật!', 'success');
                        window.location.href = SITE_URL + 'departments';
                    } else {
                        show_feedback('error','Lỗi trong quá trình cập nhật dữ liệu', 'error');
                    }
                }).catch(function (error) {});
            }
        }
    }
})