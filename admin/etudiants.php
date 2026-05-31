<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('administrateur');
}

$message = "";
$erreur = "";
$etudiant_modif = null;

if (isset($_GET['supprimer'])) {
    $id_etudiant = intval($_GET['supprimer']);

    $stmt = mysqli_prepare($conn, "SELECT id_user FROM etudiant WHERE id_etudiant = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_etudiant);
    mysqli_stmt_execute($stmt);
    $etudiant = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($etudiant) {
        $stmt_verif = mysqli_prepare($conn, "SELECT (SELECT COUNT(*) FROM inscription WHERE id_etudiant = ?) AS nb_inscriptions, (SELECT COUNT(*) FROM note WHERE id_etudiant = ?) AS nb_notes");
        mysqli_stmt_bind_param($stmt_verif, "ii", $id_etudiant, $id_etudiant);
        mysqli_stmt_execute($stmt_verif);
        $verif = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_verif));
        mysqli_stmt_close($stmt_verif);

        if ($verif['nb_inscriptions'] > 0 || $verif['nb_notes'] > 0) {
            $stmt_desactiver = mysqli_prepare($conn, "UPDATE utilisateur SET actif = 0 WHERE id_user = ?");
            mysqli_stmt_bind_param($stmt_desactiver, "i", $etudiant['id_user']);
            mysqli_stmt_execute($stmt_desactiver);
            mysqli_stmt_close($stmt_desactiver);
            $message = "Etudiant desactive car il possede deja des inscriptions ou des notes.";
        } else {
            mysqli_begin_transaction($conn);
            $stmt_delete_etudiant = mysqli_prepare($conn, "DELETE FROM etudiant WHERE id_etudiant = ?");
            mysqli_stmt_bind_param($stmt_delete_etudiant, "i", $id_etudiant);
            $ok_etudiant = mysqli_stmt_execute($stmt_delete_etudiant);
            mysqli_stmt_close($stmt_delete_etudiant);

            $stmt_delete_user = mysqli_prepare($conn, "DELETE FROM utilisateur WHERE id_user = ?");
            mysqli_stmt_bind_param($stmt_delete_user, "i", $etudiant['id_user']);
            $ok_user = mysqli_stmt_execute($stmt_delete_user);
            mysqli_stmt_close($stmt_delete_user);

            if ($ok_etudiant && $ok_user) {
                mysqli_commit($conn);
                $message = "Etudiant supprime avec succes.";
            } else {
                mysqli_rollback($conn);
                $erreur = "Erreur lors de la suppression de l'etudiant.";
            }
        }
    }
}

if (isset($_POST['ajouter_etudiant'])) {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $numero_etudiant = trim($_POST['numero_etudiant'] ?? '');
    $niveau = trim($_POST['niveau'] ?? '');
    $groupe_classe = trim($_POST['groupe_classe'] ?? '');
    $date_naissance = trim($_POST['date_naissance'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');

    if ($nom === '' || $prenom === '' || $email === '' || $mot_de_passe === '' || $numero_etudiant === '' || $niveau === '' || $groupe_classe === '') {
        $erreur = "Veuillez remplir tous les champs obligatoires.";
    } else {
        $stmt_email = mysqli_prepare($conn, "SELECT id_user FROM utilisateur WHERE email = ?");
        mysqli_stmt_bind_param($stmt_email, "s", $email);
        mysqli_stmt_execute($stmt_email);
        $email_existe = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_email));
        mysqli_stmt_close($stmt_email);

        $stmt_numero = mysqli_prepare($conn, "SELECT id_etudiant FROM etudiant WHERE numero_etudiant = ?");
        mysqli_stmt_bind_param($stmt_numero, "s", $numero_etudiant);
        mysqli_stmt_execute($stmt_numero);
        $numero_existe = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_numero));
        mysqli_stmt_close($stmt_numero);

        if ($email_existe) {
            $erreur = "Erreur : cet email existe deja.";
        } elseif ($numero_existe) {
            $erreur = "Erreur : ce numero etudiant existe deja.";
        } else {
            mysqli_begin_transaction($conn);
            $role = "etudiant";
            $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $stmt_user = mysqli_prepare($conn, "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_user, "sssss", $nom, $prenom, $email, $mot_de_passe_hash, $role);
            $ok_user = mysqli_stmt_execute($stmt_user);
            $id_user = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt_user);

            $date_sql = $date_naissance !== '' ? $date_naissance : null;
            $stmt_etudiant = mysqli_prepare($conn, "INSERT INTO etudiant (numero_etudiant, niveau, groupe_classe, date_naissance, telephone, id_user) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_etudiant, "sssssi", $numero_etudiant, $niveau, $groupe_classe, $date_sql, $telephone, $id_user);
            $ok_etudiant = mysqli_stmt_execute($stmt_etudiant);
            mysqli_stmt_close($stmt_etudiant);

            if ($ok_user && $ok_etudiant) {
                mysqli_commit($conn);
                $message = "Etudiant et compte utilisateur ajoutes avec succes.";
            } else {
                mysqli_rollback($conn);
                $erreur = "Erreur lors de l'ajout de l'etudiant.";
            }
        }
    }
}

