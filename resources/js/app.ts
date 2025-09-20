import { createApp } from "vue";
import "vuetify/styles";
import "@mdi/font/css/materialdesignicons.css"; // Import MDI CSS
import { createVuetify } from "vuetify";
import * as components from "vuetify/components";
import * as directives from "vuetify/directives";
import { mdi } from "vuetify/iconsets/mdi"; // Import MDI iconset

// Vue Components
import AdminUserShow from "@/admin/users/Show.vue";
import AdminUsersIndex from "@/admin/users/Index.vue";
import AdminUsersEdit from "@/admin/users/Edit.vue";
import AdminContractsCreate from "@/admin/contracts/Create.vue";
import AdminContractsShow from "@/admin/contracts/Show.vue";
import AdminContractsEdit from "@/admin/contracts/Edit.vue";
import AdminContractsIndex from "@/admin/contracts/Index.vue";
// import BookerBookingsCreate from './booker/bookings/Create.vue'; // TODO: Implement this component

const vuetify = createVuetify({
    components,
    directives,
    icons: { // Add icons configuration
        defaultSet: 'mdi',
        sets: {
            mdi,
        },
    },
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
        case "admin-users-index":
            component = AdminUsersIndex;
            mountProps = JSON.parse(props.props || '{}');
            break;
        case "admin-users-edit":
            component = AdminUsersEdit;
            mountProps = JSON.parse(props.props || '{}');
            break;
        case "admin-contracts-create":
            component = AdminContractsCreate;
            mountProps = JSON.parse(props.props || '{}');
            break;
        case "admin-contracts-show":
            component = AdminContractsShow;
            mountProps = JSON.parse(props.props || '{}');
            break;
        case "admin-contracts-edit":
            component = AdminContractsEdit;
            mountProps = JSON.parse(props.props || '{}');
            break;
        case "admin-contracts-index":
            component = AdminContractsIndex;
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
