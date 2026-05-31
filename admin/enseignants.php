<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('administrateur');
}

$message = "";
$erreur = "";
$enseignant_modif = null;

if (isset($_GET['supprimer'])) {
    $id_enseignant = intval($_GET['supprimer']);

    $stmt = mysqli_prepare($conn, "SELECT id_user FROM enseignant WHERE id_enseignant = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_enseignant);
    mysqli_stmt_execute($stmt);
    $enseignant = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($enseignant) {
        $stmt_cours = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM cours WHERE id_enseignant = ?");
        mysqli_stmt_bind_param($stmt_cours, "i", $id_enseignant);
        mysqli_stmt_execute($stmt_cours);
        $nb_cours = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cours))['total'] ?? 0;
        mysqli_stmt_close($stmt_cours);

        if ($nb_cours > 0) {
            $stmt_desactiver = mysqli_prepare($conn, "UPDATE utilisateur SET actif = 0 WHERE id_user = ?");
            mysqli_stmt_bind_param($stmt_desactiver, "i", $enseignant['id_user']);
            mysqli_stmt_execute($stmt_desactiver);
            mysqli_stmt_close($stmt_desactiver);
            $message = "Enseignant desactive car il possede des cours associes.";
        } else {
            mysqli_begin_transaction($conn);
            $stmt_delete_enseignant = mysqli_prepare($conn, "DELETE FROM enseignant WHERE id_enseignant = ?");
            mysqli_stmt_bind_param($stmt_delete_enseignant, "i", $id_enseignant);
            $ok_enseignant = mysqli_stmt_execute($stmt_delete_enseignant);
            mysqli_stmt_close($stmt_delete_enseignant);

            $stmt_delete_user = mysqli_prepare($conn, "DELETE FROM utilisateur WHERE id_user = ?");
            mysqli_stmt_bind_param($stmt_delete_user, "i", $enseignant['id_user']);
            $ok_user = mysqli_stmt_execute($stmt_delete_user);
            mysqli_stmt_close($stmt_delete_user);

            if ($ok_enseignant && $ok_user) {
                mysqli_commit($conn);
                $message = "Enseignant supprime avec succes.";
            } else {
                mysqli_rollback($conn);
                $erreur = "Erreur lors de la suppression de l'enseignant.";
            }
        }
    }
}

if (isset($_POST['ajouter_enseignant'])) {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $numero_enseignant = trim($_POST['numero_enseignant'] ?? '');
    $specialite = trim($_POST['specialite'] ?? '');
    $bureau = trim($_POST['bureau'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');

    if ($nom === '' || $prenom === '' || $email === '' || $mot_de_passe === '' || $numero_enseignant === '' || $specialite === '') {
        $erreur = "Veuillez remplir tous les champs obligatoires.";
    } else {
        $stmt_email = mysqli_prepare($conn, "SELECT id_user FROM utilisateur WHERE email = ?");
        mysqli_stmt_bind_param($stmt_email, "s", $email);
        mysqli_stmt_execute($stmt_email);
        $email_existe = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_email));
        mysqli_stmt_close($stmt_email);

        $stmt_numero = mysqli_prepare($conn, "SELECT id_enseignant FROM enseignant WHERE numero_enseignant = ?");
        mysqli_stmt_bind_param($stmt_numero, "s", $numero_enseignant);
        mysqli_stmt_execute($stmt_numero);
        $numero_existe = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_numero));
        mysqli_stmt_close($stmt_numero);

        if ($email_existe) {
            $erreur = "Erreur : cet email existe deja.";
        } elseif ($numero_existe) {
            $erreur = "Erreur : ce numero enseignant existe deja.";
        } else {
            mysqli_begin_transaction($conn);
            $role = "enseignant";
            $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $stmt_user = mysqli_prepare($conn, "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_user, "sssss", $nom, $prenom, $email, $mot_de_passe_hash, $role);
            $ok_user = mysqli_stmt_execute($stmt_user);
            $id_user = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt_user);

            $stmt_enseignant = mysqli_prepare($conn, "INSERT INTO enseignant (numero_enseignant, specialite, bureau, telephone, id_user) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_enseignant, "ssssi", $numero_enseignant, $specialite, $bureau, $telephone, $id_user);
            $ok_enseignant = mysqli_stmt_execute($stmt_enseignant);
            mysqli_stmt_close($stmt_enseignant);

            if ($ok_user && $ok_enseignant) {
                mysqli_commit($conn);
                $message = "Enseignant et compte utilisateur ajoutes avec succes.";
            } else {
                mysqli_rollback($conn);
                $erreur = "Erreur lors de l'ajout de l'enseignant.";
            }
        }
    }
}

