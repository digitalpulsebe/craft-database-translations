import { defineStore } from 'pinia'
import axios from "axios";

export const useDashboardStore = defineStore('dashboard', {
    state: () => (
        {
        locales: [],
        selectedLocales: [],
        selectedColumns: [
            'category',
            'message',
            'dateUpdated'
        ],
        columns: [],
        filters: {
            order: 'message asc'
        },
        categories: [],
        selectedRows: [],
        sourceMessages: [],
        isBusy: true,
        direction: 'asc',
        column: 'message'
    }),
    mounted: () => {
        console.log(this)
    },
    actions: {
        getData() {
            let self = this;

            self.isBusy = true;

            // Post the data with filters.
            axios.post('database-translations/api/index', {
                filters: this.filters
            }).then(function (response) {
                let sourceMessages = response.data.sourceMessages;
                for (const i in sourceMessages) {
                    if (sourceMessages[i].messages.length === 0) {
                        // make it an object when empty
                        sourceMessages[i].messages = {};
                    }
                    for (const j in response.data.locales) {
                        const locale = response.data.locales[j];
                        if (!sourceMessages[i].messages.hasOwnProperty(locale)) {
                            // fill unknown translations with null
                            sourceMessages[i].messages[locale] = null;
                        }
                    }
                }

                self.sourceMessages = sourceMessages;
                self.locales = response.data.locales;
                self.categories = response.data.categories;

                if (self.selectedLocales.length === 0) {
                    // first time
                    self.selectedLocales = self.locales;
                } else {
                    // check if all selectedLocales still exist
                    for (const i in self.selectedLocales) {
                        if (!self.locales.includes(self.selectedLocales[i])) {
                            // language must have been removed
                            self.selectedLocales.splice(i,1);
                            localStorage.setItem('selectedLocales', JSON.stringify(self.selectedLocales));
                        }
                    }
                }
            })
            .catch(function (error) {
                alert(error);
            })
            .finally(function () {
                self.isBusy = false;
            });
        },
        save() {
            let self = this;
            self.isBusy = true;

            let messages = {}

            // Loop over the source messages.
            self.sourceMessages.forEach((sourceMessage) => {
                // Create a message variable.
                let message = {};
                // Loop over the messages in the source message and assign the translation to the message that matches the language.
                for (const locale in sourceMessage.messages) {
                    message[locale] = sourceMessage.messages[locale];
                }
                // Add the message to messages based on the id.
                messages[sourceMessage.id] = message;
            })

            // Post the data.
            axios.post('database-translations/translations/update', {
                messages: messages
            })
            .then(function (response) {
                window.Craft.cp.displayNotice('Save successful');
            })
            .catch(function (error) {
                window.Craft.cp.displayError(error);
                console.log(error);
            })
            .finally(function () {
                self.isBusy = false;
            });
        },
        export() {
            let self = this;
            axios.post('database-translations/translations/export', {
                filters: {id: self.selectedRows}
            })
            .then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data.file]))
                const link = document.createElement('a')
                link.href = url
                link.setAttribute('download', response.data.fileName)
                document.body.appendChild(link)
                link.click()
            })
            .catch((error) => window.Craft.cp.displayError(error));
        },
        delete() {
            let self = this;
            this.isBusy = true;

            axios.post('database-translations/translations/delete', {
                messages: self.selectedRows,
            })
            .then(function (response) {
                self.getData();
                window.Craft.cp.displayNotice('Row(s) deleted');
            })
            .catch(function (error) {
                console.log(error);
                window.Craft.cp.displayError(error);
            })
        }
    }
})