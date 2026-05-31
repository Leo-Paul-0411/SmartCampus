<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('enseignant');
}

$id_enseignant = $_SESSION['id_enseignant'] ?? 0;

include __DIR__ . '/../includes/header.php';
?>

<section class="container">
    <h1>Dashboard enseignant</h1>
    <p class="page-subtitle">Acces rapide aux cours, aux etudiants inscrits et aux notes a finaliser.</p>
    <?php
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM cours WHERE id_enseignant = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_enseignant);
    mysqli_stmt_execute($stmt);
    $ligne = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $total_cours = $ligne['total'] ?? 0;

    $stmt_etudiants = mysqli_prepare(
        $conn,
        "SELECT COUNT(DISTINCT inscription.id_etudiant) AS total
         FROM inscription
         INNER JOIN cours ON inscription.id_cours = cours.id_cours
         WHERE cours.id_enseignant = ?
         AND inscription.statut = 'inscrit'"
    );
    mysqli_stmt_bind_param($stmt_etudiants, "i", $id_enseignant);
    mysqli_stmt_execute($stmt_etudiants);
    $total_etudiants = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_etudiants))['total'] ?? 0;

    $stmt_notes = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total
         FROM note
         INNER JOIN cours ON note.id_cours = cours.id_cours
         WHERE cours.id_enseignant = ?
         AND note.validee = 0"
    );
    mysqli_stmt_bind_param($stmt_notes, "i", $id_enseignant);
    mysqli_stmt_execute($stmt_notes);
    $notes_non_validees = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_notes))['total'] ?? 0;
    ?>
    <section class="stats-grid">
        <article class="stat-card"><h2>Mes cours</h2><p><?php echo $total_cours; ?></p></article>
        <article class="stat-card"><h2>Etudiants inscrits</h2><p><?php echo $total_etudiants; ?></p></article>
        <article class="stat-card"><h2>Notes non validees</h2><p><?php echo $notes_non_validees; ?></p></article>
    </section>
    <section class="menu-cards">
        <a class="card-link" href="mes_cours.php">Consulter mes cours</a>
        <a class="card-link" href="notes.php">Saisie des notes</a>
    </section>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
