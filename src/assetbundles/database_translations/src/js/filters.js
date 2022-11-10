import Vue from 'vue';
import Filters from '../vue/Filters.vue';

// Create our vue instance
const vm = new Vue({
    el: "#filters",
    components: {
        'filters': Filters,
    }
});
