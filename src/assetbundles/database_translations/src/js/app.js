import Vue from 'vue';
import Filters from '../vue/Filters.vue';
import Dashboard from '../vue/Dashboard.vue';
import Actions from '../vue/Actions.vue';

// Create our vue instance
const vm = new Vue({
    el: "#main",
    components: {
        'dashboard': Dashboard,
        'filters': Filters,
        'actions': Actions
    },
    methods: {
        updateData(messages) {

            let self = this;

            // Post the data.
            axios.post('admin/database-translations/translations/update', {
                messages: messages
            })

            // Catch the response.
            .then(function (response) {
                
                // Set the source messages
                self.sourceMessages = response.data.sourceMessages;
            })

            // Catch the error.
            .catch(function (error) {

                // Handle the error.
                console.log(error);
            });
        },
        getData() {

            let self = this;

            // Post the data with filters.
            axios.post('database-translations/api/index', {
                filters: this.filters
            })

            // Catch the response.
            .then(function (response) {

                // Set the source messages
                self.sourceMessages = response.data.sourceMessages;

                for (const i in self.sourceMessages) {
                    if (self.sourceMessages[i].messages.length === 0) {
                        // make it an object when empty
                        self.sourceMessages[i].messages = {};
                    }
                    for (const j in response.data.locales) {
                        const locale = response.data.locales[j];
                        if (!self.sourceMessages[i].messages.hasOwnProperty(locale)) {
                            // fill unknown translations with null
                            self.sourceMessages[i].messages[locale] = null;
                        }
                    }
                }

                // Fire the emit 'emit-source-messages' with the source messages.
                self.$root.$emit('emit-source-messages', self.sourceMessages);

                // Fire the emit 'emit-locales' with the locales.
                self.$root.$emit('emit-locales', response.data.locales);

                // Fire the emit 'emit-categories' with the categories.
                self.$root.$emit('emit-categories', response.data.categories);
            })

            // Catch the error.
            .catch(function (error) {

                // Handle the error.
                console.log(error);
            });
        },
        actionDelete(ids) {
            axios.post('database-translations/translations/delete', {
                messages: ids,
                filters: this.filters
            })
            .then(function (response) {
                console.log(response.data);
            })
            .catch(function (error) {
                console.log(error);
            });

            this.getData();
        },
        actionExport(ids) {
            axios.post('database-translations/translations/export', {
                filters: {id: ids}
            })
            .then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data.file]))
                const link = document.createElement('a')
                link.href = url
                link.setAttribute('download', response.data.fileName)
                document.body.appendChild(link)
                link.click()
            })
            .catch(() => console.log('error occured'));
        }
    },
    created: function() {

        // Get the data.
        this.getData();

        // Listen to the emit 'emit-order-by'.
        this.$root.$on('emit-order-by', (column, direction) => {

            // set the column and direction
            this.filters.order = column + ' ' + direction

            // Get the data.
            this.getData();
        });

        // Listen to the emit 'emit-filter-category'.
        this.$root.$on('emit-filter-category', (category) => {

            // Set the category.
            this.filters.category = category;

            // Get the data.
            this.getData();
        });

        // Listen to the emit 'emit-search'.
        this.$root.$on('emit-search', (search) => {

            // Set the search
            this.filters.search = search;

            // Get the data.
            this.getData();
        });

        // Listen to the emit 'emit-filter-missing'.
        this.$root.$on('emit-filter-missing', (missing) => {

            // Set the search
            this.filters.missing = missing;

            // Get the data.
            this.getData();
        });

        // Listen to the emit 'emit-update'.
        this.$root.$on('emit-update', () => {

            // Create messages object.
            let messages = {}

            // Loop over the source messages.
            this.sourceMessages.forEach((sourceMessage) => {

                // Create a message variable.
                let message = {};

                // Loop over the messages in the source message and assign the translation to the message that matches the language.
                for (const locale in sourceMessage.messages) {
                    message[locale] = sourceMessage.messages[locale];
                }

                // Add the message to messages based on the id.
                messages[sourceMessage.id] = message;
            })

            // Fire the update function.
            this.updateData(messages);
        });

        // Listen to bulk actions
        this.$root.$on('emit-action-export', this.actionExport);
        this.$root.$on('emit-action-delete', this.actionDelete);

    },
    data: function() {
        return {
            sourceMessages: [],
            filters: {
                'order': 'category asc',
            },
        }
    },
});
