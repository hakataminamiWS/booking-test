import { createApp } from 'vue';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import { aliases, mdi } from 'vuetify/iconsets/mdi';
import axios from 'axios';

// --- Axiosのグローバル設定 ---
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['X-CSRF-TOKEN'] = 
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
// --- ここまで ---

const vuetify = createVuetify({
    components,
    directives,
    icons: {
        defaultSet: 'mdi',
        aliases,
        sets: { mdi },
    },
});

// --- Vueコンポーネントの動的インポート ---
const pages: Record<string, any> = {
    'booker-bookings-index': () => import('./booker/bookings/Index.vue'),
    'booker-bookings-show': () => import('./booker/bookings/Show.vue'),
    'staff-bookings-index': () => import('./staff/bookings/Index.vue'),
    'staff-dashboard': () => import('./staff/Dashboard.vue'),
    'staff-bookings-create': () => import('./staff/bookings/Create.vue'),
    'staff-bookings-confirm': () => import('./staff/bookings/Confirm.vue'),
    'staff-schedules-index': () => import('./staff/schedules/Index.vue'),
    'owner-dashboard': () => import('./owner/Dashboard.vue'),
    'owner-shops-index': () => import('./owner/shops/Index.vue'),
    'owner-shops-show': () => import('./owner/shops/Show.vue'),
    'owner-shops-staff-index': () => import('./owner/shops/staff/Index.vue'),
    'owner-shops-staff-edit': () => import('./owner/shops/staff/Edit.vue'),
        'owner-contracts-index': () => import('./owner/contracts/Index.vue'),
    'admin-owners-index': () => import('./admin/owners/Index.vue'),
    'admin-owners-show': () => import('./admin/owners/Show.vue'),
    'admin-contracts-index': () => import('./admin/contracts/Index.vue'),
    'admin-contracts-show': () => import('./admin/contracts/Show.vue'),
    // 必要に応じて他のページコンポーネントを追加
};

document.addEventListener('DOMContentLoaded', async () => {
    const appElement = document.getElementById('app');
    if (!appElement) return;

    const pageName = appElement.dataset.page;
    if (!pageName || !pages[pageName]) {
        console.error(`Component not found for page: ${pageName}`);
        return;
    }

    try {
        const componentModule = await pages[pageName]();
        const component = componentModule.default;

        const props: { [key: string]: any } = { ...appElement.dataset };

        // 必要に応じて特定のページでpropsを加工する（例：JSONパース）
        // if (pageName === 'booking-confirm' && props.bookingData) {
        //     props.bookingData = JSON.parse(props.bookingData);
        // }

        const app = createApp(component, props);
        app.use(vuetify);
        app.mount('#app');
    } catch (e) {
        console.error(`Failed to load component for page: ${pageName}`, e);
    }
});

