DROP DATABASE IF EXISTS smartcampus;
CREATE DATABASE smartcampus CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE smartcampus;

CREATE TABLE utilisateur (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('administrateur','enseignant','etudiant') NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actif TINYINT(1) DEFAULT 1,
    UNIQUE (email)
) ENGINE=InnoDB;

CREATE TABLE notification (
    id_notification INT AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    type_notification VARCHAR(100) NOT NULL,
    lue TINYINT(1) DEFAULT 0,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_user INT NOT NULL,
    CONSTRAINT fk_notification_utilisateur
        FOREIGN KEY (id_user) REFERENCES utilisateur(id_user)
) ENGINE=InnoDB;

CREATE TABLE enseignant (
    id_enseignant INT AUTO_INCREMENT PRIMARY KEY,
    numero_enseignant VARCHAR(50) NOT NULL,
    specialite VARCHAR(100) NOT NULL,
    bureau VARCHAR(50),
    telephone VARCHAR(30),
    id_user INT NOT NULL,
    UNIQUE (numero_enseignant),
    CONSTRAINT fk_enseignant_utilisateur
        FOREIGN KEY (id_user) REFERENCES utilisateur(id_user)
) ENGINE=InnoDB;

CREATE TABLE etudiant (
    id_etudiant INT AUTO_INCREMENT PRIMARY KEY,
    numero_etudiant VARCHAR(50) NOT NULL,
    niveau VARCHAR(50) NOT NULL,
    groupe_classe VARCHAR(50) NOT NULL,
    date_naissance DATE,
    telephone VARCHAR(30),
    id_user INT NOT NULL,
    UNIQUE (numero_etudiant),
    CONSTRAINT fk_etudiant_utilisateur
        FOREIGN KEY (id_user) REFERENCES utilisateur(id_user)
) ENGINE=InnoDB;

CREATE TABLE cours (
    id_cours INT AUTO_INCREMENT PRIMARY KEY,
    code_cours VARCHAR(50) NOT NULL,
    titre VARCHAR(150) NOT NULL,
    description TEXT,
    capacite_max INT NOT NULL,
    jour VARCHAR(20) NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    salle VARCHAR(50) NOT NULL,
    semestre VARCHAR(50) NOT NULL,
    id_enseignant INT NOT NULL,
    UNIQUE (code_cours),
    CONSTRAINT fk_cours_enseignant
        FOREIGN KEY (id_enseignant) REFERENCES enseignant(id_enseignant)
) ENGINE=InnoDB;

CREATE TABLE note (
    id_note INT AUTO_INCREMENT PRIMARY KEY,
    note_controle DECIMAL(5,2),
    note_exam DECIMAL(5,2),
    note_projet DECIMAL(5,2),
    moyenne DECIMAL(5,2),
    validee TINYINT(1) DEFAULT 0,
    date_saisie DATETIME,
    date_validation DATETIME,
    id_etudiant INT NOT NULL,
    id_cours INT NOT NULL,
    CONSTRAINT fk_note_etudiant
        FOREIGN KEY (id_etudiant) REFERENCES etudiant(id_etudiant),
    CONSTRAINT fk_note_cours
        FOREIGN KEY (id_cours) REFERENCES cours(id_cours)
) ENGINE=InnoDB;

CREATE TABLE inscription (
    id_inscription INT AUTO_INCREMENT PRIMARY KEY,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('inscrit','desinscrit','en_attente') DEFAULT 'en_attente',
    id_etudiant INT NOT NULL,
    id_cours INT NOT NULL,
    UNIQUE (id_etudiant, id_cours),
    CONSTRAINT fk_inscription_etudiant
        FOREIGN KEY (id_etudiant) REFERENCES etudiant(id_etudiant),
    CONSTRAINT fk_inscription_cours
        FOREIGN KEY (id_cours) REFERENCES cours(id_cours)
) ENGINE=InnoDB;

-- Comptes de demonstration.
-- Les mots de passe sont en clair car public/login.php accepte password_verify
-- et une comparaison simple pour les donnees de test.
INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role, actif) VALUES
('Admin', 'Principal', 'admin@smartcampus.test', 'admin123', 'administrateur', 1),
('Martin', 'Paul', 'paul.martin@smartcampus.test', 'enseignant123', 'enseignant', 1),
('Leroy', 'Sophie', 'sophie.leroy@smartcampus.test', 'enseignant123', 'enseignant', 1),
('Benali', 'Karim', 'karim.benali@smartcampus.test', 'enseignant123', 'enseignant', 1),
('Durand', 'Alice', 'alice.durand@smartcampus.test', 'etudiant123', 'etudiant', 1),
('Bernard', 'Hugo', 'hugo.bernard@smartcampus.test', 'etudiant123', 'etudiant', 1),
('Petit', 'Emma', 'emma.petit@smartcampus.test', 'etudiant123', 'etudiant', 1),
('Moreau', 'Nathan', 'nathan.moreau@smartcampus.test', 'etudiant123', 'etudiant', 1),
('Robert', 'Lea', 'lea.robert@smartcampus.test', 'etudiant123', 'etudiant', 1);

