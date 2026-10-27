import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
<<<<<<< HEAD
=======
import react from '@vitejs/plugin-react';
>>>>>>> version1

export default defineConfig({
    plugins: [
        laravel({
<<<<<<< HEAD
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
=======
            input: ['resources/js/app.jsx'],
            refresh: true,
        }),
        tailwindcss(),
        react(),
>>>>>>> version1
    ],
});
