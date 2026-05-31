<?php
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

<h2>Connexion</h2>

<?php if ($message !== "") { ?>
    <p><?php echo htmlspecialchars($message); ?></p>
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

<?php include '../includes/footer.php'; ?>
