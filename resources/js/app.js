import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import { createInertiaApp } from '@inertiajs/vue3';
import serviceController from '@/Controllers/serviceController';

const configs = await serviceController.get('/configs?h=images');
if (configs?.data) {
    localStorage.setItem('images', JSON.stringify(configs.data));
}

const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        return pages[`./Pages/${name}.vue`]
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .mount(el);
    },
});