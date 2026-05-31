<?php
// Fichier central de connexion MySQL.
// Toutes les pages incluent ce fichier et utilisent uniquement la variable $conn.

$serveur = "localhost";
$utilisateur = "root";
$mot_de_passe = "";
$base = "smartcampus";

$conn = new mysqli($serveur, $utilisateur, $mot_de_passe, $base);

if ($conn->connect_error) {
    die("Erreur de connexion a la base de donnees smartcampus : " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
