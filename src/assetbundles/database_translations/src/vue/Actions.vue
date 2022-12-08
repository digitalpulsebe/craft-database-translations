<template>
    <div class="flex flex-grow">
        <div id="count-container" class="light flex-grow">
            Pagination
            <!-- {% include '_includes/pagination' %} -->
        </div>
        <div class="flex">
            <button type="button" id="export-btn" class="btn">Export</button>
            <button @click="save" class="btn submit">Save All</button>
        </div>
    </div>
</template>

<script>
    // Our component exports
    export default {
        methods: {
            save() {
                this.$root.$emit('emit-update');
            }
        }
    }
</script>
