import React from 'react';
import { App, router } from '@inertiajs/react';
import { render, screen, within, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi } from 'vitest';
import axios from 'axios';
import Home from '../../resources/js/Pages/Public/Home';
import Login from '../../resources/js/Pages/Auth/Login';
import Tracking from '../../resources/js/Pages/Public/Tracking';
import Workspace from '../../resources/js/Layouts/Workspace';
import CreateRepair from '../../resources/js/Pages/Repairs/Create';
import RoleForm from '../../resources/js/Pages/Roles/Form';
import UserForm from '../../resources/js/Pages/Users/Form';
import Catalog from '../../resources/js/Pages/Catalog/Index';
import Dashboard from '../../resources/js/Pages/Dashboard';
import RepairStatus from '../../resources/js/Components/RepairStatus';
import ErrorPage from '../../resources/js/Pages/Error';

const labels = { en_attente: 'En attente', en_cours: 'En cours', termine: 'Terminée · à restituer', restitue: 'Restituée' };
function mount(Component, props = {}, { permissions = {}, url = '/', user = null } = {}) {
    const page = { component: 'Test', props: { errors: {}, auth: { user, permissions }, flash: {}, timezone: 'UTC', statusLabels: labels, ...props }, url, version: null, clearHistory: false, encryptHistory: false };
    return render(<App initialPage={page} initialComponent={Component} resolveComponent={async () => Component} />);
}
const pagination = (data) => ({ data, total: data.length, last_page: 1, links: [] });

