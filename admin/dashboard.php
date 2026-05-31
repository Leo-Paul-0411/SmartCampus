<?php
// Tableau de bord administrateur : resume l'etat global de la plateforme.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('administrateur');
}

include __DIR__ . '/../includes/header.php';
?>

<section class="container">
    <h1>Dashboard administrateur</h1>
    <p class="page-subtitle">Vue rapide pour piloter la demo : effectifs, cours, inscriptions et demandes a traiter.</p>

    <?php
    // Petite fonction locale pour recuperer les compteurs affiches en cartes.
    function compter($conn, $sql) {
        $resultat = mysqli_query($conn, $sql);
        if (!$resultat) { return 0; }
        $ligne = mysqli_fetch_assoc($resultat);
        return $ligne['total'];
    }

    $total_etudiants = compter($conn, "SELECT COUNT(*) AS total FROM etudiant");
    $total_enseignants = compter($conn, "SELECT COUNT(*) AS total FROM enseignant");
    $total_cours = compter($conn, "SELECT COUNT(*) AS total FROM cours");
    $total_inscriptions = compter($conn, "SELECT COUNT(*) AS total FROM inscription WHERE statut = 'inscrit'");
    $total_demandes = compter($conn, "SELECT COUNT(*) AS total FROM inscription WHERE statut = 'en_attente'");
    $total_cours_complets = compter(
        $conn,
        "SELECT COUNT(*) AS total
         FROM cours
         WHERE (SELECT COUNT(*) FROM inscription WHERE inscription.id_cours = cours.id_cours AND statut = 'inscrit') >= capacite_max"
    );
    ?>

    <section class="stats-grid">
        <article class="stat-card"><h2>Etudiants</h2><p><?php echo $total_etudiants; ?></p></article>
        <article class="stat-card"><h2>Enseignants</h2><p><?php echo $total_enseignants; ?></p></article>
        <article class="stat-card"><h2>Cours</h2><p><?php echo $total_cours; ?></p></article>
        <article class="stat-card"><h2>Inscriptions valides</h2><p><?php echo $total_inscriptions; ?></p></article>
        <article class="stat-card"><h2>Demandes en attente</h2><p><?php echo $total_demandes; ?></p></article>
        <article class="stat-card"><h2>Cours complets</h2><p><?php echo $total_cours_complets; ?></p></article>
    </section>

    <section class="menu-cards">
        <a class="card-link" href="/SmartCampus/admin/etudiants.php">Gestion etudiants</a>
        <a class="card-link" href="/SmartCampus/admin/enseignants.php">Gestion enseignants</a>
        <a class="card-link" href="/SmartCampus/admin/cours.php">Gestion cours</a>
        <a class="card-link" href="/SmartCampus/admin/inscriptions.php">Gestion inscriptions</a>
    </section>

    <section>
        <h2>Inscriptions recentes</h2>
        <?php
        // Dernieres inscriptions utiles pour suivre l'activite recente.
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
            <thead><tr><th>Etudiant</th><th>Cours</th><th>Date</th><th>Statut</th></tr></thead>
            <tbody>
                <?php if ($resultat && mysqli_num_rows($resultat) > 0): ?>
                    <?php while ($ligne = mysqli_fetch_assoc($resultat)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ligne['prenom'] . ' ' . $ligne['nom']); ?></td>
                            <td><?php echo htmlspecialchars($ligne['titre']); ?></td>
                            <td><?php echo htmlspecialchars($ligne['date_inscription']); ?></td>
                            <td><span class="badge badge-info"><?php echo htmlspecialchars($ligne['statut']); ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">Aucune inscription recente.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
