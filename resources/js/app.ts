import { createApp, defineAsyncComponent } from "vue";
import "vuetify/styles";
import "@mdi/font/css/materialdesignicons.css"; // Import MDI CSS
import { createVuetify } from "vuetify";
import * as components from "vuetify/components";
import * as directives from "vuetify/directives";
import { mdi } from "vuetify/iconsets/mdi"; // Import MDI iconset
import { ja } from "vuetify/locale";

const pages = {
    // Admin
    "admin/contracts/Create": defineAsyncComponent(
        () => import("@/admin/contracts/Create.vue")
    ),
    "admin/contracts/Show": defineAsyncComponent(
        () => import("@/admin/contracts/Show.vue")
    ),
    "admin/contracts/Edit": defineAsyncComponent(
        () => import("@/admin/contracts/Edit.vue")
    ),
    "admin/contracts/Index": defineAsyncComponent(
        () => import("@/admin/contracts/Index.vue")
    ),
    "admin/contract-applications/Index": defineAsyncComponent(
        () => import("@/admin/contract-applications/Index.vue")
    ),
    "admin/contract-applications/Show": defineAsyncComponent(
        () => import("@/admin/contract-applications/Show.vue")
    ),
    "admin/contract-applications/Edit": defineAsyncComponent(
        () => import("@/admin/contract-applications/Edit.vue")
    ),

    // Owner
    "owner/contract/Apply": defineAsyncComponent(
        () => import("@/owner/contract/Apply.vue")
    ),
    "owner/shops/Index": defineAsyncComponent(
        () => import("@/owner/shops/Index.vue")
    ),
    "owner/shops/Create": defineAsyncComponent(
        () => import("@/owner/shops/Create.vue")
    ),
    "owner/shops/Show": defineAsyncComponent(
        () => import("@/owner/shops/Show.vue")
    ),
    "owner/shops/Edit": defineAsyncComponent(
        () => import("@/owner/shops/Edit.vue")
    ),
    "owner/shops/business-hours/Index": defineAsyncComponent(
        () => import("@/owner/shops/business-hours/Index.vue")
    ),
    "owner/shops/business-hours/regular/Edit": defineAsyncComponent(
        () => import("@/owner/shops/business-hours/regular/Edit.vue")
    ),
    "owner/shops/business-hours/special-open-days/Create": defineAsyncComponent(
        () =>
            import("@/owner/shops/business-hours/special-open-days/Create.vue")
    ),
    "owner/shops/business-hours/special-open-days/Edit": defineAsyncComponent(
        () => import("@/owner/shops/business-hours/special-open-days/Edit.vue")
    ),
    "owner/shops/business-hours/special-closed-days/Create":
        defineAsyncComponent(
            () =>
                import(
                    "@/owner/shops/business-hours/special-closed-days/Create.vue"
                )
        ),
    "owner/shops/business-hours/special-closed-days/Edit": defineAsyncComponent(
        () =>
            import("@/owner/shops/business-hours/special-closed-days/Edit.vue")
    ),
    "owner/shops/staff-applications/Index": defineAsyncComponent(
        () => import("@/owner/shops/staff-applications/Index.vue")
    ),
    "owner/shops/staffs/Index": defineAsyncComponent(
        () => import("@/owner/shops/staffs/Index.vue")
    ),
    "owner/shops/staffs/ProfileEdit": defineAsyncComponent(
        () => import("@/owner/shops/staffs/ProfileEdit.vue")
    ),
    "owner/shops/staffs/Create": defineAsyncComponent(
        () => import("@/owner/shops/staffs/Create.vue")
    ),
    "owner/shops/staffs/shifts/Index": defineAsyncComponent(
        () => import("@/owner/shops/staffs/shifts/Index.vue")
    ),
    "owner/shops/staffs/shifts/Edit": defineAsyncComponent(
        () => import("@/owner/shops/staffs/shifts/Edit.vue")
    ),
    "owner/shops/menus/Index": defineAsyncComponent(
        () => import("@/owner/shops/menus/Index.vue")
    ),
    "owner/shops/menus/Create": defineAsyncComponent(
        () => import("@/owner/shops/menus/Create.vue")
    ),
    "owner/shops/menus/Edit": defineAsyncComponent(
        () => import("@/owner/shops/menus/Edit.vue")
    ),
    "owner/shops/options/Index": defineAsyncComponent(
        () => import("@/owner/shops/options/Index.vue")
    ),
    "owner/shops/options/Create": defineAsyncComponent(
        () => import("@/owner/shops/options/Create.vue")
    ),
    "owner/shops/options/Edit": defineAsyncComponent(
        () => import("@/owner/shops/options/Edit.vue")
    ),

    // Staff
    "staff/applications/Create": defineAsyncComponent(
        () => import("@/staff/applications/Create.vue")
    ),
    "staff/applications/Complete": defineAsyncComponent(
        () => import("@/staff/applications/Complete.vue")
    ),
};

const vuetify = createVuetify({
    components,
    directives,
    icons: {
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
    const pageName = appElement.dataset.page as keyof typeof pages;
    const propsData = appElement.dataset.props
        ? JSON.parse(appElement.dataset.props)
        : {};

    if (pages[pageName]) {
        const component = pages[pageName];
        createApp(component, propsData).use(vuetify).mount(appElement);
    } else {
        console.error("Vue component not found for page:", pageName);
    }
}
