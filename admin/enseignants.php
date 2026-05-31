<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('administrateur');
}

include __DIR__ . '/../includes/header.php';
?>

<section class="container">
    <h1>Gestion des enseignants</h1>
    <section class="toolbar"><input type="text" placeholder="Rechercher un enseignant"><button type="button">Ajouter un enseignant</button></section>
    <?php
    $sql = "SELECT enseignant.numero_enseignant, enseignant.specialite, enseignant.bureau, enseignant.telephone, utilisateur.nom, utilisateur.prenom, utilisateur.email FROM enseignant INNER JOIN utilisateur ON enseignant.id_user = utilisateur.id_user ORDER BY utilisateur.nom, utilisateur.prenom";
    $resultat = mysqli_query($conn, $sql);
    ?>
    <table>
        <thead><tr><th>Numéro enseignant</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Spécialité</th><th>Bureau</th><th>Téléphone</th></tr></thead>
        <tbody>
            <?php if ($resultat && mysqli_num_rows($resultat) > 0): ?>
                <?php while ($enseignant = mysqli_fetch_assoc($resultat)): ?>
                    <tr><td><?php echo htmlspecialchars($enseignant['numero_enseignant']); ?></td><td><?php echo htmlspecialchars($enseignant['nom']); ?></td><td><?php echo htmlspecialchars($enseignant['prenom']); ?></td><td><?php echo htmlspecialchars($enseignant['email']); ?></td><td><?php echo htmlspecialchars($enseignant['specialite']); ?></td><td><?php echo htmlspecialchars($enseignant['bureau']); ?></td><td><?php echo htmlspecialchars($enseignant['telephone']); ?></td></tr>
                <?php endwhile; ?>
            <?php else: ?><tr><td colspan="7">Aucun enseignant trouvé.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
