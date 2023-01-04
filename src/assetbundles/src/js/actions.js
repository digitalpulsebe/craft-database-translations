import Vue from 'vue';
import Actions from '../vue/Actions.vue';

// Create our vue instance
const vm = new Vue({
    el: "#actions",
    components: {
        'actions': Actions,
    }
});
