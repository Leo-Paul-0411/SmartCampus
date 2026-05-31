<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('enseignant');
}

$id_enseignant = $_SESSION['id_enseignant'] ?? 0;
$message = "";
$erreur = "";

function cours_appartient_enseignant($conn, $id_cours, $id_enseignant)
{
    $sql = "SELECT COUNT(*) AS total
            FROM cours
            WHERE id_cours = ?
            AND id_enseignant = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "ii", $id_cours, $id_enseignant);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);
    $ligne = mysqli_fetch_assoc($resultat);
    mysqli_stmt_close($stmt);

    return $ligne['total'] > 0;
}

function etudiant_est_inscrit_au_cours($conn, $id_etudiant, $id_cours, $id_enseignant)
{
    $sql = "SELECT COUNT(*) AS total
            FROM inscription
            INNER JOIN cours ON inscription.id_cours = cours.id_cours
            WHERE inscription.id_etudiant = ?
            AND inscription.id_cours = ?
            AND inscription.statut = 'inscrit'
            AND cours.id_enseignant = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "iii", $id_etudiant, $id_cours, $id_enseignant);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);
    $ligne = mysqli_fetch_assoc($resultat);
    mysqli_stmt_close($stmt);

    return $ligne['total'] > 0;
}

function note_est_valide($valeur)
{
    return $valeur !== '' && is_numeric($valeur) && $valeur >= 0 && $valeur <= 20;
}

$id_cours = isset($_GET['id_cours']) ? intval($_GET['id_cours']) : (isset($_POST['id_cours']) ? intval($_POST['id_cours']) : 0);

if ($id_enseignant <= 0) {
    $erreur = "Erreur : enseignant non reconnu dans la session.";
}

if ($id_cours > 0 && !cours_appartient_enseignant($conn, $id_cours, $id_enseignant)) {
    $erreur = "Erreur : ce cours ne vous appartient pas.";
    $id_cours = 0;
}

if ($erreur === "" && isset($_POST['valider_notes'])) {
    if ($id_cours <= 0) {
        $erreur = "Erreur : cours invalide.";
    } else {
        $sql_valider = "UPDATE note
                        INNER JOIN cours ON note.id_cours = cours.id_cours
                        SET note.validee = 1, note.date_validation = NOW()
                        WHERE note.id_cours = ?
                        AND cours.id_enseignant = ?";
        $stmt_valider = mysqli_prepare($conn, $sql_valider);

        if ($stmt_valider) {
            mysqli_stmt_bind_param($stmt_valider, "ii", $id_cours, $id_enseignant);

            if (mysqli_stmt_execute($stmt_valider)) {
                $message = "Notes validees avec succes.";
            } else {
                $erreur = "Erreur lors de la validation des notes.";
            }

            mysqli_stmt_close($stmt_valider);
        } else {
            $erreur = "Erreur de preparation de la requete.";
        }
    }
}

