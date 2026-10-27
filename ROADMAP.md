# Roadmap — Gestion des réparations de téléphones

Date de l'audit : 26 octobre 2026.

## Objectif et périmètre

Rendre le parcours de dépôt et de suivi cohérent, sécuriser les responsabilités et garder une application Laravel simple à maintenir. Ce document sert de base de travail commune : les corrections seront réalisées par lots, avec validation du fonctionnement à chaque étape.

L'audit initial couvre les routes, contrôleurs, modèles, migrations, vues, configuration des tests et fichiers de démarrage. Les constats du tableau décrivent l'état initial ; les sections de suivi indiquent les corrections réalisées depuis. Aucun parcours complet dans le navigateur ni déploiement n'a été réalisé. Le module AUTH a ensuite été modifié et testé, et l'email du compte demandé a été renseigné en base. Les propositions métier restent à discuter ; les changements locaux existants sont conservés.

## Module RBAC — Rôles personnalisés et permissions (26 octobre 2026)

Ce module remplace les restrictions admin/user codées en dur sur les actions applicatives. Les états historiques des sections précédentes restent des photographies des étapes antérieures. Un rôle est attribué à chaque compte ; ses permissions sont persistées en base et contrôlées par les Gates Laravel et les middlewares can.

### Utilisation livrée

1. Ouvrir Comptes utilisateurs, puis Rôles et permissions.
2. Cliquer Créer un rôle, saisir son nom et cocher ses permissions par module.
3. Les consultations nécessaires à une action sont cochées automatiquement. Le serveur refuse également toute combinaison incomplète ou permission inconnue.
4. Enregistrer le rôle, puis le sélectionner dans la création ou la modification d’un compte.
5. Une modification des permissions du rôle impose une nouvelle connexion à tous ses utilisateurs.

### Catalogue disponible

| Permission | Actions autorisées |
| --- | --- |
| dashboard.view | Consulter les compteurs et graphiques globaux ; les dossiers détaillés ne sont affichés qu’avec repairs.view. |
| clients.view | Consulter le répertoire et les coordonnées des clients. |
| clients.create | Ajouter un client ; nécessite clients.view. |
| repairs.view | Consulter la liste des réparations, leur détail et les tickets. |
| repairs.create | Rechercher un client et enregistrer un appareil ; nécessite repairs.view et clients.view. |
| repairs.change_status | Mettre à jour le statut ; nécessite repairs.view. |
| repairs.set_price | Renseigner/modifier le prix, y compris au dépôt ; nécessite repairs.view. |
| users.manage | Gérer les comptes et leur rôle dans la limite des permissions du gestionnaire. |
| roles.manage | Privilège étendu : créer et modifier les rôles avec les permissions du catalogue. |

### Réalisé

- [x] Table roles, noms uniques, identifiants stables, description et permissions JSON ; relation utilisateur → rôle par la clé historique users.role.
- [x] Migration 2026_10_26_160000 appliquée, avec clé étrangère empêchant une référence de rôle inexistante. Aucun compte existant réaffecté.
- [x] Rôles initiaux Administrateur et Utilisateur conservant les accès existants ; les éventuels autres rôles historiques sont repris sans élargissement automatique.
- [x] Liste des rôles, création, modification et attribution dans le formulaire de compte ; filtres des utilisateurs adaptés aux rôles personnalisés.
- [x] Rôle Administrateur non modifiable ; dernier administrateur actif et comptes administrateurs protégés.
- [x] Gestionnaire délégué limité : il ne peut attribuer de permissions supérieures aux siennes ni modifier/désactiver un compte plus privilégié. Le pouvoir de gestion de rôles est explicitement décrit comme étendu.
- [x] Contrôles serveur sur les lectures et écritures des modules ; boutons et menus conditionnés aux mêmes permissions.
- [x] Révocation des sessions après modification des permissions, dans la même transaction que l’enregistrement du rôle. Renommer un rôle ne révoque pas les sessions et conserve son identifiant d’attribution.
- [x] Connexion orientée vers un module accessible ; écran explicatif pour un rôle sans permission.
- [x] Protection contre l’injection d’un prix lors d’un dépôt sans repairs.set_price.
- [x] Pages Comptes utilisateurs et Rôles et permissions harmonisées en Tailwind CSS v4 avec le tableau de bord ; composants Blade communs pour erreurs et pagination.
- [x] Routes de réparations limitées aux méthodes réellement implémentées.

### Validation et suite

Compilation npm run build réussie. php artisan test sous PHP 8.3.30 : 46 tests réussis, 356 assertions. Pint valide les 17 fichiers PHP concernés. Les tests couvrent création/attribution, permissions inconnues, dépendances, rôle protégé, accès directs refusés, élévation de droits, révocation, rôle vide, prix injecté et confidentialité des détails sur le dashboard. Migration indiquée Ran et middlewares des routes vérifiés. Le formulaire Tailwind de permissions a été contrôlé visuellement dans Edge ; les dépendances côté JavaScript n’ont pas été vérifiées manuellement jusqu’au clic final, le contrôle serveur étant testé. Aucun rôle personnalisé ni attribution de démonstration enregistré dans la base active.

Restent à définir : rôles métier préconfigurés à partir de l’usage réel, journal d’audit des changements de rôles, archivage de rôles inutilisés, et éventuelles restrictions par technicien/dossier affecté. Les permissions actuelles portent sur les modules entiers ; aucune restriction par dossier affecté n’est annoncée. Les tests automatisés ne démontrent pas à eux seuls le comportement de requêtes concurrentes MySQL. Le suivi public par code garde son comportement existant.

## Améliorations recommandées et ordre de travail

Les éléments suivants sont des recommandations pour la suite, pas des fonctionnalités déjà livrées. L'objectif prioritaire est de rendre le dépôt utilisable de bout en bout, puis de fiabiliser le travail quotidien. Les fonctionnalités commerciales restent conditionnées aux besoins de l'atelier.

| Ordre | Module | Amélioration recommandée | Critère de réussite |
| --- | --- | --- | --- |
| 1 | AUTH — Accès | Compléter les emails des anciens comptes, prévoir l'initialisation interne du premier administrateur et permettre de changer son propre mot de passe avec vérification du mot de passe actuel. | Les comptes prévus peuvent se connecter ; un utilisateur ne peut pas modifier les accès d'un autre sans permission. |
| 2 | CLIENTS — Répertoire et dépôt | Séparer liste et création, rechercher par nom/téléphone, normaliser les numéros et conserver le client sélectionné jusqu'à la confirmation du dépôt. | Nouveau client et client existant aboutissent tous deux à un dépôt enregistré sans impasse. |
| 3 | DOSSIERS — Réparation | Créer une fiche par réparation avec référence, appareil, problème déclaré, date de dépôt et historique. Prévoir les accessoires confiés et l'état visible à la réception si utiles à l'atelier. | Chaque appareil déposé possède une fiche consultable depuis la liste et le client. |
| 4 | SUIVI — Workflow | Distinguer travaux terminés et appareil restitué ; définir les transitions, les exceptions et l'auteur de chaque changement. Affecter un technicien si plusieurs personnes interviennent. | Une transition invalide est refusée et l'historique explique qui a fait quoi et quand. |
| 5 | RBAC — Rôles et permissions | Mettre en place la matrice proposée ci-dessous avec contrôles serveur et tests ; remplacer progressivement le rôle générique user. | Chaque rôle accède seulement aux dossiers et actions autorisés, même en envoyant une requête directe. |
| 6 | INTERFACE — Usage quotidien | Uniformiser les layouts, les messages, les erreurs, les retours et les boutons ; ajouter pagination, filtres de statut et recherche par référence. | Les parcours fonctionnent sur mobile et ordinateur ; les filtres sont conservés au retour d'une fiche. |
| 7 | PILOTAGE — Tableau de bord | Afficher les dépôts du jour, dossiers en attente, en cours, terminés à restituer et dossiers anciens. Définir précisément chaque compteur et le seuil de retard. | Les compteurs correspondent aux listes filtrées auxquelles ils renvoient. |
| 8 | EXPLOITATION — Données et livraison | Confirmer le dossier public du serveur, isoler les exports, documenter l'installation, automatiser les vérifications et prévoir une sauvegarde avec restauration testée. | Une installation de test et une restauration sont reproductibles sans toucher aux données actives. |
| 9 | COMMERCIAL — À confirmer | Ajouter devis, accord du client, prix, acompte, solde et reçu seulement si le suivi financier fait partie du périmètre. Stocker les montants avec une précision définie et distinguer paiements et statuts techniques. | Si retenu : le solde est calculé à partir des paiements enregistrés et une correction financière conserve une trace. |
| 10 | COMMUNICATION — À confirmer | Prévoir un reçu de dépôt imprimable, puis éventuellement des notifications quand l'appareil est prêt. Définir le canal et les coordonnées à utiliser. | Si retenu : le reçu reprend la référence et les informations du dépôt ; un échec de notification ne bloque pas le dossier. |

