import "./bootstrap";

import Alpine from "alpinejs";

window.Alpine = Alpine;

Alpine.start();
// resources/js/app.js
window.csrf = () =>
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content") || "";

window.api = async function (url, { method = "GET", data = null } = {}) {
    const headers = {
        "X-Requested-With": "XMLHttpRequest",
        "X-CSRF-TOKEN": window.csrf(),
    };
    if (data !== null) headers["Content-Type"] = "application/json";
    const resp = await fetch(url, {
        method,
        headers,
        body: data ? JSON.stringify(data) : null,
    });
    if (!resp.ok) {
        const txt = await resp.text();
        throw new Error(txt || `HTTP ${resp.status}`);
    }
    return await resp.json();
};
