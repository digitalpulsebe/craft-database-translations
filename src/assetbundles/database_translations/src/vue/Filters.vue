<template>
    <div class="flex flex-nowrap">
        <div class="flex flex-nowrap">
            <div class="btn menubtn statusmenubtn">Columns</div>
            <div class="menu">
                <ul class="">
                    <li v-for="locale in locales" class="checkbox-menu-item">
                        <input :id="`selected-locale-${locale}`"
                               class="checkbox"
                               type="checkbox"
                               v-model="selectedLocales"
                               :value="locale"
                        >
                        <label :for="`selected-locale-${locale}`">Language: {{ locale }}</label>
                    </li>
                    <li v-for="column in columnOptions" :key="column.value" class="checkbox-menu-item">
                        <input :id="`selected-locale-${column.value}`"
                               class="checkbox"
                               type="checkbox"
                               v-model="selectedColumns"
                               :value="column.value"
                        >
                        <label :for="`selected-locale-${column.value}`">{{ column.label }}</label>
                    </li>
                </ul>
            </div>
            <div class="select">
                <select id="filter-category" v-model="category">
                    <option value="">Category</option>
                    <option v-for="item in categories" :value="item">{{ item }}</option>
                </select>
            </div>
            <div class="select">
                <select id="filter-category" v-model="missing">
                    <option value="">Filter missing</option>
                    <option v-for="item in locales" :value="item">{{ item }}</option>
                </select>
            </div>
            <div class="flex-grow texticon search icon clearable">
                <input type="text" class="text fullwidth" placeholder="search..." v-model="search" autocomplete="off">
            </div>
            <button @click="reset" class="btn submit">Reset</button>
        </div>
    </div>
</template>

<script>
    // Our component exports
    import {debounce} from "debounce";

    export default {
        methods: {
            reset() {

                // Reset the category.
                this.category = '';

                // Reset the search.
                this.search = '';
            },
            save() {

                // Fire the emit 'emit-update'..
                this.$root.$emit('emit-update');
            },
            handleSearch(value) {
              // Fire the emit 'emit-search' with the value.
              this.$root.$emit('emit-search', value);
            }
        },
        mounted: function() {

            // Listen to the emit 'emit-categories'.
            this.$root.$on('emit-categories', (categories) => {
                // Set the categories.
                this.categories = categories;
            })
            this.$root.$on('emit-locales', (locales) => {
                // Set the categories.
                this.locales = locales;
                this.selectedLocales = locales;
            })
        },
        created: function() {
          this.handleSearch = debounce(this.handleSearch, 400);
        },
        watch: {
            search: function(value) {
              this.handleSearch(value);
            },
            category: function(value) {
                // Fire the emit 'emit-filter category' with the value.
                this.$root.$emit('emit-filter-category', value);
            },
            missing: function(value) {
                // Fire the emit 'emit-filter category' with the value.
                this.$root.$emit('emit-filter-missing', value);
            },
            selectedLocales: function(value) {
                // Fire the emit 'emit-selected-locales' with the value.
                this.$root.$emit('emit-selected-locales', value);
            },
            selectedColumns: function(value) {
                // Fire the emit 'emit-selected-columns' with the value.
                this.$root.$emit('emit-selected-columns', value);
            }
        },
        data: function() {
            return {
                categories: [],
                locales: [],
                selectedLocales: [],
                selectedColumns: [
                    'dateUpdated'
                ],
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
