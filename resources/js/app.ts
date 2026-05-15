import Alpine from "alpinejs";
import scrollspy from "./components/scrollspy";

window.Alpine = Alpine;

Alpine.data("scrollspy", scrollspy);

Alpine.start();
