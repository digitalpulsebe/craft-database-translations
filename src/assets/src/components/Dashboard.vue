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
                        class="orderable"
                        :class="{ 'ordered': item.handle === store.column, 'asc': item.handle === store.column && store.direction === 'asc', 'desc': item.handle === store.column && store.direction === 'desc' }"
                        :style="{ 'width': item.width, 'min-width': item.minWidth }"
                    >
                        <button v-if="item.handle === 'message'" v-html="item.title" style="resize: horizontal; min-width: 100px; overflow: auto;"></button>
                        <button v-else v-html="item.title"></button>
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
                    <td v-html="sourceMessage.category" v-if="store.selectedColumns.includes('category')"></td>
                    <td v-html="sourceMessage.message" style="max-width: 200px;" v-if="store.selectedColumns.includes('message')"></td>
                    <template v-for="locale in store.locales">
                        <td v-if="store.selectedLocales.includes(locale)">
                            <textarea class="text fullwidth text-auto-size" v-model="sourceMessage.messages[locale]" rows="1" @input="autosizeTextarea" @click="autosizeRowTextareas" style="resize: none"></textarea>
                        </td>
                    </template>
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
            autosizeRowTextareas(e) {
                const row = e.target.parentNode.parentNode;
                const textareas = row.getElementsByClassName('text-auto-size')
                for (var i = 0; i < textareas.length; i++) {
                    let textarea = textareas[i];
                    textarea.style.height = 0;
                    textarea.style.height = (textarea.scrollHeight) + 2 + "px";
                }
            },
            autosizeTextarea(e) {
                let textarea = e.target;
                textarea.style.height = 0;
                textarea.style.height = (textarea.scrollHeight) + 2 + "px";
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
                        width: '270px',
                        minWidth: '270px',
                    }
                ];

                // Set the last two columns.
                let lastColumns = [
                    {
                        title: 'Created',
                        handle: 'dateCreated',
                        width: '180px',
                        minWidth: '180px',
                    },
                    {
                        title: 'Updated',
                        handle: 'dateUpdated',
                        width: '180px',
                        minWidth: '180px',
                    }
                ];

                // Create an empty columns array.
                let columns = [];

                // Add the first two columns to the columns array.
                firstColumns.forEach((column) => {
                    if (this.store.selectedColumns.includes(column.handle)) {
                        columns.push(column)
                    }
                })

                // Add foreach locale a column to the columns array.
                this.store.locales.forEach((column) => {
                    if (this.store.selectedLocales.includes(column)) {
                        columns.push({
                            title: column,
                            handle: column,
                            minWidth: '250px',
                        })
                    }
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
