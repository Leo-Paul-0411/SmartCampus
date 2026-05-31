# Journal d'utilisation de l'IA - SmartCampus

Ce document explique comment l'IA a ete utilisee pendant le projet. L'IA a servi d'assistant de travail, mais les choix finaux, les tests et les arbitrages restent sous la responsabilite de l'equipe.

## Outils utilises

- ChatGPT
- Codex

## Taches ou l'IA a aide

- Audit du code apres integration des differentes parties.
- Detection de bugs PHP, SQL et chemins `include`.
- Correction d'incoherences entre PHP et la base MySQL.
- Amelioration des regles metier des inscriptions.
- Clarification du workflow inscription : demande, attente, validation, refus.
- Aide a la generation de prompts de travail.
- Documentation du projet et preparation de la soutenance.
- Amelioration UX/CSS en restant dans une architecture PHP simple.

## Exemples de reponses utiles

- Reperage de l'obligation d'utiliser `$conn` partout.
- Reperage de la colonne officielle `note_exam`.
- Proposition de regles pour eviter la double inscription.
- Proposition de controles pour la capacite maximale et les conflits horaires.
- Proposition d'un parcours de test admin / enseignant / etudiant.
- Aide a la structuration du README final.

## Limites et erreurs rencontrees

- Certaines analyses ont pu signaler un conflit sur `db.php` a partir d'un zip ou d'un etat de projet qui n'etait pas a jour.
- L'IA propose parfois de trop restructurer le projet ; l'equipe a choisi de garder une architecture PHP procedurale simple.
- Les corrections doivent toujours etre relues pour verifier qu'elles correspondent au niveau du projet.
- Les tests navigateur restent indispensables.
- Les imports SQL doivent etre testes avec WAMP/phpMyAdmin.

## Validations realisees par l'equipe

- Tests navigateur sur les espaces admin, enseignant et etudiant.
- Import de `database/smartcampus.sql`.
- Verification des comptes de test.
- Verification du parcours d'inscription.
- Verification de la saisie et de la validation des notes.
- Verification de la coherence generale avant soutenance.

## Conclusion

L'IA a ete utilisee comme assistant pour gagner du temps, analyser le code et produire de la documentation. Elle n'a pas remplace le travail de conception, de test et de validation de l'equipe. Les propositions ont ete adaptees et simplifiees pour rester coherentes avec le sujet, WAMP, PHP/MySQL et le niveau attendu.
