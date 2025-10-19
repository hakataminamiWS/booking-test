import { createApp } from "vue";
import "vuetify/styles";
import "@mdi/font/css/materialdesignicons.css"; // Import MDI CSS
import { createVuetify } from "vuetify";
import * as components from "vuetify/components";
import * as directives from "vuetify/directives";
import { mdi } from "vuetify/iconsets/mdi"; // Import MDI iconset
import { ja } from "vuetify/locale";

// Vue Components
// admin
import AdminContractsCreate from "@/admin/contracts/Create.vue";
import AdminContractsShow from "@/admin/contracts/Show.vue";
import AdminContractsEdit from "@/admin/contracts/Edit.vue";
import AdminContractsIndex from "@/admin/contracts/Index.vue";
import OwnerContractApply from "@/owner/contract/Apply.vue";
import AdminContractApplicationsIndex from "@/admin/contract-applications/Index.vue";
import AdminContractApplicationsShow from "@/admin/contract-applications/Show.vue";
import AdminContractApplicationsEdit from "@/admin/contract-applications/Edit.vue";
// owner
import OwnerShopsIndex from "@/owner/shops/Index.vue";
import OwnerShopsCreate from "@/owner/shops/Create.vue";
import OwnerShopsShow from "@/owner/shops/Show.vue";
import OwnerShopsEdit from "@/owner/shops/Edit.vue";
import BusinessHoursRegularEdit from "@/owner/shops/business-hours/regular/Edit.vue";
import SpecialOpenDaysCreate from "@/owner/shops/business-hours/special-open-days/Create.vue";
import SpecialClosedDaysCreate from "@/owner/shops/business-hours/special-closed-days/Create.vue";
import OwnerBusinessHoursIndex from "@/owner/shops/business-hours/Index.vue";
import SpecialOpenDaysEdit from "@/owner/shops/business-hours/special-open-days/Edit.vue";
import SpecialClosedDaysEdit from "@/owner/shops/business-hours/special-closed-days/Edit.vue";
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
    locale: {
        locale: "ja",
        messages: { ja },
    },
});

const appElement = document.getElementById("app");

if (appElement) {
    const page = appElement.dataset.page;
    const propsData = appElement.dataset.props
        ? JSON.parse(appElement.dataset.props)
        : {};

    let component;

    switch (page) {
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
        case "admin-contract-applications-show":
            component = AdminContractApplicationsShow;
            break;
        case "admin-contract-applications-edit":
            component = AdminContractApplicationsEdit;
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
        case "owner-shops-index":
            component = OwnerShopsIndex;
            break;
        case "owner-shops-create":
            component = OwnerShopsCreate;
            break;
        case "owner-shops-show":
            component = OwnerShopsShow;
            break;
        case "owner-shops-edit":
            component = OwnerShopsEdit;
            break;
        case "owner-shops-business-hours-regular-edit":
            component = BusinessHoursRegularEdit;
            break;
        case "owner-shops-business-hours-special-open-days-create":
            component = SpecialOpenDaysCreate;
            break;
        case "owner-shops-business-hours-special-closed-days-create":
            component = SpecialClosedDaysCreate;
            break;
        case "owner/shops/business-hours/index":
            component = OwnerBusinessHoursIndex;
            break;
        case "owner-shops-business-hours-special-open-days-edit":
            component = SpecialOpenDaysEdit;
            break;
        case "owner-shops-business-hours-special-closed-days-edit":
            component = SpecialClosedDaysEdit;
            break;
        // case 'booker-bookings-create':
        //     component = BookerBookingsCreate;
        //     break;
    }

    if (component) {
        createApp(component, propsData).use(vuetify).mount(appElement);
    }
}
