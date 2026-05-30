<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (function_exists('verifier_role')) {
    verifier_role('administrateur');
}

include __DIR__ . '/../includes/header.php';
?>

<main class="container">
    <h1>Gestion des cours</h1>
    <section class="toolbar">
        <form method="get" action="cours.php">
            <input type="text" name="recherche" placeholder="Rechercher par code ou titre" value="<?php echo isset($_GET['recherche']) ? htmlspecialchars($_GET['recherche']) : ''; ?>">
            <button type="submit">Rechercher</button>
            <a class="button secondary" href="cours.php">Réinitialiser</a>
        </form>
        <button type="button">Ajouter un cours</button>
    </section>
    <?php
    $recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';
    if ($recherche !== '') {
        $mot = '%' . $recherche . '%';
        $sql = "SELECT cours.*, enseignant.numero_enseignant FROM cours LEFT JOIN enseignant ON cours.id_enseignant = enseignant.id_enseignant WHERE cours.code_cours LIKE ? OR cours.titre LIKE ? ORDER BY cours.code_cours";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $mot, $mot);
        mysqli_stmt_execute($stmt);
        $resultat = mysqli_stmt_get_result($stmt);
    } else {
        $resultat = mysqli_query($conn, "SELECT cours.*, enseignant.numero_enseignant FROM cours LEFT JOIN enseignant ON cours.id_enseignant = enseignant.id_enseignant ORDER BY cours.code_cours");
    }
    ?>
    <table>
        <thead><tr><th>Code</th><th>Titre</th><th>Jour</th><th>Début</th><th>Fin</th><th>Salle</th><th>Semestre</th><th>Capacité</th><th>Enseignant</th></tr></thead>
        <tbody>
            <?php if ($resultat && mysqli_num_rows($resultat) > 0): ?>
                <?php while ($cours = mysqli_fetch_assoc($resultat)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cours['code_cours']); ?></td><td><?php echo htmlspecialchars($cours['titre']); ?></td><td><?php echo htmlspecialchars($cours['jour']); ?></td><td><?php echo htmlspecialchars($cours['heure_debut']); ?></td><td><?php echo htmlspecialchars($cours['heure_fin']); ?></td><td><?php echo htmlspecialchars($cours['salle']); ?></td><td><?php echo htmlspecialchars($cours['semestre']); ?></td><td><?php echo htmlspecialchars($cours['capacite_max']); ?></td><td><?php echo htmlspecialchars($cours['numero_enseignant'] ?? 'Non défini'); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?><tr><td colspan="9">Aucun cours trouvé.</td></tr><?php endif; ?>
        </tbody>
    </table>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
