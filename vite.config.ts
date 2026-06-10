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
                    variants: [
                        {
                            src: "resources/fonts/Rubik/Rubik-VariableFont_wght.ttf",
                            weight: "100 900",
                        },
                        {
                            src: "resources/fonts/Rubik/Rubik-Italic-VariableFont_wght.ttf",
                            weight: "100 900",
                            style: "italic",
                        },
                    ],
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
