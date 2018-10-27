import VueDraggable from '../../../vue/modules/vue-draggable/index.js';
Vue.use(VueDraggable);
var app = new Vue({
    el: '#content',
    
    data: function() {
        return {
        	options: {
        		dropzoneSelector: 'ul',
        		draggableSelector: 'li',
        		itemList: {
        			'target': [],
        			'owner': [
            			{'id': '1', 'label': 'item 1'},
            			{'id': '2', 'label': 'item 2'},
            			{'id': '3', 'label': 'item 3'},
            			{'id': '4', 'label': 'item 4'},
            			{'id': '5', 'label': 'item 5'},
            			{'id': '45', 'label': 'item 45'},
            			{'id': '35', 'label': 'item 35'},
            			{'id': '25', 'label': 'item 25'},
            			{'id': '15', 'label': 'item 15'}
            		]
        		},
        		excludeOlderBrowsers: true,
        		multipleDropzonesItemsDraggingEnabled: true,
        		onDrop: this.onDrop,
        		onDragstart: this.onDragstart,
        		onDragend: this.onDragend,
        	},
            selected_item: {},
            resource: SITE_URL + 'items/suggest',
            upload_url: SITE_URL + 'departments/upload',
        }
    },
    created: function(){
        var vueObject = JSON.parse(VUE_OBJECT);
    },
    methods: {
        onDrop: function(event) {
        	console.log(event);
        },
        onDragstart: function(event) {
        	
        },
        onDragend: function(event) {
        	
        },
        on_selected: function(selected) {
            this.selected_item = selected;
        },
        
        upload_completed: function(response) {
            console.log(response);
        }
    }
})