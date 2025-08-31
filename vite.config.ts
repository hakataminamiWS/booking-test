import { fileURLToPath, URL } from "node:url";

import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import vuetify from 'vite-plugin-vuetify'; // ★ インポート
import vueDevTools from "vite-plugin-vue-devtools";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.ts"],
            refresh: true,
        }),
        vue(),
        vuetify({ autoImport: true }), // ★ プラグインを有効化
        vueDevTools(),
    ],
    resolve: {
        alias: {
            "@": fileURLToPath(new URL("resources/js", import.meta.url)),
        },
    },
});
