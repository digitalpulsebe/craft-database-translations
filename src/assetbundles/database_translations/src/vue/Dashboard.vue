<template>
    <div class="main">
        <div class="elements">
            <div class="tableview tablepane">
                <form id="translations-table" method="post" accept-charset="UTF-8" saveshortcut data-confirm-unload>
                    <table class="data fullwidth">
                        <thead>
                            <tr>
                                <th class="checkbox-cell selectallcontainer" role="checkbox" tabindex="0" style="width: 4%" @click="toggleSelectAllRows()">
                                  <div
                                      class="checkbox"
                                      :class="checkedState()"
                                  ></div>
                                </th>
                                <th v-for="item in columns" v-html="item.title" @click="orderBy(item.handle)" :class="{ 'ordered': item.handle == column, 'asc': item.handle == column && direction == 'asc', 'desc': item.handle == column && direction == 'desc' }"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sourceMessage in sourceMessages">
                                <td class="checkbox-cell">
                                    <input :id="'source-message-' + sourceMessage.id" type="checkbox" class="checkbox"
                                           title="Select"
                                           :value="sourceMessage.id"
                                           v-model="selectedRows">
                                    <label :for="'source-message-' + sourceMessage.id"></label>
                                </td>
                                <td v-html="sourceMessage.category"></td>
                                <td v-html="sourceMessage.message"></td>
                                <td v-for="locale in locales">
                                    <input v-if="sourceMessage.messages.length > 0 && sourceMessage.messages.filter(message => message.language == locale).length > 0" class="text fullwidth" type="text" v-model="sourceMessage.messages.filter(message => message.language == locale)[0]['translation']">
                                    <input v-else class="text fullwidth" type="text" value="">
                                </td>
                                <td v-html="sourceMessage.dateCreated"></td>
                                <td v-html="sourceMessage.dateUpdated"></td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

// Our component exports
export default {
    components: {
    },
    props: {
    },
    methods: {
        orderBy(column) {
            this.direction = this.column == column ? this.direction == 'asc' ? 'desc' : 'asc' : 'asc';
            this.column = column;
            this.$root.$emit('emit-order-by', this.column, this.direction);
        },
        toggleSelectAllRows() {
            if (this.selectedRows.length > 0) {
                this.selectedRows = [];
            } else {
                for (const sourceMessage of this.sourceMessages) {
                    this.selectedRows.push(sourceMessage.id);
                }
            }
        },
        checkedState () {
            if (this.selectedRows.length > 0) {
                if (this.selectedRows.length === this.sourceMessages.length) {
                    return 'checked';
                } else {
                    return 'indeterminate';
                }
            }
            return '';
        },
        getColumns() {
            let firstColumns = [
                {
                    title: 'Category',
                    handle: 'category'
                },
                {
                    title: 'Message',
                    handle: 'message'
                }
            ];
            let lastColumns = [
                {
                    title: 'Created',
                    handle: 'dateCreated'
                },
                {
                    title: 'Updated',
                    handle: 'dateUpdated'
                }
            ];

            let columns = [];

            firstColumns.forEach((column) => {
                columns.push(column)
            })

            this.locales.forEach((column) => {
                columns.push({
                    title: column,
                    handle: 'language_' + column
                })
            })

            lastColumns.forEach((column) => {
                columns.push(column)
            })

            this.columns = columns;
        },
        getSourceMessages() {
            let self = this;
            axios.get('database-translations/api/index')
            .then(function (response) {
                self.sourceMessages = response.data.sourceMessages;
                self.locales = response.data.locales;
                self.getColumns();
            })
            .catch(function (error) {
                // handle error
                console.log(error);
            })
            .then(function () {
                // always executed
            });
        },
        updateData() {
            // database-translations/translations/update
        }
    },
    created: function() {
        this.$root.$on('emit-source-messages', (sourceMessages) => {
            this.sourceMessages = sourceMessages;
        })

        this.$root.$on('emit-locales', (locales) => {
            this.locales = locales;

            this.getColumns()
        })

        this.$root.$on('emit-categories', (categories) => {
            this.categories = categories;
        })
    },
    data: function() {
        return {
            locales: [],
            columns: [],
            selectedRows: [],
            sourceMessages: [],
            direction: 'asc',
            column: 'category'
        }
    },
}
</script>
