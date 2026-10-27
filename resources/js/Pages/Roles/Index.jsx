import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Title, Table, Pagination } from '../../Components/UI';
export default function Roles({ roles }) {
    return <><Head title="Rôles et permissions" /><Title title="Rôles et permissions" description="Définissez les actions autorisées pour chaque fonction de l’atelier." action={<Link href="/roles/create" className="button">＋ Créer un rôle</Link>} /><section className="panel"><Table headings={['Rôle', 'Permissions', 'Utilisateurs', 'Actions']}>{roles.data.map((role) => <tr key={role.id}><td><strong>{role.name}</strong><p className="muted mt-1">{role.description}</p></td><td>{role.slug === 'admin' ? 'Accès complet' : `${role.permissions.length} permission(s)`}</td><td>{role.users_count}</td><td>{role.slug === 'admin' ? <span className="badge">Rôle protégé</span> : <Link href={`/roles/${role.id}/edit`} className="text-link">Modifier</Link>}</td></tr>)}</Table><Pagination items={roles} /></section></>;
}
