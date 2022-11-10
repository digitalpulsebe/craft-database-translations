import Vue from 'vue';
import Filters from '../vue/Filters.vue';
import Dashboard from '../vue/Dashboard.vue';

// Create our vue instance
const vm = new Vue({
    el: "#main",
    components: {
        'dashboard': Dashboard,
        'filters': Filters,
    },
    methods: {
        updateData() {
            // To do
            console.log('update the source messages')
            // let self = this;
            // axios.post('database-translations/api/update', {
            // })
            // .then(function (response) {
            //     console.log(response.data);
            // })
            // .catch(function (error) {
            //     // handle error
            //     console.log(error);
            // })
            // .then(function () {
            //     // always executed
            // });
        },
        getData() {
            let self = this;
            axios.post('database-translations/api/index', {
                filters: {
                    order: this.column + ' ' + this.direction,
                    category: this.category,
                    search: this.search
                }
            })
            .then(function (response) {
                self.$root.$emit('emit-source-messages', response.data.sourceMessages);
                self.$root.$emit('emit-locales', response.data.locales);
                self.$root.$emit('emit-categories', response.data.categories);
            })
            .catch(function (error) {
                // handle error
                console.log(error);
            })
            .then(function () {
                // always executed
            });
        },
    },
    created: function() {
        this.getData(this.column, this.direction);

        this.$root.$on('emit-order-by', (column, direction) => {
            this.column = column;
            this.direction = direction;
            this.getData();
        });

        this.$root.$on('emit-filter-category', (category) => {
            this.category = category;
            this.getData();
        });

        this.$root.$on('emit-search', (search) => {
            this.search = search;
            this.getData();
        });

        this.$root.$on('emit-update', (search) => {
            this.updateData();
        });

    },
    data: function() {
        return {
            direction: 'asc',
            column: 'category',
            search: '',
            category: ''
        }
    },
});
