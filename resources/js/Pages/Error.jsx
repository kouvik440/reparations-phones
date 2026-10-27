import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
export default function ErrorPage({ status, message }) {
    const { auth = { user: null } } = usePage().props;
    return <main className="public-wrap flex items-center justify-center p-5"><Head title={`Erreur ${status}`} /><section className="panel max-w-lg"><p className="text-sm font-semibold text-atelier-700">Erreur {status}</p><h1 className="page-title mt-3">{message}</h1><p className="muted mt-4">{status === 419 ? 'Votre session a expiré. Reconnectez-vous pour continuer.' : 'Vous pouvez revenir à votre espace et reprendre votre navigation.'}</p><Link className="button mt-6" href={status === 419 ? '/login' : auth.user?.landing_url || '/'}>Retour à {auth.user ? 'mon espace' : 'l’accueil'}</Link></section></main>;
}