Les numéros donnent un ordre indicatif. Les lots existants ci-dessous restent le découpage technique ; CLIENTS correspond au lot 2, DOSSIERS/SUIVI au lot 3, INTERFACE/PILOTAGE au lot 4 et EXPLOITATION au lot 5. AUTH et RBAC prolongent le lot 1. COMMERCIAL et COMMUNICATION sont des extensions à confirmer.

### Proposition de rôles et de permissions

Proposition de départ à valider avant implémentation : administrateur, accueil et technicien. Les droits sont attachés aux actions ; aucun choix de rôle ne doit être proposé sur la page de connexion.

| Action / permission proposée | Administrateur | Accueil | Technicien |
| --- | --- | --- | --- |
| Gérer les comptes et attribuer les rôles (`users.manage`) | Oui | Non | Non |
| Consulter/créer/modifier les clients (`clients.view/create/update`) | Oui | Oui | Coordonnées nécessaires aux dossiers affectés uniquement |
| Enregistrer un dépôt (`repairs.create`) | Oui | Oui | Non |
| Consulter une réparation (`repairs.view`) | Tous les dossiers | Tous les dossiers | Dossiers affectés uniquement |
| Affecter un technicien (`repairs.assign`) | Oui | Non, à confirmer | Non |
| Renseigner diagnostic et travaux (`repairs.work`) | Oui | Lecture uniquement | Dossiers affectés uniquement |
| Changer le statut technique (`repairs.transition`) | Transitions autorisées | Non | Transitions autorisées des dossiers affectés |
| Confirmer la restitution (`repairs.deliver`) | Oui | Oui | Non |
| Réouvrir ou annuler un dossier (`repairs.reopen/cancel`) | Oui, avec motif | Non | Non |
| Consulter les indicateurs (`dashboard.view`) | Vue globale | Vue opérationnelle | Vue de ses dossiers |
| Gérer les paiements, si retenus (`payments.manage`) | Oui | À confirmer | Non |
| Exporter des données (`exports.create`) | Oui, selon périmètre | Non | Non |

« Oui » ne contourne ni les validations ni les transitions autorisées. Les restrictions aux dossiers affectés doivent être vérifiées sur chaque lecture et écriture, pas seulement dans la liste. L'accès technique aux coordonnées du client doit être limité aux champs nécessaires ; l'accès à la pièce d'identité ne doit pas être accordé par défaut au technicien.

- [ ] Valider cette matrice et décider si une même personne peut cumuler accueil et technicien.
- [ ] Préparer une migration explicite des comptes user ; ne pas leur attribuer automatiquement des droits plus étendus.
- [x] Désactiver les comptes plutôt que les supprimer afin de conserver leurs références.
- [x] Révoquer les sessions lors d’une désactivation ou d’un changement sensible des accès.
- [ ] Traiter les modifications concurrentes : deux employés ne doivent pas écraser silencieusement un changement de statut ; le contrôle du dernier administrateur doit aussi résister à deux requêtes simultanées.

### Règles de qualité à appliquer aux prochains modules

- [ ] Enregistrer de façon atomique les opérations liées : création d'un dossier et de sa première trace, transition et historique, paiement et mouvement associé si le module financier est retenu.
- [ ] Éviter les doublons lors d'un double clic ou d'une nouvelle soumission du formulaire de dépôt.
- [ ] Remplacer la suppression définitive des dossiers par un archivage ou une annulation motivée selon la règle métier retenue ; examiner la suppression en cascade client → réparations avant d'activer une suppression de client.
- [ ] Collecter seulement les informations utiles et définir ensemble la conservation des pièces d'identité, coordonnées et exports. Ne pas stocker de code de déverrouillage d'appareil dans un commentaire libre.
- [ ] Conserver les mots de passe, jetons, codes d'appareil et données personnelles hors des logs et de cette roadmap.
- [ ] Pour chaque module : définir le parcours, les permissions et les données, puis vérifier un cas réussi, un cas invalide et un accès refusé ; contrôler le rendu des écrans modifiés dans le navigateur.

Prochaine étape recommandée : terminer la reprise des accès existants, puis réaliser CLIENTS et le dépôt complet. Valider la matrice avant d'implémenter les permissions détaillées.

## Module AUTH — Authentification, rôles et permissions

Mise à jour : 26 octobre 2026. Ce module actualise les constats SEC-01, SEC-02, SEC-03 et VAL-01 de l'audit initial ci-dessous ; le tableau initial reste une photographie de départ.

### Réalisé dans cette première étape

- [x] Connexion par email et mot de passe ; le nom reste une information de profil.
- [x] Normalisation de l'email (espaces supprimés aux extrémités, minuscules).
- [x] Suppression du lien, de la vue, des routes GET/POST et du code d'inscription publique administrateur.
- [x] Page de connexion dédiée à l'atelier, responsive, indépendante de la navigation interne, avec erreurs par champ, email conservé et bouton afficher/masquer le mot de passe.
- [x] Gestion des comptes réservée aux administrateurs côté serveur ; route /admin redirigée vers la vraie liste protégée.
- [x] Email obligatoire et unique dans la création/modification des comptes ; mot de passe de 8 caractères minimum pour ces actions.
- [x] Protection du dernier administrateur contre la suppression et la rétrogradation dans les actions de gestion.
- [x] Régénération de session à la connexion ; invalidation à la déconnexion ; routes guest/auth appropriées.
- [x] Limitation après 5 échecs par combinaison email/IP pendant 60 secondes et message d'erreur sans divulgation de l'existence du compte.
- [x] Compte a associé à l'email fourni par l'utilisateur ; mot de passe et rôle standard conservés. Aucun identifiant sensible n'est consigné dans ce document.
- [x] Tests de connexion, refus des identifiants invalides, limitation, inscription désactivée, droits administrateur, unicité de l'email et déconnexion.

### À réaliser : rôles et permissions détaillés

État de la première étape AUTH : admin/user uniquement. Actualisation : le module RBAC ci-dessus livre désormais des rôles personnalisés et des permissions par action.

