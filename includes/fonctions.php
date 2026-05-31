<?php

function securiser($valeur)
{
    return htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8');
}

// Verifie si l'etudiant est deja inscrit au cours.
function etudiant_deja_inscrit($conn, $id_etudiant, $id_cours)
{
    $sql = "SELECT COUNT(*) AS total
            FROM inscription
            WHERE id_etudiant = ?
            AND id_cours = ?
            AND statut = 'inscrit'";

    $requete = mysqli_prepare($conn, $sql);

    if (!$requete) {
        return false;
    }

    mysqli_stmt_bind_param($requete, "ii", $id_etudiant, $id_cours);
    mysqli_stmt_execute($requete);
    $resultat = mysqli_stmt_get_result($requete);
    $ligne = mysqli_fetch_assoc($resultat);
    mysqli_stmt_close($requete);

    return $ligne['total'] > 0;
}

// Recupere une inscription existante pour le couple etudiant / cours.
function recuperer_inscription($conn, $id_etudiant, $id_cours)
{
    $sql = "SELECT id_inscription, statut
            FROM inscription
            WHERE id_etudiant = ?
            AND id_cours = ?
            LIMIT 1";

    $requete = mysqli_prepare($conn, $sql);

    if (!$requete) {
        return false;
    }

    mysqli_stmt_bind_param($requete, "ii", $id_etudiant, $id_cours);
    mysqli_stmt_execute($requete);
    $resultat = mysqli_stmt_get_result($requete);
    $inscription = mysqli_fetch_assoc($resultat);
    mysqli_stmt_close($requete);

    return $inscription;
}

// Reactive une inscription deja existante.
function reactiver_inscription($conn, $id_inscription)
{
    $sql = "UPDATE inscription
            SET statut = 'inscrit', date_inscription = NOW()
            WHERE id_inscription = ?";

    $requete = mysqli_prepare($conn, $sql);

    if (!$requete) {
        return false;
    }

    mysqli_stmt_bind_param($requete, "i", $id_inscription);
    $succes = mysqli_stmt_execute($requete);
    mysqli_stmt_close($requete);

    return $succes;
}

// Verifie si le nombre d'inscrits atteint la capacite maximale du cours.
function cours_est_complet($conn, $id_cours)
{
    $sql = "SELECT c.capacite_max, COUNT(i.id_inscription) AS total
            FROM cours c
            LEFT JOIN inscription i ON c.id_cours = i.id_cours AND i.statut = 'inscrit'
            WHERE c.id_cours = ?
            GROUP BY c.id_cours, c.capacite_max";

    $requete = mysqli_prepare($conn, $sql);

    if (!$requete) {
        return false;
    }

    mysqli_stmt_bind_param($requete, "i", $id_cours);
    mysqli_stmt_execute($requete);
    $resultat = mysqli_stmt_get_result($requete);
    $ligne = mysqli_fetch_assoc($resultat);
    mysqli_stmt_close($requete);

    if (!$ligne) {
        return false;
    }

    return $ligne['total'] >= $ligne['capacite_max'];
}

// Verifie si le nouveau cours chevauche un cours deja inscrit le meme jour.
function conflit_horaire($conn, $id_etudiant, $id_cours)
{
    $sql = "SELECT COUNT(*) AS total
            FROM cours nouveau
            INNER JOIN inscription i ON i.id_etudiant = ? AND i.statut = 'inscrit'
            INNER JOIN cours existant ON i.id_cours = existant.id_cours
            WHERE nouveau.id_cours = ?
            AND nouveau.jour = existant.jour
            AND nouveau.heure_debut < existant.heure_fin
            AND nouveau.heure_fin > existant.heure_debut";

    $requete = mysqli_prepare($conn, $sql);

    if (!$requete) {
        return false;
    }

    mysqli_stmt_bind_param($requete, "ii", $id_etudiant, $id_cours);
    mysqli_stmt_execute($requete);
    $resultat = mysqli_stmt_get_result($requete);
    $ligne = mysqli_fetch_assoc($resultat);
    mysqli_stmt_close($requete);

    return $ligne['total'] > 0;
}

// Cree une notification pour un utilisateur.
function creer_notification($conn, $id_user, $message, $type_notification)
{
    $sql = "INSERT INTO notification (id_user, message, type_notification)
            VALUES (?, ?, ?)";

    $requete = mysqli_prepare($conn, $sql);

    if (!$requete) {
        return false;
    }

    mysqli_stmt_bind_param($requete, "iss", $id_user, $message, $type_notification);
    $succes = mysqli_stmt_execute($requete);
    mysqli_stmt_close($requete);

    return $succes;
}
