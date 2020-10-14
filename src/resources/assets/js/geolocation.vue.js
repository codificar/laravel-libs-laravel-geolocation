window.vue = require('vue');
require('lodash');
import Vue from 'vue';

// register the plugin on vue
import Toasted from 'vue-toasted';
Vue.use(Toasted);

import vSelect from "vue-select";
Vue.component("v-select", vSelect);
import "vue-select/dist/vue-select.css";

//Allows localization using trans()
Vue.prototype.trans = (key) => {
    return _.get(window.lang, key, key);
};
//Tells if an JSON parsed object is empty
Vue.prototype.isEmpty = (obj) => {
    return _.isEmpty(obj);
};

//Provider Company
import teste from './pages/teste.vue';
import GeoLocationSettings from './pages/Settings';
// import AddressAutocomplete from './components/AddressAutocomplete';

//Main vue instance
new Vue({
    el: '#VueJs',

    data: {
        
    },

    components: {
        teste: teste,
        geolocationsettings: GeoLocationSettings
    },

    created: function () {
        console.log("MOUNT VueJS");
    }
})