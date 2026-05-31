<?php
include '../config/db.php';

$message = "";
$type_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_etudiant = (int) $_POST['id_etudiant'];
    $id_cours = (int) $_POST['id_cours'];

    if ($id_etudiant <= 0 || $id_cours <= 0) {
        $message = "Erreur : veuillez choisir un etudiant et un cours.";
        $type_message = "erreur";
    } else {
        $sql_deja_inscrit = "SELECT COUNT(*) AS total
                             FROM inscription
                             WHERE id_etudiant = ? AND id_cours = ? AND statut = 'inscrit'";
        $requete_deja_inscrit = mysqli_prepare($conn, $sql_deja_inscrit);

        if ($requete_deja_inscrit) {
            mysqli_stmt_bind_param($requete_deja_inscrit, "ii", $id_etudiant, $id_cours);
            mysqli_stmt_execute($requete_deja_inscrit);
            $result_deja_inscrit = mysqli_stmt_get_result($requete_deja_inscrit);
            $deja_inscrit = mysqli_fetch_assoc($result_deja_inscrit);
            mysqli_stmt_close($requete_deja_inscrit);

            if ($deja_inscrit['total'] > 0) {
                $message = "Erreur : cet etudiant est deja inscrit a ce cours.";
                $type_message = "erreur";
            }
        } else {
            $message = "Erreur de preparation de la requete : " . mysqli_error($conn);
            $type_message = "erreur";
        }
    }

    if ($message === "") {
        $sql_cours = "SELECT capacite_max, jour, heure_debut, heure_fin
                      FROM cours
                      WHERE id_cours = ?";
        $requete_cours = mysqli_prepare($conn, $sql_cours);

        if ($requete_cours) {
            mysqli_stmt_bind_param($requete_cours, "i", $id_cours);
            mysqli_stmt_execute($requete_cours);
            $result_cours_choisi = mysqli_stmt_get_result($requete_cours);
            $cours_choisi = mysqli_fetch_assoc($result_cours_choisi);
            mysqli_stmt_close($requete_cours);

            if (!$cours_choisi) {
                $message = "Erreur : le cours choisi n'existe pas.";
                $type_message = "erreur";
            }
        } else {
            $message = "Erreur de preparation de la requete : " . mysqli_error($conn);
            $type_message = "erreur";
        }
    }

    if ($message === "") {
        $sql_capacite = "SELECT COUNT(*) AS total
                         FROM inscription
                         WHERE id_cours = ? AND statut = 'inscrit'";
        $requete_capacite = mysqli_prepare($conn, $sql_capacite);

        if ($requete_capacite) {
            mysqli_stmt_bind_param($requete_capacite, "i", $id_cours);
            mysqli_stmt_execute($requete_capacite);
            $result_capacite = mysqli_stmt_get_result($requete_capacite);
            $capacite = mysqli_fetch_assoc($result_capacite);
            mysqli_stmt_close($requete_capacite);

            if ($capacite['total'] >= $cours_choisi['capacite_max']) {
                $message = "Erreur : ce cours est complet.";
                $type_message = "erreur";
            }
        } else {
            $message = "Erreur de preparation de la requete : " . mysqli_error($conn);
            $type_message = "erreur";
        }
    }

    if ($message === "") {
        $sql_conflit = "SELECT c.code_cours, c.titre, c.heure_debut, c.heure_fin
                        FROM inscription i
                        INNER JOIN cours c ON i.id_cours = c.id_cours
                        WHERE i.id_etudiant = ?
                        AND i.statut = 'inscrit'
                        AND c.jour = ?
                        AND ? < c.heure_fin
                        AND ? > c.heure_debut
                        LIMIT 1";
        $requete_conflit = mysqli_prepare($conn, $sql_conflit);

        if ($requete_conflit) {
            mysqli_stmt_bind_param(
                $requete_conflit,
                "isss",
                $id_etudiant,
                $cours_choisi['jour'],
                $cours_choisi['heure_debut'],
                $cours_choisi['heure_fin']
            );
            mysqli_stmt_execute($requete_conflit);
            $result_conflit = mysqli_stmt_get_result($requete_conflit);
            $conflit = mysqli_fetch_assoc($result_conflit);
            mysqli_stmt_close($requete_conflit);

            if ($conflit) {
                $message = "Erreur : conflit horaire avec le cours "
                    . $conflit['code_cours'] . " - " . $conflit['titre']
                    . " (" . $conflit['heure_debut'] . " - " . $conflit['heure_fin'] . ").";
                $type_message = "erreur";
            }
        } else {
            $message = "Erreur de preparation de la requete : " . mysqli_error($conn);
            $type_message = "erreur";
        }
    }

    if ($message === "") {
        $statut = "inscrit";
        $sql_insert = "INSERT INTO inscription (id_etudiant, id_cours, statut)
                       VALUES (?, ?, ?)";
        $requete_insert = mysqli_prepare($conn, $sql_insert);

        if ($requete_insert) {
            mysqli_stmt_bind_param($requete_insert, "iis", $id_etudiant, $id_cours, $statut);

            if (mysqli_stmt_execute($requete_insert)) {
                $message = "Inscription ajoutee avec succes.";
                $type_message = "succes";
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
