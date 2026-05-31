<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('administrateur');
}

$message = "";
$erreur = "";
$cours_modif = null;
$recherche = trim($_GET['recherche'] ?? '');
$filtre_semestre = trim($_GET['semestre'] ?? '');
$filtre_jour = trim($_GET['jour'] ?? '');
$filtre_enseignant = intval($_GET['id_enseignant'] ?? 0);
$tri = $_GET['tri'] ?? 'code';

function conflit_horaire_modification_cours($conn, $id_cours, $jour, $heure_debut, $heure_fin)
{
    $sql = "SELECT COUNT(*) AS total
            FROM inscription i1
            INNER JOIN inscription i2 ON i1.id_etudiant = i2.id_etudiant
                AND i2.id_cours <> ?
                AND i2.statut = 'inscrit'
            INNER JOIN cours c2 ON i2.id_cours = c2.id_cours
            WHERE i1.id_cours = ?
            AND i1.statut = 'inscrit'
            AND c2.jour = ?
            AND ? < c2.heure_fin
            AND ? > c2.heure_debut";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return true;
    }

    mysqli_stmt_bind_param($stmt, "iisss", $id_cours, $id_cours, $jour, $heure_debut, $heure_fin);
    mysqli_stmt_execute($stmt);
    $ligne = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    return $ligne['total'] > 0;
}

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

if (isset($_POST['ajouter_cours']) || isset($_POST['modifier_cours'])) {
    $id_cours = intval($_POST['id_cours'] ?? 0);
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
    $est_modification = isset($_POST['modifier_cours']);

    if ($code_cours === '' || $titre === '' || $capacite_max <= 0 || $jour === '' || $heure_debut === '' || $heure_fin === '' || $salle === '' || $semestre === '' || $id_enseignant <= 0) {
        $erreur = "Veuillez remplir tous les champs obligatoires.";
    } elseif ($heure_debut >= $heure_fin) {
        $erreur = "L'heure de debut doit etre inferieure a l'heure de fin.";
    } elseif ($est_modification && conflit_horaire_modification_cours($conn, $id_cours, $jour, $heure_debut, $heure_fin)) {
        $erreur = "Modification refusee : ce nouvel horaire cree un conflit pour au moins un etudiant inscrit.";
    } else {
        $stmt_code = mysqli_prepare($conn, "SELECT id_cours FROM cours WHERE code_cours = ? AND id_cours <> ?");
        mysqli_stmt_bind_param($stmt_code, "si", $code_cours, $id_cours);
        mysqli_stmt_execute($stmt_code);
        $code_existe = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_code));
        mysqli_stmt_close($stmt_code);

        if ($code_existe) {
            $erreur = "Erreur : ce code cours existe deja.";
        } elseif ($est_modification) {
            $sql_update = "UPDATE cours
                           SET code_cours = ?, titre = ?, description = ?, capacite_max = ?, jour = ?,
                               heure_debut = ?, heure_fin = ?, salle = ?, semestre = ?, id_enseignant = ?
                           WHERE id_cours = ?";
            $stmt = mysqli_prepare($conn, $sql_update);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sssisssssii", $code_cours, $titre, $description, $capacite_max, $jour, $heure_debut, $heure_fin, $salle, $semestre, $id_enseignant, $id_cours);

                if (mysqli_stmt_execute($stmt)) {
                    $message = "Cours modifie avec succes.";
                } else {
                    $erreur = "Erreur lors de la modification du cours : " . mysqli_error($conn);
                }

                mysqli_stmt_close($stmt);
            }
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
}

if (isset($_GET['modifier'])) {
    $id_cours = intval($_GET['modifier']);
    $stmt_modif = mysqli_prepare($conn, "SELECT * FROM cours WHERE id_cours = ?");
    mysqli_stmt_bind_param($stmt_modif, "i", $id_cours);
    mysqli_stmt_execute($stmt_modif);
    $cours_modif = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_modif));
    mysqli_stmt_close($stmt_modif);
}

