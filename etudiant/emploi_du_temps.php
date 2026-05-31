<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('etudiant');
}

$id_etudiant = $_SESSION['id_etudiant'] ?? 0;

include __DIR__ . '/../includes/header.php';
?>

<section class="container">
    <h1>Emploi du temps</h1>
    <p class="page-subtitle">Seuls les cours avec le statut inscrit sont affiches.</p>

    <?php
    $sql = "SELECT cours.jour, cours.heure_debut, cours.heure_fin, cours.titre, cours.salle,
                   utilisateur.nom, utilisateur.prenom
            FROM inscription
            INNER JOIN cours ON inscription.id_cours = cours.id_cours
            INNER JOIN enseignant ON cours.id_enseignant = enseignant.id_enseignant
            INNER JOIN utilisateur ON enseignant.id_user = utilisateur.id_user
            WHERE inscription.id_etudiant = ?
            AND inscription.statut = 'inscrit'
            ORDER BY cours.jour, cours.heure_debut";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_etudiant);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);

    $cours_par_jour = [];
    if ($resultat) {
        while ($cours = mysqli_fetch_assoc($resultat)) {
            $cours_par_jour[$cours['jour']][] = $cours;
        }
    }
    ?>

    <section class="schedule">
    <?php if (!empty($cours_par_jour)): ?>
        <?php foreach ($cours_par_jour as $jour => $liste_cours): ?>
            <section class="day-block schedule-day">
                <h2><?php echo htmlspecialchars($jour); ?></h2>
                <table>
                    <thead>
                        <tr>
                            <th>Debut</th>
                            <th>Fin</th>
                            <th>Cours</th>
                            <th>Salle</th>
                            <th>Enseignant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($liste_cours as $cours): ?>
                            <tr>
                                <td><span class="schedule-time"><?php echo htmlspecialchars($cours['heure_debut']); ?></span></td>
                                <td><?php echo htmlspecialchars($cours['heure_fin']); ?></td>
                                <td><span class="schedule-course"><?php echo htmlspecialchars($cours['titre']); ?></span></td>
                                <td><?php echo htmlspecialchars($cours['salle']); ?></td>
                                <td><?php echo htmlspecialchars($cours['prenom'] . ' ' . $cours['nom']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="empty-state">Aucun cours dans l'emploi du temps.</p>
    <?php endif; ?>
    </section>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
