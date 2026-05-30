<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('enseignant');
}

// TODO : remplacer par l'id enseignant venant de la session quand l'authentification sera finalisée.
$id_enseignant = $_SESSION['id_enseignant'] ?? 1;

include __DIR__ . '/../includes/header.php';
?>

<main class="container">
    <h1>Dashboard enseignant</h1>
    <?php
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM cours WHERE id_enseignant = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_enseignant); mysqli_stmt_execute($stmt); $ligne = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)); $total_cours = $ligne['total'] ?? 0;
    ?>
    <section class="stats-grid"><article class="stat-card"><h2>Mes cours</h2><p><?php echo $total_cours; ?></p></article></section>
    <section class="menu-cards"><a class="card-link" href="mes_cours.php">Consulter mes cours</a><a class="card-link" href="notes.php">Saisie des notes</a></section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
