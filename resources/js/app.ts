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
import OwnerContractApply from "@/owner/contract/Apply.vue";
import AdminContractApplicationsIndex from "@/admin/contract-applications/Index.vue";
import AdminOwnersIndex from "@/admin/owners/Index.vue";
import AdminOwnersShow from "@/admin/owners/Show.vue";
import AdminOwnersEdit from "@/admin/owners/Edit.vue";
// import BookerBookingsCreate from './booker/bookings/Create.vue'; // TODO: Implement this component

const vuetify = createVuetify({
    components,
    directives,
    icons: {
        // Add icons configuration
        defaultSet: "mdi",
        sets: {
            mdi,
        },
    },
});

const appElement = document.getElementById("app");

if (appElement) {
    const page = appElement.dataset.page;
    const propsData = appElement.dataset.props ? JSON.parse(appElement.dataset.props) : {};

    let component;

    switch (page) {
        case "admin-users-show":
            component = AdminUserShow;
            break;
        case "admin-users-index":
            component = AdminUsersIndex;
            break;
        case "admin-users-edit":
            component = AdminUsersEdit;
            break;
        case "admin-contracts-create":
            component = AdminContractsCreate;
            break;
        case "admin-contracts-show":
            component = AdminContractsShow;
            break;
        case "admin-contracts-edit":
            component = AdminContractsEdit;
            break;
        case "admin-contracts-index":
            component = AdminContractsIndex;
            break;
        case "owner-contract-apply":
            component = OwnerContractApply;
            break;
        case "admin-contract-applications-index":
            component = AdminContractApplicationsIndex;
            break;
        case "admin-owners-index":
            component = AdminOwnersIndex;
            break;
        case "admin-owners-show":
            component = AdminOwnersShow;
            break;
        case "admin-owners-edit":
            component = AdminOwnersEdit;
            break;
        // case 'booker-bookings-create':
        //     component = BookerBookingsCreate;
        //     break;
    }

    if (component) {
        createApp(component, propsData).use(vuetify).mount(appElement);
    }
}