if ($erreur === "" && isset($_POST['enregistrer_notes'])) {
    if ($id_cours <= 0) {
        $erreur = "Erreur : cours invalide.";
    } elseif (!isset($_POST['notes']) || !is_array($_POST['notes'])) {
        $erreur = "Erreur : aucune note a enregistrer.";
    } else {
        foreach ($_POST['notes'] as $id_etudiant => $notes) {
            $id_etudiant = intval($id_etudiant);

            if (!etudiant_est_inscrit_au_cours($conn, $id_etudiant, $id_cours, $id_enseignant)) {
                $erreur = "Erreur : un etudiant ne correspond pas a ce cours.";
                break;
            }

            $note_controle_saisie = trim($notes['note_controle'] ?? '');
            $note_exam_saisie = trim($notes['note_exam'] ?? '');
            $note_projet_saisie = trim($notes['note_projet'] ?? '');

            if (
                !note_est_valide($note_controle_saisie)
                || !note_est_valide($note_exam_saisie)
                || !note_est_valide($note_projet_saisie)
            ) {
                $erreur = "Erreur : chaque note doit etre renseignee et comprise entre 0 et 20.";
                break;
            }

            $note_controle = (float) $note_controle_saisie;
            $note_exam = (float) $note_exam_saisie;
            $note_projet = (float) $note_projet_saisie;
            $moyenne = ($note_controle * 0.3) + ($note_exam * 0.5) + ($note_projet * 0.2);

            $stmt_existe = mysqli_prepare($conn, "SELECT id_note, validee FROM note WHERE id_etudiant = ? AND id_cours = ?");
            mysqli_stmt_bind_param($stmt_existe, "ii", $id_etudiant, $id_cours);
            mysqli_stmt_execute($stmt_existe);
            $note_existante = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_existe));
            mysqli_stmt_close($stmt_existe);

            if ($note_existante && intval($note_existante['validee']) === 1) {
                continue;
            }

            if ($note_existante) {
                $stmt_update = mysqli_prepare($conn, "UPDATE note SET note_controle = ?, note_exam = ?, note_projet = ?, moyenne = ?, date_saisie = NOW() WHERE id_note = ?");
                mysqli_stmt_bind_param($stmt_update, "ddddi", $note_controle, $note_exam, $note_projet, $moyenne, $note_existante['id_note']);
                mysqli_stmt_execute($stmt_update);
                mysqli_stmt_close($stmt_update);
            } else {
                $stmt_insert = mysqli_prepare($conn, "INSERT INTO note (note_controle, note_exam, note_projet, moyenne, validee, date_saisie, id_etudiant, id_cours) VALUES (?, ?, ?, ?, 0, NOW(), ?, ?)");
                mysqli_stmt_bind_param($stmt_insert, "ddddii", $note_controle, $note_exam, $note_projet, $moyenne, $id_etudiant, $id_cours);
                mysqli_stmt_execute($stmt_insert);
                mysqli_stmt_close($stmt_insert);
            }
        }

        if ($erreur === "") {
            $message = "Notes enregistrees avec succes.";
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<section class="container">
    <h1>Saisie des notes</h1>

    <?php if ($message): ?>
        <p class="success"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if ($erreur): ?>
        <p class="error"><?php echo htmlspecialchars($erreur); ?></p>
    <?php endif; ?>

    <?php
    $stmt_cours = mysqli_prepare($conn, "SELECT id_cours, code_cours, titre FROM cours WHERE id_enseignant = ? ORDER BY code_cours");
    mysqli_stmt_bind_param($stmt_cours, "i", $id_enseignant);
    mysqli_stmt_execute($stmt_cours);
    $cours_result = mysqli_stmt_get_result($stmt_cours);
    ?>

    <form method="get" action="notes.php" class="form-card">
        <label for="id_cours">Choisir un cours</label>
        <select name="id_cours" id="id_cours" required>
            <option value="">Selectionner un cours</option>
            <?php while ($cours = mysqli_fetch_assoc($cours_result)): ?>
                <option value="<?php echo $cours['id_cours']; ?>" <?php if ($id_cours == $cours['id_cours']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($cours['code_cours'] . ' - ' . $cours['titre']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Afficher</button>
    </form>

    <?php if ($id_cours > 0): ?>
        <?php
        $sql = "SELECT etudiant.id_etudiant, utilisateur.nom, utilisateur.prenom,
                       note.note_controle, note.note_exam, note.note_projet, note.moyenne, note.validee
                FROM inscription
                INNER JOIN cours ON inscription.id_cours = cours.id_cours
                INNER JOIN etudiant ON inscription.id_etudiant = etudiant.id_etudiant
                INNER JOIN utilisateur ON etudiant.id_user = utilisateur.id_user
                LEFT JOIN note ON note.id_etudiant = etudiant.id_etudiant AND note.id_cours = inscription.id_cours
                WHERE inscription.id_cours = ?
                AND inscription.statut = 'inscrit'
                AND cours.id_enseignant = ?
                ORDER BY utilisateur.nom, utilisateur.prenom";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $id_cours, $id_enseignant);
        mysqli_stmt_execute($stmt);
        $etudiants = mysqli_stmt_get_result($stmt);
        ?>

        <form method="post" action="notes.php">
            <input type="hidden" name="id_cours" value="<?php echo $id_cours; ?>">

            <table>
                <thead>
                    <tr>
                        <th>Etudiant</th>
                        <th>Note controle</th>
                        <th>Note exam</th>
                        <th>Note projet</th>
                        <th>Moyenne</th>
                        <th>Validation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($etudiants && mysqli_num_rows($etudiants) > 0): ?>
                        <?php while ($etu = mysqli_fetch_assoc($etudiants)): ?>
                            <?php $disabled = $etu['validee'] ? 'disabled' : ''; ?>
                            <tr>
                                <td><?php echo htmlspecialchars($etu['prenom'] . ' ' . $etu['nom']); ?></td>
                                <td><input type="number" step="0.01" min="0" max="20" name="notes[<?php echo $etu['id_etudiant']; ?>][note_controle]" value="<?php echo htmlspecialchars($etu['note_controle'] ?? ''); ?>" <?php echo $disabled; ?>></td>
                                <td><input type="number" step="0.01" min="0" max="20" name="notes[<?php echo $etu['id_etudiant']; ?>][note_exam]" value="<?php echo htmlspecialchars($etu['note_exam'] ?? ''); ?>" <?php echo $disabled; ?>></td>
                                <td><input type="number" step="0.01" min="0" max="20" name="notes[<?php echo $etu['id_etudiant']; ?>][note_projet]" value="<?php echo htmlspecialchars($etu['note_projet'] ?? ''); ?>" <?php echo $disabled; ?>></td>
                                <td><?php echo isset($etu['moyenne']) ? number_format($etu['moyenne'], 2) : '-'; ?></td>
                                <td><?php echo $etu['validee'] ? 'Validee' : 'Non validee'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">Aucun etudiant inscrit a ce cours.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <button type="submit" name="enregistrer_notes">Enregistrer les notes</button>
            <button type="submit" name="valider_notes" onclick="return confirm('Valider definitivement les notes ?');">Valider les notes</button>
        </form>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
