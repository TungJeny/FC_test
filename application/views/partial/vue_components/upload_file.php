<script type="text/x-template" id="vue_upload_file">
<div class="col-sm-12 col-md-12 col-lg-12">
<div class="mask" v-show="loading">
        <div class="spinner">
			<div class="rect1"></div>
			<div class="rect2"></div>
		  <div class="rect3"></div>
		</div>
</div>
<ul class="list-unstyled avatar-list">
    <li>
        <input type="file" name="vfile" id="vfile" v-on:change="upload" ref="vfile" class="filestyle" style="position: absolute; clip: rect(0px, 0px, 0px, 0px);"/>
    </li>
</ul>
</div>
</script>

<script type="text/javascript">
Vue.component('v-upload', {
    data: function() {
        return {
            loading: false,
        }
    },
    props: {
        url: {
            type: String,
            required: true
        }
    },
    created: function(){
        console.log(this.url);
    },
    methods: {
        upload: function() {
            var that = this;
            that.loading = true;
            var file = this.$refs.vfile.files[0];
            var fd = new FormData();
            fd.append("vfile", file);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', this.url, true);
            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    var percentComplete = (e.loaded / e.total) * 100;
                    console.log(percentComplete + '% uploaded');
                }
            }
            xhr.onload = function() {
                if (this.status == 200) {
                    try {
                    	JSON.parse(this.response);
                    } catch(e) {
                    	show_feedback('error','Kiểm tra dữ liệu file. File chưa đúng chuẩn hoặc file đang được sử dụng.', 'Lỗi file');
                    	that.loading = false;
                    	return;
                    }
                    var response = JSON.parse(this.response);
                    that.$emit('completed', response);
                    that.loading = false;
                }
            }
            setTimeout(function(){ xhr.send(fd);; }, 500);
        }
    },
    template: '#vue_upload_file'
});
</script>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>