$enseignants_liste = [];
$enseignants_result = mysqli_query(
    $conn,
    "SELECT enseignant.id_enseignant, utilisateur.nom, utilisateur.prenom
     FROM enseignant
     INNER JOIN utilisateur ON enseignant.id_user = utilisateur.id_user
     WHERE utilisateur.actif = 1
     ORDER BY utilisateur.nom"
);
if ($enseignants_result) {
    while ($enseignant = mysqli_fetch_assoc($enseignants_result)) {
        $enseignants_liste[] = $enseignant;
    }
}

$semestres = mysqli_query($conn, "SELECT DISTINCT semestre FROM cours ORDER BY semestre");
$jours = mysqli_query($conn, "SELECT DISTINCT jour FROM cours ORDER BY jour");

$tri_autorise = [
    'code' => 'cours.code_cours',
    'titre' => 'cours.titre',
    'jour' => 'cours.jour',
    'heure_debut' => 'cours.heure_debut',
    'semestre' => 'cours.semestre'
];
$order_by = $tri_autorise[$tri] ?? $tri_autorise['code'];

$where = [];
$types = "";
$params = [];

if ($recherche !== '') {
    $where[] = "(cours.code_cours LIKE ? OR cours.titre LIKE ?)";
    $mot_cle = "%" . $recherche . "%";
    $types .= "ss";
    $params[] = $mot_cle;
    $params[] = $mot_cle;
}
if ($filtre_semestre !== '') {
    $where[] = "cours.semestre = ?";
    $types .= "s";
    $params[] = $filtre_semestre;
}
if ($filtre_jour !== '') {
    $where[] = "cours.jour = ?";
    $types .= "s";
    $params[] = $filtre_jour;
}
if ($filtre_enseignant > 0) {
    $where[] = "cours.id_enseignant = ?";
    $types .= "i";
    $params[] = $filtre_enseignant;
}

$sql_liste = "SELECT cours.*, enseignant.numero_enseignant
              FROM cours
              LEFT JOIN enseignant ON cours.id_enseignant = enseignant.id_enseignant";
if (!empty($where)) {
    $sql_liste .= " WHERE " . implode(" AND ", $where);
}
$sql_liste .= " ORDER BY " . $order_by;

