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
    "owner/shops/bookings/Create": defineAsyncComponent(
        () => import("@/owner/shops/bookings/Create.vue")
    ),
    "owner/shops/bookings/Index": defineAsyncComponent(
        () => import("@/owner/shops/bookings/Index.vue")
    ),
    "owner/shops/bookings/Edit": defineAsyncComponent(
        () => import("@/owner/shops/bookings/Edit.vue")
    ),

    "owner/shops/bookers/Index": defineAsyncComponent(
        () => import("@/owner/shops/bookers/Index.vue")
    ),
    "owner/shops/bookers/Create": defineAsyncComponent(
        () => import("@/owner/shops/bookers/Create.vue")
    ),
    "owner/shops/bookers/Edit": defineAsyncComponent(
        () => import("@/owner/shops/bookers/Edit.vue")
    ),

    // Staff
    "staff/applications/Create": defineAsyncComponent(
        () => import("@/staff/applications/Create.vue")
    ),
    "staff/applications/Complete": defineAsyncComponent(
        () => import("@/staff/applications/Complete.vue")
    ),
    "staff/profile/Edit": defineAsyncComponent(
        () => import("@/staff/staffs/ProfileEdit.vue")
    ),
    "staff/shifts/Index": defineAsyncComponent(
        () => import("@/staff/shifts/Index.vue")
    ),
    "staff/shifts/Edit": defineAsyncComponent(
        () => import("@/staff/shifts/Edit.vue")
    ),
    "staff/bookings/Index": defineAsyncComponent(
        () => import("@/staff/bookings/Index.vue")
    ),
    "staff/bookings/Create": defineAsyncComponent(
        () => import("@/staff/bookings/Create.vue")
    ),
    "staff/bookings/Edit": defineAsyncComponent(
        () => import("@/staff/bookings/Edit.vue")
    ),
    "staff/bookers/Index": defineAsyncComponent(
        () => import("@/staff/bookers/Index.vue")
    ),
    "staff/bookers/Create": defineAsyncComponent(
        () => import("@/staff/bookers/Create.vue")
    ),
    "staff/bookers/Edit": defineAsyncComponent(
        () => import("@/staff/bookers/Edit.vue")
    ),
    "staff/staffs/Index": defineAsyncComponent(
        () => import("@/staff/staffs/Index.vue")
    ),

    // Booker
    "booker/shops/Index": defineAsyncComponent(
        () => import("@/booker/shops/Index.vue")
    ),
    "booker/profile/Edit": defineAsyncComponent(
        () => import("@/booker/profile/Edit.vue")
    ),
    "booker/bookings/Index": defineAsyncComponent(
        () => import("@/booker/bookings/Index.vue")
    ),
    "booker/bookings/Create": defineAsyncComponent(
        () => import("@/booker/bookings/Create.vue")
    ),
    "booker/bookings/Show": defineAsyncComponent(
        () => import("@/booker/bookings/Show.vue")
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
