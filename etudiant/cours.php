<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('etudiant');
}

// TODO : remplacer par l'id étudiant venant de la session quand l'authentification sera finalisée.
$id_etudiant = $_SESSION['id_etudiant'] ?? 1;

include __DIR__ . '/../includes/header.php';
?>

<main class="container"><h1>Mes cours</h1><?php $sql = "SELECT cours.code_cours, cours.titre, cours.jour, cours.heure_debut, cours.heure_fin, cours.salle, cours.semestre, inscription.statut FROM inscription INNER JOIN cours ON inscription.id_cours = cours.id_cours WHERE inscription.id_etudiant = ? ORDER BY cours.jour, cours.heure_debut"; $stmt = mysqli_prepare($conn, $sql); mysqli_stmt_bind_param($stmt, "i", $id_etudiant); mysqli_stmt_execute($stmt); $resultat = mysqli_stmt_get_result($stmt); ?><table><thead><tr><th>Code</th><th>Cours</th><th>Jour</th><th>Début</th><th>Fin</th><th>Salle</th><th>Semestre</th><th>Statut</th></tr></thead><tbody><?php if ($resultat && mysqli_num_rows($resultat) > 0): while ($cours = mysqli_fetch_assoc($resultat)): ?><tr><td><?php echo htmlspecialchars($cours['code_cours']); ?></td><td><?php echo htmlspecialchars($cours['titre']); ?></td><td><?php echo htmlspecialchars($cours['jour']); ?></td><td><?php echo htmlspecialchars($cours['heure_debut']); ?></td><td><?php echo htmlspecialchars($cours['heure_fin']); ?></td><td><?php echo htmlspecialchars($cours['salle']); ?></td><td><?php echo htmlspecialchars($cours['semestre']); ?></td><td><?php echo htmlspecialchars($cours['statut']); ?></td></tr><?php endwhile; else: ?><tr><td colspan="8">Aucun cours trouvé.</td></tr><?php endif; ?></tbody></table></main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
