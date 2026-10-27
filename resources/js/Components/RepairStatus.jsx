import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { router, usePage } from '@inertiajs/react';
import { Field } from './UI';
const steps = ['en_attente', 'en_cours', 'termine', 'restitue'];
export default function RepairStatus({ repair }) {
    const { statusLabels } = usePage().props;
    const [value, setValue] = useState(repair.statut), [busy, setBusy] = useState(false), [error, setError] = useState(''), [saved, setSaved] = useState(false);
    useEffect(() => { setValue(repair.statut); }, [repair.statut, repair.status_version]);
    if (repair.statut === 'restitue') return <p className="muted">Appareil remis au client. Dossier clôturé.</p>;
    const next = steps[steps.indexOf(repair.statut) + 1];
    const change = async (target) => {
        if (target === repair.statut || busy) return;
        setValue(target); setBusy(true); setSaved(false); setError('');
        try {
            const response = await axios.put(`/reparations/${repair.id}/statut`, { statut: target, status_version: repair.status_version }, { headers: { Accept: 'application/json' } });
            if (!response.headers['content-type']?.includes('application/json')) throw new Error('expired');
            setSaved(true);
            router.reload({ only: ['reparations', 'stats', 'reparation', 'history'], preserveScroll: true, onFinish: () => setBusy(false) });
        } catch (failure) {
            setValue(repair.statut); setBusy(false);
            setError(failure.response?.status === 409 ? 'Ce dossier a été modifié. Actualisez avant de continuer.' : Object.values(failure.response?.data?.errors || {}).flat()[0] || 'Enregistrement non confirmé. Actualisez la page pour vérifier le statut.');
        }
    };
    return <div className="min-w-52"><Field as="select" label={`Statut de ${repair.code}`} value={value} disabled={busy || !!error} onChange={(e) => change(e.target.value)}><option value={repair.statut}>{statusLabels[repair.statut]}</option><option value={next}>{statusLabels[next]}</option></Field><p role="status" aria-live="polite" className={error ? 'error mt-2' : 'muted mt-2'}>{error || (busy ? 'Enregistrement en cours…' : saved ? 'Statut enregistré.' : 'Enregistrement automatique à chaque changement.')}</p>{error && <button type="button" className="button-secondary mt-2" onClick={() => router.reload({ onSuccess: () => setError('') })}>Actualiser le dossier</button>}</div>;
}
