# Tests de demonstration SmartCampus

Ce fichier sert de guide rapide pour la soutenance. Avant de tester, importer `database/smartcampus.sql` dans phpMyAdmin.

## 1. Connexion administrateur

Compte : `admin@smartcampus.test` / `admin123`

- Verifier le dashboard administrateur.
- Verifier les statistiques : etudiants, enseignants, cours, inscriptions et demandes.
- Creer un etudiant de test.
- Modifier un etudiant existant.
- Creer un enseignant de test.
- Modifier un enseignant existant.
- Creer un cours de test.
- Tester recherche, filtre et tri sur les cours.
- Aller dans les inscriptions.
- Voir les demandes en attente.
- Valider une demande.
- Refuser une demande.

## 2. Connexion etudiant

Exemple : `nathan.moreau@smartcampus.test` / `etudiant123`

- Verifier le dashboard etudiant.
- Aller dans Mes cours.
- Redemander l'inscription a INFO101.
- Verifier que la demande passe en statut en_attente.
- Se connecter avec Alice et verifier ses cours valides.
- Se desinscrire d'un cours.
- Verifier l'emploi du temps apres validation d'une inscription.
- Consulter les notes.

## 3. Connexion enseignant

Exemple : `paul.martin@smartcampus.test` / `enseignant123`

- Verifier le dashboard enseignant.
- Consulter Mes cours.
- Aller dans Notes.
- Selectionner INFO102.
- Modifier les notes de Hugo.
- Tester une note invalide, par exemple 25.
- Enregistrer des notes valides.
- Valider definitivement les notes.
- Verifier que les champs sont verrouilles apres validation.

## 4. Tests metier

### Double inscription

- Se connecter en admin.
- Essayer d'inscrire Alice Durand a INFO101.
- Resultat attendu : refus car Alice est deja inscrite.

### Capacite maximale

- TESTCAP a une capacite de 1.
- Lea Robert est deja inscrite a TESTCAP.
- Essayer d'inscrire un autre etudiant a TESTCAP.
- Resultat attendu : refus car le cours est complet.

### Conflit horaire

- Hugo Bernard est inscrit a INFO102 le mardi de 14:00 a 16:00.
- WEB201 a lieu le mardi de 15:00 a 17:00.
- Essayer d'inscrire Hugo a WEB201.
- Resultat attendu : refus pour conflit horaire.

### Note validee non modifiable

- Alice a une note validee sur INFO101.
- Se connecter avec Paul Martin.
- Aller dans Notes > INFO101.
- Resultat attendu : les champs de la note validee sont desactives.

## 5. Verification finale

- Verifier que la navigation change selon le role.
- Verifier que les liens de deconnexion fonctionnent.
- Verifier que les messages de succes et d'erreur sont lisibles.
- Verifier que les tableaux restent lisibles sur petit ecran.
