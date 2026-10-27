<?php

return [
    'catalog.manage' => ['group' => 'Catalogue', 'label' => 'Gérer les marques et modèles', 'description' => 'Créer, renommer et supprimer les éléments non utilisés du catalogue.', 'requires' => []],
    'dashboard.view' => ['group' => 'Tableau de bord', 'label' => 'Consulter les indicateurs', 'description' => 'Voir les compteurs et graphiques globaux de l’atelier.', 'requires' => []],
    'clients.view' => ['group' => 'Clients', 'label' => 'Consulter les clients', 'description' => 'Accéder au répertoire et aux coordonnées des clients.', 'requires' => []],
    'clients.create' => ['group' => 'Clients', 'label' => 'Créer un client', 'description' => 'Ajouter un client au répertoire.', 'requires' => ['clients.view']],
    'repairs.view' => ['group' => 'Réparations', 'label' => 'Consulter les réparations', 'description' => 'Voir les dossiers, les tickets et les prix enregistrés.', 'requires' => []],
    'repairs.create' => ['group' => 'Réparations', 'label' => 'Enregistrer un dépôt', 'description' => 'Rechercher un client et enregistrer son appareil.', 'requires' => ['repairs.view', 'clients.view']],
    'repairs.change_status' => ['group' => 'Réparations', 'label' => 'Modifier le statut', 'description' => 'Faire avancer la réparation et confirmer la remise de l’appareil au client.', 'requires' => ['repairs.view']],
    'repairs.set_price' => ['group' => 'Réparations', 'label' => 'Renseigner le prix', 'description' => 'Enregistrer ou modifier le prix d’une réparation.', 'requires' => ['repairs.view']],
    'users.manage' => ['group' => 'Administration', 'label' => 'Gérer les comptes utilisateurs', 'description' => 'Créer, modifier, désactiver les comptes et leur attribuer un rôle. Un gestionnaire délégué ne peut gérer ni attribuer des permissions supérieures aux siennes.', 'requires' => []],
    'roles.manage' => ['group' => 'Administration', 'label' => 'Gérer les rôles et permissions', 'description' => 'Privilège étendu : créer des rôles et attribuer toute permission du catalogue.', 'requires' => []],
];
