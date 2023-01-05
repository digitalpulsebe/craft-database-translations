<template>
    <div class="elements" :class="{'busy': store.isBusy}">
        <div class="tableview tablepane">
            <table class="data fullwidth">
                <thead>
                <tr>
                    <th class="checkbox-cell selectallcontainer" role="checkbox" tabindex="0" style="width: 50px" @click="toggleSelectAllRows()">
                        <div class="checkbox" :class="checkedState()"></div>
                    </th>
                    <th
                        v-for="item in getColumns()"
                        @click="orderBy(item.handle)"
                        :class="{ 'ordered': item.handle === store.column, 'asc': item.handle === store.column && store.direction === 'asc', 'desc': item.handle === store.column && store.direction === 'desc' }"
                        :style="{ 'width': item.width }"
                    >
                        <div v-if="item.handle === 'message'" v-html="item.title" style="resize: horizontal; min-width: 100px; overflow: auto;"></div>
                        <div v-else v-html="item.title"></div>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="sourceMessage in store.sourceMessages">
                    <td class="checkbox-cell">
                        <input :id="'source-message-' + sourceMessage.id" type="checkbox" class="checkbox"
                               title="Select"
                               :value="sourceMessage.id"
                               v-model="store.selectedRows">
                        <label :for="'source-message-' + sourceMessage.id"></label>
                    </td>
                    <td v-html="sourceMessage.category"></td>
                    <td v-html="sourceMessage.message" style="max-width: 200px;"></td>
                    <td v-for="locale in store.selectedLocales">
                        <textarea class="text fullwidth" v-model="sourceMessage.messages[locale]" rows="1" style="resize: vertical"></textarea>
                    </td>
                    <td v-html="sourceMessage.dateCreated" v-if="store.selectedColumns.includes('dateCreated')"></td>
                    <td v-html="sourceMessage.dateUpdated" v-if="store.selectedColumns.includes('dateUpdated')"></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    import {useDashboardStore} from "../js/store";

    export default {
        setup() {
            const store = useDashboardStore();

            // read localstorage
            if (localStorage.getItem('selectedLocales')) {
                store.selectedLocales = JSON.parse(localStorage.getItem('selectedLocales'));
            }
            if (localStorage.getItem('selectedColumns')) {
                store.selectedColumns = JSON.parse(localStorage.getItem('selectedColumns'));
            }
            if (localStorage.getItem('selectedSearch')) {
                store.filters.search = localStorage.getItem('selectedSearch');
            }
            if (localStorage.getItem('selectedCategory')) {
                store.filters.category = localStorage.getItem('selectedCategory');
            }

            store.getData();
            return { store }
        },
        methods: {
            orderBy(column) {
                // Set the direction.
                const direction = this.store.column === column ? this.store.direction === 'asc' ? 'desc' : 'asc' : 'asc'
                this.store.direction = direction;
                // Set the column
                this.store.column = column;
                this.store.filters.order = column + ' ' + direction;
                this.store.getData();
            },
            toggleSelectAllRows() {
                if (this.store.selectedRows.length > 0) {
                    this.store.selectedRows = [];
                } else {
                    for (const sourceMessage of this.store.sourceMessages) {
                        this.store.selectedRows.push(sourceMessage.id);
                    }
                }
            },
            checkedState() {
                if (this.store.selectedRows.length > 0) {
                    if (this.store.selectedRows.length === this.store.sourceMessages.length) {
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
                this.store.selectedLocales.forEach((column) => {
                    columns.push({
                        title: column,
                        handle: column
                    })
                })

                // Add the last two columns to the columns array.
                lastColumns.forEach((column) => {
                    if (this.store.selectedColumns.includes(column.handle)) {
                        columns.push(column)
                    }
                })

                // Set the columns.
                return columns;
            }
        }
    }
</script>