if (!empty($params)) {
    $stmt_liste = mysqli_prepare($conn, $sql_liste);
    mysqli_stmt_bind_param($stmt_liste, $types, ...$params);
    mysqli_stmt_execute($stmt_liste);
    $resultat = mysqli_stmt_get_result($stmt_liste);
} else {
    $resultat = mysqli_query($conn, $sql_liste);
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
            <label>Code du cours</label><input type="text" name="code_cours" required>
            <label>Titre</label><input type="text" name="titre" required>
            <label>Description</label><textarea name="description"></textarea>
            <label>Capacite maximale</label><input type="number" name="capacite_max" min="1" required>
            <label>Jour</label><input type="text" name="jour" placeholder="Ex : Lundi" required>
            <label>Heure debut</label><input type="time" name="heure_debut" required>
            <label>Heure fin</label><input type="time" name="heure_fin" required>
            <label>Salle</label><input type="text" name="salle" required>
            <label>Semestre</label><input type="text" name="semestre" required>
            <label>Enseignant</label>
            <select name="id_enseignant" required>
                <option value="">Choisir un enseignant</option>
                <?php foreach ($enseignants_liste as $ens): ?>
                    <option value="<?php echo $ens['id_enseignant']; ?>"><?php echo htmlspecialchars($ens['prenom'] . ' ' . $ens['nom']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="ajouter_cours">Ajouter</button>
        </form>
    </section>

    <?php if ($cours_modif): ?>
        <section class="form-card">
            <h2>Modifier un cours</h2>
            <form method="post" action="cours.php">
                <input type="hidden" name="id_cours" value="<?php echo htmlspecialchars($cours_modif['id_cours']); ?>">
                <label>Code du cours</label><input type="text" name="code_cours" value="<?php echo htmlspecialchars($cours_modif['code_cours']); ?>" required>
                <label>Titre</label><input type="text" name="titre" value="<?php echo htmlspecialchars($cours_modif['titre']); ?>" required>
                <label>Description</label><textarea name="description"><?php echo htmlspecialchars($cours_modif['description']); ?></textarea>
                <label>Capacite maximale</label><input type="number" name="capacite_max" min="1" value="<?php echo htmlspecialchars($cours_modif['capacite_max']); ?>" required>
                <label>Jour</label><input type="text" name="jour" value="<?php echo htmlspecialchars($cours_modif['jour']); ?>" required>
                <label>Heure debut</label><input type="time" name="heure_debut" value="<?php echo htmlspecialchars($cours_modif['heure_debut']); ?>" required>
                <label>Heure fin</label><input type="time" name="heure_fin" value="<?php echo htmlspecialchars($cours_modif['heure_fin']); ?>" required>
                <label>Salle</label><input type="text" name="salle" value="<?php echo htmlspecialchars($cours_modif['salle']); ?>" required>
                <label>Semestre</label><input type="text" name="semestre" value="<?php echo htmlspecialchars($cours_modif['semestre']); ?>" required>
                <label>Enseignant</label>
                <select name="id_enseignant" required>
                    <?php foreach ($enseignants_liste as $ens): ?>
                        <option value="<?php echo $ens['id_enseignant']; ?>" <?php if ($cours_modif['id_enseignant'] == $ens['id_enseignant']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($ens['prenom'] . ' ' . $ens['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="modifier_cours">Modifier</button>
                <a class="button secondary" href="cours.php">Annuler</a>
            </form>
        </section>
    <?php endif; ?>

    <section class="toolbar">
        <form method="get" action="cours.php">
            <input type="text" name="recherche" placeholder="Rechercher par code ou titre" value="<?php echo htmlspecialchars($recherche); ?>">
            <select name="semestre">
                <option value="">Tous les semestres</option>
                <?php if ($semestres): ?>
                    <?php while ($semestre = mysqli_fetch_assoc($semestres)): ?>
                        <option value="<?php echo htmlspecialchars($semestre['semestre']); ?>" <?php if ($filtre_semestre === $semestre['semestre']) echo 'selected'; ?>><?php echo htmlspecialchars($semestre['semestre']); ?></option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
            <select name="id_enseignant">
                <option value="0">Tous les enseignants</option>
                <?php foreach ($enseignants_liste as $ens): ?>
                    <option value="<?php echo $ens['id_enseignant']; ?>" <?php if ($filtre_enseignant === (int) $ens['id_enseignant']) echo 'selected'; ?>><?php echo htmlspecialchars($ens['prenom'] . ' ' . $ens['nom']); ?></option>
                <?php endforeach; ?>
            </select>
            <select name="jour">
                <option value="">Tous les jours</option>
                <?php if ($jours): ?>
                    <?php while ($jour_option = mysqli_fetch_assoc($jours)): ?>
                        <option value="<?php echo htmlspecialchars($jour_option['jour']); ?>" <?php if ($filtre_jour === $jour_option['jour']) echo 'selected'; ?>><?php echo htmlspecialchars($jour_option['jour']); ?></option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
            <select name="tri">
                <option value="code" <?php if ($tri === 'code') echo 'selected'; ?>>Tri code</option>
                <option value="titre" <?php if ($tri === 'titre') echo 'selected'; ?>>Tri titre</option>
                <option value="jour" <?php if ($tri === 'jour') echo 'selected'; ?>>Tri jour</option>
                <option value="heure_debut" <?php if ($tri === 'heure_debut') echo 'selected'; ?>>Tri heure</option>
                <option value="semestre" <?php if ($tri === 'semestre') echo 'selected'; ?>>Tri semestre</option>
            </select>
            <button type="submit">Filtrer</button>
            <a class="button secondary" href="cours.php">Reinitialiser</a>
        </form>
    </section>

    <table>
        <thead><tr><th>Code</th><th>Titre</th><th>Jour</th><th>Debut</th><th>Fin</th><th>Salle</th><th>Semestre</th><th>Capacite</th><th>Enseignant</th><th>Actions</th></tr></thead>
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
                            <a class="button secondary" href="cours.php?modifier=<?php echo $cours['id_cours']; ?>">Modifier</a>
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
