<?php
include '../config/db.php';
include '../includes/header.php';

$result_etudiants = mysqli_query($conn, "SELECT COUNT(*) AS total FROM etudiant");
$result_enseignants = mysqli_query($conn, "SELECT COUNT(*) AS total FROM enseignant");
$result_cours = mysqli_query($conn, "SELECT COUNT(*) AS total FROM cours");
$result_inscriptions = mysqli_query($conn, "SELECT COUNT(*) AS total FROM inscription");

$nb_etudiants = 0;
$nb_enseignants = 0;
$nb_cours = 0;
$nb_inscriptions = 0;

if ($result_etudiants) {
    $ligne = mysqli_fetch_assoc($result_etudiants);
    $nb_etudiants = $ligne['total'];
}

if ($result_enseignants) {
    $ligne = mysqli_fetch_assoc($result_enseignants);
    $nb_enseignants = $ligne['total'];
}

if ($result_cours) {
    $ligne = mysqli_fetch_assoc($result_cours);
    $nb_cours = $ligne['total'];
}

if ($result_inscriptions) {
    $ligne = mysqli_fetch_assoc($result_inscriptions);
    $nb_inscriptions = $ligne['total'];
}
?>

<h2>Dashboard administrateur</h2>

<section>
    <h3>Statistiques generales</h3>

    <div>
        <p>Nombre d'etudiants : <?php echo htmlspecialchars($nb_etudiants); ?></p>
        <p>Nombre d'enseignants : <?php echo htmlspecialchars($nb_enseignants); ?></p>
        <p>Nombre de cours : <?php echo htmlspecialchars($nb_cours); ?></p>
        <p>Nombre d'inscriptions : <?php echo htmlspecialchars($nb_inscriptions); ?></p>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
