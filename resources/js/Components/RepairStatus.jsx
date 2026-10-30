import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { router, usePage } from '@inertiajs/react';
import { Field } from './UI';
export default function RepairStatus({ repair }) {
    const { statusLabels, timezone } = usePage().props;
    const today = new Intl.DateTimeFormat('en-CA', { timeZone: timezone || 'Africa/Ouagadougou', year: 'numeric', month: '2-digit', day: '2-digit' }).format(new Date());
    const [value, setValue] = useState(repair.statut), [withdrawalDate, setWithdrawalDate] = useState(repair.date_retrait || today);
    const [busy, setBusy] = useState(false), [error, setError] = useState(''), [saved, setSaved] = useState(false);
    const [conflict, setConflict] = useState(false);
    useEffect(() => { setValue(repair.statut); setWithdrawalDate(repair.date_retrait || today); }, [repair.statut, repair.status_version, repair.date_retrait]);
    const change = async (target) => {
        if (busy) return;
        setBusy(true); setSaved(false); setError('');
        try {
            const response = await axios.put(`/reparations/${repair.id}/statut`, { statut: target, status_version: repair.status_version, date_retrait: target === 'restitue' ? withdrawalDate : null }, { headers: { Accept: 'application/json' } });
            if (!response.headers['content-type']?.includes('application/json')) throw new Error('expired');
            setSaved(true);
            router.reload({ only: ['reparations', 'stats', 'reparation', 'history'], preserveScroll: true, onFinish: () => setBusy(false) });
        } catch (failure) {
            setBusy(false);
            setConflict(failure.response?.status === 409);
            setError(failure.response?.status === 409 ? 'Ce dossier a été modifié. Actualisez avant de continuer.' : Object.values(failure.response?.data?.errors || {}).flat()[0] || 'Enregistrement non confirmé. Actualisez pour vérifier le statut.');
        }
    };
    return <form className="min-w-52" onSubmit={(event) => { event.preventDefault(); change(value); }}>
        <Field as="select" label={`Statut de ${repair.code}`} value={value} disabled={busy || conflict} onChange={(event) => { const target = event.target.value; setValue(target); setSaved(false); setError(''); if (target !== 'restitue') change(target); }}>{Object.entries(statusLabels).map(([status, label]) => <option key={status} value={status}>{label}</option>)}</Field>
        {value === 'restitue' && <div className="mt-3"><Field type="date" label="Date de retrait" value={withdrawalDate} min={repair.date_depot?.slice(0, 10)} max={today} required disabled={busy} onChange={(event) => { setWithdrawalDate(event.target.value); setSaved(false); }} /><button className="button-secondary mt-2" disabled={busy}>Enregistrer la restitution</button></div>}
        <p role="status" aria-live="polite" className={error ? 'error mt-2' : 'muted mt-2'}>{error || (busy ? 'Enregistrement en cours…' : saved ? 'Modification enregistrée.' : value === 'restitue' ? 'Vérifiez la date puis confirmez la restitution.' : 'Enregistrement automatique à chaque changement.')}</p>
        {error && <button type="button" className="button-secondary mt-2" onClick={() => router.reload({ onSuccess: () => { setError(''); setConflict(false); } })}>Actualiser le dossier</button>}
    </form>;
}
