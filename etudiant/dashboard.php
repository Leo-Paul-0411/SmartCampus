<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('etudiant');
}

// TODO : remplacer par l'id étudiant venant de la session quand l'authentification sera finalisée.
$id_etudiant = $_SESSION['id_etudiant'] ?? 1;

include __DIR__ . '/../includes/header.php';
?>

<section class="container"><h1>Dashboard étudiant</h1><?php $stmt_cours = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM inscription WHERE id_etudiant = ?"); mysqli_stmt_bind_param($stmt_cours, "i", $id_etudiant); mysqli_stmt_execute($stmt_cours); $total_cours = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cours))['total'] ?? 0; $stmt_notes = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM note WHERE id_etudiant = ?"); mysqli_stmt_bind_param($stmt_notes, "i", $id_etudiant); mysqli_stmt_execute($stmt_notes); $total_notes = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_notes))['total'] ?? 0; ?><section class="stats-grid"><article class="stat-card"><h2>Cours inscrits</h2><p><?php echo $total_cours; ?></p></article><article class="stat-card"><h2>Notes disponibles</h2><p><?php echo $total_notes; ?></p></article></section><section class="menu-cards"><a class="card-link" href="cours.php">Mes cours</a><a class="card-link" href="notes.php">Mes notes</a><a class="card-link" href="emploi_du_temps.php">Emploi du temps</a></section></section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