- [ ] Définir ensemble les rôles métier (administration, accueil, technicien selon les besoins).
- [ ] Valider la matrice des permissions : consulter/créer/modifier les clients, recevoir un appareil, consulter les dossiers, changer les statuts, restituer un appareil, gérer les comptes.
- [x] Stocker les rôles/permissions et appliquer la migration avec reprise des rôles existants.
- [x] Centraliser les autorisations dans les Gates et les appliquer aux routes/actions et à l’affichage des boutons.
- [x] Tester les permissions autorisées/refusées, y compris sur les requêtes envoyées directement.
- [ ] Prévoir l'attribution et la révocation des rôles et l'historique des changements sensibles.
- [ ] Définir la création du premier administrateur par une commande interne, sans rétablir d'inscription publique.
- [ ] Définir la récupération du mot de passe et la configuration email avant de proposer un lien de réinitialisation.
- [ ] Renseigner les emails réels de tous les anciens comptes : après mise à jour du compte demandé, 1 compte de la base active reste sans email. Aucun email fictif n'a été attribué. Ce compte ne pourra plus se connecter par son nom.
- [ ] Après reprise des anciens comptes, rendre email non nullable en base par une migration corrective. La contrainte unique existe déjà dans les migrations initiales.

### Validation de cette étape

Commande : php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit

Résultat : 12 tests réussis, 51 assertions, sur SQLite en mémoire. Le PHP CLI local n'active pas PDO SQLite par défaut : la commande php artisan test seule échoue sur le pilote manquant. L'extension est chargée uniquement pour cette exécution, sans modification globale du poste.

Le rendu HTML de la connexion est couvert par un test HTTP. La validation visuelle dans un navigateur reste à faire. Aucune migration de la base active n'a été exécutée.

## Module CLIENTS — Répertoire et création (26 octobre 2026)

- [x] Remplacer le formulaire affiché sur /clients par une vraie liste triée et paginée (12 clients par page).
- [x] Rechercher par nom/prénom combinés ou par téléphone, en ignorant les séparateurs usuels du numéro.
- [x] Filtrer les clients avec réparation en attente/en cours et les clients sans dépôt ; conserver les filtres dans la pagination.
- [x] Afficher les compteurs globaux, le nombre de dépôts et les suivis actifs par client, sans chargement des réparations ligne par ligne.
- [x] Ajouter le formulaire /clients/create, les validations, les erreurs par champ et la conservation des saisies.
- [x] Normaliser les numéros à la création et repérer les doublons parmi les anciennes écritures avec espaces, tirets, points et parenthèses. Ne pas convertir implicitement un numéro local vers un pays donné.
- [x] Enregistrer uniquement les champs validés et proposer deux suites : fiche client ou nouveau dépôt avec client sélectionné.
- [x] Harmoniser liste, création et fiche client ; paginer l'historique et ajouter un accès direct au dépôt.
- [x] Limiter les routes clients aux actions réellement disponibles.
- [x] Vérifier visuellement la liste à deux largeurs de fenêtre et le formulaire de création dans Edge sur le domaine local ; ne créer aucune donnée de démonstration dans la base active.

Validation : php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit — 18 tests réussis, 99 assertions. Couverture : accès invité, recherche, filtres, compteurs, création, redirections, données invalides, doublons existants, pagination et historique.

Restent à faire : refonte du formulaire de réparation, règles de transitions, permissions fines et modification des clients. La validation locale/internationale d'un même numéro doit être définie avec la règle pays avant une migration des anciens numéros. La contrainte unique actuelle protège les numéros identiques en base ; une normalisation complète des anciennes données et sa protection contre les créations concurrentes restent à préparer. Le rendu sur un téléphone réel n'a pas été vérifié.

## Module COMPTES — Gestion des accès utilisateurs (26 octobre 2026)

- [x] Gestionnaire réservé aux administrateurs sur /users, accessible depuis la navigation.
- [x] Recherche par nom/email, filtres par rôle et état, compteurs et pagination.
- [x] Création et modification avec email unique normalisé et confirmation du mot de passe (8 à 72 caractères) ; mot de passe haché par le modèle et jamais réaffiché.
- [x] Validation dédiée SaveUserRequest ; mises à jour sensibles centralisées dans UpdateAccount.
- [x] Activation/désactivation du compte. L'ancienne action DELETE désactive désormais le compte sans effacer ses références.
- [x] Connexion refusée aux comptes désactivés ; date de dernière connexion enregistrée.
- [x] Version des sessions contrôlée à chaque requête web : email, rôle, mot de passe ou activation modifiés imposent une nouvelle connexion. Les anciennes sessions ne retrouvent pas leur validité après réactivation.
- [x] Désactivation de son propre compte interdite ; maintien d'au moins un administrateur actif, vérifié dans une transaction avec verrouillage ordonné des comptes. Les tests SQLite couvrent la règle fonctionnelle ; la concurrence effective MySQL reste à tester.
- [x] Migration additive 2026_10_26_140000 appliquée : is_active, session_version et last_login_at. Comptes existants conservés et actifs par défaut.
- [x] Ancienne méthode User::admin() qui retournait une vue supprimée.

Validation : php artisan test exécuté avec PDO SQLite chargé temporairement pour cette commande et ses sous-processus — 31 tests réussis, 228 assertions. Pint appliqué aux 8 fichiers PHP concernés ; routes vérifiées et migration indiquée Ran. Aucun taux de couverture mesuré ; ne pas assimiler le nombre de tests à une couverture de 85 %. Les trois vues du gestionnaire sont rendues par les tests HTTP ; la validation visuelle du gestionnaire connecté dans le navigateur reste à faire.

Accès initial : la base actuelle dispose déjà d’un administrateur actif avec un email renseigné ; aucune attribution d’email ni promotion n’a été faite pendant cette étape.

À compléter : commande interne de création du premier administrateur ; changement de mot de passe par l'utilisateur lui-même ; journal d'audit des changements de comptes ; permissions fines. Aucun email fictif, aucune promotion automatique du compte standard, aucun envoi de mot de passe par email. Les rôles restent admin/user à ce stade.

## Diagnostic général

Le projet possède déjà les bonnes entités de départ : utilisateurs, clients et réparations. Le problème principal est le manque de continuité entre les écrans, les actions réellement implémentées et les règles métier. Une réécriture complète ou un changement de framework ne paraît pas nécessaire à ce stade.

## Incohérences constatées

