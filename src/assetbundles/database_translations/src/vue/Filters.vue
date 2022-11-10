<template>
    <div class="flex flex-nowrap">
        <div class="flex flex-nowrap">
            <div class="select">
                <select id="filter-category" v-model="category">
                    <option value="">Category</option>
                    <option v-for="item in categories" :value="item">{{ item }}</option>
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
    export default {
        components: {
        },
        props: {
        },
        methods: {
            reset() {
                this.category = '';
                this.search = '';
            },
            save() {
                this.$root.$emit('emit-update');
            }
        },
        created: function() {
        },
        mounted: function() {
            this.$root.$on('emit-categories', (categories) => {
                this.categories = categories;
            })
        },
        watch: {
            search: function(value) {
                this.$root.$emit('emit-search', value);
            },
            category: function(value) {
                this.$root.$emit('emit-filter-category', value);
            }
        },
        data: function() {
            return {
                categories: [],
                category: '',
                search: ''
            }
        },
    }
</script>
