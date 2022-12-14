<template>
    <table class="data fullwidth">
        <thead>
            <tr>
                <th class="checkbox-cell selectallcontainer" role="checkbox" tabindex="0" style="width: 50px" @click="toggleSelectAllRows()">
                    <div class="checkbox" :class="checkedState()"></div>
                </th>
                <th
                    v-for="item in columns"
                    v-html="item.title"
                    @click="orderBy(item.handle)"
                    :class="{ 'ordered': item.handle === column, 'asc': item.handle === column && direction === 'asc', 'desc': item.handle === column && direction === 'desc' }"
                    :style="{ 'width': item.width }"
                ></th>
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
                <td v-html="sourceMessage.message" style="max-width: 200px;"></td>
                <td v-for="locale in selectedLocales">
                    <input class="text fullwidth" type="text" v-model="sourceMessage.messages[locale]">
                </td>
                <td v-html="sourceMessage.dateCreated" v-if="selectedColumns.includes('dateCreated')"></td>
                <td v-html="sourceMessage.dateUpdated" v-if="selectedColumns.includes('dateUpdated')"></td>
            </tr>
        </tbody>
    </table>
</template>

<script>
    // Our component exports
    export default {
        methods: {
            orderBy(column) {

                // Set the direction.
                this.direction = this.column == column ? this.direction == 'asc' ? 'desc' : 'asc' : 'asc';
                
                // Set the column
                this.column = column;

                // Fire the emit 'emit-order-by' with the column and the direction.
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
            checkedState() {
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

                // Set the first two columns.
                let firstColumns = [
                    {
                        title: 'Category',
                        handle: 'category',
                        width: '80px'
                    },
                    {
                        title: 'Message',
                        handle: 'message',
                        width: '250px'
                    }
                ];

                // Set the last two columns.
                let lastColumns = [
                    {
                        title: 'Created',
                        handle: 'dateCreated',
                        width: '200px'
                    },
                    {
                        title: 'Updated',
                        handle: 'dateUpdated',
                        width: '200px'
                    }
                ];

                // Create an empty columns array.
                let columns = [];

                // Add the first two columns to the columns array.
                firstColumns.forEach((column) => {
                    columns.push(column)
                })

                // Add foreach locale a column to the columns array.
                this.selectedLocales.forEach((column) => {
                    columns.push({
                        title: column,
                        handle: column
                    })
                })

                // Add the last two columns to the columns array.
                lastColumns.forEach((column) => {
                    if (this.selectedColumns.includes(column.handle)) {
                        columns.push(column)
                    }
                })

                // Set the columns.
                this.columns = columns;
            }
        },
        created: function() {

            // Listen to the emit 'emit-source-messages'.
            this.$root.$on('emit-source-messages', (sourceMessages) => {

                // Set the source messages.
                this.sourceMessages = sourceMessages;
            })

            // Listen to the emit 'emit-locales'.
            this.$root.$on('emit-locales', (locales) => {

                // Set the locales.
                this.locales = locales;

                // Get the columns.
                this.getColumns()
            })

            // Listen to the emit 'emit-selected-locales'.
            this.$root.$on('emit-selected-locales', (selectedLocales) => {

                // Set the search
                this.selectedLocales = selectedLocales;

                // Get the columns.
                this.getColumns()
            });

            // Listen to the emit 'emit-selected-locales'.
            this.$root.$on('emit-selected-columns', (selectedColumns) => {

                // Set the search
                this.selectedColumns = selectedColumns;

                // Get the columns.
                this.getColumns()
            });

            // Listen to the emit 'emit-categories'.
            this.$root.$on('emit-categories', (categories) => {

                // Set the categories.
                this.categories = categories;
            })
        },
        watch: {
            selectedRows: function (value) {
                this.$root.$emit('emit-selection-changed', value);
            }
        },
        data: function() {
            return {
                locales: [],
                selectedLocales: [],
                selectedColumns: [
                    'dateUpdated'
                ],
                columns: [],
                selectedRows: [],
                sourceMessages: [],
                direction: 'asc',
                column: 'category'
            }
        },
    }
</script>
