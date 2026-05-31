<?php
include '../config/db.php';

$recherche = "";
$result_etudiants = false;

if (isset($_GET['recherche'])) {
    $recherche = trim($_GET['recherche']);
}

// Recuperation des etudiants avec recherche si un mot-cle est saisi.
if ($recherche !== "") {
    $sql = "SELECT e.numero_etudiant, e.niveau, e.groupe_classe, e.date_naissance, e.telephone,
                   u.nom, u.prenom, u.email
            FROM etudiant e
            INNER JOIN utilisateur u ON e.id_user = u.id_user
            WHERE u.nom LIKE ?
               OR u.prenom LIKE ?
               OR u.email LIKE ?
               OR e.numero_etudiant LIKE ?
            ORDER BY u.nom, u.prenom";

    $requete = mysqli_prepare($conn, $sql);

    if ($requete) {
        $mot_cle = "%" . $recherche . "%";

        mysqli_stmt_bind_param($requete, "ssss", $mot_cle, $mot_cle, $mot_cle, $mot_cle);
        mysqli_stmt_execute($requete);
        $result_etudiants = mysqli_stmt_get_result($requete);
    }
} else {
    // Recuperation de tous les etudiants.
    $result_etudiants = mysqli_query(
        $conn,
        "SELECT e.numero_etudiant, e.niveau, e.groupe_classe, e.date_naissance, e.telephone,
                u.nom, u.prenom, u.email
         FROM etudiant e
         INNER JOIN utilisateur u ON e.id_user = u.id_user
         ORDER BY u.nom, u.prenom"
    );
}

include '../includes/header.php';
?>

<h2>Gestion des etudiants</h2>

<section>
    <h3>Recherche</h3>

    <form method="get" action="etudiants.php">
        <p>
            <label for="recherche">Nom, prenom, email ou numero etudiant</label><br>
            <input type="text" id="recherche" name="recherche" value="<?php echo htmlspecialchars($recherche); ?>">
        </p>

        <button type="submit">Rechercher</button>
        <a href="etudiants.php">Afficher tous</a>
    </form>
</section>

<section>
    <h3>Liste des etudiants</h3>

    <table border="1">
        <thead>
            <tr>
                <th>numero_etudiant</th>
                <th>nom</th>
                <th>prenom</th>
                <th>email</th>
                <th>niveau</th>
                <th>groupe_classe</th>
                <th>date_naissance</th>
                <th>telephone</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_etudiants && mysqli_num_rows($result_etudiants) > 0) { ?>
                <?php while ($etudiant = mysqli_fetch_assoc($result_etudiants)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($etudiant['numero_etudiant']); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['nom']); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['email']); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['niveau']); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['groupe_classe']); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['date_naissance']); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['telephone']); ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="8">Aucun etudiant trouve.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</section>

<?php
if (isset($requete) && $requete) {
    mysqli_stmt_close($requete);
}

include '../includes/footer.php';
?>
