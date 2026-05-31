<?php
// Consultation des notes de l'etudiant connecte uniquement.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('etudiant');
}

$id_etudiant = $_SESSION['id_etudiant'] ?? 0;

// Moyenne generale affichee seulement a partir des notes validees.
$stmt_moyenne = mysqli_prepare($conn, "SELECT AVG(moyenne) AS moyenne_generale FROM note WHERE id_etudiant = ? AND validee = 1");
mysqli_stmt_bind_param($stmt_moyenne, "i", $id_etudiant);
mysqli_stmt_execute($stmt_moyenne);
$moyenne_generale = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_moyenne))['moyenne_generale'] ?? null;

// Detail des notes et de leur statut de validation.
$sql = "SELECT cours.code_cours, cours.titre,
               note.note_controle, note.note_exam, note.note_projet,
               note.moyenne, note.validee
        FROM note
        INNER JOIN cours ON note.id_cours = cours.id_cours
        WHERE note.id_etudiant = ?
        ORDER BY cours.code_cours";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_etudiant);
mysqli_stmt_execute($stmt);
$resultat = mysqli_stmt_get_result($stmt);

include __DIR__ . '/../includes/header.php';
?>

<section class="container">
    <h1>Mes notes</h1>
    <p class="page-subtitle">Les moyennes sont calculees avec la formule : controle 30%, examen 50%, projet 20%.</p>

    <section class="stats-grid">
        <article class="stat-card">
            <h2>Moyenne generale validee</h2>
            <p><?php echo $moyenne_generale !== null ? number_format($moyenne_generale, 2) : '-'; ?></p>
        </article>
    </section>

    <table>
        <thead>
            <tr>
                <th>Cours</th>
                <th>Note controle</th>
                <th>Note exam</th>
                <th>Note projet</th>
                <th>Moyenne</th>
                <th>Validation</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultat && mysqli_num_rows($resultat) > 0): ?>
                <?php while ($note = mysqli_fetch_assoc($resultat)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($note['code_cours'] . ' - ' . $note['titre']); ?></td>
                        <td><?php echo htmlspecialchars($note['note_controle']); ?></td>
                        <td><?php echo htmlspecialchars($note['note_exam']); ?></td>
                        <td><?php echo htmlspecialchars($note['note_projet']); ?></td>
                        <td><?php echo htmlspecialchars(number_format((float) $note['moyenne'], 2)); ?></td>
                        <td>
                            <span class="badge <?php echo $note['validee'] ? 'badge-success' : 'badge-warning'; ?>">
                                <?php echo $note['validee'] ? 'Validee' : 'En attente'; ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">Aucune note disponible.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
