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

if (isset($_POST['demander_inscription'])) {
    $id_cours = intval($_POST['id_cours'] ?? 0);

    if ($id_etudiant <= 0 || $id_cours <= 0) {
        $erreur = "Erreur : demande impossible.";
    } else {
        $stmt_cours = mysqli_prepare($conn, "SELECT titre FROM cours WHERE id_cours = ?");
        mysqli_stmt_bind_param($stmt_cours, "i", $id_cours);
        mysqli_stmt_execute($stmt_cours);
        $cours_demande = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cours));
        mysqli_stmt_close($stmt_cours);

        $inscription = recuperer_inscription($conn, $id_etudiant, $id_cours);

        if (!$cours_demande) {
            $erreur = "Erreur : cours introuvable.";
        } elseif (!$inscription) {
            $statut = "en_attente";
            $stmt_insert = mysqli_prepare($conn, "INSERT INTO inscription (id_etudiant, id_cours, statut) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt_insert, "iis", $id_etudiant, $id_cours, $statut);

            if (mysqli_stmt_execute($stmt_insert)) {
                creer_notification($conn, $id_user, "Votre demande d'inscription au cours " . $cours_demande['titre'] . " a ete envoyee.", "inscription");
                $message = "Demande d'inscription envoyee.";
            } else {
                $erreur = "Erreur lors de l'envoi de la demande.";
            }

            mysqli_stmt_close($stmt_insert);
        } elseif ($inscription['statut'] === 'desinscrit') {
            $stmt_update = mysqli_prepare($conn, "UPDATE inscription SET statut = 'en_attente', date_inscription = NOW() WHERE id_inscription = ?");
            mysqli_stmt_bind_param($stmt_update, "i", $inscription['id_inscription']);

            if (mysqli_stmt_execute($stmt_update)) {
                creer_notification($conn, $id_user, "Votre demande d'inscription au cours " . $cours_demande['titre'] . " a ete envoyee.", "inscription");
                $message = "Demande d'inscription envoyee.";
            } else {
                $erreur = "Erreur lors de l'envoi de la demande.";
            }

            mysqli_stmt_close($stmt_update);
        } elseif ($inscription['statut'] === 'en_attente') {
            $message = "Demande deja en attente.";
        } elseif ($inscription['statut'] === 'inscrit') {
            $message = "Vous etes deja inscrit a ce cours.";
        }
    }
}

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
    <h1>Mes cours et demandes</h1>

    <?php if ($message): ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <?php if ($erreur): ?><p class="error"><?php echo htmlspecialchars($erreur); ?></p><?php endif; ?>

    <?php
    $sql = "SELECT c.id_cours, c.code_cours, c.titre, c.jour, c.heure_debut, c.heure_fin,
                   c.salle, c.semestre, c.capacite_max,
                   u.nom AS enseignant_nom, u.prenom AS enseignant_prenom,
                   i.id_inscription, i.statut,
                   (SELECT COUNT(*) FROM inscription i2 WHERE i2.id_cours = c.id_cours AND i2.statut = 'inscrit') AS nb_inscrits
            FROM cours c
            INNER JOIN enseignant ens ON c.id_enseignant = ens.id_enseignant
            INNER JOIN utilisateur u ON ens.id_user = u.id_user
            LEFT JOIN inscription i ON c.id_cours = i.id_cours AND i.id_etudiant = ?
            ORDER BY c.jour, c.heure_debut, c.code_cours";
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
                <th>Horaire</th>
                <th>Salle</th>
                <th>Enseignant</th>
                <th>Capacite</th>
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
                        <td><?php echo htmlspecialchars($cours['heure_debut'] . ' - ' . $cours['heure_fin']); ?></td>
                        <td><?php echo htmlspecialchars($cours['salle']); ?></td>
                        <td><?php echo htmlspecialchars($cours['enseignant_prenom'] . ' ' . $cours['enseignant_nom']); ?></td>
                        <td><?php echo htmlspecialchars($cours['nb_inscrits'] . ' / ' . $cours['capacite_max']); ?></td>
                        <td>
                            <?php
                            if ($cours['statut'] === 'inscrit') {
                                echo 'Inscrit';
                            } elseif ($cours['statut'] === 'en_attente') {
                                echo 'Demande en attente';
                            } elseif ($cours['statut'] === 'desinscrit') {
                                echo 'Desinscrit';
                            } else {
                                echo 'Disponible';
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($cours['statut'] === 'inscrit'): ?>
                                <form method="post" action="cours.php">
                                    <input type="hidden" name="id_inscription" value="<?php echo htmlspecialchars($cours['id_inscription']); ?>">
                                    <button type="submit" name="desinscrire" class="danger js-confirm-delete" data-confirm="Se desinscrire de ce cours ?">Se desinscrire</button>
                                </form>
                            <?php elseif ($cours['statut'] === 'en_attente'): ?>
                                Demande en attente
                            <?php elseif ($cours['statut'] === 'desinscrit'): ?>
                                <form method="post" action="cours.php">
                                    <input type="hidden" name="id_cours" value="<?php echo htmlspecialchars($cours['id_cours']); ?>">
                                    <button type="submit" name="demander_inscription">Redemander l'inscription</button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="cours.php">
                                    <input type="hidden" name="id_cours" value="<?php echo htmlspecialchars($cours['id_cours']); ?>">
                                    <button type="submit" name="demander_inscription">Demander l'inscription</button>
                                </form>
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
