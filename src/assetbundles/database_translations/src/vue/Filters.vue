<template>
    <div class="flex flex-nowrap">
        <div class="flex flex-nowrap">
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
        <div class="flex flex-nowrap">
            <button @click="save" class="btn submit">Save All</button>
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
            }
        },
        data: function() {
            return {
                categories: [],
                locales: [],
                category: '',
                missing: '',
                search: ''
            }
        },
    }
</script>
