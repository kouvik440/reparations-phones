import React, { useId } from 'react';
import { Link, router, usePage } from '@inertiajs/react';

export const money = (value) => value === null || value === undefined ? 'À déterminer' : `${Number(value).toLocaleString('fr-FR')} FCFA`;
export const date = (value) => value ? new Intl.DateTimeFormat('fr-FR').format(new Date(String(value).slice(0, 10) + 'T12:00:00Z')) : 'Non renseignée';
export const can = (props, permission) => !!props.auth?.permissions?.[permission];
export function Title({ title, description, action }) {
    return <div className="mb-7 flex flex-wrap items-start justify-between gap-4"><div><h1 className="page-title">{title}</h1>{description && <p className="muted mt-2 max-w-2xl">{description}</p>}</div>{action}</div>;
}
export function Field({ label, error, as: Tag = 'input', children, help, id: suppliedId, ...props }) {
    const generated = useId();
    const id = suppliedId || generated;
    return <div className="min-w-0"><label htmlFor={id} className="mb-2 block text-sm font-semibold">{label}</label><Tag id={id} className="field" aria-invalid={!!error} aria-describedby={error ? `${id}-error` : help ? `${id}-help` : undefined} {...props}>{children}</Tag>{help && <p id={`${id}-help`} className="muted mt-2">{help}</p>}{error && <p className="error mt-2" role="alert" id={`${id}-error`}>{error}</p>}</div>;
}
export function Errors({ errors }) {
    const messages = Object.values(errors || {}).flat();
    return messages.length > 0 ? <div role="alert" className="mb-5 rounded-lg bg-red-50 p-4 text-sm leading-6 text-red-800">{[...new Set(messages)].map((message) => <p key={message}>{message}</p>)}</div> : null;
}
export function Pagination({ items }) {
    if (!items || items.last_page < 2) return null;
    return <nav aria-label="Pagination" className="mt-5 flex flex-wrap items-center gap-2">{items.links.map((link, i) => {
        const raw = String(link.label);
        const label = raw.includes('Previous') || raw.includes('précédent') || raw.includes('&laquo;') ? '← Précédent' : raw.includes('Next') || raw.includes('suivant') || raw.includes('&raquo;') ? 'Suivant →' : raw.replace(/<[^>]+>/g, '');
        return link.url ? <Link className={link.active ? 'button' : 'button-secondary'} href={link.url} key={i} preserveScroll aria-current={link.active ? 'page' : undefined}>{label}</Link> : <span key={i} className="px-3 text-sm text-slate-400">{label}</span>;
    })}</nav>;
}
export function Search({ path, children, reset = true }) {
    return <form className="mb-6 flex flex-wrap items-end gap-3" role="search" onSubmit={(event) => {
        event.preventDefault();
        const values = Object.fromEntries(new FormData(event.currentTarget));
        router.get(path, values, { preserveState: true, replace: true });
    }}>{children}<button className="button">Rechercher</button>{reset && <Link className="button-secondary" href={path}>Réinitialiser</Link>}</form>;
}
export function Table({ headings, children }) {
    return <div className="overflow-x-auto"><table className="data-table"><thead><tr>{headings.map((label) => <th key={label} scope="col">{label}</th>)}</tr></thead><tbody>{children}</tbody></table></div>;
}
export function Empty({ title, children }) {
    return <div className="py-10 text-center"><h2 className="text-lg font-semibold">{title}</h2>{children && <div className="muted mt-3">{children}</div>}</div>;
}
export function Status({ value }) {
    const { statusLabels } = usePage().props;
    return <span className={`badge ${value === 'termine' ? '!bg-emerald-50 !text-emerald-800' : value === 'en_cours' ? '!bg-blue-50 !text-blue-800' : ''}`}>{statusLabels[value] || value}</span>;
}
export function Count({ label, value, href }) {
    const content = <><span className="muted">{label}</span><strong className="mt-2 block text-3xl font-semibold tracking-tight">{value}</strong></>;
    return href ? <Link className="panel block hover:bg-slate-50" href={href}>{content}</Link> : <div className="panel">{content}</div>;
}
