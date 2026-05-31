# Checklist finale avant rendu - SmartCampus

Cette checklist sert a verifier que le projet est pret avant la soutenance et le rendu final.

## 1. Git

- [ ] La branche correcte est utilisee.
- [ ] `git status` ne contient pas de modification oubliee.
- [ ] Les commits importants sont faits.
- [ ] Les commits sont pousses sur GitHub.
- [ ] Le tag final est cree si demande.
- [ ] Aucun fichier temporaire inutile n'est present.

## 2. Base de donnees

- [ ] `database/smartcampus.sql` est importable dans phpMyAdmin.
- [ ] La base `smartcampus` est bien creee.
- [ ] Les comptes de test sont presents.
- [ ] Les donnees de demonstration sont presentes.
- [ ] Le test de double inscription est possible.
- [ ] Le test de capacite maximale est possible.
- [ ] Le test de conflit horaire est possible.

## 3. Fonctionnalites administrateur

- [ ] Dashboard administrateur accessible.
- [ ] Gestion des etudiants accessible.
- [ ] Gestion des enseignants accessible.
- [ ] Gestion des cours accessible.
- [ ] Gestion des inscriptions accessible.
- [ ] Validation d'une demande possible.
- [ ] Refus d'une demande possible.
- [ ] Desinscription possible.

## 4. Fonctionnalites enseignant

- [ ] Dashboard enseignant accessible.
- [ ] Page Mes cours accessible.
- [ ] Page Notes accessible.
- [ ] La moyenne ponderee est calculee.
- [ ] Les notes peuvent etre validees.
- [ ] Les notes validees sont verrouillees.

## 5. Fonctionnalites etudiant

- [ ] Dashboard etudiant accessible.
- [ ] Demande d'inscription possible.
- [ ] Page Mes cours accessible.
- [ ] Desinscription possible.
- [ ] Page Mes notes accessible.
- [ ] Emploi du temps accessible.
- [ ] Notifications visibles.

## 6. Securite minimale

- [ ] Les pages sont protegees par role.
- [ ] La deconnexion fonctionne.
- [ ] Un enseignant ne voit pas les donnees d'un autre enseignant.
- [ ] Un etudiant ne voit pas les donnees d'un autre etudiant.
- [ ] La variable de connexion utilisee est `$conn`.
- [ ] Le code n'utilise aucune autre variable de connexion que `$conn`.

## 7. Soutenance

- [ ] Le parcours de demonstration est pret.
- [ ] Le fichier `TESTS_DEMO.md` a ete relu.
- [ ] Le PowerPoint est coherent avec le projet final.
- [ ] Les limites du projet sont connues.
- [ ] L'explication PHP / HTML / CSS / JavaScript est prete.
- [ ] L'explication de l'utilisation de l'IA est prete.