if (isset($_POST['modifier_enseignant'])) {
    $id_enseignant = intval($_POST['id_enseignant'] ?? 0);
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $specialite = trim($_POST['specialite'] ?? '');
    $bureau = trim($_POST['bureau'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');

    if ($id_enseignant <= 0 || $nom === '' || $prenom === '' || $email === '' || $specialite === '') {
        $erreur = "Veuillez remplir tous les champs obligatoires.";
    } else {
        $stmt_info = mysqli_prepare($conn, "SELECT id_user FROM enseignant WHERE id_enseignant = ?");
        mysqli_stmt_bind_param($stmt_info, "i", $id_enseignant);
        mysqli_stmt_execute($stmt_info);
        $info = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_info));
        mysqli_stmt_close($stmt_info);

        if ($info) {
            $stmt_email = mysqli_prepare($conn, "SELECT id_user FROM utilisateur WHERE email = ? AND id_user <> ?");
            mysqli_stmt_bind_param($stmt_email, "si", $email, $info['id_user']);
            mysqli_stmt_execute($stmt_email);
            $email_existe = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_email));
            mysqli_stmt_close($stmt_email);

            if ($email_existe) {
                $erreur = "Erreur : cet email est deja utilise.";
            } else {
                mysqli_begin_transaction($conn);

                $stmt_user = mysqli_prepare($conn, "UPDATE utilisateur SET nom = ?, prenom = ?, email = ? WHERE id_user = ?");
                mysqli_stmt_bind_param($stmt_user, "sssi", $nom, $prenom, $email, $info['id_user']);
                $ok_user = mysqli_stmt_execute($stmt_user);
                mysqli_stmt_close($stmt_user);

                $stmt_enseignant = mysqli_prepare($conn, "UPDATE enseignant SET specialite = ?, bureau = ?, telephone = ? WHERE id_enseignant = ?");
                mysqli_stmt_bind_param($stmt_enseignant, "sssi", $specialite, $bureau, $telephone, $id_enseignant);
                $ok_enseignant = mysqli_stmt_execute($stmt_enseignant);
                mysqli_stmt_close($stmt_enseignant);

                if ($ok_user && $ok_enseignant) {
                    mysqli_commit($conn);
                    $message = "Enseignant modifie avec succes.";
                } else {
                    mysqli_rollback($conn);
                    $erreur = "Erreur lors de la modification de l'enseignant.";
                }
            }
        }
    }
}

if (isset($_GET['modifier'])) {
    $id_enseignant = intval($_GET['modifier']);
    $stmt_modif = mysqli_prepare(
        $conn,
        "SELECT e.id_enseignant, e.numero_enseignant, e.specialite, e.bureau, e.telephone,
                u.nom, u.prenom, u.email
         FROM enseignant e
         INNER JOIN utilisateur u ON e.id_user = u.id_user
         WHERE e.id_enseignant = ?"
    );
    mysqli_stmt_bind_param($stmt_modif, "i", $id_enseignant);
    mysqli_stmt_execute($stmt_modif);
    $enseignant_modif = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_modif));
    mysqli_stmt_close($stmt_modif);
}

$resultat = mysqli_query(
    $conn,
    "SELECT e.id_enseignant, e.numero_enseignant, e.specialite, e.bureau, e.telephone,
            u.nom, u.prenom, u.email, u.actif,
            COUNT(c.id_cours) AS nb_cours,
            GROUP_CONCAT(c.code_cours ORDER BY c.code_cours SEPARATOR ', ') AS cours_associes
     FROM enseignant e
     INNER JOIN utilisateur u ON e.id_user = u.id_user
     LEFT JOIN cours c ON e.id_enseignant = c.id_enseignant
     GROUP BY e.id_enseignant, e.numero_enseignant, e.specialite, e.bureau, e.telephone, u.nom, u.prenom, u.email, u.actif
     ORDER BY u.nom, u.prenom"
);

