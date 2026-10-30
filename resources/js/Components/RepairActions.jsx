import React, { useState } from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import { can } from './UI';

export default function RepairActions({ repair }) {
    const { props } = usePage();
    const [confirming, setConfirming] = useState(false);
    const form = useForm({ confirmation: repair.code });
    if (!can(props, 'repairs.edit') && !can(props, 'repairs.delete')) return <span className="muted">—</span>;
    return <div className="flex min-w-40 flex-col items-start gap-2">
        {can(props, 'repairs.edit') && <Link className="button-secondary text-sm" href={`/reparations/${repair.id}/edit`}>Modifier</Link>}
        {can(props, 'repairs.delete') && (!confirming ? <button type="button" className="text-sm font-semibold text-red-700" onClick={() => setConfirming(true)}>Supprimer</button> : <div className="max-w-56 rounded-lg border border-red-200 p-3" role="group" aria-label={`Confirmer la suppression de ${repair.code}`}>
            <p className="text-sm">Supprimer le dossier <strong>{repair.code}</strong> et son suivi public ?</p>
            <div className="mt-3 flex flex-wrap gap-3"><button type="button" className="text-sm font-semibold text-red-700" disabled={form.processing} onClick={() => form.delete(`/reparations/${repair.id}`, { preserveScroll: true })}>{form.processing ? 'Suppression…' : 'Confirmer'}</button><button type="button" className="text-sm" disabled={form.processing} onClick={() => setConfirming(false)}>Annuler</button></div>
            {Object.values(form.errors).map((error, index) => <p key={index} className="error mt-2" role="alert">{error}</p>)}
        </div>)}
    </div>;
}
