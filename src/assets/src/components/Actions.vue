<template>
    <div class="flex flex-grow">
        <div id="count-container" class="light flex-grow">
        </div>
        <div class="flex">
            <button @click="actionToggleTranslateHud"
                    id="translate-action-toggle-hud"
                    class="btn"
                    :class="{ disabled: this.store.selectedRows.length === 0, active: this.store.translateHudActive}"
                    :aria-expanded="this.store.translateHudActive === true"
                    :disabled="this.store.selectedRows.length === 0">
                Translate
            </button>
            <button @click="actionExport" class="btn" :class="{ disabled: this.store.selectedRows.length === 0}"
                    :disabled="this.store.selectedRows.length === 0">
                Export
            </button>
            <button @click="actionDelete" class="btn" :class="{ disabled: this.store.selectedRows.length === 0}"
                    :disabled="this.store.selectedRows.length === 0">
                Delete
            </button>
            <button @click="actionSave" class="btn submit">
                Save All
            </button>
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
        mounted() {
            document.addEventListener("keydown", this.handleKeyDown);
        },
        beforeDestroy() {
            document.removeEventListener("keydown", this.handleKeyDown);
        },
        methods: {
            handleKeyDown(e) {
                if (e.keyCode === 83 && (e.ctrlKey||e.metaKey)) {
                    e.preventDefault();
                    this.store.save();
                }
            },
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
            actionToggleTranslateHud() {
                this.store.translateHudActive = true;
                const toggleBtn = document.getElementById('translate-action-toggle-hud');

                const form = document.createElement("form");
                form.className = "translate-form";

                let sourceOptions = [
                    {label: '- select source -', value: ''},
                    {label: 'message', value: 'message'}
                ];
                let targetOptions = [
                    {label: '- select target -', value: ''}
                ];
                for (let i = 0; i < this.store.locales.length; i++) {
                    sourceOptions.push({
                        label: this.store.locales[i],
                        value: this.store.locales[i]
                    });
                    targetOptions.push({
                        label: this.store.locales[i],
                        value: this.store.locales[i]
                    });
                }

                const infoField = document.createElement("p")
                infoField.innerHTML = 'Translate using the <a href="https://plugins.craftcms.com/multi-translator" target="_blank">Multi Translator</a> plugin.';
                form.append(infoField);

                const sourceSelect = Craft.ui.createSelectField({
                    label: "Source",
                    options: sourceOptions,
                    class: "fullwidth",
                    required: true,
                }).appendTo(form);
                var targetSelect = Craft.ui.createSelectField({
                    label: "Target",
                    options: targetOptions,
                    class: "fullwidth",
                    required: true
                }).appendTo(form);

                var submitButton = Craft.ui.createSubmitButton({
                    class: "fullwidth",
                    label: Craft.t("app", "Translate"),
                    spinner: !0
                }).appendTo(form);
                const multiFunctionBtn = new Garnish.MultiFunctionBtn(submitButton);

                let store = this.store;

                const hud = new Garnish.HUD(toggleBtn, form);

                hud.on("hide", (function() {
                    store.translateHudActive = false;
                }));

                form.addEventListener("submit", (function(submitEvent) {
                    submitEvent.preventDefault();
                    const sourceLocale = sourceSelect.find("select").val();
                    const targetLocale = targetSelect.find("select").val();

                    if (sourceLocale === '' || targetLocale === '') {
                        // nothing
                    } else {
                        multiFunctionBtn.busyEvent();
                        store.translateHudActive = false;
                        hud.hide();

                        store.translate(sourceLocale, targetLocale)
                    }
                }));
            },
        }
    }
</script>
