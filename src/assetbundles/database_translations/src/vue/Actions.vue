<template>
    <div class="flex flex-grow">
        <div id="count-container" class="light flex-grow">
        </div>
        <div class="flex">
            <button @click="actionExport" class="btn" :class="{ disabled: selectedRows.length === 0}" :disabled="selectedRows.length === 0">Export</button>
            <button @click="actionDelete" class="btn" :class="{ disabled: selectedRows.length === 0}" :disabled="selectedRows.length === 0">Delete</button>
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
            },
            actionExport() {
                this.$root.$emit('emit-action-export', this.selectedRows);
            },
            actionDelete() {
                if (confirm("Do you really want to delete "+this.selectedRows.length+" selected rows?")){
                    this.$root.$emit('emit-action-delete', this.selectedRows);
                }
            },
        },
        created: function() {
            // Listen to the emit 'emit-selection-changed'.
            this.$root.$on('emit-selection-changed', (selectedRows) => {
                this.selectedRows = selectedRows;
            })
        },
        data: function() {
            return {
                selectedRows: []
            }
        }
    }
</script>
