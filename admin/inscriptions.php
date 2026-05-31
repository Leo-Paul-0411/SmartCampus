<?php
include '../config/db.php';
include '../includes/auth.php';
include '../includes/fonctions.php';

verifier_role('admin');

$message = "";
$type_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_etudiant = (int) $_POST['id_etudiant'];
    $id_cours = (int) $_POST['id_cours'];
    $inscription_existante = false;

    if ($id_etudiant <= 0 || $id_cours <= 0) {
        $message = "Erreur : veuillez choisir un etudiant et un cours.";
        $type_message = "erreur";
    } else {
        $inscription_existante = recuperer_inscription($conn, $id_etudiant, $id_cours);
    }

    if ($message === "" && $inscription_existante && $inscription_existante['statut'] === 'inscrit') {
        $message = "Erreur : étudiant déjà inscrit.";
        $type_message = "erreur";
    } elseif ($message === "" && cours_est_complet($conn, $id_cours)) {
        $message = "Erreur : ce cours est complet.";
        $type_message = "erreur";
    } elseif ($message === "" && conflit_horaire($conn, $id_etudiant, $id_cours)) {
        $message = "Erreur : cet etudiant a deja un cours sur ce creneau.";
        $type_message = "erreur";
    }

    if ($message === "") {
        $inscription_reussie = false;

        if ($inscription_existante) {
            if (reactiver_inscription($conn, $inscription_existante['id_inscription'])) {
                $message = "Inscription reactivee avec succes.";
                $type_message = "succes";
                $inscription_reussie = true;
            } else {
                $message = "Erreur lors de la reactivation de l'inscription : " . mysqli_error($conn);
                $type_message = "erreur";
            }
        } else {
        $statut = "inscrit";
        $sql_insert = "INSERT INTO inscription (id_etudiant, id_cours, statut)
                       VALUES (?, ?, ?)";
        $requete_insert = mysqli_prepare($conn, $sql_insert);

        if ($requete_insert) {
            mysqli_stmt_bind_param($requete_insert, "iis", $id_etudiant, $id_cours, $statut);

            if (mysqli_stmt_execute($requete_insert)) {
                $message = "Inscription ajoutee avec succes.";
                $type_message = "succes";
                $inscription_reussie = true;
            } else {
                $message = "Erreur lors de l'inscription : " . mysqli_error($conn);
                $type_message = "erreur";
            }

            mysqli_stmt_close($requete_insert);
        } else {
            $message = "Erreur de preparation de la requete : " . mysqli_error($conn);
            $type_message = "erreur";
        }
        }

        if ($inscription_reussie) {
            // Recuperation du compte utilisateur de l'etudiant pour creer la notification.
            $sql_etudiant = "SELECT id_user
                             FROM etudiant
                             WHERE id_etudiant = ?";
            $requete_etudiant = mysqli_prepare($conn, $sql_etudiant);

            if ($requete_etudiant) {
                mysqli_stmt_bind_param($requete_etudiant, "i", $id_etudiant);
                mysqli_stmt_execute($requete_etudiant);
                $result_etudiant = mysqli_stmt_get_result($requete_etudiant);
                $etudiant = mysqli_fetch_assoc($result_etudiant);
                mysqli_stmt_close($requete_etudiant);

                if ($etudiant) {
                    creer_notification(
                        $conn,
                        $etudiant['id_user'],
                        "Vous avez ete inscrit a un cours.",
                        "inscription"
                    );
                }
            }
        }
    }
}

$result_etudiants = mysqli_query(
    $conn,
    "SELECT e.id_etudiant, e.numero_etudiant, u.nom, u.prenom
     FROM etudiant e
     INNER JOIN utilisateur u ON e.id_user = u.id_user
     ORDER BY u.nom, u.prenom"
);

$result_cours = mysqli_query(
    $conn,
    "SELECT id_cours, code_cours, titre, jour, heure_debut, heure_fin
     FROM cours
     ORDER BY code_cours"
);

$result_inscriptions = mysqli_query(
    $conn,
    "SELECT i.date_inscription, i.statut,
            u.nom, u.prenom, e.numero_etudiant,
            c.code_cours, c.titre
     FROM inscription i
     INNER JOIN etudiant e ON i.id_etudiant = e.id_etudiant
     INNER JOIN utilisateur u ON e.id_user = u.id_user
     INNER JOIN cours c ON i.id_cours = c.id_cours
     ORDER BY i.date_inscription DESC"
);

include '../includes/header.php';
?>

<h2>Gestion des inscriptions</h2>

<?php if ($message !== "") { ?>
    <p><?php echo htmlspecialchars($message); ?></p>
<?php } ?>

<section>
    <h3>Inscrire un etudiant a un cours</h3>

    <form method="post" action="inscriptions.php">
        <p>
            <label for="id_etudiant">Etudiant</label><br>
            <select id="id_etudiant" name="id_etudiant" required>
                <option value="">-- Choisir un etudiant --</option>
                <?php if ($result_etudiants) { ?>
                    <?php while ($etudiant = mysqli_fetch_assoc($result_etudiants)) { ?>
                        <option value="<?php echo htmlspecialchars($etudiant['id_etudiant']); ?>">
                            <?php echo htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom'] . ' - ' . $etudiant['numero_etudiant']); ?>
                        </option>
                    <?php } ?>
                <?php } ?>
            </select>
        </p>

        <p>
            <label for="id_cours">Cours</label><br>
            <select id="id_cours" name="id_cours" required>
                <option value="">-- Choisir un cours --</option>
                <?php if ($result_cours) { ?>
                    <?php while ($cours = mysqli_fetch_assoc($result_cours)) { ?>
                        <option value="<?php echo htmlspecialchars($cours['id_cours']); ?>">
                            <?php echo htmlspecialchars($cours['code_cours'] . ' - ' . $cours['titre'] . ' (' . $cours['jour'] . ', ' . $cours['heure_debut'] . ' - ' . $cours['heure_fin'] . ')'); ?>
                        </option>
                    <?php } ?>
                <?php } ?>
            </select>
        </p>

        <button type="submit">Inscrire</button>
    </form>
</section>

<section>
    <h3>Liste des inscriptions</h3>

    <table border="1">
        <thead>
            <tr>
                <th>etudiant</th>
                <th>cours</th>
                <th>date_inscription</th>
                <th>statut</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_inscriptions && mysqli_num_rows($result_inscriptions) > 0) { ?>
                <?php while ($inscription = mysqli_fetch_assoc($result_inscriptions)) { ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($inscription['prenom'] . ' ' . $inscription['nom'] . ' - ' . $inscription['numero_etudiant']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($inscription['code_cours'] . ' - ' . $inscription['titre']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($inscription['date_inscription']); ?></td>
                        <td><?php echo htmlspecialchars($inscription['statut']); ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="4">Aucune inscription trouvee.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</section>

<?php include '../includes/footer.php'; ?>
