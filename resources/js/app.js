import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: async name => {
        const pages = import.meta.glob([
            './Pages/**/*.vue', 
            '../../Modules/**/resources/assets/js/Pages/**/*.vue'
        ]);
    
        let pagePath;
    
        if (name.includes('::')) {
            const [module, page] = name.split('::');
            pagePath = Object.keys(pages).find(path =>
                path.includes(`/Modules/${module}/`) && path.endsWith(`${page}.vue`)
            );
        } else {
            pagePath = Object.keys(pages).find(path => path.endsWith(`${name}.vue`));
        }
    
        if (!pagePath) {
            throw new Error(`Page not found: ${name}`);
        }
    
        return pages[pagePath]();
    },
    
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(createPinia())
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
