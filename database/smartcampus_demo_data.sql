-- ============================================================
--  SmartCampus — Données de démonstration
--  À exécuter APRÈS smartcampus.sql
-- ============================================================

USE smartcampus;

-- ============================================================
-- 1. UTILISATEURS
-- Mot de passe pour tous : "password123"
-- Hash bcrypt de "password123"
-- ============================================================

INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role, actif) VALUES
-- Administrateur
('Martin',    'Sophie',   'admin@smartcampus.fr',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrm3a0am', 'administrateur', 1),

-- Enseignants
('Dubois',    'Pierre',   'p.dubois@smartcampus.fr',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrm3a0am', 'enseignant', 1),
('Bernard',   'Claire',   'c.bernard@smartcampus.fr',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrm3a0am', 'enseignant', 1),
('Leroy',     'Marc',     'm.leroy@smartcampus.fr',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrm3a0am', 'enseignant', 1),
('Moreau',    'Julie',    'j.moreau@smartcampus.fr',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrm3a0am', 'enseignant', 1),

-- Étudiants
('Petit',     'Lucas',    'lucas.petit@etud.fr',         '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrm3a0am', 'etudiant', 1),
('Garnier',   'Emma',     'emma.garnier@etud.fr',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrm3a0am', 'etudiant', 1),
('Roux',      'Nathan',   'nathan.roux@etud.fr',         '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrm3a0am', 'etudiant', 1),
('Faure',     'Léa',      'lea.faure@etud.fr',           '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrm3a0am', 'etudiant', 1),
('Simon',     'Hugo',     'hugo.simon@etud.fr',          '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrm3a0am', 'etudiant', 1),
('Laurent',   'Inès',     'ines.laurent@etud.fr',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrm3a0am', 'etudiant', 1),
('Michel',    'Tom',      'tom.michel@etud.fr',          '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrm3a0am', 'etudiant', 1),
('Lefebvre',  'Camille',  'camille.lefebvre@etud.fr',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrm3a0am', 'etudiant', 1);

-- ============================================================
-- 2. ENSEIGNANTS  (id_user correspond aux enseignants ci-dessus : 2,3,4,5)
-- ============================================================

INSERT INTO enseignant (numero_enseignant, specialite, bureau, telephone, id_user) VALUES
('ENS001', 'Mathématiques',        'B201', '0612345678', 2),
('ENS002', 'Informatique',         'B305', '0623456789', 3),
('ENS003', 'Physique',             'B102', '0634567890', 4),
('ENS004', 'Bases de données',     'B410', '0645678901', 5);

-- ============================================================
-- 3. ÉTUDIANTS  (id_user : 6 à 13)
-- ============================================================

INSERT INTO etudiant (numero_etudiant, niveau, groupe_classe, date_naissance, telephone, id_user) VALUES
('ETU2024001', 'L3', 'Groupe A', '2002-03-15', '0611111111', 6),
('ETU2024002', 'L3', 'Groupe A', '2002-07-22', '0622222222', 7),
('ETU2024003', 'L3', 'Groupe B', '2001-11-08', '0633333333', 8),
('ETU2024004', 'L3', 'Groupe B', '2002-01-30', '0644444444', 9),
('ETU2024005', 'M1', 'Groupe A', '2000-05-19', '0655555555', 10),
('ETU2024006', 'M1', 'Groupe A', '2001-09-03', '0666666666', 11),
('ETU2024007', 'M1', 'Groupe B', '2000-12-25', '0677777777', 12),
('ETU2024008', 'L3', 'Groupe A', '2002-04-11', '0688888888', 13);

-- ============================================================
-- 4. COURS  (id_enseignant : 1=Dubois, 2=Bernard, 3=Leroy, 4=Moreau)
-- ============================================================

INSERT INTO cours (code_cours, titre, description, capacite_max, jour, heure_debut, heure_fin, salle, semestre, id_enseignant) VALUES
('MATH101', 'Mathématiques',         'Algèbre et analyse pour ingénieurs',           32, 'Lundi',    '09:00', '11:00', 'A101', 'S1', 1),
('INFO201', 'Algorithmique avancée', 'Structures de données et complexité',          30, 'Mardi',    '10:00', '12:00', 'B204', 'S1', 2),
('PHYS101', 'Physique',              'Mécanique et thermodynamique',                 28, 'Mercredi', '14:00', '16:00', 'C305', 'S1', 3),
('BDD301',  'Bases de données',      'Conception et requêtes SQL avancées',          25, 'Jeudi',    '08:00', '10:00', 'B102', 'S1', 4),
('INFO202', 'Projet Web dynamique',  'Développement PHP/MySQL, architecture MVC',    20, 'Vendredi', '13:00', '17:00', 'B201', 'S2', 2),
('MATH102', 'Statistiques',          'Probabilités et statistiques appliquées',      30, 'Lundi',    '14:00', '16:00', 'A203', 'S2', 1),
('PHYS102', 'Électronique',          'Circuits électroniques et signaux',            25, 'Mardi',    '08:00', '10:00', 'C101', 'S2', 3),
('BDD302',  'NoSQL et Big Data',     'MongoDB, Redis, architectures distribuées',    20, 'Jeudi',    '14:00', '16:00', 'B410', 'S2', 4);

-- ============================================================
-- 5. INSCRIPTIONS
-- ============================================================

INSERT INTO inscription (id_etudiant, id_cours, statut) VALUES
-- Lucas Petit (ETU001) — L3 Groupe A
(1, 1, 'validee'),   -- MATH101
(1, 2, 'validee'),   -- INFO201
(1, 3, 'validee'),   -- PHYS101
(1, 4, 'validee'),   -- BDD301

-- Emma Garnier (ETU002) — L3 Groupe A
(2, 1, 'validee'),   -- MATH101
(2, 2, 'validee'),   -- INFO201
(2, 4, 'validee'),   -- BDD301
(2, 5, 'en_attente'),-- Projet Web

-- Nathan Roux (ETU003) — L3 Groupe B
(3, 1, 'validee'),   -- MATH101
(3, 3, 'validee'),   -- PHYS101
(3, 4, 'validee'),   -- BDD301
(3, 6, 'validee'),   -- Statistiques

-- Léa Faure (ETU004) — L3 Groupe B
(4, 1, 'validee'),   -- MATH101
(4, 2, 'validee'),   -- INFO201
(4, 3, 'en_attente'),-- PHYS101
(4, 7, 'validee'),   -- Électronique

-- Hugo Simon (ETU005) — M1 Groupe A
(5, 5, 'validee'),   -- Projet Web
(5, 8, 'validee'),   -- NoSQL
(5, 6, 'validee'),   -- Statistiques

-- Inès Laurent (ETU006) — M1 Groupe A
(6, 5, 'validee'),   -- Projet Web
(6, 8, 'validee'),   -- NoSQL
(6, 4, 'validee'),   -- BDD301

-- Tom Michel (ETU007) — M1 Groupe B
(7, 5, 'validee'),   -- Projet Web
(7, 8, 'validee'),   -- NoSQL
(7, 7, 'validee'),   -- Électronique

-- Camille Lefebvre (ETU008) — L3 Groupe A
(8, 1, 'validee'),   -- MATH101
(8, 2, 'refusee'),   -- INFO201 (refusée, cours complet)
(8, 6, 'validee');   -- Statistiques

-- ============================================================
-- 6. NOTES
-- ============================================================

INSERT INTO note (note_controle, note_exam, note_projet, moyenne, validee, date_saisie, date_validation, id_etudiant, id_cours) VALUES
-- Lucas — MATH101
(14.5, 16.0, NULL, 15.25, 1, '2025-01-20 10:00:00', '2025-01-25 09:00:00', 1, 1),
-- Lucas — INFO201
(12.0, 13.5, 15.0, 13.5,  1, '2025-01-22 10:00:00', '2025-01-27 09:00:00', 1, 2),
-- Lucas — PHYS101
(11.0, 10.5, NULL, 10.75, 0, '2025-01-23 10:00:00', NULL,                  1, 3),

-- Emma — MATH101
(17.0, 18.0, NULL, 17.5,  1, '2025-01-20 11:00:00', '2025-01-25 09:00:00', 2, 1),
-- Emma — INFO201
(15.0, 14.5, 16.0, 15.17, 1, '2025-01-22 11:00:00', '2025-01-27 09:00:00', 2, 2),

-- Nathan — MATH101
(9.0,  11.0, NULL, 10.0,  1, '2025-01-20 12:00:00', '2025-01-25 09:00:00', 3, 1),
-- Nathan — PHYS101
(13.0, 12.5, NULL, 12.75, 0, '2025-01-23 12:00:00', NULL,                  3, 3),

-- Léa — MATH101
(16.0, 15.5, NULL, 15.75, 1, '2025-01-20 14:00:00', '2025-01-25 09:00:00', 4, 1),
-- Léa — INFO201
(14.0, 13.0, 15.5, 14.17, 0, '2025-01-22 14:00:00', NULL,                  4, 2),

-- Hugo — Projet Web
(NULL, NULL, 17.0, 17.0,  0, '2025-02-10 09:00:00', NULL,                  5, 5),
-- Hugo — NoSQL
(15.0, 14.0, NULL, 14.5,  0, '2025-02-12 09:00:00', NULL,                  5, 8),

-- Inès — Projet Web
(NULL, NULL, 18.5, 18.5,  0, '2025-02-10 10:00:00', NULL,                  6, 5),

-- Tom — Projet Web
(NULL, NULL, 12.0, 12.0,  0, '2025-02-10 11:00:00', NULL,                  7, 5);

-- ============================================================
-- 7. NOTIFICATIONS
-- ============================================================

INSERT INTO notification (message, type_notification, lue, id_user) VALUES
-- Étudiants
('Votre inscription au cours Projet Web dynamique est en attente de validation.', 'inscription',   0, 7),
('Votre note de Mathématiques a été publiée : 15.25/20.',                         'note',          0, 6),
('Votre note de Mathématiques a été publiée : 17.5/20.',                          'note',          1, 7),
('Votre inscription au cours INFO201 a été refusée.',                             'inscription',   0, 13),
('Votre note de Algorithmique avancée a été publiée.',                            'note',          0, 8),

-- Enseignants
('15 étudiants inscrits à votre cours MATH101.',                                  'information',   1, 2),
('Nouvelle inscription en attente pour Projet Web dynamique.',                    'inscription',   0, 3),
('Rappel : saisie des notes pour BDD301 avant le 30 janvier.',                    'rappel',        0, 5),

-- Admin
('8 nouveaux étudiants inscrits cette semaine.',                                  'information',   1, 1),
('3 inscriptions en attente nécessitent une validation.',                         'rappel',        0, 1);
