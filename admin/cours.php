<?php
include '../config/db.php';

$message = "";
$type_message = "";

// Ajout d'un cours apres validation du formulaire.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code_cours = trim($_POST['code_cours']);
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $id_enseignant = (int) $_POST['id_enseignant'];
    $capacite_max = (int) $_POST['capacite_max'];
    $jour = trim($_POST['jour']);
    $heure_debut = $_POST['heure_debut'];
    $heure_fin = $_POST['heure_fin'];
    $salle = trim($_POST['salle']);
    $semestre = trim($_POST['semestre']);

    if ($heure_debut >= $heure_fin) {
        $message = "Erreur : l'heure de debut doit etre inferieure a l'heure de fin.";
        $type_message = "erreur";
    } else {
        $sql = "INSERT INTO cours
                (code_cours, titre, description, capacite_max, jour, heure_debut, heure_fin, salle, semestre, id_enseignant)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $requete = mysqli_prepare($conn, $sql);

        if ($requete) {
            mysqli_stmt_bind_param(
                $requete,
                "sssisssssi",
                $code_cours,
                $titre,
                $description,
                $capacite_max,
                $jour,
                $heure_debut,
                $heure_fin,
                $salle,
                $semestre,
                $id_enseignant
            );

            if (mysqli_stmt_execute($requete)) {
                $message = "Cours ajoute avec succes.";
                $type_message = "succes";
            } else {
                $message = "Erreur lors de l'ajout du cours : " . mysqli_error($conn);
                $type_message = "erreur";
            }

            mysqli_stmt_close($requete);
        } else {
            $message = "Erreur de preparation de la requete : " . mysqli_error($conn);
            $type_message = "erreur";
        }
    }
}

// Recuperation des enseignants pour la liste deroulante.
$result_enseignants = mysqli_query(
    $conn,
    "SELECT e.id_enseignant, u.nom, u.prenom
     FROM enseignant e
     INNER JOIN utilisateur u ON e.id_user = u.id_user
     ORDER BY u.nom, u.prenom"
);

// Recuperation des cours avec le nom de l'enseignant.
$result_cours = mysqli_query(
    $conn,
    "SELECT c.code_cours, c.titre, c.capacite_max, c.jour, c.heure_debut, c.heure_fin,
            c.salle, c.semestre, u.nom, u.prenom
     FROM cours c
     INNER JOIN enseignant e ON c.id_enseignant = e.id_enseignant
     INNER JOIN utilisateur u ON e.id_user = u.id_user
     ORDER BY c.code_cours"
);

include '../includes/header.php';
?>

<h2>Gestion des cours</h2>

<?php if ($message !== "") { ?>
    <p><?php echo htmlspecialchars($message); ?></p>
<?php } ?>

<section>
    <h3>Ajouter un cours</h3>

    <form method="post" action="cours.php">
        <p>
            <label for="code_cours">Code du cours</label><br>
            <input type="text" id="code_cours" name="code_cours" required>
        </p>

        <p>
            <label for="titre">Titre</label><br>
            <input type="text" id="titre" name="titre" required>
        </p>

        <p>
            <label for="description">Description</label><br>
            <textarea id="description" name="description"></textarea>
        </p>

        <p>
            <label for="id_enseignant">Enseignant</label><br>
            <select id="id_enseignant" name="id_enseignant" required>
                <option value="">-- Choisir un enseignant --</option>
                <?php if ($result_enseignants) { ?>
                    <?php while ($enseignant = mysqli_fetch_assoc($result_enseignants)) { ?>
                        <option value="<?php echo htmlspecialchars($enseignant['id_enseignant']); ?>">
                            <?php echo htmlspecialchars($enseignant['prenom'] . ' ' . $enseignant['nom']); ?>
                        </option>
                    <?php } ?>
                <?php } ?>
            </select>
        </p>

        <p>
            <label for="capacite_max">Capacite maximale</label><br>
            <input type="number" id="capacite_max" name="capacite_max" min="1" required>
        </p>

        <p>
            <label for="jour">Jour</label><br>
            <input type="text" id="jour" name="jour" required>
        </p>

        <p>
            <label for="heure_debut">Heure de debut</label><br>
            <input type="time" id="heure_debut" name="heure_debut" required>
        </p>

        <p>
            <label for="heure_fin">Heure de fin</label><br>
            <input type="time" id="heure_fin" name="heure_fin" required>
        </p>

        <p>
            <label for="salle">Salle</label><br>
            <input type="text" id="salle" name="salle" required>
        </p>

        <p>
            <label for="semestre">Semestre</label><br>
            <input type="text" id="semestre" name="semestre" required>
        </p>

        <button type="submit">Ajouter</button>
    </form>
</section>

<section>
    <h3>Liste des cours</h3>

    <table border="1">
        <thead>
            <tr>
                <th>code_cours</th>
                <th>titre</th>
                <th>enseignant</th>
                <th>capacite_max</th>
                <th>jour</th>
                <th>heure_debut</th>
                <th>heure_fin</th>
                <th>salle</th>
                <th>semestre</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_cours && mysqli_num_rows($result_cours) > 0) { ?>
                <?php while ($cours = mysqli_fetch_assoc($result_cours)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cours['code_cours']); ?></td>
                        <td><?php echo htmlspecialchars($cours['titre']); ?></td>
                        <td><?php echo htmlspecialchars($cours['prenom'] . ' ' . $cours['nom']); ?></td>
                        <td><?php echo htmlspecialchars($cours['capacite_max']); ?></td>
                        <td><?php echo htmlspecialchars($cours['jour']); ?></td>
                        <td><?php echo htmlspecialchars($cours['heure_debut']); ?></td>
                        <td><?php echo htmlspecialchars($cours['heure_fin']); ?></td>
                        <td><?php echo htmlspecialchars($cours['salle']); ?></td>
                        <td><?php echo htmlspecialchars($cours['semestre']); ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="9">Aucun cours trouve.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</section>

<?php include '../includes/footer.php'; ?>
