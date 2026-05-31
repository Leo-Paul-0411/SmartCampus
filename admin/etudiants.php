<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('administrateur');
}

include __DIR__ . '/../includes/header.php';
?>

<section class="container">
    <h1>Gestion des étudiants</h1>
    <section class="toolbar"><input type="text" placeholder="Rechercher un étudiant"><button type="button">Ajouter un étudiant</button></section>
    <?php
    $sql = "SELECT etudiant.numero_etudiant, etudiant.niveau, etudiant.groupe_classe, etudiant.date_naissance, etudiant.telephone, utilisateur.nom, utilisateur.prenom, utilisateur.email FROM etudiant INNER JOIN utilisateur ON etudiant.id_user = utilisateur.id_user ORDER BY utilisateur.nom, utilisateur.prenom";
    $resultat = mysqli_query($conn, $sql);
    ?>
    <table>
        <thead><tr><th>Numéro étudiant</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Niveau</th><th>Groupe</th><th>Téléphone</th></tr></thead>
        <tbody>
            <?php if ($resultat && mysqli_num_rows($resultat) > 0): ?>
                <?php while ($etudiant = mysqli_fetch_assoc($resultat)): ?>
                    <tr><td><?php echo htmlspecialchars($etudiant['numero_etudiant']); ?></td><td><?php echo htmlspecialchars($etudiant['nom']); ?></td><td><?php echo htmlspecialchars($etudiant['prenom']); ?></td><td><?php echo htmlspecialchars($etudiant['email']); ?></td><td><?php echo htmlspecialchars($etudiant['niveau']); ?></td><td><?php echo htmlspecialchars($etudiant['groupe_classe']); ?></td><td><?php echo htmlspecialchars($etudiant['telephone']); ?></td></tr>
                <?php endwhile; ?>
            <?php else: ?><tr><td colspan="7">Aucun étudiant trouvé.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
