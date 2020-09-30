window.vue = require('vue');
require('lodash');
import Vue from 'vue';

//Allows localization using trans()
Vue.prototype.trans = (key) => {
    return _.get(window.lang, key, key);
};
//Tells if an JSON parsed object is empty
Vue.prototype.isEmpty = (obj) => {
    return _.isEmpty(obj);
};


//Main vue instance
new Vue({
    el: '#VueJs',

    data: {
    },

    components: {
       
    },

    created: function () {
        console.log("MOUNT VueJS");
    }
})