| ID | Priorité | Constat et preuve | Conséquence | Correction prévue |
| --- | --- | --- | --- | --- |
| SEC-01 | P0 | `routes/web.php` protège `users.*` uniquement par `auth`. `AdminMiddleware` existe, mais n'est ni appliqué à ces routes ni déclaré comme alias dans `bootstrap/app.php`. | Un compte standard peut accéder aux actions de gestion des comptes, y compris créer un administrateur. | Appliquer une autorisation administrateur côté serveur à toutes les actions concernées ; tester les refus en lecture et en écriture. |
| SEC-02 | P0 | `AuthController::registerForm()` vérifie l'existence d'un administrateur, mais `register()` ne le fait pas. Le code d'entrée est codé en dur et affiché dans le champ du formulaire. | Un POST direct peut créer un autre administrateur après l'installation. | Définir un mécanisme d'initialisation unique, contrôlé côté serveur, sans secret affiché ; traiter également les requêtes concurrentes. |
| FLOW-01 | P1 | `ClientController::create()` appelle `clients.create`, mais cette vue n'existe pas. `clients/index.blade.php` contient le formulaire de création. | Le lien « Créer un nouveau client » aboutit à une erreur ; l'écran annoncé comme liste ne liste pas les clients. | Séparer liste, création et fiche client. |
| FLOW-02 | P1 | Après création d'un client, `store()` renvoie vers `clients.index`. Le bouton « Ajouter réparation » de cette vue ne transmet aucun `client_id`. | L'utilisateur perd le contexte du client et arrive sur « Aucun client sélectionné ». | Conserver le client dans le parcours et proposer immédiatement le dépôt d'un appareil. |
| FLOW-03 | P1 | Les routes resource exposent des méthodes absentes : clients `edit/update/destroy`, réparations `show/edit/update/destroy`, utilisateurs `show`. | Des URL déclarées aboutissent à des actions inexistantes. | Limiter les resources avec `only()` aux actions disponibles ; ajouter les autres uniquement lorsqu'elles sont nécessaires. |
| FLOW-04 | P1 | `reparations/show.blade.php` affiche une fiche client et son historique, avec `$client` et `$reparations`, mais aucune méthode `ReparationController::show()` ne l'alimente. | Il manque une vraie fiche de réparation consultable par référence. | Créer une fiche dédiée et ajouter les liens depuis les listes et la fiche client. |
| FLOW-05 | P1 | `updateStatut()` accepte tout passage entre `en_attente`, `en_cours` et `termine`. Aucune règle de transition, aucun auteur ni historique n'est enregistré. | Impossible de distinguer progression, réouverture et erreur de manipulation ; aucun suivi de restitution. | Définir les transitions et les droits, puis journaliser chaque changement. |
| DATA-01 | P1 | `ReparationController::store()` utilise `$request->all()` ; `statut` et `date_reception` figurent dans les attributs remplissables sans être validés ici. | Une requête construite manuellement peut imposer un statut initial ou une date non prévus. | Utiliser les seules données validées et fixer les valeurs métier côté serveur. Faire de même pour la création des clients. |
| DATA-02 | P1 | `date_depot` et `date_reception` coexistent sans définition distincte. La migration définit `date_reception` avec `default(now())` ; le dump SQL montre une date littérale de 2025. | Une date par défaut calculée lors de la migration peut être réutilisée pour des dépôts ultérieurs. | Choisir une date de dépôt de référence et générer la date courante à chaque création ; préparer la reprise des données existantes. |
| SEC-03 | P1 | La suppression de son propre compte est bloquée, mais la rétrogradation du dernier administrateur ne l'est pas. | Possibilité de perdre tout accès à l'administration. | Protéger la présence d'au moins un administrateur, y compris lors des mises à jour de rôle. |
| VAL-01 | P1 | `UserController::update()` appelle `$request->validate(['password'])` sans règle associée. | Validation du changement de mot de passe incorrecte ; le minimum utilisé à la création n'est pas garanti. | Définir une règle explicite pour le mot de passe facultatif et tester les valeurs invalides. |
| UI-01 | P2 | Plusieurs vues incluent un document HTML complet (`layouts.app`) dans leur propre `<head>` ou `<body>`. | Documents imbriqués, chargements Bootstrap répétés et messages affichés de façon incohérente. | Utiliser un seul layout avec `@extends` et des sections ; extraire la navigation et les messages communs. |
| UI-02 | P2 | Les formulaires clients/réparations restituent peu les erreurs de validation et n'utilisent pas `old()` pour récupérer les saisies. | Après un refus serveur, l'utilisateur comprend mal le problème et doit ressaisir les champs. | Afficher les erreurs près des champs et conserver les valeurs non sensibles. |
| NAV-01 | P2 | `/admin` charge directement `users.index` sans `$users` ; `AuthController::admin()` et `User::admin()` visent une vue absente. Deux vues de dashboard coexistent, dont `clients/dashboard` sans route dédiée. | Plusieurs points d'entrée contradictoires et code inutilisé. | Garder un point d'entrée par rôle, supprimer les chemins morts et retirer la logique de vue du modèle User. |
| LIST-01 | P2 | Les listes chargent tous les enregistrements via `all()` ou `get()` ; la recherche client utilise le téléphone exact sans normalisation. | Listes peu adaptées au volume ; un même numéro écrit différemment peut ne pas être retrouvé. | Ajouter pagination, recherche et filtres ; définir une normalisation adaptée aux numéros réellement utilisés. |
| DB-01 | P2 | La migration nommée `remove_email_from_users_table` ajoute en réalité `role`. La connexion utilise `name`, unique dans les validations mais pas dans la migration initiale. | Historique de schéma trompeur ; unicité du nom non garantie en base. | Documenter l'historique, rechercher les doublons et ajouter une migration corrective ; ne pas renommer les migrations déjà appliquées sans stratégie. |
| OPS-01 | P1 à vérifier | `index.php` et `.htaccess` existent à la racine en plus de `public/`. Un dump SQL contenant clients, utilisateurs et sessions est versionné. | Si le serveur sert la racine du dépôt, certains fichiers internes peuvent être exposés. L'exposition HTTP effective n'a pas été vérifiée. | Vérifier le document root Laragon et du déploiement, cibler `public/`, sortir les exports de données du dépôt et traiter les données déjà présentes dans l'historique si nécessaire. |
| TEST-01 | P1 | Les deux tests sont ceux du squelette. Le test HTTP attend 200 sur `/`, alors que la route redirige vers `/login`. | Suite déjà en échec et absence de protection des parcours critiques. | Corriger l'attente puis couvrir les droits, l'initialisation et le dépôt complet. |
| MAINT-01 | P2 | README générique Laravel avec une fin contenant des caractères NUL ; dépendance Prisma sans usage trouvé dans le code applicatif examiné ; route `/test-message` publique. | Installation mal documentée et éléments techniques dont l'utilité reste à clarifier. | Écrire un README projet, vérifier les dépendances et supprimer les éléments de démonstration inutiles. |

P0 : contrôle d'accès à traiter en premier. P1 : parcours bloqué, intégrité ou fiabilité. P2 : cohérence et maintenance.

## Parcours cible proposé

1. L'employé se connecte et accède à la liste des réparations avec une action « Nouveau dépôt » visible.
2. Il recherche le client par téléphone normalisé ou par nom.
3. Si le client existe, il le sélectionne et consulte son historique. Sinon, il le crée sans quitter le parcours de dépôt.
4. Il saisit l'appareil, le problème et la date de dépôt. Le serveur attribue la référence et le statut initial.
5. La confirmation ouvre la fiche de réparation, avec le client, les informations de dépôt et les actions autorisées.
6. Les changements de statut suivent les règles retenues et sont historisés avec date et auteur.
7. La fin des travaux et la restitution au client sont distinguées si ce besoin est confirmé.

Proposition minimale à discuter : `en_attente → en_cours → termine → restitue`. Le diagnostic, le devis, l'attente de pièces, l'annulation, l'irréparabilité et les retours en arrière doivent être définis selon le fonctionnement réel de l'atelier, avant de multiplier les statuts.

## Structure technique proposée

Conserver Laravel, Eloquent et Blade. Organiser par responsabilité, avec une extraction progressive :

```text
app/
  Http/
    Controllers/                 # Actions HTTP et redirections
    Requests/                    # Validation des formulaires
    Middleware/                  # Accès administrateur
  Models/                        # Données, relations et casts
  Enums/ReparationStatut.php      # Statuts partagés
  Actions/Reparations/           # Création et transitions si elles le justifient
  Policies/                      # Autorisations métier par ressource
resources/views/
  layouts/app.blade.php           # Document HTML commun
  partials/                      # Navigation et messages
  clients/                       # index, create, show
  reparations/                   # index, create, show
  users/                         # Gestion réservée aux administrateurs
tests/Feature/                   # Accès et parcours métier
```

Ne pas introduire de couches de services ou de repositories systématiques : extraire uniquement les règles qui ont une responsabilité propre. Utiliser des migrations correctives pour une base déjà utilisée ; réserver les réinitialisations aux bases de test jetables.

