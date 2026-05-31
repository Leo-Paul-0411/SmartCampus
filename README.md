# SmartCampus - Plateforme de gestion academique

SmartCampus est une application web dynamique de gestion academique pour une ecole d'ingenieurs. Elle permet de gerer les utilisateurs, les cours, les inscriptions, les notes et les emplois du temps avec des espaces separes par role.

## Technologies utilisees

- HTML
- CSS
- JavaScript
- PHP procedural
- MySQL
- WAMP
- Git / GitHub

React a ete etudie dans le module mais n'a pas ete retenu comme technologie principale afin de conserver une architecture PHP/MySQL simple, stable et coherente avec le perimetre du projet.

## Architecture du projet

- `admin/` : pages de gestion pour l'administrateur.
- `enseignant/` : espace enseignant, cours et notes.
- `etudiant/` : espace etudiant, cours, notes et emploi du temps.
- `public/` : pages publiques, connexion et deconnexion.
- `includes/` : fichiers communs, authentification, header, footer et fonctions metier.
- `config/` : connexion MySQL centralisee.
- `assets/css/` : feuille de style principale.
- `assets/js/` : JavaScript leger pour confirmations et confort d'utilisation.
- `database/` : script SQL complet de creation et donnees de demonstration.
- `docs/` : documents de suivi du projet.

## Roles utilisateurs

### Administrateur

- Gestion des etudiants.
- Gestion des enseignants.
- Gestion des cours.
- Gestion des inscriptions.
- Validation ou refus des demandes d'inscription.

### Enseignant

- Consultation de ses cours.
- Saisie et modification des notes.
- Validation finale des notes.

### Etudiant

- Consultation des cours.
- Demande d'inscription.
- Desinscription.
- Consultation des notes.
- Consultation de l'emploi du temps.
- Notifications simples.

## Fonctionnalites principales

- Authentification par email et mot de passe.
- Navigation adaptee au role connecte.
- Dashboards admin, enseignant et etudiant.
- Gestion des etudiants.
- Gestion des enseignants.
- Gestion des cours.
- Demandes d'inscription et validation administrative.
- Desinscription.
- Regles metier sur les inscriptions.
- Saisie, calcul et validation des notes.
- Emploi du temps etudiant.
- Notifications simples.

## Regles metier

- Un etudiant ne peut pas etre inscrit deux fois au meme cours.
- Un cours ne peut pas depasser sa capacite maximale.
- Un etudiant ne peut pas avoir deux cours inscrits sur un meme creneau horaire.
- Une note validee n'est plus modifiable par l'enseignant.
- Chaque page sensible est protegee selon le role connecte.

## Installation avec WAMP

1. Copier le dossier du projet dans `C:\wamp64\www\SmartCampus`.
2. Demarrer WAMP.
3. Ouvrir phpMyAdmin.
4. Importer le fichier `database/smartcampus.sql`.
5. Acceder a l'application : `http://localhost/SmartCampus/public/login.php`.

## Comptes de test

### Administrateur

- `admin@smartcampus.test` / `admin123`

### Enseignants

- `paul.martin@smartcampus.test` / `enseignant123`
- `sophie.leroy@smartcampus.test` / `enseignant123`
- `karim.benali@smartcampus.test` / `enseignant123`

### Etudiants

- `alice.durand@smartcampus.test` / `etudiant123`
- `hugo.bernard@smartcampus.test` / `etudiant123`
- `emma.petit@smartcampus.test` / `etudiant123`
- `nathan.moreau@smartcampus.test` / `etudiant123`
- `lea.robert@smartcampus.test` / `etudiant123`

## Parcours de test rapide

### Administrateur

- Se connecter.
- Verifier les statistiques du dashboard.
- Creer ou modifier un etudiant.
- Creer ou modifier un enseignant.
- Creer ou modifier un cours.
- Valider ou refuser une demande d'inscription.
- Tester les cas de conflit horaire et de capacite maximale.

### Etudiant

- Demander une inscription a un cours.
- Consulter le statut de la demande.
- Se desinscrire d'un cours.
- Verifier l'emploi du temps apres validation.
- Consulter les notes.

### Enseignant

- Consulter ses cours.
- Saisir des notes.
- Tester une note invalide.
- Valider les notes.
- Verifier que les notes validees sont verrouillees.

## Limites et compromis

- Application pedagogique realisee dans le cadre d'un module de web dynamique.
- Securite minimale adaptee au perimetre du projet.
- Messagerie complete non implementee.
- Gestion des presences et exports PDF non retenus car optionnels.
- Priorite donnee a la stabilite PHP/MySQL et aux regles metier principales.

## IA et assistance

Une IA a ete utilisee comme aide au debugging, a la structuration, a la correction et a la documentation. Le code a ete verifie, adapte et simplifie par l'equipe pour rester coherent avec le niveau attendu et l'architecture du projet.