include __DIR__ . '/../includes/header.php';
?>

<section class="container">
    <h1>Gestion des enseignants</h1>
    <p class="page-subtitle">Ajouter, modifier et verifier les cours associes a chaque enseignant.</p>

    <?php if ($message): ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <?php if ($erreur): ?><p class="error"><?php echo htmlspecialchars($erreur); ?></p><?php endif; ?>

    <section class="form-card">
        <h2>Ajouter un enseignant</h2>
        <form method="post" action="enseignants.php">
            <label>Nom</label><input type="text" name="nom" required>
            <label>Prenom</label><input type="text" name="prenom" required>
            <label>Email</label><input type="email" name="email" required>
            <label>Mot de passe</label><input type="password" name="mot_de_passe" required>
            <label>Numero enseignant</label><input type="text" name="numero_enseignant" required>
            <label>Specialite</label><input type="text" name="specialite" required>
            <label>Bureau</label><input type="text" name="bureau">
            <label>Telephone</label><input type="text" name="telephone">
            <button type="submit" name="ajouter_enseignant">Ajouter</button>
        </form>
    </section>

    <?php if ($enseignant_modif): ?>
        <section class="form-card">
            <h2>Modifier un enseignant</h2>
            <form method="post" action="enseignants.php">
                <input type="hidden" name="id_enseignant" value="<?php echo htmlspecialchars($enseignant_modif['id_enseignant']); ?>">
                <p>Numero enseignant : <?php echo htmlspecialchars($enseignant_modif['numero_enseignant']); ?></p>
                <label>Nom</label><input type="text" name="nom" value="<?php echo htmlspecialchars($enseignant_modif['nom']); ?>" required>
                <label>Prenom</label><input type="text" name="prenom" value="<?php echo htmlspecialchars($enseignant_modif['prenom']); ?>" required>
                <label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($enseignant_modif['email']); ?>" required>
                <label>Specialite</label><input type="text" name="specialite" value="<?php echo htmlspecialchars($enseignant_modif['specialite']); ?>" required>
                <label>Bureau</label><input type="text" name="bureau" value="<?php echo htmlspecialchars($enseignant_modif['bureau']); ?>">
                <label>Telephone</label><input type="text" name="telephone" value="<?php echo htmlspecialchars($enseignant_modif['telephone']); ?>">
                <button type="submit" name="modifier_enseignant">Modifier</button>
                <a class="button secondary" href="enseignants.php">Annuler</a>
            </form>
        </section>
    <?php endif; ?>

    <table>
        <thead><tr><th>Numero</th><th>Nom</th><th>Prenom</th><th>Email</th><th>Specialite</th><th>Bureau</th><th>Telephone</th><th>Cours</th><th>Statut</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if ($resultat && mysqli_num_rows($resultat) > 0): ?>
                <?php while ($enseignant = mysqli_fetch_assoc($resultat)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($enseignant['numero_enseignant']); ?></td>
                        <td><?php echo htmlspecialchars($enseignant['nom']); ?></td>
                        <td><?php echo htmlspecialchars($enseignant['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($enseignant['email']); ?></td>
                        <td><?php echo htmlspecialchars($enseignant['specialite']); ?></td>
                        <td><?php echo htmlspecialchars($enseignant['bureau']); ?></td>
                        <td><?php echo htmlspecialchars($enseignant['telephone']); ?></td>
                        <td><?php echo htmlspecialchars($enseignant['nb_cours'] . ' cours : ' . ($enseignant['cours_associes'] ?? '-')); ?></td>
                        <td>
                            <span class="badge <?php echo $enseignant['actif'] ? 'badge-success' : 'badge-muted'; ?>">
                                <?php echo $enseignant['actif'] ? 'Actif' : 'Desactive'; ?>
                            </span>
                        </td>
                        <td>
                            <a class="button secondary" href="enseignants.php?modifier=<?php echo $enseignant['id_enseignant']; ?>">Modifier</a>
                            <a class="button danger js-confirm-delete" href="enseignants.php?supprimer=<?php echo $enseignant['id_enseignant']; ?>" data-confirm="Supprimer ou desactiver cet enseignant ?">Supprimer</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="10">Aucun enseignant trouve.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