## Lots de réalisation

### Lot 1 — Sécuriser les accès et l'initialisation

- [x] SEC-01 : protéger toutes les routes utilisateurs et résoudre `/admin`.
- [x] SEC-02, fermeture publique : supprimer l'inscription administrateur contournable.
- [ ] SEC-02, installation : ajouter une commande interne d'initialisation du premier administrateur.
- [x] SEC-03 et VAL-01 : protéger le dernier administrateur dans les actions existantes et corriger la validation des mots de passe ; renforcement contre les requêtes concurrentes à prévoir.
- [ ] Vérifier le document root et le traitement des exports SQL (OPS-01).
- [x] Ajouter les tests : invité redirigé, utilisateur standard refusé sur les actions administratives, administrateur autorisé, inscription publique refusée par POST direct.
- [ ] Tester la commande interne d'initialisation lorsqu'elle sera ajoutée.

Critère de fin : les droits sont garantis côté serveur, indépendamment des liens affichés.

### Lot 2 — Rétablir un dépôt complet

- [x] Créer la vraie liste des clients et le formulaire manquant (FLOW-01).
- [ ] Conserver le client sélectionné jusqu'au dépôt ; gérer un `client_id` invalide (FLOW-02).
- [ ] Limiter les routes aux méthodes existantes (FLOW-03).
- [ ] Utiliser les données validées et imposer le statut initial (DATA-01).
- [ ] Afficher erreurs et anciennes saisies (UI-02).
- [ ] Tester : nouveau client → dépôt ; client existant → nouveau dépôt ; refus des données invalides et d'un statut initial injecté.

Critère de fin : un employé peut enregistrer un dépôt sans impasse ni ressaisie du client.

### Lot 3 — Structurer le suivi des réparations

- [ ] Valider les statuts et transitions avec le fonctionnement de l'atelier.
- [x] Créer la fiche de réparation et ses liens (FLOW-04).
- [x] Centraliser les statuts et appliquer les transitions autorisées (FLOW-05).
- [x] Ajouter l'historique : ancien statut, nouveau statut, auteur, date et éventuel motif.
- [ ] Clarifier les dates et préparer une migration de données contrôlée (DATA-02).
- [x] Tester les transitions permises/interdites et la conservation de l'historique.

Critère de fin : chaque dossier a un état compréhensible et une progression traçable.

### Lot 4 — Unifier les écrans et la navigation

- [ ] Passer les vues au layout commun (UI-01).
- [ ] Harmoniser la navigation et les points d'entrée selon le rôle (NAV-01).
- [ ] Ajouter recherche, filtres, pagination et états de liste vide (LIST-01).
- [ ] Ajouter au tableau de bord les compteurs utiles, après validation de leur définition.
- [ ] Vérifier les parcours dans le navigateur sur ordinateur et mobile.

Critère de fin : les mêmes actions et messages fonctionnent de manière cohérente sur tous les écrans.

### Lot 5 — Fiabiliser l'installation et la maintenance

- [ ] Vérifier la reconstruction du schéma dans une base jetable et la compatibilité avec les données existantes.
- [ ] Revoir l'unicité du nom de profil, désormais distinct de l'email de connexion, et documenter les migrations ambiguës (DB-01).
- [x] Corriger le test de redirection (TEST-01).
- [ ] Étendre la suite métier aux parcours de dépôt et de suivi à mesure de leur implémentation.
- [ ] Remplacer le README par les instructions réelles : installation, base, premier administrateur, lancement, tests et sauvegardes.
- [ ] Examiner Prisma, les vues inutilisées et les routes de démonstration (MAINT-01).

Critère de fin : une autre personne peut installer le projet et vérifier les parcours à partir de la documentation.

## Décisions métier à prendre ensemble

- Qui fait quoi : administrateur, accueil, technicien ? Le rôle `user` suffit-il ?
- L'atelier distingue-t-il réparation terminée et appareil remis au client ?
- Faut-il diagnostic, devis accepté, attente de pièces, annulation et réouverture ?
- Faut-il gérer prix, acompte, solde et reçu dans ce projet ? Aucun module dédié n'a été repéré.
- La pièce d'identité est-elle toujours obligatoire ? Quelles informations sont vraiment nécessaires au dépôt ?
- Quels numéros sont utilisés : locaux, internationaux, plusieurs contacts par client, numéro partagé ?
- Quelles données existantes doivent être conservées et quelle est la base de référence : base active, dump SQL ou autre fichier ?
- Peut-on corriger ou archiver un dépôt ? Qui a ce droit, et quelles traces conserver ?

Ces décisions conditionnent les lots concernés ; elles ne bloquent pas le diagnostic ni la préparation des premières corrections techniques.

## Vérifications de l'audit initial

- `php artisan route:list --except-vendor` : succès, 33 routes affichées. La déclaration des routes ne garantit pas l'existence des méthodes correspondantes ; celles-ci ont été comparées au code.
- `php artisan test` : 1 test réussi, 1 test échoué. Échec du test HTTP de démonstration : 200 attendu, 302 reçu sur `/`, conformément à la redirection présente dans les routes.
- Lecture des contrôleurs, modèles, migrations, vues principales, scripts Composer/npm, configuration PHPUnit et points d'entrée HTTP.
- Inspection de la structure du dump SQL sans reproduire les données personnelles dans ce document.
- Pas de migration exécutée sur la base active, pas d'import SQL et pas de modification des règles applicatives.

## Journal de suivi

| Date | Travail | Résultat |
| --- | --- | --- |
| 2026-10-26 | Audit initial et création de cette roadmap | Constats documentés, parcours cible proposé, 5 lots définis. Implémentation à venir. |
| 2026-10-26 | Module COMPTES | Gestion des comptes, activation et révocation des sessions. Migration appliquée ; 31 tests et 228 assertions réussis. Administrateur actif avec email présent dans la base actuelle. |
| 2026-10-26 | Module AUTH, étape 1 | Connexion email, nouvelle page, inscription publique supprimée, gestion des comptes protégée, compte demandé mis à jour. 12 tests / 51 assertions réussis. Permissions détaillées à définir. |
| 2026-10-26 | Module CLIENTS | Répertoire, recherche/filtres, pagination, création et fiche refondus. Parcours vers le dépôt conservé. 18 tests / 99 assertions réussis ; liste et formulaire vérifiés dans Edge. |
| 2026-10-26 | Recommandations et permissions proposées | Ordre de travail par module, matrice administrateur/accueil/technicien, critères de réussite et règles de qualité ajoutés. Cases actualisées selon le travail déjà réalisé. Documentation uniquement pour cette étape. |

À chaque lot terminé : cocher les actions, noter les décisions prises, les tests exécutés et les limites restantes dans ce journal.

## Correctif environnement PHP — 26 octobre 2026

Cause : le PHP CLI par défaut du poste est 8.4.15, alors que le serveur Apache de Laragon charge PHP 8.3.30. Le verrou Composer contenait cinq composants Symfony 8.1 exigeant PHP >= 8.4.1.

- [x] Définir config.platform.php à 8.3.30 dans composer.json pour aligner la résolution des dépendances sur le serveur.
- [x] Remplacer uniquement les cinq composants incompatibles (clock, css-selector, event-dispatcher, string et translation) par Symfony 7.4 et actualiser composer.lock/vendor.
- [x] Réunir les deux clés post-update-cmd dupliquées en conservant les deux scripts.
- [x] Reconstruire la découverte des packages et vider les caches Laravel. Le contrôle de plateforme Composer reste actif.
- [x] Vérifier les exigences de plateforme et exécuter les tests sous le PHP 8.3.30 réel de Laragon : 31 tests réussis, 228 assertions.
- [x] Vérifier HTTP /login : statut 200, formulaire email présent, aucune erreur de plateforme. Composer validate est valide.

