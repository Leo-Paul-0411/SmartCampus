# Repartition du travail - SmartCampus

Ce document presente la repartition finale du travail pour la version rendue de SmartCampus.

## 1. Tristan

- Integration globale du projet.
- Merge final des differentes parties.
- Corrections PHP / SQL apres integration.
- Connexion MySQL et coherence autour de `$conn`.
- Authentification et redirection selon le role.
- Page d'inscriptions et workflow admin.
- Regles metier : double inscription, capacite maximale, conflit horaire.
- Tests WAMP et verification de l'import SQL.
- Coordination de l'utilisation de Codex / IA.
- Documentation finale.
- Preparation de la soutenance.

## 2. Nicolas

- Espace administrateur.
- Dashboard administrateur.
- Gestion des etudiants.
- Gestion des enseignants.
- Gestion des cours.
- Inscriptions cote administrateur.
- Tableaux et formulaires de gestion.

## 3. Leo-Paul

- Espace enseignant.
- Espace etudiant.
- Consultation des cours.
- Saisie et affichage des notes.
- Calcul et affichage des resultats.
- Emploi du temps etudiant.
- Pages de consultation.

## 4. Travail collectif

- Choix du sujet SmartCampus.
- Wireframes et storyboard.
- Definition de la base de donnees.
- Tests fonctionnels.
- Preparation du PowerPoint.
- Corrections finales avant rendu.
- Harmonisation generale du projet.

## 5. Remarque sur l'integration

Apres le merge des parties, certaines zones ont ete harmonisees pour rendre le projet coherent et stable :

- navigation par role ;
- CSS global ;
- coherence SQL / PHP ;
- noms des roles ;
- workflow des inscriptions ;
- documentation ;
- donnees de demonstration.

Ces harmonisations n'ont pas eu pour objectif de changer l'architecture du projet, mais de rendre l'application plus stable, lisible et defendable en soutenance.

## Noms importants a respecter

Tables :

- `utilisateur`
- `notification`
- `enseignant`
- `etudiant`
- `cours`
- `note`
- `inscription`

Attributs importants :

- `id_user`
- `id_enseignant`
- `id_etudiant`
- `id_cours`
- `note_exam`
- `mot_de_passe`

A ne pas utiliser :

- une autre variable de connexion que `$conn`
- une variante de colonne differente de `note_exam`
