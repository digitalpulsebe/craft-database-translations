<template>
    <div class="flex flex-grow">
        <div id="count-container" class="light flex-grow">
        </div>
        <div class="flex">
            <button @click="actionExport" class="btn" :class="{ disabled: this.store.selectedRows.length === 0}" :disabled="this.store.selectedRows.length === 0">Export</button>
            <button @click="actionDelete" class="btn" :class="{ disabled: this.store.selectedRows.length === 0}" :disabled="this.store.selectedRows.length === 0">Delete</button>
            <button @click="actionSave" class="btn submit">Save All</button>
        </div>
    </div>
</template>

<script>
    import {useDashboardStore} from "../js/store";

    export default {
        setup() {
            const store = useDashboardStore();
            return { store }
        },
        methods: {
            actionSave() {
                this.store.save()
            },
            actionExport() {
                this.store.export()
            },
            actionDelete() {
                if (confirm("Do you really want to delete "+this.store.selectedRows.length+" selected rows?")){
                    this.store.delete()
                }
            },
        }
    }
</script>
