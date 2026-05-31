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

INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role) VALUES
('Admin', 'Principal', 'admin@smartcampus.test', 'admin123', 'administrateur'),
('Martin', 'Paul', 'paul.martin@smartcampus.test', 'enseignant123', 'enseignant'),
('Durand', 'Alice', 'alice.durand@smartcampus.test', 'etudiant123', 'etudiant'),
('Bernard', 'Hugo', 'hugo.bernard@smartcampus.test', 'etudiant123', 'etudiant');

INSERT INTO enseignant (numero_enseignant, specialite, bureau, telephone, id_user) VALUES
('ENS001', 'Informatique', 'B201', '0600000001', 2);

INSERT INTO etudiant (numero_etudiant, niveau, groupe_classe, date_naissance, telephone, id_user) VALUES
('ETU001', 'ING2', 'Groupe A', '2004-03-12', '0611111111', 3),
('ETU002', 'ING2', 'Groupe B', '2003-11-20', '0622222222', 4);

INSERT INTO cours (code_cours, titre, description, capacite_max, jour, heure_debut, heure_fin, salle, semestre, id_enseignant) VALUES
('INFO101', 'Programmation PHP', 'Bases de PHP et MySQL', 30, 'Lundi', '09:00:00', '11:00:00', 'S101', 'Semestre 1', 1),
('INFO102', 'Base de donnees', 'Modelisation et SQL', 25, 'Mardi', '14:00:00', '16:00:00', 'S102', 'Semestre 1', 1);

INSERT INTO inscription (id_etudiant, id_cours, statut) VALUES
(1, 1, 'inscrit'),
(2, 1, 'en_attente'),
(2, 2, 'inscrit');

INSERT INTO note (note_controle, note_exam, note_projet, moyenne, validee, date_saisie, date_validation, id_etudiant, id_cours) VALUES
(14.00, 15.50, 16.00, 15.15, 1, '2026-05-01 10:00:00', '2026-05-02 09:00:00', 1, 1),
(12.00, 13.00, 14.00, 12.90, 0, '2026-05-03 10:00:00', NULL, 2, 2);

INSERT INTO notification (message, type_notification, lue, id_user) VALUES
('Bienvenue sur SmartCampus.', 'information', 0, 1),
('Votre cours Programmation PHP est disponible.', 'cours', 0, 2),
('Votre inscription est enregistree.', 'inscription', 0, 3);
