import {createApp} from 'vue';
import {createPinia} from 'pinia';
import Filters from '../components/Filters.vue';
import Dashboard from '../components/Dashboard.vue';
import Actions from '../components/Actions.vue';

const app = createApp({
    mounted() {
        // reset Jquery selectors after mount
        this.$craft.cp.$headerContainer = this.$jquery('#header-container');
        this.$craft.cp.$header = this.$jquery('#header');
        this.$craft.cp.$mainContent = this.$jquery('#main-content');
        this.$craft.cp.$details = this.$jquery('#details');
        this.$craft.cp.$contentContainer = this.$jquery('#content-container');
    }
})
    .use(createPinia())
    .component('Filters', Filters)
    .component('Dashboard', Dashboard)
    .component('Actions', Actions)
;

app.config.globalProperties.$craft = window.Craft;
app.config.globalProperties.$jquery = window.$;
app.mount('#main');