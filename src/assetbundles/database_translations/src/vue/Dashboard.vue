<template>
    <table class="data fullwidth">
        <thead>
            <tr>
                <th v-for="item in columns" v-html="item.title" @click="orderBy(item.handle)" :class="{ 'ordered': item.handle == column, 'asc': item.handle == column && direction == 'asc', 'desc': item.handle == column && direction == 'desc' }"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="sourceMessage in sourceMessages">
                <td v-html="sourceMessage.category"></td>
                <td v-html="sourceMessage.message"></td>
                <td v-for="locale in locales">
                    <input class="text fullwidth" type="text" v-model="sourceMessage.messages[locale]">
                </td>
                <td v-html="sourceMessage.dateCreated"></td>
                <td v-html="sourceMessage.dateUpdated"></td>
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
            getColumns() {

                // Set the first two columns.
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

                // Set the last two columns.
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

                // Create an empty columns array.
                let columns = [];

                // Add the first two columns to the columns array.
                firstColumns.forEach((column) => {
                    columns.push(column)
                })

                // Add foreach locale a column to the columns array.
                this.locales.forEach((column) => {
                    columns.push({
                        title: column,
                        handle: column
                    })
                })

                // Add the last two columns to the columns array.
                lastColumns.forEach((column) => {
                    columns.push(column)
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

            // Listen to the emit 'emit-categories'.
            this.$root.$on('emit-categories', (categories) => {

                // Set the categories.
                this.categories = categories;
            })
        },
        data: function() {
            return {
                locales: [],
                columns: [],
                sourceMessages: [],
                direction: 'asc',
                column: 'category'
            }
        },
    }
</script>
