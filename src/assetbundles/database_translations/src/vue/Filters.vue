<template>
    <!-- <form action="" method="get" class="flex flex-nowrap" enctype="application/x-www-form-urlencoded" accept-charset="UTF-8">
        <div class="select">
            <select name="filters[category]" id="filter-category" onchange="this.form.submit()">
                <option value="">Category</option>
                {% for category in craft.databaseTranslations.categories %}
                    <option value="{{ category }}" {{ (filters.category|default(null) == category) ? 'selected' : '' }}>{{ category }}</option>
                {% endfor %}
            </select>
        </div>
        <div class="select">
            <select name="filters[missing]" id="filter-missing" onchange="this.form.submit()">
                <option value="">Filter missing</option>
                {% for locale in locales %}
                    <option value="{{ locale }}" {{ (filters.missing|default(null) == locale) ? 'selected' : '' }}>{{ locale }}</option>
                {% endfor %}
            </select>
        </div>
        <div class="flex-grow texticon search icon clearable">
            <input type="text" class="text fullwidth" placeholder="search..." name="filters[search]" value="{{ filters.search|default(null) }}" autocomplete="off">
        </div>
        <div class="select">
            <select name="filters[order]" id="filter-missing" onchange="this.form.submit()">
                <option value="">Order by</option>
                <optgroup label="Translations">
                    {% for locale in locales %}
                        <option value="{{ locale }}" {{ (filters.order|default(null) == locale) ? 'selected' : '' }}>{{ locale }}</option>
                    {% endfor %}
                </optgroup>
                <optgroup label="Columns">
                    {% set options = {
                        "category": "Category",
                        "message": "Message",
                        "dateCreated": "Date Created Asc.",
                        "dateUpdated": "Date Updated Asc.",
                        "dateCreated desc": "Date Created Desc.",
                        "dateUpdated desc": "Date Updated Desc.",
                    } %}
                    {% for value, label in options %}
                        <option value="{{ value }}" {{ (filters.order|default(null) == value) ? 'selected' : '' }}>
                            {{ label }}
                        </option>
                    {% endfor %}
                </optgroup>
            </select>
        </div>
        <a class="btn submit" href="{{ url('database-translations') }}">Reset</a>
        <button type="submit" class="btn submit">Filter</button>
    </form> -->
</template>

<script>

    // Our component exports
    export default {
        components: {
        },
        props: {
        },
        methods: {
        },
        created: function() {
        },
        data: function() {
            return {
            }
        },
    }
</script>
