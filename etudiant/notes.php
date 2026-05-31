<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('etudiant');
}

// TODO : remplacer par l'id étudiant venant de la session quand l'authentification sera finalisée.
$id_etudiant = $_SESSION['id_etudiant'] ?? 0;

include __DIR__ . '/../includes/header.php';
?>

<section class="container"><h1>Mes notes</h1><?php $sql = "SELECT cours.code_cours, cours.titre, note.note_controle, note.note_exam, note.note_projet, note.moyenne, note.validee FROM note INNER JOIN cours ON note.id_cours = cours.id_cours WHERE note.id_etudiant = ? ORDER BY cours.code_cours"; $stmt = mysqli_prepare($conn, $sql); mysqli_stmt_bind_param($stmt, "i", $id_etudiant); mysqli_stmt_execute($stmt); $resultat = mysqli_stmt_get_result($stmt); ?><table><thead><tr><th>Cours</th><th>Note contrôle</th><th>Note exam</th><th>Note projet</th><th>Moyenne</th><th>Validation</th></tr></thead><tbody><?php if ($resultat && mysqli_num_rows($resultat) > 0): while ($note = mysqli_fetch_assoc($resultat)): ?><tr><td><?php echo htmlspecialchars($note['code_cours'] . ' - ' . $note['titre']); ?></td><td><?php echo htmlspecialchars($note['note_controle']); ?></td><td><?php echo htmlspecialchars($note['note_exam']); ?></td><td><?php echo htmlspecialchars($note['note_projet']); ?></td><td><?php echo htmlspecialchars($note['moyenne']); ?></td><td><?php echo $note['validee'] ? 'Validée' : 'Non validée'; ?></td></tr><?php endwhile; else: ?><tr><td colspan="6">Aucune note disponible.</td></tr><?php endif; ?></tbody></table></section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
