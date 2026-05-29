# Répartition du travail - SmartCampus

## Règles générales

* Personne ne travaille directement sur main.
* Chaque membre travaille sur sa branche.
* Une Pull Request est obligatoire avant intégration dans main.
* Un commit doit correspondre à une seule petite tâche.
* Les gros commits sont interdits.
* Les noms des tables et attributs du document technique ne doivent pas être modifiés.
* Chaque fonctionnalité doit être testée localement avec WAMP avant Pull Request.

## Branches

* main : version stable du projet
* Tristan : base de données, authentification, règles métier, intégration
* Nicolas : espace administrateur, dashboard, gestion des étudiants, enseignants et cours
* Leo-Paul : espace enseignant, espace étudiant, notes, emploi du temps, CSS

## Tristan

Fichiers principaux :

* database/smartcampus.sql
* config/db.php
* includes/auth.php
* includes/fonctions.php
* public/login.php
* public/logout.php
* admin/inscriptions.php

Responsabilités :

* connexion MySQL
* données de test
* authentification
* sessions
* redirection selon rôle
* règles métier des inscriptions
* double inscription
* cours complet
* conflit horaire
* intégration finale

## Nicolas

Fichiers principaux :

* admin/dashboard.php
* admin/etudiants.php
* admin/enseignants.php
* admin/cours.php

Responsabilités :

* dashboard administrateur
* statistiques
* affichage des étudiants
* affichage des enseignants
* gestion des cours
* tableaux administrateur

## Leo-Paul

Fichiers principaux :

* enseignant/dashboard.php
* enseignant/mes_cours.php
* enseignant/notes.php
* etudiant/dashboard.php
* etudiant/cours.php
* etudiant/notes.php
* etudiant/emploi_du_temps.php
* assets/css/style.css
* assets/js/script.js

Responsabilités :

* espace enseignant
* saisie des notes
* calcul visuel des moyennes
* espace étudiant
* consultation des cours
* consultation des notes
* emploi du temps
* amélioration visuelle CSS

## Fichiers sensibles

Ces fichiers ne doivent pas être modifiés sans accord du groupe :

* database/smartcampus.sql
* config/db.php
* includes/auth.php
* includes/fonctions.php

## Noms à respecter

Tables :

* utilisateur
* notification
* enseignant
* etudiant
* cours
* note
* inscription

Attributs importants :

* id_user
* id_enseignant
* id_etudiant
* id_cours
* note_exam
* mot_de_passe

Ne jamais utiliser :

* note_examen
* id_enseignannt
