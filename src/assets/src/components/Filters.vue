<template>
    <div class="flex flex-nowrap flex-grow">
        <div class="btn menubtn statusmenubtn">Columns</div>
        <div class="menu">
            <ul style="padding: 2px">
                <li v-for="locale in store.locales" style="padding: 3px 0">
                    <input :id="`selected-locale-${locale}`"
                           class="checkbox"
                           type="checkbox"
                           v-model="store.selectedLocales"
                           :value="locale"
                    >
                    <label :for="`selected-locale-${locale}`">Language: {{ locale }}</label>
                </li>
                <li v-for="column in columnOptions" :key="column.value" style="padding: 5px 2px">
                    <input :id="`selected-locale-${column.value}`"
                           class="checkbox"
                           type="checkbox"
                           v-model="store.selectedColumns"
                           :value="column.value"
                    >
                    <label :for="`selected-locale-${column.value}`">{{ column.label }}</label>
                </li>
            </ul>
        </div>
        <div class="select">
            <select id="filter-category" v-model="category">
                <option value="">Category</option>
                <option v-for="item in store.categories" :value="item">{{ item }}</option>
            </select>
        </div>
        <div class="select">
            <select id="filter-category" v-model="missing">
                <option value="">Filter missing</option>
                <option v-for="item in store.locales" :value="item">{{ item }}</option>
            </select>
        </div>
        <div class="flex-grow texticon search icon clearable">
            <input type="text" class="text fullwidth" placeholder="search..." v-model="search" autocomplete="off">
        </div>
        <button @click="reset" class="btn submit">Reset</button>
    </div>
</template>

<script>
    import {debounce} from "debounce";
    import {useDashboardStore} from "../js/store";

    export default {
        setup() {
            const store = useDashboardStore();

            return { store }
        },
        methods: {
            reset() {
                this.category = '';
                this.search = '';
                this.missing = '';
            },
            handleSearch(value) {
                this.store.filters.search = value;
                this.store.getData();
            }
        },
        mounted: function() {
            if (localStorage.getItem('selectedSearch')) {
                this.search = localStorage.getItem('selectedSearch');
                this.search = localStorage.getItem('selectedSearch');
            }
            if (localStorage.getItem('selectedCategory')) {
                this.category = localStorage.getItem('selectedCategory');
            }
        },
        created: function() {
          this.handleSearch = debounce(this.handleSearch, 400);
        },
        watch: {
            search: function(value) {
                localStorage.setItem('selectedSearch', value);
                this.handleSearch(value);
            },
            category: function(value) {
                localStorage.setItem('selectedCategory', value);
                this.store.filters.category = value;
                this.store.getData();
            },
            missing: function(value) {
                this.store.filters.missing = value;
                this.store.getData();
            },
            'store.selectedLocales': function(value) {
                localStorage.setItem('selectedLocales', JSON.stringify(value));
            },
            'store.selectedColumns': function(value) {
                localStorage.setItem('selectedColumns', JSON.stringify(value));
            }
        },
        data: function() {
            return {
                columnOptions: [
                    {'label': 'Date Created','value': 'dateCreated'},
                    {'label': 'Date Updated','value': 'dateUpdated'}
                ],
                category: '',
                missing: '',
                search: ''
            }
        },
    }
</script>
