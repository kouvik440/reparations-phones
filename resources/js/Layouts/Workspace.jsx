import React, { useEffect, useRef, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';

const modules = [
    ['Tableau de bord', '/dashboard', 'dashboard.view', 'Atelier'],
    ['Réparations', '/reparations', 'repairs.view', 'Atelier'],
    ['Clients', '/clients', 'clients.view', 'Atelier'],
    ['Marques et modèles', '/catalogue', 'catalog.manage', 'Administration'],
    ['Comptes utilisateurs', '/users', 'users.manage', 'Administration'],
    ['Rôles et permissions', '/roles', 'roles.manage', 'Administration'],
];
export default function Workspace({ children }) {
    const { auth, flash } = usePage().props;
    const { url } = usePage();
    const dialog = useRef(null), trigger = useRef(null), account = useRef(null);
    const [busy, setBusy] = useState(false);
    const path = url.split('?')[0];
    const visible = modules.filter(([, , permission]) => auth.permissions[permission]);
    const current = modules.find(([, href]) => path === href || path.startsWith(href + '/'));
    useEffect(() => { dialog.current?.close(); if (account.current) account.current.open = false; }, [url]);
    useEffect(() => {
        const media = window.matchMedia('(min-width: 1024px)');
        const close = () => dialog.current?.close();
        const outside = (event) => { if (account.current && !account.current.contains(event.target)) account.current.open = false; };
        const escape = (event) => { if (event.key === 'Escape' && account.current?.open) { account.current.open = false; account.current.querySelector('summary').focus(); } };
        media.addEventListener('change', close); document.addEventListener('click', outside); document.addEventListener('keydown', escape);
        return () => { media.removeEventListener('change', close); document.removeEventListener('click', outside); document.removeEventListener('keydown', escape); };
    }, []);
    const nav = (scope) => <nav aria-label={scope === 'desktop' ? 'Navigation principale' : 'Navigation mobile'} data-navigation-scope={scope} className="p-4">{['Atelier', 'Administration'].map((group) => {
        const links = visible.filter(([, , , section]) => section === group);
        return links.length > 0 && <div className="mb-7" key={group}><p className="mb-2 px-3 text-xs font-semibold text-slate-400">{group}</p>{links.map(([label, href]) => <Link key={href} href={href} className="nav-link" aria-current={path === href || path.startsWith(href + '/') ? 'page' : undefined}>{label}</Link>)}</div>;
    })}{auth.permissions['repairs.create'] && <Link href="/reparations/find-client" className="button-secondary w-full">＋ Nouveau dépôt</Link>}</nav>;
    return <div className="min-h-dvh bg-[#f5f7f9] text-slate-800"><a href="#main" className="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:bg-white focus:p-3">Aller au contenu</a>
        <aside className="fixed inset-y-0 left-0 hidden w-60 flex-col border-r border-slate-200 bg-[#fafbfc] lg:flex"><Link className="px-7 py-7 text-lg font-semibold tracking-tight" href={auth.user.landing_url}>Atelier<span className="mt-1 block text-xs font-normal text-slate-500">Gestion des réparations</span></Link>{nav('desktop')}</aside>
        <div className="min-w-0 lg:ml-60"><header className="sticky top-0 z-30 flex h-16 items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 sm:px-7"><div className="flex min-w-0 items-center gap-3"><button ref={trigger} className="button-secondary !border-0 lg:!hidden" type="button" onClick={() => dialog.current.showModal()} aria-label="Ouvrir la navigation" aria-haspopup="dialog"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg></button><span className="truncate text-sm font-medium">{current?.[0] || 'Espace de travail'}</span></div><details ref={account} className="relative"><summary className="flex items-center gap-3 rounded-lg px-2 text-sm"><span className="flex size-8 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold">{auth.user.name.slice(0, 2).toUpperCase()}</span><span className="hidden sm:block">{auth.user.name}</span><span aria-hidden="true">⌄</span></summary><div className="absolute right-0 mt-2 w-[min(280px,calc(100vw-32px))] rounded-lg border border-slate-200 bg-white p-4"><p className="font-semibold">{auth.user.name}</p><p className="muted break-all">{auth.user.email}</p><p className="muted">{auth.user.role_name}</p><button disabled={busy} className="button-secondary mt-4 w-full" onClick={() => { setBusy(true); router.post('/logout', {}, { onFinish: () => setBusy(false) }); }}>Se déconnecter</button></div></details></header>
        <main id="main" tabIndex="-1" className="mx-auto max-w-[1500px] px-4 py-7 sm:px-7 lg:px-9">{flash.success && <p role="status" className="notice mb-5">{flash.success}</p>}{flash.error && <p role="alert" className="error mb-5">{flash.error}</p>}{children}</main></div>
        <dialog ref={dialog} aria-label="Navigation de l’atelier" onClose={() => trigger.current?.focus()} onClick={(event) => { if (event.target === dialog.current) { const r = dialog.current.getBoundingClientRect(); if (event.clientX > r.right || event.clientX < r.left || event.clientY < r.top || event.clientY > r.bottom) dialog.current.close(); } }} className="fixed inset-y-0 left-0 m-0 h-dvh max-h-dvh w-[min(320px,calc(100vw-32px))] max-w-none bg-slate-50 p-0"><div className="flex items-center justify-between px-6 py-4"><strong>Atelier Réparations</strong><button type="button" className="button-secondary" onClick={() => dialog.current.close()} aria-label="Fermer la navigation">×</button></div>{nav('mobile')}</dialog>
    </div>;
}
