<?php
// Page publique de connexion : verifie le compte puis initialise la session.
include '../config/db.php';
include '../includes/auth.php';

$message = "";
$email = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    $sql = "SELECT id_user, nom, prenom, email, mot_de_passe, role, actif
            FROM utilisateur
            WHERE email = ?
            LIMIT 1";

    $requete = mysqli_prepare($conn, $sql);

    if ($requete) {
        mysqli_stmt_bind_param($requete, "s", $email);
        mysqli_stmt_execute($requete);
        $resultat = mysqli_stmt_get_result($requete);
        $utilisateur = mysqli_fetch_assoc($resultat);
        mysqli_stmt_close($requete);

        if ($utilisateur && $utilisateur['actif'] == 1) {
            // Les comptes crees par l'application sont hashes.
            // Les comptes de demonstration SQL peuvent rester en clair.
            $mot_de_passe_valide = password_verify($mot_de_passe, $utilisateur['mot_de_passe'])
                || $mot_de_passe === $utilisateur['mot_de_passe'];

            if ($mot_de_passe_valide) {
                $role = $utilisateur['role'];

                if ($role === 'administrateur') {
                    $role = 'admin';
                }

                $_SESSION['id_user'] = $utilisateur['id_user'];
                $_SESSION['nom'] = $utilisateur['nom'];
                $_SESSION['prenom'] = $utilisateur['prenom'];
                $_SESSION['email'] = $utilisateur['email'];
                $_SESSION['role'] = $role;
                unset($_SESSION['id_enseignant'], $_SESSION['id_etudiant']);

                // On conserve aussi l'identifiant metier pour filtrer les pages enseignant.
                if ($role === 'enseignant') {
                    $sql_enseignant = "SELECT id_enseignant
                                       FROM enseignant
                                       WHERE id_user = ?";
                    $requete_enseignant = mysqli_prepare($conn, $sql_enseignant);

                    if ($requete_enseignant) {
                        mysqli_stmt_bind_param($requete_enseignant, "i", $utilisateur['id_user']);
                        mysqli_stmt_execute($requete_enseignant);
                        $resultat_enseignant = mysqli_stmt_get_result($requete_enseignant);
                        $enseignant = mysqli_fetch_assoc($resultat_enseignant);
                        mysqli_stmt_close($requete_enseignant);

                        if ($enseignant) {
                            $_SESSION['id_enseignant'] = $enseignant['id_enseignant'];
                        }
                    }
                }

                // Meme principe pour l'espace etudiant : chaque etudiant voit ses donnees.
                if ($role === 'etudiant') {
                    $sql_etudiant = "SELECT id_etudiant
                                     FROM etudiant
                                     WHERE id_user = ?";
                    $requete_etudiant = mysqli_prepare($conn, $sql_etudiant);

                    if ($requete_etudiant) {
                        mysqli_stmt_bind_param($requete_etudiant, "i", $utilisateur['id_user']);
                        mysqli_stmt_execute($requete_etudiant);
                        $resultat_etudiant = mysqli_stmt_get_result($requete_etudiant);
                        $etudiant = mysqli_fetch_assoc($resultat_etudiant);
                        mysqli_stmt_close($requete_etudiant);

                        if ($etudiant) {
                            $_SESSION['id_etudiant'] = $etudiant['id_etudiant'];
                        }
                    }
                }

                rediriger_selon_role($role);
            } else {
                $message = "Erreur : email ou mot de passe incorrect.";
            }
        } else {
            $message = "Erreur : utilisateur introuvable ou compte inactif.";
        }
    } else {
        $message = "Erreur de preparation de la requete : " . mysqli_error($conn);
    }
}

include '../includes/header.php';
?>

<section class="container login-card">
<h2>Connexion a SmartCampus</h2>
<p class="page-subtitle">Plateforme de gestion academique</p>

<?php if ($message !== "") { ?>
    <p class="error"><?php echo htmlspecialchars($message); ?></p>
<?php } ?>

<form method="post" action="login.php">
    <p>
        <label for="email">Email</label><br>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
    </p>

    <p>
        <label for="mot_de_passe">Mot de passe</label><br>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required>
    </p>

<button type="submit">Se connecter</button>
</form>
</section>

<?php include '../includes/footer.php'; ?>
