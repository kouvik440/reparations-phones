import React from 'react';
import { Head } from '@inertiajs/react';
export default function Pending() {
    return <section className="panel max-w-2xl"><Head title="Accès en attente" /><h1 className="page-title">Vos accès restent à configurer</h1><p className="muted mt-4">Votre compte est connecté mais ne dispose pas encore de permissions. Contactez le responsable pour accéder aux modules nécessaires à votre travail.</p></section>;
}