INSERT INTO enseignant (numero_enseignant, specialite, bureau, telephone, id_user) VALUES
('ENS001', 'Informatique', 'B201', '0600000001', 2),
('ENS002', 'Mathematiques', 'B202', '0600000002', 3),
('ENS003', 'Reseaux', 'B203', '0600000003', 4);

INSERT INTO etudiant (numero_etudiant, niveau, groupe_classe, date_naissance, telephone, id_user) VALUES
('ETU001', 'ING2', 'Groupe A', '2004-03-12', '0611111111', 5),
('ETU002', 'ING2', 'Groupe B', '2003-11-20', '0622222222', 6),
('ETU003', 'ING2', 'Groupe A', '2004-06-05', '0633333333', 7),
('ETU004', 'ING2', 'Groupe C', '2003-09-17', '0644444444', 8),
('ETU005', 'ING2', 'Groupe B', '2004-01-28', '0655555555', 9);

INSERT INTO cours (
    code_cours,
    titre,
    description,
    capacite_max,
    jour,
    heure_debut,
    heure_fin,
    salle,
    semestre,
    id_enseignant
) VALUES
('INFO101', 'Programmation PHP', 'Bases de PHP, formulaires et acces MySQL.', 3, 'Lundi', '09:00:00', '11:00:00', 'S101', 'Semestre 1', 1),
('INFO102', 'Base de donnees', 'Modelisation relationnelle et requetes SQL.', 2, 'Mardi', '14:00:00', '16:00:00', 'S102', 'Semestre 1', 1),
('WEB201', 'JavaScript avance', 'JavaScript moderne et interactions web.', 3, 'Mardi', '15:00:00', '17:00:00', 'S103', 'Semestre 1', 1),
('MATH201', 'Analyse numerique', 'Methodes numeriques pour ingenieurs.', 4, 'Mercredi', '10:00:00', '12:00:00', 'M201', 'Semestre 1', 2),
('NET201', 'Reseaux et securite', 'Introduction aux reseaux et a la securite.', 2, 'Jeudi', '13:00:00', '15:00:00', 'R301', 'Semestre 1', 3),
('TESTCAP', 'Cours capacite test', 'Cours utilise pour tester la capacite maximale.', 1, 'Vendredi', '10:00:00', '12:00:00', 'S999', 'Semestre 1', 1);

INSERT INTO inscription (id_etudiant, id_cours, statut, date_inscription) VALUES
(1, 1, 'inscrit', '2026-05-01 09:00:00'),
(2, 2, 'inscrit', '2026-05-01 09:10:00'),
(3, 1, 'en_attente', '2026-05-02 10:00:00'),
(4, 1, 'desinscrit', '2026-05-03 11:00:00'),
(5, 6, 'inscrit', '2026-05-01 09:20:00'),
(1, 4, 'inscrit', '2026-05-01 09:30:00'),
(2, 5, 'inscrit', '2026-05-01 09:40:00');

INSERT INTO note (
    note_controle,
    note_exam,
    note_projet,
    moyenne,
    validee,
    date_saisie,
    date_validation,
    id_etudiant,
    id_cours
) VALUES
(14.00, 15.50, 16.00, 15.15, 1, '2026-05-10 10:00:00', '2026-05-11 09:00:00', 1, 1),
(12.00, 13.00, 14.00, 12.90, 0, '2026-05-10 11:00:00', NULL, 2, 2),
(15.00, 14.00, 13.00, 14.20, 0, '2026-05-12 10:00:00', NULL, 1, 4),
(10.00, 11.00, 12.00, 10.90, 0, '2026-05-12 11:00:00', NULL, 2, 5);

INSERT INTO notification (message, type_notification, lue, date_creation, id_user) VALUES
('Bienvenue sur SmartCampus.', 'information', 0, '2026-05-01 08:00:00', 1),
('Votre inscription au cours Programmation PHP a ete validee.', 'inscription', 0, '2026-05-01 09:05:00', 5),
('Votre inscription au cours Base de donnees a ete validee.', 'inscription', 0, '2026-05-01 09:15:00', 6),
('Votre demande pour Programmation PHP est en attente de validation.', 'inscription', 0, '2026-05-02 10:05:00', 7),
('Votre inscription au cours capacite test a ete validee.', 'inscription', 0, '2026-05-01 09:25:00', 9);
