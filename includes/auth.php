<?php
// Fonctions minimales pour preparer la gestion de session.

function demarrer_session()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function est_connecte()
{
    demarrer_session();
    return isset($_SESSION['id_user']);
}

function verifier_role($role)
{
    demarrer_session();
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}
