# SmartCampus

SmartCampus est une plateforme web dynamique de gestion académique.

## Objectif

Le projet permet de préparer une application simple pour la gestion des cours,
des inscriptions, des notes et des espaces utilisateurs.

## Rôles

- administrateur
- enseignant
- étudiant

## Technologies

- HTML
- CSS
- JavaScript
- PHP sans framework
- MySQL
- WAMP / Apache / PHP / MySQL
- GitHub

## Organisation des dossiers

- `database/` : script SQL de création de la base de données.
- `config/` : configuration de la connexion à la base MySQL.
- `includes/` : fichiers PHP communs aux pages.
- `public/` : pages publiques comme l'accueil, la connexion et la déconnexion.
- `admin/` : espace administrateur.
- `enseignant/` : espace enseignant.
- `etudiant/` : espace étudiant.
- `assets/` : fichiers CSS, JavaScript et images.

## Règle Git

Chaque membre travaille sur sa propre branche et réalise des petits commits
réguliers avec des messages clairs.

## Organisation du travail

Le projet est développé avec une organisation par branches afin de garantir une répartition claire du travail.

Documents utiles :

* docs/repartition_travail.md
* docs/journal_ia.md
* docs/checklist_pr.md

Chaque membre travaille sur sa branche, réalise des petits commits, teste localement avec WAMP, puis propose ses modifications via Pull Request.
