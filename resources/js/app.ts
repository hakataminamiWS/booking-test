import { createApp } from "vue";
import "vuetify/styles";
import { createVuetify } from "vuetify";
import * as components from "vuetify/components";
import * as directives from "vuetify/directives";

// Vue Components
import AdminUserShow from "@/admin/users/Show.vue";
// import BookerBookingsCreate from './booker/bookings/Create.vue'; // TODO: Implement this component

const vuetify = createVuetify({
    components,
    directives,
});

const appElement = document.getElementById("app");

if (appElement) {
    const page = appElement.dataset.page;
    const props = { ...appElement.dataset };

    let component;
    let mountProps = {};

    switch (page) {
        case "admin-users-show":
            component = AdminUserShow;
            mountProps = JSON.parse(props.props || '{}');
            break;
        // case 'booker-bookings-create':
        //     component = BookerBookingsCreate;
        //     mountProps = {
        //         // props for booking create
        //     };
        //     break;
    }

    if (component) {
        createApp(component, mountProps).use(vuetify).mount(appElement);
    }
}
