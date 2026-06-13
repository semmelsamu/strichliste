import tailwindcss from "@tailwindcss/vite";
import laravel from "laravel-vite-plugin";
import { google } from "laravel-vite-plugin/fonts";
import { defineConfig } from "vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.ts"],
            refresh: true,
            fonts: [
                google("Rubik", {
                    weights: [400, 500, 600, 700, 800],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
});
