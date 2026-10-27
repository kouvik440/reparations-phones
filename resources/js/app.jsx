import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import axios from 'axios';
import Workspace from './Layouts/Workspace';
import '../css/spa.css';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const pages = import.meta.glob('./Pages/**/*.jsx');
createInertiaApp({
    title: (title) => `${title} — Atelier Réparations`,
    resolve: async (name) => {
        const load = pages[`./Pages/${name}.jsx`];
        if (!load) throw new Error(`Écran introuvable : ${name}`);
        const page = await load();
        if (!name.startsWith('Public/') && name !== 'Auth/Login' && name !== 'Error') {
            page.default.layout = (content) => <Workspace>{content}</Workspace>;
        }
        return page;
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: { color: '#148775', showSpinner: false },
});
