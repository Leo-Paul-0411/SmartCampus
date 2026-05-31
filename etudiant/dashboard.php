<?php
// Dashboard etudiant : resume les cours, demandes, notes et notifications personnelles.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('etudiant');
}

$id_etudiant = $_SESSION['id_etudiant'] ?? 0;
$id_user = $_SESSION['id_user'] ?? 0;

include __DIR__ . '/../includes/header.php';
?>

<section class="container">
    <h1>Dashboard etudiant</h1>
    <p class="page-subtitle">Suivi rapide des cours, demandes d'inscription, notes et notifications.</p>

    <?php
    // Tous les compteurs sont filtres avec l'id de l'etudiant connecte.
    $stmt_cours = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM inscription WHERE id_etudiant = ? AND statut = 'inscrit'");
    mysqli_stmt_bind_param($stmt_cours, "i", $id_etudiant);
    mysqli_stmt_execute($stmt_cours);
    $total_cours = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cours))['total'] ?? 0;

    $stmt_notes = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM note WHERE id_etudiant = ?");
    mysqli_stmt_bind_param($stmt_notes, "i", $id_etudiant);
    mysqli_stmt_execute($stmt_notes);
    $total_notes = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_notes))['total'] ?? 0;

    $stmt_demandes = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM inscription WHERE id_etudiant = ? AND statut = 'en_attente'");
    mysqli_stmt_bind_param($stmt_demandes, "i", $id_etudiant);
    mysqli_stmt_execute($stmt_demandes);
    $total_demandes = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_demandes))['total'] ?? 0;

    $stmt_moyenne = mysqli_prepare($conn, "SELECT AVG(moyenne) AS moyenne_generale FROM note WHERE id_etudiant = ? AND validee = 1");
    mysqli_stmt_bind_param($stmt_moyenne, "i", $id_etudiant);
    mysqli_stmt_execute($stmt_moyenne);
    $moyenne_generale = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_moyenne))['moyenne_generale'] ?? null;
    ?>

    <section class="stats-grid">
        <article class="stat-card"><h2>Cours inscrits</h2><p><?php echo $total_cours; ?></p></article>
        <article class="stat-card"><h2>Demandes en attente</h2><p><?php echo $total_demandes; ?></p></article>
        <article class="stat-card"><h2>Notes disponibles</h2><p><?php echo $total_notes; ?></p></article>
        <article class="stat-card"><h2>Moyenne generale</h2><p><?php echo $moyenne_generale !== null ? number_format($moyenne_generale, 2) : '-'; ?></p></article>
    </section>

    <section class="menu-cards">
        <a class="card-link" href="cours.php">Mes cours</a>
        <a class="card-link" href="notes.php">Mes notes</a>
        <a class="card-link" href="emploi_du_temps.php">Emploi du temps</a>
    </section>

    <section>
        <h2>Notifications recentes</h2>
        <?php
        $stmt_notifications = mysqli_prepare(
            $conn,
            "SELECT message, type_notification, date_creation
             FROM notification
             WHERE id_user = ?
             ORDER BY date_creation DESC
             LIMIT 5"
        );
        mysqli_stmt_bind_param($stmt_notifications, "i", $id_user);
        mysqli_stmt_execute($stmt_notifications);
        $notifications = mysqli_stmt_get_result($stmt_notifications);
        ?>

        <table>
            <thead>
                <tr>
                    <th>Message</th>
                    <th>Type</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($notifications && mysqli_num_rows($notifications) > 0): ?>
                    <?php while ($notification = mysqli_fetch_assoc($notifications)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($notification['message']); ?></td>
                            <td><span class="badge badge-info"><?php echo htmlspecialchars($notification['type_notification']); ?></span></td>
                            <td><?php echo htmlspecialchars($notification['date_creation']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3">Aucune notification recente.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
