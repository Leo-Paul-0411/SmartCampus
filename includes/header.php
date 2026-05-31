<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? '';
$nom = $_SESSION['nom'] ?? '';
$prenom = $_SESSION['prenom'] ?? '';
$page_courante = $_SERVER['SCRIPT_NAME'] ?? '';

if ($role === 'administrateur') {
    $role = 'admin';
}

$libelle_role = [
    'admin' => 'Administrateur',
    'enseignant' => 'Enseignant',
    'etudiant' => 'Etudiant'
][$role] ?? '';

function lien_actif($page_courante, $lien)
{
    return $page_courante === $lien ? ' class="active"' : '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCampus</title>
    <link rel="stylesheet" href="/SmartCampus/assets/css/style.css">
</head>
<body>
    <header>
        <div class="topbar">
            <h1>SmartCampus</h1>
            <?php if ($role !== '') { ?>
                <p class="user-context">
                    Connecte en tant que <?php echo htmlspecialchars($libelle_role); ?>
                    <?php if ($prenom !== '' || $nom !== '') { ?>
                        - <?php echo htmlspecialchars(trim($prenom . ' ' . $nom)); ?>
                    <?php } ?>
                </p>
            <?php } ?>
        </div>
        <nav>
            <?php if ($role === '') { ?>
                <a href="/SmartCampus/public/login.php"<?php echo lien_actif($page_courante, '/SmartCampus/public/login.php'); ?>>Connexion</a>
            <?php } elseif ($role === 'admin') { ?>
                <a href="/SmartCampus/admin/dashboard.php"<?php echo lien_actif($page_courante, '/SmartCampus/admin/dashboard.php'); ?>>Tableau de bord</a>
                <a href="/SmartCampus/admin/etudiants.php"<?php echo lien_actif($page_courante, '/SmartCampus/admin/etudiants.php'); ?>>Etudiants</a>
                <a href="/SmartCampus/admin/enseignants.php"<?php echo lien_actif($page_courante, '/SmartCampus/admin/enseignants.php'); ?>>Enseignants</a>
                <a href="/SmartCampus/admin/cours.php"<?php echo lien_actif($page_courante, '/SmartCampus/admin/cours.php'); ?>>Cours</a>
                <a href="/SmartCampus/admin/inscriptions.php"<?php echo lien_actif($page_courante, '/SmartCampus/admin/inscriptions.php'); ?>>Inscriptions</a>
                <a href="/SmartCampus/public/logout.php">Deconnexion</a>
            <?php } elseif ($role === 'enseignant') { ?>
                <a href="/SmartCampus/enseignant/dashboard.php"<?php echo lien_actif($page_courante, '/SmartCampus/enseignant/dashboard.php'); ?>>Tableau de bord</a>
                <a href="/SmartCampus/enseignant/mes_cours.php"<?php echo lien_actif($page_courante, '/SmartCampus/enseignant/mes_cours.php'); ?>>Mes cours</a>
                <a href="/SmartCampus/enseignant/notes.php"<?php echo lien_actif($page_courante, '/SmartCampus/enseignant/notes.php'); ?>>Notes</a>
                <a href="/SmartCampus/public/logout.php">Deconnexion</a>
            <?php } elseif ($role === 'etudiant') { ?>
                <a href="/SmartCampus/etudiant/dashboard.php"<?php echo lien_actif($page_courante, '/SmartCampus/etudiant/dashboard.php'); ?>>Tableau de bord</a>
                <a href="/SmartCampus/etudiant/cours.php"<?php echo lien_actif($page_courante, '/SmartCampus/etudiant/cours.php'); ?>>Mes cours</a>
                <a href="/SmartCampus/etudiant/notes.php"<?php echo lien_actif($page_courante, '/SmartCampus/etudiant/notes.php'); ?>>Mes notes</a>
                <a href="/SmartCampus/etudiant/emploi_du_temps.php"<?php echo lien_actif($page_courante, '/SmartCampus/etudiant/emploi_du_temps.php'); ?>>Emploi du temps</a>
                <a href="/SmartCampus/public/logout.php">Deconnexion</a>
            <?php } ?>
        </nav>
    </header>
    <main>
