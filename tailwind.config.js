/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: "class",
    content: [
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.js",
        "./resources/css/**/*.css",
        "./app/View/Components/**/*.php",
    ],
    theme: {
        extend: {
            colors: {
                soft: "#F4F6F8", // background lembut
                dark: "#343A40",
                danger: "#B00E24",
                accent: "#37BCD8",
                brand: "#09759A", // warna utama
            },
            boxShadow: {
                "custom-soft":
                    "0 10px 15px -3px rgba(9,117,154,0.10), 0 4px 6px -2px rgba(9,117,154,0.06)",
            },
        },
    },
    safelist: [
        // util warna brand yang dipanggil dinamis dari komponen
        { pattern: /(bg|text|border|ring)-(brand|accent|danger|dark|soft)/ },
        // state umum
        "hover:brightness-95",
        "rounded-xl",
        "rounded-2xl",
        "shadow",
        "shadow-md",
        "shadow-lg",
        "shadow-custom-soft",
        "border-l-4",
        { pattern: /dark:(bg|text|border)-(dark)/ },
    ],
    plugins: [
        // require('@tailwindcss/forms'), // aktifkan kalau mau form look rapi
    ],
};