describe('React pages', () => {
    it('keeps home public, links to each section and escapes workshop text', () => {
        const { container } = mount(Home, { workshop: { history: { paragraphs: ['<script>alert(1)</script>'] } } });
        expect(screen.getByRole('link', { name: /Se connecter/ })).toHaveAttribute('href', '/login');
        expect(screen.getByRole('link', { name: /Suivre ma réparation/ })).toHaveAttribute('href', '/suivi');
        for (const id of ['histoire', 'services', 'contact', 'questions']) {
            expect(container.querySelector(`a[href="#${id}"]`)).toBeTruthy();
            expect(container.querySelector(`#${id}`)).toBeTruthy();
        }
        expect(container.querySelector('script')).toBeNull();
        expect(screen.getByText('<script>alert(1)</script>')).toBeInTheDocument();
        expect(screen.queryByText('Comptes utilisateurs')).not.toBeInTheDocument();
    });

    it('uses email/password login and lets the user reveal the password', async () => {
        mount(Login);
        expect(screen.getByLabelText('Adresse email')).toHaveAttribute('type', 'email');
        const password = screen.getByLabelText('Mot de passe');
        expect(password).toHaveAttribute('type', 'password');
        await userEvent.click(screen.getByRole('button', { name: /Afficher le mot de passe/ }));
        expect(password).toHaveAttribute('type', 'text');
        expect(screen.queryByText('Créer un administrateur')).not.toBeInTheDocument();
    });

    it('filters navigation by permissions and has exactly one active module in both menus', async () => {
        const Page = () => <Workspace><p>Fiche</p></Workspace>;
        const { container } = mount(Page, {}, { url: '/clients/create', user: { id: 1, name: 'Accueil', email: 'a@example.com', role_name: 'Accueil', landing_url: '/clients' }, permissions: { 'clients.view': true, 'clients.create': true } });
        for (const scope of ['desktop', 'mobile']) {
            const nav = container.querySelector(`[data-navigation-scope="${scope}"]`);
            expect(nav.querySelectorAll('[aria-current="page"]')).toHaveLength(1);
            expect(nav.querySelector('[aria-current="page"]')).toHaveAttribute('href', '/clients');
        }
        expect(screen.queryByText('Comptes utilisateurs')).not.toBeInTheDocument();
        await userEvent.click(screen.getByRole('button', { name: 'Ouvrir la navigation' }));
        expect(container.querySelector('dialog').open).toBe(true);
        await userEvent.click(screen.getByRole('button', { name: 'Fermer la navigation' }));
        expect(container.querySelector('dialog').open).toBe(false);
    });

    it('searches brands and only permits models belonging to the selected brand', async () => {
        mount(CreateRepair, { client: { id: 1, prenom: 'Aicha', nom: 'Test', telephone: '70000000' }, brands: [{ id: 1, name: 'Apple', device_models: [{ id: 11, name: 'iPhone 13' }] }, { id: 2, name: 'Samsung', device_models: [{ id: 22, name: 'Galaxy A15' }] }] });
        expect(screen.getByLabelText('Modèle')).toBeDisabled();
        await userEvent.type(screen.getByLabelText('Rechercher une marque'), 'sAM');
        expect(screen.queryByRole('option', { name: 'Apple' })).not.toBeInTheDocument();
        await userEvent.selectOptions(screen.getByLabelText('Marque'), '2');
        expect(screen.getByLabelText('Modèle')).toBeEnabled();
        expect(screen.getByRole('option', { name: 'Galaxy A15' })).toBeInTheDocument();
        expect(screen.queryByRole('option', { name: 'iPhone 13' })).not.toBeInTheDocument();
        await userEvent.selectOptions(screen.getByLabelText('Modèle'), '22');
        await userEvent.clear(screen.getByLabelText('Rechercher une marque'));
        await userEvent.selectOptions(screen.getByLabelText('Marque'), '1');
        expect(screen.getByLabelText('Modèle')).toHaveValue('');
        expect(screen.queryByRole('option', { name: 'Galaxy A15' })).not.toBeInTheDocument();
    });

    it('automatically selects required permissions and removes dependent permissions', async () => {
        const groups = { Clients: { 'clients.view': { label: 'Consulter les clients', description: '', requires: [] } }, Réparations: { 'repairs.view': { label: 'Consulter les réparations', description: '', requires: [] }, 'repairs.create': { label: 'Enregistrer un dépôt', description: '', requires: ['clients.view', 'repairs.view'] } } };
        mount(RoleForm, { groups });
        await userEvent.click(screen.getByRole('checkbox', { name: /Enregistrer un dépôt/ }));
        expect(screen.getByRole('checkbox', { name: 'Consulter les clients' })).toBeChecked();
        expect(screen.getByRole('checkbox', { name: 'Consulter les réparations' })).toBeChecked();
        await userEvent.click(screen.getByRole('checkbox', { name: 'Consulter les clients' }));
        expect(screen.getByRole('checkbox', { name: /Enregistrer un dépôt/ })).not.toBeChecked();
    });

    it('saves a status immediately without a document reload', async () => {
        const put = vi.spyOn(axios, 'put').mockResolvedValue({ headers: { 'content-type': 'application/json' } });
        const reload = vi.spyOn(router, 'reload').mockImplementation((options) => options.onFinish?.());
        mount(() => <RepairStatus repair={{ id: 1, code: 'ABCDEFGH', statut: 'en_attente', status_version: 4 }} />);
        await userEvent.selectOptions(screen.getByLabelText('Statut de ABCDEFGH'), 'en_cours');
        await waitFor(() => expect(put).toHaveBeenCalledWith('/reparations/1/statut', { statut: 'en_cours', status_version: 4 }, expect.any(Object)));
        expect(reload).toHaveBeenCalledWith(expect.objectContaining({ only: ['reparations', 'stats', 'reparation', 'history'] }));
        expect(screen.getByRole('status')).toHaveTextContent('Statut enregistré');
    });

    it('prevents retrying a stale status and offers a refresh', async () => {
        vi.spyOn(axios, 'put').mockRejectedValue({ response: { status: 409 } });
        mount(() => <RepairStatus repair={{ id: 1, code: 'ABCDEFGH', statut: 'en_cours', status_version: 1 }} />);
        await userEvent.selectOptions(screen.getByLabelText('Statut de ABCDEFGH'), 'termine');
        await waitFor(() => expect(screen.getByRole('status')).toHaveTextContent('Ce dossier a été modifié'));
        expect(screen.getByLabelText('Statut de ABCDEFGH')).toBeDisabled();
        expect(screen.getByRole('button', { name: 'Actualiser le dossier' })).toBeInTheDocument();
    });

    it('displays delivery independently from completion and provides a public return link', () => {
        mount(Tracking, { searched: true, reparation: { code: 'ABCDEFGH', marque: 'Samsung', modele: 'A15', date_depot: '2026-10-27', prix: null, statut: 'restitue' } });
        expect(screen.getByText(/Appareil restitué/)).toHaveAttribute('aria-current', 'step');
        expect(screen.getByRole('link', { name: /Retour à l’accueil/ })).toHaveAttribute('href', '/');
        expect(screen.getByText('À déterminer après diagnostic')).toBeInTheDocument();
    });

    it('keeps passwords empty on edit and allows confirmation of a replacement password', () => {
        mount(UserForm, { user: { id: 3, name: 'Accueil', email: 'a@example.com', role: 'user' }, roles: [{ id: 1, slug: 'user', name: 'Utilisateur' }] });
        expect(screen.getByLabelText('Nouveau mot de passe (facultatif)')).toHaveValue('');
        expect(screen.getByLabelText('Confirmer le mot de passe')).toHaveAttribute('type', 'password');
    });

    it('renders catalogue CRUD with separate brand and model forms', () => {
        mount(Catalog, { brands: pagination([{ id: 1, name: 'Samsung' }]), selectedBrand: { id: 1, name: 'Samsung' }, models: pagination([{ id: 2, name: 'A15' }]), search: '' });
        expect(screen.getByLabelText('Nouvelle marque')).toBeInTheDocument();
        expect(screen.getByLabelText('Nouveau modèle')).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Supprimer le modèle inutilisé' })).toBeInTheDocument();
    });

    it('renders zero activity and prevents inaccessible details in a dashboard-only role', () => {
        const statuses = Object.fromEntries(Object.entries(labels).map(([key, label]) => [key, { label, count: 0, color: '#148775' }]));
        mount(Dashboard, { statuses, totalRepairs: 0, totalClients: 0, todayDeposits: 0, days: [{ date: '2026-10-27', label: 'mar.', full_label: '27 octobre', count: 0 }], chartMax: 1, weekTotal: 0, weekDifference: 0, recentRepairs: [], date: '2026-10-27' });
        expect(screen.getByText(/Aucun dépôt sur cette période/)).toBeInTheDocument();
        expect(screen.queryByText('Derniers dépôts')).not.toBeInTheDocument();
        expect(document.body.textContent).not.toContain('NaN');
    });

    it('renders a routing error even when no web middleware shared auth props', () => {
        mount(ErrorPage, { status: 404, message: 'Cette page est introuvable.', auth: undefined });
        expect(screen.getByRole('heading', { name: 'Cette page est introuvable.' })).toBeInTheDocument();
        expect(screen.getByRole('link', { name: /Retour à/ })).toHaveAttribute('href', '/');
    });
});
