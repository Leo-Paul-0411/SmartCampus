<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/fonctions.php';

if (function_exists('verifier_role')) {
    verifier_role('etudiant');
}

$id_etudiant = $_SESSION['id_etudiant'] ?? 0;
$id_user = $_SESSION['id_user'] ?? 0;
$message = "";
$erreur = "";

if (isset($_POST['desinscrire'])) {
    $id_inscription = intval($_POST['id_inscription'] ?? 0);

    $sql = "SELECT i.id_inscription, c.titre
            FROM inscription i
            INNER JOIN cours c ON i.id_cours = c.id_cours
            WHERE i.id_inscription = ?
            AND i.id_etudiant = ?
            AND i.statut = 'inscrit'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id_inscription, $id_etudiant);
    mysqli_stmt_execute($stmt);
    $inscription = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($inscription) {
        $stmt_update = mysqli_prepare($conn, "UPDATE inscription SET statut = 'desinscrit' WHERE id_inscription = ? AND id_etudiant = ?");
        mysqli_stmt_bind_param($stmt_update, "ii", $id_inscription, $id_etudiant);

        if (mysqli_stmt_execute($stmt_update)) {
            creer_notification($conn, $id_user, "Vous vous etes desinscrit du cours " . $inscription['titre'] . ".", "inscription");
            $message = "Desinscription effectuee avec succes.";
        } else {
            $erreur = "Erreur lors de la desinscription.";
        }

        mysqli_stmt_close($stmt_update);
    } else {
        $erreur = "Erreur : inscription introuvable ou deja inactive.";
    }
}

include __DIR__ . '/../includes/header.php';
?>

<section class="container">
    <h1>Mes cours</h1>

    <?php if ($message): ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <?php if ($erreur): ?><p class="error"><?php echo htmlspecialchars($erreur); ?></p><?php endif; ?>

    <?php
    $sql = "SELECT i.id_inscription, i.statut,
                   cours.code_cours, cours.titre, cours.jour, cours.heure_debut, cours.heure_fin, cours.salle, cours.semestre
            FROM inscription i
            INNER JOIN cours ON i.id_cours = cours.id_cours
            WHERE i.id_etudiant = ?
            ORDER BY cours.jour, cours.heure_debut";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_etudiant);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);
    ?>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Cours</th>
                <th>Jour</th>
                <th>Debut</th>
                <th>Fin</th>
                <th>Salle</th>
                <th>Semestre</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultat && mysqli_num_rows($resultat) > 0): ?>
                <?php while ($cours = mysqli_fetch_assoc($resultat)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cours['code_cours']); ?></td>
                        <td><?php echo htmlspecialchars($cours['titre']); ?></td>
                        <td><?php echo htmlspecialchars($cours['jour']); ?></td>
                        <td><?php echo htmlspecialchars($cours['heure_debut']); ?></td>
                        <td><?php echo htmlspecialchars($cours['heure_fin']); ?></td>
                        <td><?php echo htmlspecialchars($cours['salle']); ?></td>
                        <td><?php echo htmlspecialchars($cours['semestre']); ?></td>
                        <td><?php echo htmlspecialchars($cours['statut']); ?></td>
                        <td>
                            <?php if ($cours['statut'] === 'inscrit'): ?>
                                <form method="post" action="cours.php">
                                    <input type="hidden" name="id_inscription" value="<?php echo htmlspecialchars($cours['id_inscription']); ?>">
                                    <button type="submit" name="desinscrire" class="danger js-confirm-delete" data-confirm="Se desinscrire de ce cours ?">Se desinscrire</button>
                                </form>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9">Aucun cours trouve.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
