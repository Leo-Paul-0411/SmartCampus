<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('administrateur');
}

$message = "";
$erreur = "";
$recherche = trim($_GET['recherche'] ?? '');

if (isset($_GET['supprimer'])) {
    $id_cours = intval($_GET['supprimer']);

    $stmt_verif = mysqli_prepare(
        $conn,
        "SELECT
            (SELECT COUNT(*) FROM inscription WHERE id_cours = ?) AS nb_inscriptions,
            (SELECT COUNT(*) FROM note WHERE id_cours = ?) AS nb_notes"
    );

    if ($stmt_verif) {
        mysqli_stmt_bind_param($stmt_verif, "ii", $id_cours, $id_cours);
        mysqli_stmt_execute($stmt_verif);
        $verif = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_verif));
        mysqli_stmt_close($stmt_verif);

        if ($verif['nb_inscriptions'] > 0 || $verif['nb_notes'] > 0) {
            $erreur = "Impossible de supprimer ce cours car il possede des inscriptions ou des notes.";
        } else {
            $stmt_delete = mysqli_prepare($conn, "DELETE FROM cours WHERE id_cours = ?");

            if ($stmt_delete) {
                mysqli_stmt_bind_param($stmt_delete, "i", $id_cours);

                if (mysqli_stmt_execute($stmt_delete)) {
                    $message = "Cours supprime avec succes.";
                } else {
                    $erreur = "Erreur lors de la suppression du cours.";
                }

                mysqli_stmt_close($stmt_delete);
            }
        }
    }
}

if (isset($_POST['ajouter_cours'])) {
    $code_cours = trim($_POST['code_cours'] ?? '');
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $capacite_max = intval($_POST['capacite_max'] ?? 0);
    $jour = trim($_POST['jour'] ?? '');
    $heure_debut = trim($_POST['heure_debut'] ?? '');
    $heure_fin = trim($_POST['heure_fin'] ?? '');
    $salle = trim($_POST['salle'] ?? '');
    $semestre = trim($_POST['semestre'] ?? '');
    $id_enseignant = intval($_POST['id_enseignant'] ?? 0);

    if ($code_cours === '' || $titre === '' || $capacite_max <= 0 || $jour === '' || $heure_debut === '' || $heure_fin === '' || $salle === '' || $semestre === '' || $id_enseignant <= 0) {
        $erreur = "Veuillez remplir tous les champs obligatoires.";
    } elseif ($heure_debut >= $heure_fin) {
        $erreur = "L'heure de debut doit etre inferieure a l'heure de fin.";
    } else {
        $sql_insert = "INSERT INTO cours
                       (code_cours, titre, description, capacite_max, jour, heure_debut, heure_fin, salle, semestre, id_enseignant)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql_insert);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssisssssi", $code_cours, $titre, $description, $capacite_max, $jour, $heure_debut, $heure_fin, $salle, $semestre, $id_enseignant);

            if (mysqli_stmt_execute($stmt)) {
                $message = "Cours ajoute avec succes.";
            } else {
                $erreur = "Erreur lors de l'ajout du cours : " . mysqli_error($conn);
            }

            mysqli_stmt_close($stmt);
        }
    }
}

$enseignants = mysqli_query(
    $conn,
    "SELECT enseignant.id_enseignant, utilisateur.nom, utilisateur.prenom
     FROM enseignant
     INNER JOIN utilisateur ON enseignant.id_user = utilisateur.id_user
     ORDER BY utilisateur.nom"
);

if ($recherche !== '') {
    $stmt_liste = mysqli_prepare(
        $conn,
        "SELECT cours.*, enseignant.numero_enseignant
         FROM cours
         LEFT JOIN enseignant ON cours.id_enseignant = enseignant.id_enseignant
         WHERE cours.code_cours LIKE ? OR cours.titre LIKE ?
         ORDER BY cours.code_cours"
    );
    if ($stmt_liste) {
        $mot_cle = "%" . $recherche . "%";
        mysqli_stmt_bind_param($stmt_liste, "ss", $mot_cle, $mot_cle);
        mysqli_stmt_execute($stmt_liste);
        $resultat = mysqli_stmt_get_result($stmt_liste);
    } else {
        $resultat = false;
    }
} else {
    $resultat = mysqli_query(
        $conn,
        "SELECT cours.*, enseignant.numero_enseignant
         FROM cours
         LEFT JOIN enseignant ON cours.id_enseignant = enseignant.id_enseignant
         ORDER BY cours.code_cours"
    );
}

include __DIR__ . '/../includes/header.php';
?>

<section class="container">
    <h1>Gestion des cours</h1>

    <?php if ($message): ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <?php if ($erreur): ?><p class="error"><?php echo htmlspecialchars($erreur); ?></p><?php endif; ?>

    <section class="form-card">
        <h2>Ajouter un cours</h2>
        <form method="post" action="cours.php">
            <label>Code du cours</label>
            <input type="text" name="code_cours" required>
            <label>Titre</label>
            <input type="text" name="titre" required>
            <label>Description</label>
            <textarea name="description"></textarea>
            <label>Capacite maximale</label>
            <input type="number" name="capacite_max" min="1" required>
            <label>Jour</label>
            <input type="text" name="jour" placeholder="Ex : Lundi" required>
            <label>Heure debut</label>
            <input type="time" name="heure_debut" required>
            <label>Heure fin</label>
            <input type="time" name="heure_fin" required>
            <label>Salle</label>
            <input type="text" name="salle" required>
            <label>Semestre</label>
            <input type="text" name="semestre" required>
            <label>Enseignant</label>
            <select name="id_enseignant" required>
                <option value="">Choisir un enseignant</option>
                <?php if ($enseignants): ?>
                    <?php while ($ens = mysqli_fetch_assoc($enseignants)): ?>
                        <option value="<?php echo $ens['id_enseignant']; ?>">
                            <?php echo htmlspecialchars($ens['prenom'] . ' ' . $ens['nom']); ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
            <button type="submit" name="ajouter_cours">Ajouter</button>
        </form>
    </section>

    <section class="toolbar">
        <form method="get" action="cours.php">
            <input type="text" name="recherche" placeholder="Rechercher par code ou titre" value="<?php echo htmlspecialchars($recherche); ?>">
            <button type="submit">Rechercher</button>
            <a class="button secondary" href="cours.php">Reinitialiser</a>
        </form>
    </section>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Titre</th>
                <th>Jour</th>
                <th>Debut</th>
                <th>Fin</th>
                <th>Salle</th>
                <th>Semestre</th>
                <th>Capacite</th>
                <th>Enseignant</th>
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
                        <td><?php echo htmlspecialchars($cours['capacite_max']); ?></td>
                        <td><?php echo htmlspecialchars($cours['numero_enseignant'] ?? 'Non defini'); ?></td>
                        <td>
                            <a class="button danger js-confirm-delete" href="cours.php?supprimer=<?php echo $cours['id_cours']; ?>" data-confirm="Supprimer ce cours ?">Supprimer</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="10">Aucun cours trouve.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
