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

<main class="container"><h1>Saisie des notes</h1>
<?php $id_cours = isset($_GET['id_cours']) ? intval($_GET['id_cours']) : 0; $cours_result = mysqli_query($conn, "SELECT id_cours, code_cours, titre FROM cours WHERE id_enseignant = $id_enseignant ORDER BY code_cours"); ?>
<form method="get" action="notes.php" class="form-card"><label for="id_cours">Choisir un cours</label><select name="id_cours" id="id_cours" required><option value="">Sélectionner un cours</option><?php while ($cours = mysqli_fetch_assoc($cours_result)): ?><option value="<?php echo $cours['id_cours']; ?>" <?php if ($id_cours == $cours['id_cours']) echo 'selected'; ?>><?php echo htmlspecialchars($cours['code_cours'] . ' - ' . $cours['titre']); ?></option><?php endwhile; ?></select><button type="submit">Afficher</button></form>
<?php if ($id_cours > 0): ?><?php $sql = "SELECT etudiant.id_etudiant, utilisateur.nom, utilisateur.prenom, note.note_controle, note.note_exam, note.note_projet, note.moyenne, note.validee FROM inscription INNER JOIN etudiant ON inscription.id_etudiant = etudiant.id_etudiant INNER JOIN utilisateur ON etudiant.id_user = utilisateur.id_user LEFT JOIN note ON note.id_etudiant = etudiant.id_etudiant AND note.id_cours = inscription.id_cours WHERE inscription.id_cours = ? ORDER BY utilisateur.nom, utilisateur.prenom"; $stmt = mysqli_prepare($conn, $sql); mysqli_stmt_bind_param($stmt, "i", $id_cours); mysqli_stmt_execute($stmt); $etudiants = mysqli_stmt_get_result($stmt); ?><h2>Étudiants inscrits</h2><table><thead><tr><th>Étudiant</th><th>Note contrôle</th><th>Note exam</th><th>Note projet</th><th>Moyenne</th><th>Validation</th></tr></thead><tbody><?php if ($etudiants && mysqli_num_rows($etudiants) > 0): while ($etu = mysqli_fetch_assoc($etudiants)): ?><tr><td><?php echo htmlspecialchars($etu['prenom'] . ' ' . $etu['nom']); ?></td><td><input type="number" step="0.01" min="0" max="20" value="<?php echo htmlspecialchars($etu['note_controle'] ?? ''); ?>"></td><td><input type="number" step="0.01" min="0" max="20" value="<?php echo htmlspecialchars($etu['note_exam'] ?? ''); ?>"></td><td><input type="number" step="0.01" min="0" max="20" value="<?php echo htmlspecialchars($etu['note_projet'] ?? ''); ?>"></td><td><?php echo htmlspecialchars($etu['moyenne'] ?? '-'); ?></td><td><?php echo $etu['validee'] ? 'Validée' : 'Non validée'; ?></td></tr><?php endwhile; else: ?><tr><td colspan="6">Aucun étudiant inscrit à ce cours.</td></tr><?php endif; ?></tbody></table><p class="info">TODO : enregistrement des notes dans le prochain commit.</p><?php endif; ?></main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

