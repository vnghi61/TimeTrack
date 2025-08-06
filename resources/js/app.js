import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { i18n } from './plugins/i18n';
import AppLoader from './Components/AppLoader.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        try {
            return resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'))
        } catch (error) {
            window.location.href = '/dashboard'
            return resolvePageComponent('./Pages/Dashboard.vue', import.meta.glob('./Pages/**/*.vue'))
        }
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
        
        // Show loading screen
        const loader = document.createElement('div')
        loader.id = 'app-loader'
        document.body.appendChild(loader)
        
        const loaderApp = createApp(AppLoader)
        loaderApp.mount('#app-loader')
        
        // Initialize i18n
        i18n.init().then(() => {
            // Hide loader after i18n is ready
            setTimeout(() => {
                loaderApp.unmount()
                document.body.removeChild(loader)
            }, 500)
        })
        
        return app.mount(el);
    },
    progress: {
        delay: 250,
        color: '#4B5563',
        includeCSS: true,
        showSpinner: true,
    },
    onError: (error) => {
        if (error.status === 404) {
            window.location.href = '/dashboard'
        }
    }
});
