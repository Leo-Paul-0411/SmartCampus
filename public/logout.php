<?php
// Deconnexion : on vide la session puis on revient vers la page de connexion.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = array();
session_destroy();

header('Location: login.php');
exit;