À suivre séparément : composer audit signale des avis pour phpunit/phpunit (CVE-2026-24765) et psy/psysh (CVE-2026-25129) ; préparer leur mise à jour ciblée et ses tests. La configuration Composer existante indique également TLS désactivé ; rétablir sa vérification après examen de la configuration des certificats. Ces points ne sont pas corrigés par le présent alignement PHP.

## Module DASHBOARD — Vue d’ensemble de l’atelier (26 octobre 2026)

- [x] Remplacer l’écran de bienvenue par un tableau de bord Tailwind CSS v4 : navigation latérale sur bureau, menu compact sur fenêtre étroite et profil connecté.
- [x] Afficher les dépôts du jour et les réparations en attente, en cours et terminées à partir de la base réelle.
- [x] Afficher les dépôts quotidiens des sept derniers jours et la différence en nombre de dépôts avec les sept jours précédents ; inclure les jours sans activité.
- [x] Afficher la répartition actuelle de tous les dossiers par statut, avec effectifs et pourcentages.
- [x] Présenter les cinq dépôts les plus récents, leur client, référence, date et statut, avec accès au dossier.
- [x] Ajouter nouveau dépôt, répertoire clients et prochaine action adaptée à la charge actuelle ; réserver le lien comptes utilisateurs aux administrateurs.
- [x] Relier les cartes aux listes filtrées, y compris un filtre par date de dépôt ajouté à la liste des réparations.
- [x] Séparer les agrégations dans GetDashboardData et utiliser un contrôleur dédié ; précharger les clients des derniers dépôts.
- [x] Compiler une entrée CSS dédiée au tableau de bord avec Vite afin de ne pas charger Bootstrap et Tailwind sur le même écran. Ajouter le lien Tableau de bord dans la navigation existante.
- [x] Prévoir des états vides et des descriptions accessibles des graphiques ; vérifier visuellement le dashboard connecté dans Edge à deux largeurs de fenêtre.

Validation : npm run build réussi ; php artisan test sous PHP 8.3.30 de Laragon : 34 tests réussis, 258 assertions. Tests ajoutés sur accès invité, visibilité selon rôle, base vide, bornes des périodes, compteurs par statut, tri/limite des dossiers récents et filtre de date. Pint et route dashboard vérifiés. Aucune donnée métier de démonstration ajoutée, aucune migration requise.

Définitions : les dépôts sont comptés selon date_depot ; les états sont les statuts actuels des dossiers, tous dépôts confondus. Terminée signifie travaux terminés et ne prouve pas une restitution. Aucun chiffre d’affaires, délai moyen ou historique de clôture n’est inventé. Les données sont actualisées au chargement de la page ; le graphique ne constitue pas une synchronisation en temps réel.

À poursuivre : validation sur téléphone réel, harmonisation progressive des autres pages avec le nouveau layout, filtres de périodes plus larges si utiles et indicateurs de délai après ajout des dates/historiques métier nécessaires.

## Module NAVIGATION — Cadre commun (26 octobre 2026)

- [x] Remplacer les deux systèmes de liens internes par un composant commun partagé entre les layouts Bootstrap et Tailwind.
- [x] Menu latéral clair, libellés lisibles, icônes sobres, groupes Atelier et Administration et état actif neutre.
- [x] Corriger l’indication de page active, y compris sur les formulaires et sous-pages ; ne plus marquer le dashboard actif sur une autre rubrique.
- [x] Supprimer les onglets Comptes/Rôles redondants avec la navigation principale.
- [x] Ajouter un fil d’Ariane contextuel et un menu de compte avec identité réelle, rôle, email et déconnexion.
- [x] Conserver Nouveau dépôt comme action distincte des liens de rubriques.
- [x] Proposer un volet mobile avec dialogue natif, fermeture par bouton/Echap et arrière-plan, confinement du focus natif, et fermeture lors du passage à la largeur bureau.
- [x] Filtrer les liens par les permissions existantes, identiquement sur bureau et mobile ; contrôles serveur inchangés.
- [x] Compiler des styles Tailwind isolés sans reset global pour ne pas déformer les anciennes pages Bootstrap ; préserver le ticket imprimable.

Validation : 47 tests réussis, 386 assertions sous PHP 8.3.30. Le test de navigation vérifie cinq pages et une seule rubrique active dans chacune des navigations bureau/mobile. Compilation Vite réussie ; syntaxe JavaScript et espaces de fin de ligne vérifiés. Lint design ciblé : 0 FAIL, 1 WARN pour l’ombre du menu de compte, justifiée car il s’agit d’un élément superposé ouvert à la demande. Intervention limitée au cadre de navigation ; palette et typographie existantes conservées, sans tirage d’une nouvelle direction de page.

Limite de vérification : le contrôle visuel navigateur final est à reprendre. Après récupération de la fenêtre Edge, la capture a échoué deux fois avec « encode latest capture frame failed: window crop is outside captured monitor ». Aucun résultat d’inspection navigateur à trois largeurs ni test manuel final du volet n’est revendiqué. Aucun changement de données ou de droits utilisateurs.

## Module HOME — Accueil public client (26 octobre 2026)

- [x] Créer une page publique à la racine du site, accessible avec ou sans connexion.
- [x] Présenter le suivi des réparations et expliquer l’usage du code figurant sur le ticket de dépôt.
- [x] Limiter la page à deux liens : Se connecter et Suivre ma réparation.
- [x] Relier le suivi au parcours public existant /suivi, sans compte client obligatoire.
- [x] Créer une présentation responsive avec styles Tailwind isolés et compilation Vite dédiée.
- [x] Tester les deux destinations et l’absence de navigation interne sur l’accueil.

Validation : 49 tests réussis, 402 assertions sous PHP 8.3.30 de Laragon ; compilation Vite et Pint réussis. Vérification HTTP réelle : accueil 200 sans redirection, liens connexion et suivi présents. Lint design ciblé : 0 FAIL, 0 WARN. Direction visuelle : ticket de dépôt, fond clair et vert profond (page-is-the-object, seed 371346904).

Limite : vérification visuelle finale à reprendre ; une interaction utilisateur détectée dans Edge a interrompu le contrôle navigateur. Aucun contrôle visuel complet sur plusieurs largeurs n’est revendiqué.

Périmètre : le suivi correspond aux réparations déjà déposées ; aucun système de prise de réservation n’est ajouté. La compétence template-creator a été examinée, mais aucun modèle réutilisable n’a été créé en l’absence de référence fournie.

À poursuivre : remplacer le nom générique Atelier Réparations par l’identité réelle de l’atelier, puis valider le rendu sur téléphone réel.
## Module HOME — Contenus de l’atelier et entretien (26 octobre 2026)

- [x] Préparer des sections Blade réutilisables pour l’histoire de l’atelier (sens confirmé par le responsable du projet), les services, le contact avec horaires et les questions fréquentes.
- [x] Centraliser les textes dans config/workshop.php ; masquer les sections vides et les services/questions incomplets. Échapper les textes avec Blade.
- [x] Conserver les deux liens connexion et suivi ; aucune coordonnée, ancienneté, garantie ou prestation inventée.
- [x] Ajouter docs/FICHE-ATELIER.md pour recueillir les informations auprès du réparateur, préparer les textes et consigner leur validation avant publication.
- [x] Adapter les styles de l’accueil à ces sections dans la direction visuelle existante, sans nouvelle identité.

