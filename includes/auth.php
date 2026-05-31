<?php
// Demarrage de la session si elle n'est pas deja active.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifie si un utilisateur est connecte.
function est_connecte()
{
    return isset($_SESSION['id_user']);
}

// Oblige l'utilisateur a etre connecte.
function verifier_connexion()
{
    if (!est_connecte()) {
        header('Location: ../public/login.php');
        exit;
    }
}

// Verifie que l'utilisateur connecte possede le bon role.
function verifier_role($role)
{
    verifier_connexion();

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        rediriger_selon_role($_SESSION['role'] ?? '');
    }
}

// Redirige l'utilisateur vers le dashboard correspondant a son role.
function rediriger_selon_role($role)
{
    if ($role === 'admin') {
        header('Location: ../admin/dashboard.php');
        exit;
    }

    if ($role === 'enseignant') {
        header('Location: ../enseignant/dashboard.php');
        exit;
    }

    if ($role === 'etudiant') {
        header('Location: ../etudiant/dashboard.php');
        exit;
    }

    header('Location: ../public/login.php');
    exit;
}
