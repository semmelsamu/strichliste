import Alpine from "alpinejs";
import scrollspy from "./components/scrollspy";
import scanner from "./scanner";

window.scanner = scanner;

window.Alpine = Alpine;

Alpine.data("scrollspy", scrollspy);

Alpine.start();
