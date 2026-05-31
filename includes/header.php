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

$nom_complet = trim($prenom . ' ' . $nom);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCampus</title>
    <link rel="stylesheet" href="/SmartCampus/assets/css/style.css">
</head>
<body class="<?php echo $role === '' ? 'guest-page' : 'app-page'; ?>">
    <div class="app-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <span class="brand-mark">SC</span>
                <div>
                    <strong>SmartCampus</strong>
                    <span>Gestion academique</span>
                </div>
            </div>

            <?php if ($role !== '') { ?>
                <div class="sidebar-user">
                    <span class="user-role"><?php echo htmlspecialchars($libelle_role); ?></span>
                    <?php if ($nom_complet !== '') { ?>
                        <strong><?php echo htmlspecialchars($nom_complet); ?></strong>
                    <?php } ?>
                </div>
            <?php } ?>

            <nav class="sidebar-nav">
                <?php if ($role === '') { ?>
                    <a href="/SmartCampus/public/login.php"<?php echo lien_actif($page_courante, '/SmartCampus/public/login.php'); ?>>Connexion</a>
                <?php } elseif ($role === 'admin') { ?>
                    <a href="/SmartCampus/admin/dashboard.php"<?php echo lien_actif($page_courante, '/SmartCampus/admin/dashboard.php'); ?>>Tableau de bord</a>
                    <a href="/SmartCampus/admin/etudiants.php"<?php echo lien_actif($page_courante, '/SmartCampus/admin/etudiants.php'); ?>>Etudiants</a>
                    <a href="/SmartCampus/admin/enseignants.php"<?php echo lien_actif($page_courante, '/SmartCampus/admin/enseignants.php'); ?>>Enseignants</a>
                    <a href="/SmartCampus/admin/cours.php"<?php echo lien_actif($page_courante, '/SmartCampus/admin/cours.php'); ?>>Cours</a>
                    <a href="/SmartCampus/admin/inscriptions.php"<?php echo lien_actif($page_courante, '/SmartCampus/admin/inscriptions.php'); ?>>Inscriptions</a>
                    <a class="logout-link" href="/SmartCampus/public/logout.php">Deconnexion</a>
                <?php } elseif ($role === 'enseignant') { ?>
                    <a href="/SmartCampus/enseignant/dashboard.php"<?php echo lien_actif($page_courante, '/SmartCampus/enseignant/dashboard.php'); ?>>Tableau de bord</a>
                    <a href="/SmartCampus/enseignant/mes_cours.php"<?php echo lien_actif($page_courante, '/SmartCampus/enseignant/mes_cours.php'); ?>>Mes cours</a>
                    <a href="/SmartCampus/enseignant/notes.php"<?php echo lien_actif($page_courante, '/SmartCampus/enseignant/notes.php'); ?>>Notes</a>
                    <a class="logout-link" href="/SmartCampus/public/logout.php">Deconnexion</a>
                <?php } elseif ($role === 'etudiant') { ?>
                    <a href="/SmartCampus/etudiant/dashboard.php"<?php echo lien_actif($page_courante, '/SmartCampus/etudiant/dashboard.php'); ?>>Tableau de bord</a>
                    <a href="/SmartCampus/etudiant/cours.php"<?php echo lien_actif($page_courante, '/SmartCampus/etudiant/cours.php'); ?>>Mes cours</a>
                    <a href="/SmartCampus/etudiant/notes.php"<?php echo lien_actif($page_courante, '/SmartCampus/etudiant/notes.php'); ?>>Mes notes</a>
                    <a href="/SmartCampus/etudiant/emploi_du_temps.php"<?php echo lien_actif($page_courante, '/SmartCampus/etudiant/emploi_du_temps.php'); ?>>Emploi du temps</a>
                    <a class="logout-link" href="/SmartCampus/public/logout.php">Deconnexion</a>
                <?php } ?>
            </nav>
        </aside>

        <div class="main-content">
            <header class="topbar">
                <div>
                    <p class="topbar-kicker">Plateforme de gestion academique</p>
                    <strong>SmartCampus</strong>
                </div>
                <?php if ($role !== '') { ?>
                    <p class="user-context">
                        <?php echo htmlspecialchars($libelle_role); ?>
                        <?php if ($nom_complet !== '') { ?>
                            - <?php echo htmlspecialchars($nom_complet); ?>
                        <?php } ?>
                    </p>
                <?php } ?>
            </header>
            <main>
