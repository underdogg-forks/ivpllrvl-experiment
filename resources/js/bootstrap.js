import axios from "axios";

window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Alpine.js is provided by Filament and initialized automatically
// No need to import or start Alpine separately
