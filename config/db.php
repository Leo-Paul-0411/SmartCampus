<?php


$serveur = 'localhost';
$utilisateur = 'root';
$mot_de_passe = 'root'; 
$base = 'smartcampus';


$conn = new mysqli($serveur, $utilisateur, $mot_de_passe, $base);

if ($conn->connect_error) {
    die('Erreur de connexion a la base de donnees : ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');