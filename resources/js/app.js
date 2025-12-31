import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import serviceController from '@/Controllers/serviceController';

const configs = await serviceController.get('/configs-images');
if (configs.data) {
    localStorage.setItem('images', JSON.stringify(configs.data));
}

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        return pages[`./Pages/${name}.vue`]
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});