if (isset($_POST['modifier_etudiant'])) {
    $id_etudiant = intval($_POST['id_etudiant'] ?? 0);
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $niveau = trim($_POST['niveau'] ?? '');
    $groupe_classe = trim($_POST['groupe_classe'] ?? '');
    $date_naissance = trim($_POST['date_naissance'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');

    if ($id_etudiant <= 0 || $nom === '' || $prenom === '' || $email === '' || $niveau === '' || $groupe_classe === '') {
        $erreur = "Veuillez remplir tous les champs obligatoires.";
    } else {
        $stmt_info = mysqli_prepare($conn, "SELECT id_user FROM etudiant WHERE id_etudiant = ?");
        mysqli_stmt_bind_param($stmt_info, "i", $id_etudiant);
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
                $date_sql = $date_naissance !== '' ? $date_naissance : null;
                mysqli_begin_transaction($conn);

                $stmt_user = mysqli_prepare($conn, "UPDATE utilisateur SET nom = ?, prenom = ?, email = ? WHERE id_user = ?");
                mysqli_stmt_bind_param($stmt_user, "sssi", $nom, $prenom, $email, $info['id_user']);
                $ok_user = mysqli_stmt_execute($stmt_user);
                mysqli_stmt_close($stmt_user);

                $stmt_etudiant = mysqli_prepare($conn, "UPDATE etudiant SET niveau = ?, groupe_classe = ?, date_naissance = ?, telephone = ? WHERE id_etudiant = ?");
                mysqli_stmt_bind_param($stmt_etudiant, "ssssi", $niveau, $groupe_classe, $date_sql, $telephone, $id_etudiant);
                $ok_etudiant = mysqli_stmt_execute($stmt_etudiant);
                mysqli_stmt_close($stmt_etudiant);

                if ($ok_user && $ok_etudiant) {
                    mysqli_commit($conn);
                    $message = "Etudiant modifie avec succes.";
                } else {
                    mysqli_rollback($conn);
                    $erreur = "Erreur lors de la modification de l'etudiant.";
                }
            }
        }
    }
}

if (isset($_GET['modifier'])) {
    $id_etudiant = intval($_GET['modifier']);
    $stmt_modif = mysqli_prepare(
        $conn,
        "SELECT e.id_etudiant, e.numero_etudiant, e.niveau, e.groupe_classe, e.date_naissance, e.telephone,
                u.nom, u.prenom, u.email
         FROM etudiant e
         INNER JOIN utilisateur u ON e.id_user = u.id_user
         WHERE e.id_etudiant = ?"
    );
    mysqli_stmt_bind_param($stmt_modif, "i", $id_etudiant);
    mysqli_stmt_execute($stmt_modif);
    $etudiant_modif = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_modif));
    mysqli_stmt_close($stmt_modif);
}

