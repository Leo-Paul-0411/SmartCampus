<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('enseignant');
}

// TODO : remplacer par l'id enseignant venant de la session quand l'authentification sera finalisée.
$id_enseignant = $_SESSION['id_enseignant'] ?? 1;

include __DIR__ . '/../includes/header.php';
?>

<section class="container"><h1>Mes cours</h1>
<?php $stmt = mysqli_prepare($conn, "SELECT code_cours, titre, jour, heure_debut, heure_fin, salle, semestre FROM cours WHERE id_enseignant = ? ORDER BY jour, heure_debut"); mysqli_stmt_bind_param($stmt, "i", $id_enseignant); mysqli_stmt_execute($stmt); $resultat = mysqli_stmt_get_result($stmt); ?>
<table><thead><tr><th>Code</th><th>Titre</th><th>Jour</th><th>Début</th><th>Fin</th><th>Salle</th><th>Semestre</th></tr></thead><tbody><?php if ($resultat && mysqli_num_rows($resultat) > 0): while ($cours = mysqli_fetch_assoc($resultat)): ?><tr><td><?php echo htmlspecialchars($cours['code_cours']); ?></td><td><?php echo htmlspecialchars($cours['titre']); ?></td><td><?php echo htmlspecialchars($cours['jour']); ?></td><td><?php echo htmlspecialchars($cours['heure_debut']); ?></td><td><?php echo htmlspecialchars($cours['heure_fin']); ?></td><td><?php echo htmlspecialchars($cours['salle']); ?></td><td><?php echo htmlspecialchars($cours['semestre']); ?></td></tr><?php endwhile; else: ?><tr><td colspan="7">Aucun cours associé à cet enseignant.</td></tr><?php endif; ?></tbody></table>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

