<template>
    <div class="main">
        <div class="elements">
            <div class="tableview tablepane">
                <form id="translations-table" method="post" accept-charset="UTF-8" saveshortcut data-confirm-unload>
                    <table class="data fullwidth">
                        <thead>
                            <tr>
                                <th v-for="column in columns" v-html="column.title" @click="orderBy(column.handle)" :class="{ 'ordered': column.handle == order.column, 'asc': column.handle == order.column && order.direction == 'asc', 'desc': column.handle == order.column && order.direction == 'desc' }"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sourceMessage in sourceMessages">
                                <td>
                                    {{ sourceMessage.category }}
                                </td>
                                <td>
                                    {{ sourceMessage.message }}
                                </td>
                                    <td v-for="locale in locales">
                                        <input
                                            class="text fullwidth"
                                            type="text"
                                            value=""
                                        >
                                        <!-- <input
                                            class="text fullwidth"
                                            type="text"
                                            name="messages[{{ sourceMessage.id }}][{{ locale }}]"
                                            value="{{ sourceMessage.translation(locale) }}"
                                        > -->
                                    </td>
                                <td>
                                    {{ sourceMessage.dateCreated }}
                                </td>
                                <td>
                                    {{ sourceMessage.lastUpdated }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
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
            orderBy(column) {
                console.log(column)
            },
            getLocales() {
                JSON.parse(window.locales).forEach((item) => {
                    this.locales.push({
                        title: item,
                        handle: item
                    });
                })
            },
            getColumns() {
                let firstColumns = [
                    {
                        title: 'Category',
                        handle: 'category'
                    },
                    {
                        title: 'Message',
                        handle: 'message'
                    }
                ];
                let lastColumns = [
                    {
                        title: 'Created',
                        handle: 'created'
                    },
                    {
                        title: 'Updated',
                        handle: 'updated'
                    }
                ];

                let columns = [];

                firstColumns.forEach((column) => {
                    columns.push(column)
                })

                this.locales.forEach((column) => {
                    columns.push(column)
                })

                lastColumns.forEach((column) => {
                    columns.push(column)
                })

                this.columns = columns;
            },
            getSourceMessages() {
                // JSON.parse(window.sourceMessages).forEach((item) => {
                //     this.sourceMessages.push(item);
                // })

                this.sourceMessages = [{
                    'id': 1,
                    'category': 'site',
                    'message': 'Volgende',
                    'dateCreated': '2022-11-02 14:37:56',
                    'lastUpdated': '2022-11-02 14:37:56',
                },
                {
                    'id': 2,
                    'category': 'site',
                    'message': 'Vorige',
                    'dateCreated': '2022-11-02 14:37:59',
                    'lastUpdated': '2022-11-02 14:37:59',
                }]
            }
        },
        created: function() {
            this.getLocales();
            this.getColumns();
            this.getSourceMessages();
        },
        data: function() {
            return {
                locales: [],
                columns: [],
                sourceMessages: [],
                messages: [],
                order: {
                    column: 'category',
                    direction: 'asc'
                }
            }
        },
    }
</script>
