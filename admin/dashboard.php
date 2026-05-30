<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('administrateur');
}

include __DIR__ . '/../includes/header.php';
?>

<main class="container">
    <h1>Dashboard administrateur</h1>

    <?php
    function compter($conn, $sql) {
        $resultat = mysqli_query($conn, $sql);
        if (!$resultat) { return 0; }
        $ligne = mysqli_fetch_assoc($resultat);
        return $ligne['total'];
    }

    $total_etudiants = compter($conn, "SELECT COUNT(*) AS total FROM etudiant");
    $total_enseignants = compter($conn, "SELECT COUNT(*) AS total FROM enseignant");
    $total_cours = compter($conn, "SELECT COUNT(*) AS total FROM cours");
    $total_inscriptions = compter($conn, "SELECT COUNT(*) AS total FROM inscription");
    ?>

    <section class="stats-grid">
        <article class="stat-card"><h2>Étudiants</h2><p><?php echo $total_etudiants; ?></p></article>
        <article class="stat-card"><h2>Enseignants</h2><p><?php echo $total_enseignants; ?></p></article>
        <article class="stat-card"><h2>Cours</h2><p><?php echo $total_cours; ?></p></article>
        <article class="stat-card"><h2>Inscriptions</h2><p><?php echo $total_inscriptions; ?></p></article>
    </section>

    <section>
        <h2>Inscriptions récentes</h2>
        <?php
        $sql = "
            SELECT inscription.date_inscription, inscription.statut, utilisateur.nom, utilisateur.prenom, cours.titre
            FROM inscription
            INNER JOIN etudiant ON inscription.id_etudiant = etudiant.id_etudiant
            INNER JOIN utilisateur ON etudiant.id_user = utilisateur.id_user
            INNER JOIN cours ON inscription.id_cours = cours.id_cours
            ORDER BY inscription.date_inscription DESC
            LIMIT 5
        ";
        $resultat = mysqli_query($conn, $sql);
        ?>
        <table>
            <thead><tr><th>Étudiant</th><th>Cours</th><th>Date</th><th>Statut</th></tr></thead>
            <tbody>
                <?php if ($resultat && mysqli_num_rows($resultat) > 0): ?>
                    <?php while ($ligne = mysqli_fetch_assoc($resultat)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ligne['prenom'] . ' ' . $ligne['nom']); ?></td>
                            <td><?php echo htmlspecialchars($ligne['titre']); ?></td>
                            <td><?php echo htmlspecialchars($ligne['date_inscription']); ?></td>
                            <td><?php echo htmlspecialchars($ligne['statut']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">Aucune inscription récente.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

