import tailwindcss from "@tailwindcss/vite";
import laravel from "laravel-vite-plugin";
import { local } from "laravel-vite-plugin/fonts";
import { defineConfig } from "vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.ts"],
            refresh: true,
            fonts: [
                local("Rubik", {
                    alias: "rubik",
                    src: "resources/fonts/rubik/*.ttf",
                    fallbacks: ["ui-sans-serif", "system-ui", "sans-serif"],
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