Validation : 51 tests réussis, 416 assertions sous PHP 8.3.30 ; Pint et compilation Vite réussis. Les tests couvrent les contenus configurés, leur échappement et le masquage des sections non renseignées. Pas de validation visuelle navigateur revendiquée pour ces sections encore vides.

À faire ensemble : remplir la fiche avec l’atelier, valider les coordonnées et horaires, renseigner les textes, puis contrôler le rendu avec les contenus réels sur bureau et téléphone. Une interface d’édition administrateur pourra suivre si la modification par fichier devient insuffisante. Les formulaires de contact, WhatsApp, réservation et garanties restent à définir avant activation.

Les modèles livrés sont des sections Laravel et une fiche d’entretien ; aucun artifact-template Codex n’est créé. Template Creator exige une référence supportée et ne prend pas ce projet Laravel comme référence de Site.

## HOME — Liens vers les sections publiques (27 octobre 2026)

- [x] Ajouter sous l’en-tête les liens Notre histoire, Services, Contact et horaires et Questions fréquentes.
- [x] Relier chaque lien à une ancre de la page ; conserver connexion et suivi.
- [x] Afficher un message d’attente lorsque le contenu manque, afin que chaque lien dispose toujours d’une destination. Cette demande remplace le masquage initial des sections vides.

Validation : 4 tests accueil réussis (32 assertions), compilation Vite réussie. Contenus réels toujours à renseigner avec le réparateur ; contrôle visuel final à reprendre.

## HOME — Textes de démonstration (27 octobre 2026)

- [x] À la demande du responsable du projet, remplir histoire, services, contact/horaires et FAQ avec des contenus fictifs cohérents.
- [x] Indiquer publiquement leur caractère de démonstration ; email sous domaine .example et téléphone non composable pour éviter de désigner une personne réelle.
- [x] Centraliser les exemples dans config/workshop.php, avec indicateur demo à désactiver lors du remplacement par les informations validées.

Validation : tests accueil et compilation réussis. Remplacer les textes et coordonnées après entretien ; vérifier le rendu visuel final.

## HOME — Refonte pour téléphone (27 octobre 2026)

- [x] Remplacer les liens empilés par un menu mobile natif repliable ; navigation horizontale sur bureau.
- [x] Fermer le menu après sélection, clic extérieur, touche Échap et passage au format bureau ; restaurer le focus approprié.
- [x] Rendre le suivi prioritaire : texte raccourci, bouton pleine largeur sur téléphone et cibles tactiles de 44 à 56 pixels.
- [x] Réduire le ticket sur mobile, adapter titres et marges et organiser les services sur deux colonnes dès la tablette.
- [x] Distinguer le contact par un fond vert profond, corriger le contraste des libellés et conserver les FAQ repliables.
- [x] Garder les routes, les contenus configurables et l’indication de démonstration ; aucun changement métier ou de données.

Validation : 51 tests réussis, 422 assertions ; compilation Vite, syntaxe JS et lint source design réussis (0 FAIL, 0 WARN). Contrôle visuel réel via Edge sur bureau, puis simulation 390 × 844, 320 × 844 et 768 × 844. Menu ouvert et lien Contact testés à 390 pixels ; fermeture du menu et navigation vers la section constatées. Le contraste du contact a été corrigé puis revérifié. Aucun débordement apparent sur les captures du premier écran aux trois formats simulés. La direction ticket/vert et la typographie existantes sont conservées ; il s’agit d’une adaptation de composition. L’inspecteur CLI n’a pas été utilisé : contrôle via l’outil de navigateur disponible.

À poursuivre : essai sur téléphone physique, validation des textes et coordonnées réels. Aucun artifact-template Codex créé : Template Creator exige une référence supportée ; la présente intervention améliore les vues Laravel existantes.


## Module SUIVI — Transitions, restitution et historique automatique (27 octobre 2026)

- [x] Centraliser les statuts dans RepairStatus : en_attente → en_cours → termine → restitue. Aucune étape sautée ou marche arrière ; Restituée est terminale.
- [x] Distinguer Terminée (travaux finis, appareil encore à remettre) et Restituée (remise au client confirmée). Aucune ancienne réparation terminée n’est automatiquement marquée restituée.
- [x] Ajouter status_version et repair_status_changes via migration appliquée à la base active. Historique daté avec ancien/nouveau statut, auteur authentifié et nom conservé au moment de l’action.
- [x] Enregistrer la création des nouveaux dépôts et chaque transition dans une transaction. Un échec de journalisation annule le changement de statut.
- [x] Verrouiller la ligne et vérifier la version affichée pour refuser une requête obsolète (409). Une sélection identique ne crée pas de faux changement ; une répétition obsolète ne duplique pas l’historique.
- [x] Enregistrer automatiquement lors du changement du sélecteur, depuis la liste et la fiche, avec indication d’attente et d’erreur. Bouton de secours si JavaScript indisponible ; actualisation après succès pour resynchroniser compteurs et filtres.
- [x] Afficher l’historique paginé dans la fiche interne et adapter filtres, compteurs, graphique du dashboard, fiche client et suivi public aux quatre statuts. Aucun nom d’employé exposé dans le suivi public.
- [x] Réutiliser repairs.change_status pour l’avancement et la restitution, avec description explicite dans le catalogue de permissions.

Les anciennes actions ne sont pas inventées : leur historique commence au premier changement effectué après cette migration. L’enregistrement initial d’un nouveau dépôt conserve son auteur. Le fuseau affiché dans l’historique est celui configuré dans Laravel.

Règles choisies pour ce lot : progression linéaire uniquement. Réouverture, annulation et retour exceptionnel avec motif restent à définir ; aucun champ de motif ni mécanisme de correction administrative n’est annoncé. Une permission distincte pour la restitution pourra être ajoutée si l’organisation de l’atelier le nécessite.

Validation : tests métier couvrant les trois transitions, refus des sauts/retours, état terminal, auteur injecté ignoré, date, duplication, version obsolète, dépôt initial, anciens dossiers, rollback en cas de panne d’audit, droits et confidentialité publique. Migration MySQL appliquée (Ran). Vérification navigateur de la liste sur fenêtre étroite : quatre compteurs, sélecteurs et texte d’enregistrement automatique ; aucun statut réel modifié pour la vérification. Les tests automatisés SQLite ne constituent pas un essai de requêtes MySQL simultanées. Sauvegarde automatique par sélection contrôlée côté serveur ; la simulation d’un changement complet dans le navigateur reste à faire sur un dossier de test dédié.

Résultat final du lot SUIVI : 57 tests réussis, 488 assertions sous PHP 8.3.30 ; Pint valide les neuf fichiers PHP ciblés, syntaxe JavaScript valide et compilation Vite réussie. La fiche existante a également été contrôlée visuellement sur fenêtre étroite : sélecteur automatique, distinction terminée/restituée et historique vide explicite lisibles. Aucun changement de statut métier effectué pendant le contrôle visuel.

## Module CATALOGUE / TICKET — Dépôt et remise du code client (27 octobre 2026)

