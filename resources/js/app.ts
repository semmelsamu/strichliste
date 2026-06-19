import "htmx.org";
import Alpine from "alpinejs";
import inactivityTimeout from "./components/inactivity-timeout";
import scrollspy from "./components/scrollspy";
import scanner from "./scanner";

window.scanner = scanner;

window.Alpine = Alpine;

Alpine.data("scrollspy", scrollspy);
Alpine.data("inactivityTimeout", inactivityTimeout);

Alpine.start();
