import { Livewire, Alpine } from "../../vendor/livewire/livewire/dist/livewire.esm";
import inactivityTimeout from "./components/inactivity-timeout";
import scrollspy from "./components/scrollspy";
import scrollRestore from "./components/scroll-restore";
import scanner from "./scanner";

scrollRestore();

window.scanner = scanner;

window.Alpine = Alpine;

Alpine.data("scrollspy", scrollspy);
Alpine.data("inactivityTimeout", inactivityTimeout);

Livewire.start();
