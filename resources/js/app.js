import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createVuetify } from 'vuetify';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';



// Import router
import router from './router';

// Import components and directives
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';

// Import your App component
import App from './App.vue';

// Create Vuetify instance
const vuetify = createVuetify({
    components,
    directives,
    theme: {
        defaultTheme: 'light',
        themes: {
            light: {
                colors: {
                    primary: '#4f46e5',
                    secondary: '#7c3aed',
                    error: '#ef4444',
                    success: '#10b981',
                    warning: '#f59e0b',
                    background: '#f8fafc',
                }
            }
        }
    }
});

// Create app
const app = createApp(App);

// Create Pinia
const pinia = createPinia();

// Use plugins
app.use(pinia);
app.use(vuetify);
app.use(router);
app.use(Toast, {
    position: 'top-right',
    timeout: 3000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    closeButton: 'button',
    icon: true,
    rtl: false
});

app.mount('#app');

console.log('Church Management System mounted successfully!');
