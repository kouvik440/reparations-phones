# Interface React SPA — Atelier Réparations

L’interface est rendue par React 19 et Inertia.js 2, avec Laravel 12 pour les routes, les sessions et le métier. Inertia 2 conserve la compatibilité avec PHP 8.3.30 du serveur local. Les composants sont chargés à la demande par Vite.

## Pages actives

| Écran | Composant React |
| --- | --- |
| Accueil public | Public/Home |
| Connexion | Auth/Login |
| Suivi par code | Public/Tracking |
| Accès en attente | Auth/Pending |
| Tableau de bord | Dashboard |
| Clients : liste, création, fiche | Clients/Index, Clients/Create, Clients/Show |
| Réparations : liste, recherche client, dépôt, fiche | Repairs/Index, Repairs/FindClient, Repairs/Create, Repairs/Show |
| Comptes : liste, création/modification | Users/Index, Users/Form |
| Rôles : liste, création/modification | Roles/Index, Roles/Form |
| Marques et modèles | Catalog/Index |
| Erreurs d’accès, page absente, session expirée | Error |

Les URL existantes sont conservées. La première requête charge `resources/views/app.blade.php`, unique document de démarrage, et le point d’entrée `resources/js/app.jsx`. Les navigations et formulaires utilisent ensuite Inertia avec des réponses JSON ; aucune page métier Blade n’est chargée. Une SPA nécessite toujours ce document HTML initial et JavaScript activé.

Les anciennes pages Blade, leurs composants et les scripts/styles inutilisés ont été supprimés. Seules deux vues Blade restent : `resources/views/app.blade.php` pour démarrer React et `resources/views/reparations/ticket.blade.php` pour générer le PDF avec Dompdf. Le modèle du ticket n’est pas une page HTML destinée au navigateur.

## Où modifier les fichiers React

- `resources/js/app.jsx` : point d’entrée et chargement des pages.
- `resources/js/Pages/` : les 18 écrans, classés par module.
- `resources/js/Layouts/Workspace.jsx` : navigation, menu mobile et cadre de travail.
- `resources/js/Components/` : champs, tableaux, pagination et sélecteur de statut partagés.
- `resources/js/domain/permissions.js` : dépendances entre permissions.
- `resources/css/spa.css` et `resources/css/home.css` : styles actifs.
- `tests/Frontend/` : tests de l’interface React.

## Fonctionnement

- `Link`, `useForm` et `router` gèrent la navigation SPA, les formulaires, les erreurs et la pagination.
- Laravel garde toutes les Gates, validations, transactions et protections de session. Aucun droit ne dépend uniquement du menu React.
- `HandleInertiaRequests` partage l’identité minimale, les permissions, les messages de session et les libellés de statuts.
- Les données du suivi public sont limitées explicitement à six champs : code, marque, modèle, date de dépôt, prix et statut. Aucune pièce d’identité, panne, coordonnée du client ou trace interne n’est transmise.
- Les mots de passe ne sont ni transmis aux pages de gestion ni mémorisés dans les formulaires React après soumission.
- Le changement de statut reste automatique. Il envoie la version du dossier puis rafraîchit les données via Inertia, sans recharger le document HTML.
- Le dépôt mémorise son brouillon par client pendant la navigation et le réinitialise après succès. Le bouton Actualiser le catalogue récupère les nouvelles marques/modèles sans perdre les autres champs.
- Le ticket utilise un lien de téléchargement PDF classique, avec contrôle d’accès Laravel.
- Aucun serveur SSR ni API JWT séparée n’est nécessaire pour cette installation.

## Développement et vérification

Utiliser PHP 8.3.30 de Laragon et Node.js 22.12 ou supérieur compatible avec les dépendances installées.

```powershell
composer install
npm ci
php artisan migrate
npm run dev
```

Le serveur Laravel/Apache doit pointer vers `public`. En production ou sans serveur Vite :

```powershell
npm run build
php artisan config:clear
php artisan view:clear
```

Vérifications :

```powershell
php artisan test
npm test
npm run build
```

La suite PHP couvre le métier et les réponses Inertia. La suite React utilise Vitest et Testing Library dans un DOM simulé : menus, champs, sélection marque/modèle, permissions et sauvegarde automatique. Ces tests ne remplacent pas un essai visuel dans un navigateur et sur téléphone.

Ne pas remplacer les `Link` de navigation par `window.location`, des formulaires HTML ordinaires ou un rendu de chaînes HTML. Pour ajouter un écran : créer son composant dans `resources/js/Pages`, retourner `Inertia::render` dans le contrôleur et garder les contrôles serveur sur sa route.
