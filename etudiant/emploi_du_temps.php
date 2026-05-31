<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('etudiant');
}

// TODO : remplacer par l'id etudiant venant de la session quand l'authentification sera finalisee.
$id_etudiant = $_SESSION['id_etudiant'] ?? 1;

include __DIR__ . '/../includes/header.php';
?>

<section class="container">
    <h1>Emploi du temps</h1>

    <?php
    $sql = "SELECT cours.jour, cours.heure_debut, cours.heure_fin, cours.titre, cours.salle
            FROM inscription
            INNER JOIN cours ON inscription.id_cours = cours.id_cours
            WHERE inscription.id_etudiant = ?
            AND inscription.statut = 'inscrit'
            ORDER BY cours.jour, cours.heure_debut";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_etudiant);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);
    ?>

    <table>
        <thead>
            <tr>
                <th>Jour</th>
                <th>Debut</th>
                <th>Fin</th>
                <th>Cours</th>
                <th>Salle</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultat && mysqli_num_rows($resultat) > 0): ?>
                <?php while ($cours = mysqli_fetch_assoc($resultat)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cours['jour']); ?></td>
                        <td><?php echo htmlspecialchars($cours['heure_debut']); ?></td>
                        <td><?php echo htmlspecialchars($cours['heure_fin']); ?></td>
                        <td><?php echo htmlspecialchars($cours['titre']); ?></td>
                        <td><?php echo htmlspecialchars($cours['salle']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Aucun cours dans l'emploi du temps.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