- [x] Ajouter Retour à l’accueil sur /suivi, avant et après une recherche.
- [x] Remplacer l’impression de toute la fiche par le téléchargement d’un ticket PDF compact de 80 × 120 mm : code personnel à 8 caractères, appareil, date de dépôt et URL de suivi. Le ticket ne contient ni pièce d’identité, ni panne, ni coordonnées du client, ni auteur interne.
- [x] Après dépôt, mettre en avant le bouton Télécharger le ticket PDF. Conserver l’avancement automatique, les informations du dossier repliables, le prix et l’historique sur la fiche.
- [x] Créer les tables device_brands et device_models et leurs écrans de gestion /catalogue : création, consultation/recherche, modification et suppression des éléments inutilisés.
- [x] Ajouter la permission catalog.manage, disponible dans les rôles et appliquée côté serveur. Menu Marques et modèles sous Administration. Les administrateurs y accèdent ; les autres rôles doivent recevoir cette permission explicitement.
- [x] Éviter qu’une nouvelle permission soit implicitement attribuée au rôle Utilisateur lors d’une installation neuve : liste opérationnelle initiale désormais explicite dans la migration des rôles. Aucun accès existant élargi.
- [x] Au dépôt, rechercher une marque par ses lettres puis la sélectionner ; filtrer les modèles selon cette marque. Sans JavaScript, listes natives avec validation serveur du couple marque/modèle.
- [x] Refuser les modèles d’une autre marque et résoudre les libellés depuis les identifiants du catalogue, sans faire confiance aux textes envoyés par le navigateur.
- [x] Importer les marques/modèles des réparations existantes pendant la migration appliquée en base. Conserver les libellés historiques dans chaque dossier, même après renommage au catalogue.
- [x] Bloquer la suppression d’un modèle référencé par une réparation et d’une marque utilisée ou possédant encore des modèles, avec contraintes SQL de protection.

Le dépôt commence toujours En attente ; le statut se modifie ensuite dans la liste ou la fiche selon les transitions déjà livrées. Le code est celui du dépôt/réparation : aucun nouveau système de réservation n’est introduit.

Validation : 62 tests réussis, 561 assertions sous PHP 8.3.30. Tests du CRUD, recherche, doublons normalisés, permissions, association marque/modèle, conservation des anciens libellés, protection des suppressions, reprise des dossiers et accès au PDF. Compilation Vite et Pint réussis ; syntaxe JS vérifiée. PDF rendu puis inspecté visuellement via PyMuPDF : une page aux dimensions attendues pour un appareil standard et un libellé long. Dompdf 3.1.6 ajouté avec ses dépendances ; ressources distantes et exécution PHP/JavaScript désactivées dans le rendu. Pas de données métier de démonstration ajoutées en base pour la validation.

Limite : contrôle visuel navigateur du catalogue et du sélecteur dépendant à reprendre. La nouvelle fenêtre Edge n’exposait pas son état d’accessibilité et la capture renvoyait une autre application. Le PDF a été contrôlé séparément. Aucun template Codex n’a été créé : les modèles livrés sont des vues Laravel et un ticket applicatif, sans référence supportée pour Template Creator.

À suivre : attribuer catalog.manage aux gestionnaires souhaités, compléter le catalogue réel et essayer l’impression du PDF sur l’imprimante de l’atelier. L’URL du suivi doit être une adresse accessible aux clients lors de la mise en ligne ; le domaine local .test ne sera pas accessible depuis leur téléphone hors du poste de développement.

## Module SPA — Migration de toutes les pages actives vers React (27 octobre 2026)

- [x] Installer React, React DOM et les adaptateurs Inertia 2 compatibles avec le PHP 8.3.30 existant ; configurer Vite et son plugin React sans nouveau serveur SSR.
- [x] Convertir l’accueil, la connexion, le suivi public, le dashboard, les clients, les réparations, le catalogue, les comptes, les rôles et l’accès en attente : 18 composants de pages React, dont l’écran d’erreur.
- [x] Remplacer les retours de vues HTML des contrôleurs par des réponses Inertia ; conserver les URL et opérations métier. Seul le document de démarrage est chargé au premier accès.
- [x] Fournir une navigation commune responsive React, avec menu mobile natif, compte, déconnexion et liens filtrés par permissions.
- [x] Porter les recherches, filtres, paginations, créations, modifications, activation des comptes et dépendances de permissions dans des composants React.
- [x] Maintenir l’enregistrement automatique des statuts et l’historique ; actualiser leurs données via Inertia sans rechargement du document.
- [x] Préserver le ticket PDF, la sélection marque/modèle, la recherche de marque et le brouillon de dépôt par client ; ajouter l’actualisation partielle du catalogue.
- [x] Préserver les validations, limitations de connexion, révocations de session, permissions et contrôles de version côté Laravel.
- [x] Limiter explicitement les données du suivi public et retirer les champs inutiles des autres réponses SPA (pièces d’identité absentes des listes et des fiches de réparation, mots de passe absents des comptes).
- [x] Corriger la conservation des clés de permissions lors du regroupement des groupes de rôles, nécessaire au formulaire React.
- [x] Adapter les tests PHP aux composants et aux données Inertia ; ajouter tests du protocole JSON de navigation, des écrans et de la confidentialité. Ajouter des tests React interactifs avec Vitest/Testing Library.
- [x] Documenter l’architecture et les commandes dans docs/SPA.md.

Les anciennes vues Blade et scripts sont conservés comme références historiques mais ne sont plus rendus par les routes applicatives ni compilés comme points d’entrée. Le modèle HTML interne du ticket reste utilisé uniquement par le générateur PDF. Il n’y a aucune migration de base ni modification de dossier métier dans ce lot.

Validation : 67 tests Laravel réussis (1818 assertions), 12 tests React réussis, compilation Vite réussie. Les tests couvrent toutes les routes d’écrans, la réponse initiale unique et les navigations Inertia JSON, les erreurs et les permissions, la sélection des appareils et l’enregistrement automatique. Les dépendances npm ont reçu leurs corrections compatibles ; npm audit indique 0 vulnérabilité à cette vérification. Les avis Composer précédemment consignés restent distincts de ce lot.

Limite : le contrôle visuel complet de la SPA dans Edge reste à reprendre. L’outil a retrouvé la fenêtre et ses onglets mais les actions ont échoué avec « coordinate input geometry is unavailable » puis « failed to activate captured window ». Les tests React exécutent un DOM simulé, sans preuve de rendu CSS aux différents formats. Aucun essai visuel intégral ni changement réel de statut n’est revendiqué.

À poursuivre : recette dans le navigateur et sur téléphone avec un dossier de test dédié, puis nettoyage volontaire des anciennes vues après validation de la migration. La navigation SPA reste servie par Laravel ; elle ne constitue pas une application hors ligne.

## Module SPA — Nettoyage des anciens fichiers (27 octobre 2026)

- [x] Vérifier les références des contrôleurs, routes, points d’entrée Vite, imports CSS/JS, tests et documentation.
- [x] Supprimer 34 anciennes vues Blade : écrans remplacés, layouts et composants devenus inutilisés.
- [x] Supprimer six anciens scripts (app.js, bootstrap.js, home.js, navigation.js, repair-status.js, device-picker.js) et trois feuilles de style inutilisées (app.css, dashboard.css, navigation.css).
- [x] Conserver les deux vues actives : resources/views/app.blade.php pour démarrer React et resources/views/reparations/ticket.blade.php pour générer le PDF.
- [x] Conserver home.css et spa.css, utilisés par React ; remplacer les directives Tailwind visant les vues supprimées par la référence au composant Home.jsx.
- [x] Actualiser docs/SPA.md et docs/FICHE-ATELIER.md avec les chemins React et le fonctionnement actuel des sections.

Emplacements : resources/js/app.jsx (démarrage), resources/js/Pages (18 écrans), resources/js/Layouts/Workspace.jsx (navigation), resources/js/Components (composants partagés), resources/js/domain/permissions.js (dépendances de permissions), tests/Frontend (tests React).

Validation après suppression : compilation Vite réussie, 67 tests Laravel réussis (1818 assertions), 12 tests React réussis. Le téléchargement PDF reste couvert par les tests. Les caches de vues compilées ont été vidés. Aucun changement métier ni de données dans ce nettoyage.
