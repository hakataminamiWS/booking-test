import { createApp } from "vue";
import { createVuetify } from "vuetify";
import * as components from "vuetify/components";
import * as directives from "vuetify/directives";
import { aliases, mdi } from "vuetify/iconsets/mdi";
import axios from "axios";

import BookingsCreate from "@/booker/bookings/Create.vue";
import BookingConfirm from "./booker/bookings/Confirm.vue";
import BookingComplete from "./booker/bookings/Complete.vue"; //完了画面コンポーネントをインポート


// --- Axiosのグローバル設定 ---
axios.defaults.withCredentials = true;
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
axios.defaults.headers.common['X-CSRF-TOKEN'] = 
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
// --- ここまで ---

const appElement = document.getElementById("app");
const page = appElement?.dataset.page;

const vuetify = createVuetify({
    components,
    directives,
    icons: {
        defaultSet: "mdi",
        aliases,
        sets: { mdi },
    },
});

// pageの値に応じて処理を分岐
switch (page) {
    case "booker-bookings-create":
        createApp(BookingsCreate, { ...appElement?.dataset }).use(vuetify).mount("#app");
        break;

    case "booking-confirm": {
        // このケースのみpropsの型を柔軟にし、JSONパース処理を行う
        const props: { [key: string]: any } = { ...appElement?.dataset };
        if (props.bookingData) {
            try {
                props.bookingData = JSON.parse(props.bookingData);
            } catch (e) {
                console.error("Failed to parse booking data:", e);
                props.bookingData = {};
            }
        }
        createApp(BookingConfirm, props).use(vuetify).mount("#app");
        break;
    }

    case "booking-complete":
        createApp(BookingComplete, { ...appElement?.dataset }).use(vuetify).mount("#app");
        break;
}

