<style>
.vue-autocomplete {
    position: relative;
}
.vue-autocomplete .form-group {
    margin-bottom: 0px !important;
}

.vue-autocomplete .autocomplete-list {
    position: absolute;
    top: 40px;
    width: 100%;
    z-index: 1000;
}

.vue-autocomplete .list-group-item {
    margin-top: -1px;
    border-radius: 0px;
    cursor: pointer;
    padding: 10px;
}

.vue-autocomplete .list-group-item.is-active, .vue-autocomplete .list-group-item:hover {
    background-color: #75b6ed;
    border-color: #75b6ed;
    color: white;
}

.autocomplete-loader {
    position: absolute;
    top: 9px;
    right: 9px;
  border: 11px solid #f3f3f3;
  border-radius: 50%;
  border-top: 11px solid #3498db;
  width: 23px;
  height: 23px;
  -webkit-animation: spin 2s linear infinite; /* Safari */
  animation: spin 2s linear infinite;
}

/* Safari */
@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
<script type="text/x-template" id="vue_autocomplete">
<div class="vue-autocomplete">
    <div class="col-md-12">
        <div class="form-group">
            <input type="text" name="autocomplete-query" value="" class="form-control" v-model="search" v-bind:placeholder="placeholder"
                @keyup.down="on_arrow_down"
                @keyup.up="on_arrow_up"
                @keyup.enter="on_enter"
                @input="on_change">
            <div v-show="is_loading" class="autocomplete-loader"></div>
        </div>
        <div v-show="is_open" class="autocomplete-list form-group">
            <ul class="list-group">
                <li v-for="(item, i) in items" v-bind:class="{ 'is-active': i === arrow_counter }" @click="set_result(item)" class="list-group-item">{{item.label}}</li>
            </ul>
        </div>
    </div>
</div>
</script>

<script type="text/javascript">
Vue.component('autocomplete', {
    data: function() {
        return {
            search: '',
            selected: '',
            is_loading: false,
            is_open: false,
            items: [],
            arrow_counter: -1
        }
    },
    props: {
        resource: {
            type: String,
            required: true
        },
        placeholder: {
            type: String,
            default: 'Enter item name or scan barcode'
        },
        refresh: {
            type: Boolean,
            default: false
        },
        options: {
            required: false,
            type: Object,
            default: function(){
                return {}
            }
        },
    },
    watch: {
        search: function() {
            
        }
    },
    created: function(){
        if (typeof this.options.parent_id == 'undefined') {
            this.options.parent_id = '-1';
        }
    },
    mounted() {
        document.addEventListener('click', this.handle_click_outside)
    },
    destroyed() {
        document.removeEventListener('click', this.handle_click_outside)
    },
    methods: {
        on_change: function() {
            if (this.search.length > 0) {
                const that = this;
                that.is_loading = true;
                var url = this.resource + '?term=' + this.search;
                if (this.resource.indexOf('?') > -1) {
                    url = this.resource + '&term=' + this.search;
                }
                axios.get(url).then(function(response){
                    that.is_open = true;
                    that.items = response.data;
                    that.is_loading = false;
                });
            } else {
                this.is_open = false;
            }
        },
        on_arrow_down: function() {
            if (this.arrow_counter < this.items.length-1) {
                this.arrow_counter = this.arrow_counter + 1;
            }
        },
        on_arrow_up: function() {
            if (this.arrow_counter > 0) {
                this.arrow_counter = this.arrow_counter - 1;
            }
        },
        on_enter: function() {
            this.search = this.items[this.arrow_counter].label;
            this.items[this.arrow_counter].el_parent_id = this.options.parent_id;
            this.$emit('onselected', this.items[this.arrow_counter]);
            this.is_open = false;
            this.arrow_counter = -1;
            if (this.refresh) {
                this.search = '';
            }
        },
        set_result: function(item) {
            this.search = item.label;
            this.is_open = false;
            this.arrow_counter = -1;
            this.selected = item;
            item.el_parent_id = this.options.parent_id;
            this.$emit('onselected', item);
            if (this.refresh) {
                this.search = '';
            }
        },
        handle_click_outside: function(evt) {
            if (!this.$el.contains(evt.target)) {
                this.is_open = false;
                this.arrow_counter = -1;
            }
        }
    },
    template: '#vue_autocomplete'
});
</script>