$resultat = mysqli_query(
    $conn,
    "SELECT e.id_etudiant, e.numero_etudiant, e.niveau, e.groupe_classe, e.date_naissance, e.telephone,
            u.nom, u.prenom, u.email, u.actif
     FROM etudiant e
     INNER JOIN utilisateur u ON e.id_user = u.id_user
     ORDER BY u.nom, u.prenom"
);

include __DIR__ . '/../includes/header.php';
?>

<section class="container">
    <h1>Gestion des etudiants</h1>

    <?php if ($message): ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <?php if ($erreur): ?><p class="error"><?php echo htmlspecialchars($erreur); ?></p><?php endif; ?>

    <section class="form-card">
        <h2>Ajouter un etudiant</h2>
        <form method="post" action="etudiants.php">
            <label>Nom</label><input type="text" name="nom" required>
            <label>Prenom</label><input type="text" name="prenom" required>
            <label>Email</label><input type="email" name="email" required>
            <label>Mot de passe</label><input type="password" name="mot_de_passe" required>
            <label>Numero etudiant</label><input type="text" name="numero_etudiant" required>
            <label>Niveau</label><input type="text" name="niveau" required>
            <label>Groupe classe</label><input type="text" name="groupe_classe" required>
            <label>Date naissance</label><input type="date" name="date_naissance">
            <label>Telephone</label><input type="text" name="telephone">
            <button type="submit" name="ajouter_etudiant">Ajouter</button>
        </form>
    </section>

    <?php if ($etudiant_modif): ?>
        <section class="form-card">
            <h2>Modifier un etudiant</h2>
            <form method="post" action="etudiants.php">
                <input type="hidden" name="id_etudiant" value="<?php echo htmlspecialchars($etudiant_modif['id_etudiant']); ?>">
                <p>Numero etudiant : <?php echo htmlspecialchars($etudiant_modif['numero_etudiant']); ?></p>
                <label>Nom</label><input type="text" name="nom" value="<?php echo htmlspecialchars($etudiant_modif['nom']); ?>" required>
                <label>Prenom</label><input type="text" name="prenom" value="<?php echo htmlspecialchars($etudiant_modif['prenom']); ?>" required>
                <label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($etudiant_modif['email']); ?>" required>
                <label>Niveau</label><input type="text" name="niveau" value="<?php echo htmlspecialchars($etudiant_modif['niveau']); ?>" required>
                <label>Groupe classe</label><input type="text" name="groupe_classe" value="<?php echo htmlspecialchars($etudiant_modif['groupe_classe']); ?>" required>
                <label>Date naissance</label><input type="date" name="date_naissance" value="<?php echo htmlspecialchars($etudiant_modif['date_naissance']); ?>">
                <label>Telephone</label><input type="text" name="telephone" value="<?php echo htmlspecialchars($etudiant_modif['telephone']); ?>">
                <button type="submit" name="modifier_etudiant">Modifier</button>
                <a class="button secondary" href="etudiants.php">Annuler</a>
            </form>
        </section>
    <?php endif; ?>

    <table>
        <thead><tr><th>Numero</th><th>Nom</th><th>Prenom</th><th>Email</th><th>Niveau</th><th>Groupe</th><th>Telephone</th><th>Statut</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if ($resultat && mysqli_num_rows($resultat) > 0): ?>
                <?php while ($etudiant = mysqli_fetch_assoc($resultat)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($etudiant['numero_etudiant']); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['nom']); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['email']); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['niveau']); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['groupe_classe']); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['telephone']); ?></td>
                        <td><?php echo $etudiant['actif'] ? 'Actif' : 'Desactive'; ?></td>
                        <td>
                            <a class="button secondary" href="etudiants.php?modifier=<?php echo $etudiant['id_etudiant']; ?>">Modifier</a>
                            <a class="button danger js-confirm-delete" href="etudiants.php?supprimer=<?php echo $etudiant['id_etudiant']; ?>" data-confirm="Supprimer ou desactiver cet etudiant ?">Supprimer</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9">Aucun etudiant trouve.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
