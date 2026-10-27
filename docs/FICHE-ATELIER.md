# Fiche d’entretien — Site d’un atelier de réparation

À remplir avec le responsable. Ne publier que les réponses confirmées et les coordonnées professionnelles autorisées. Les champs vides ne sont pas affichés sur le site.

## Identité et histoire
- Nom public de l’atelier :
- Responsable et membres de l’équipe à présenter :
- Année et contexte de création :
- Parcours / expérience vérifiables :
- Pourquoi cet atelier a-t-il été créé ?
- Étapes importantes, avec dates confirmées :
- Qu’est-ce qui distingue concrètement le travail de l’atelier ?

Texte à préparer : « [Nom] a été créé en [année] à [lieu]. [Origine et parcours]. Aujourd’hui, [équipe, spécialités et manière de travailler]. » Supprimer les éléments non confirmés, ne pas publier les crochets.

## Contact et visite
- Adresse complète et quartier :
- Repère pour trouver l’entrée :
- Téléphone professionnel :
- Email professionnel :
- Jours et horaires exacts ; fermeture et jours particuliers :
- Accueil avec ou sans rendez-vous :
- Personne validant la publication de ces coordonnées :

## Services réellement proposés
Pour chaque service : nom, appareils/marques pris en charge, description, exclusions, diagnostic préalable éventuel. Exemples à faire confirmer : écran, batterie, connecteur de charge, diagnostic. Ne pas promettre un délai, un tarif ou une réparation de toute marque sans validation.

## Questions à valider avant publication
- Que doit apporter le client au dépôt ?
- Le diagnostic est-il payant ? Comment le prix est-il accepté ?
- Que se passe-t-il si la réparation n’est pas possible ?
- Quels délais peut-on annoncer et sous quelles conditions ?
- Quelle garantie s’applique réellement, avec quelles exclusions ?
- Comment et quand le client récupère-t-il son appareil ?
- Quels moyens de paiement sont acceptés ?
- Que faire si le client perd son code de suivi ?
- Quelles consignes donnez-vous pour les sauvegardes et les données personnelles ? Ne jamais collecter de code de déverrouillage dans cette fiche publique.

## Intégration dans le site
Le fichier `config/workshop.php` centralise les contenus. Les sections sont affichées par le composant React `resources/js/Pages/Public/Home.jsx`.

- `history.paragraphs` : liste de paragraphes ; vide = message d’attente dans la section histoire.
- `services` : liste avec `name` et `description` ; les entrées incomplètes sont masquées.
- `contact` : adresse, téléphone, email, repère et liste d’horaires ; seules les valeurs renseignées apparaissent.
- `faq` : liste avec `question` et `answer` ; les entrées incomplètes sont masquées.

Les textes sont affichés comme du texte simple par React : saisir du texte, pas du HTML. Les liens connexion, suivi et navigation dans les sections sont conservés. Une section vide affiche un message d’attente. Aucun formulaire de contact ni envoi de message n’est activé.

Après modification : exécuter `php artisan config:clear` (ou reconstruire le cache de configuration en déploiement). Les changements de contenu ne nécessitent pas de compilation CSS.

## Validation avec le réparateur
- Date de l’entretien :
- Informations encore à confirmer :
- Textes et coordonnées validés par :
- Date d’accord pour publication :
- Prochaine révision des horaires / services :
