<?php
// Liste des cours de l'enseignant connecte uniquement.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('enseignant');
}

$id_enseignant = $_SESSION['id_enseignant'] ?? 0;

// Le filtre id_enseignant evite d'afficher les cours d'un autre enseignant.
$sql = "SELECT c.code_cours, c.titre, c.jour, c.heure_debut, c.heure_fin,
               c.salle, c.semestre, c.capacite_max,
               (SELECT COUNT(*) FROM inscription i WHERE i.id_cours = c.id_cours AND i.statut = 'inscrit') AS nb_inscrits
        FROM cours c
        WHERE c.id_enseignant = ?
        ORDER BY c.jour, c.heure_debut";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_enseignant);
mysqli_stmt_execute($stmt);
$resultat = mysqli_stmt_get_result($stmt);

include __DIR__ . '/../includes/header.php';
?>

<section class="container">
    <h1>Mes cours</h1>
    <p class="page-subtitle">Liste des cours affectes avec le remplissage actuel.</p>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Titre</th>
                <th>Jour</th>
                <th>Debut</th>
                <th>Fin</th>
                <th>Salle</th>
                <th>Semestre</th>
                <th>Places</th>
                <th>Etat</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultat && mysqli_num_rows($resultat) > 0): ?>
                <?php while ($cours = mysqli_fetch_assoc($resultat)): ?>
                    <?php
                    $nb_inscrits = (int) $cours['nb_inscrits'];
                    $capacite = (int) $cours['capacite_max'];
                    $cours_complet = $nb_inscrits >= $capacite;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cours['code_cours']); ?></td>
                        <td><?php echo htmlspecialchars($cours['titre']); ?></td>
                        <td><?php echo htmlspecialchars($cours['jour']); ?></td>
                        <td><?php echo htmlspecialchars($cours['heure_debut']); ?></td>
                        <td><?php echo htmlspecialchars($cours['heure_fin']); ?></td>
                        <td><?php echo htmlspecialchars($cours['salle']); ?></td>
                        <td><?php echo htmlspecialchars($cours['semestre']); ?></td>
                        <td><?php echo htmlspecialchars($nb_inscrits . ' / ' . $capacite); ?></td>
                        <td>
                            <span class="badge <?php echo $cours_complet ? 'badge-danger' : 'badge-success'; ?>">
                                <?php echo $cours_complet ? 'Complet' : 'Disponible'; ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9">Aucun cours associe a cet enseignant.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
