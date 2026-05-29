<?php include '../includes/header.php'; ?>

<h2>Connexion</h2>

<!-- TODO : ajouter plus tard la vraie authentification avec la base de donnees. -->
<form method="post" action="">
    <label for="email">Email</label>
    <input type="email" id="email" name="email" required>

    <label for="mot_de_passe">Mot de passe</label>
    <input type="password" id="mot_de_passe" name="mot_de_passe" required>

    <label for="role">Rôle</label>
    <select id="role" name="role" required>
        <option value="administrateur">Administrateur</option>
        <option value="enseignant">Enseignant</option>
        <option value="etudiant">Étudiant</option>
    </select>

    <button type="submit" name="connexion">Se connecter</button>
</form>

<?php include '../includes/footer.php'; ?>
