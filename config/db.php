<?php
// Connexion simple a la base de donnees MySQL avec mysqli.

$serveur = 'localhost';
$utilisateur = 'root';
$mot_de_passe = '';
$base = 'smartcampus';

$connexion = new mysqli($serveur, $utilisateur, $mot_de_passe, $base);

if ($connexion->connect_error) {
    die('Erreur de connexion a la base de donnees.');
}

$connexion->set_charset('utf8mb4');
