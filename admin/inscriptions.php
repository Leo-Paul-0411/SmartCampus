<?php
include '../config/db.php';
include '../includes/auth.php';
include '../includes/fonctions.php';

verifier_role('admin');

// Workflow inscription :
// etudiant -> demande en_attente -> validation/refus admin -> inscrit/desinscrit.
// Les controles metier sont appliques avant toute inscription active.
$message = "";
$type_message = "";
$filtre_cours = intval($_GET['id_cours'] ?? 0);
$filtre_statut = trim($_GET['statut'] ?? '');

// Recupere les informations utiles pour traiter une demande d'inscription.
function infos_inscription($conn, $id_inscription)
{
    $sql = "SELECT i.id_inscription, i.id_etudiant, i.id_cours, i.statut,
                   e.id_user, c.titre
            FROM inscription i
            INNER JOIN etudiant e ON i.id_etudiant = e.id_etudiant
            INNER JOIN cours c ON i.id_cours = c.id_cours
            WHERE i.id_inscription = ?";
    $requete = mysqli_prepare($conn, $sql);

    if (!$requete) {
        return false;
    }

    mysqli_stmt_bind_param($requete, "i", $id_inscription);
    mysqli_stmt_execute($requete);
    $resultat = mysqli_stmt_get_result($requete);
    $inscription = mysqli_fetch_assoc($resultat);
    mysqli_stmt_close($requete);

    return $inscription;
}

function modifier_statut_inscription($conn, $id_inscription, $statut)
{
    $sql = "UPDATE inscription SET statut = ? WHERE id_inscription = ?";
    $requete = mysqli_prepare($conn, $sql);

    if (!$requete) {
        return false;
    }

    mysqli_stmt_bind_param($requete, "si", $statut, $id_inscription);
    $succes = mysqli_stmt_execute($requete);
    mysqli_stmt_close($requete);

    return $succes;
}

// Desinscription administrative : on garde l'historique et on change seulement le statut.
if (isset($_GET['desinscrire'])) {
    $id_inscription = intval($_GET['desinscrire']);
    $inscription = infos_inscription($conn, $id_inscription);

    if ($inscription && modifier_statut_inscription($conn, $id_inscription, 'desinscrit')) {
        creer_notification($conn, $inscription['id_user'], "Vous avez ete desinscrit du cours " . $inscription['titre'] . ".", "inscription");
        $message = "Etudiant desinscrit avec succes.";
        $type_message = "succes";
    } else {
        $message = "Erreur lors de la desinscription.";
        $type_message = "erreur";
    }
}

// Workflow admin : une demande etudiante arrive en_attente, puis l'admin valide ou refuse.
// Avant validation, les regles capacite / conflit horaire / double inscription sont appliquees.
if (isset($_POST['valider_demande']) || isset($_POST['refuser_demande'])) {
    $id_inscription = intval($_POST['id_inscription'] ?? 0);
    $inscription = infos_inscription($conn, $id_inscription);

    if (!$inscription || $inscription['statut'] !== 'en_attente') {
        $message = "Erreur : demande introuvable ou deja traitee.";
        $type_message = "erreur";
    } elseif (isset($_POST['refuser_demande'])) {
        if (modifier_statut_inscription($conn, $id_inscription, 'desinscrit')) {
            creer_notification($conn, $inscription['id_user'], "Votre demande d'inscription au cours " . $inscription['titre'] . " a ete refusee.", "inscription");
            $message = "Demande refusee.";
            $type_message = "succes";
        } else {
            $message = "Erreur lors du refus de la demande.";
            $type_message = "erreur";
        }
    } elseif (cours_est_complet($conn, $inscription['id_cours'])) {
        $raison = "cours complet";
        modifier_statut_inscription($conn, $id_inscription, 'desinscrit');
        creer_notification($conn, $inscription['id_user'], "Votre demande d'inscription au cours " . $inscription['titre'] . " a ete refusee : " . $raison . ".", "inscription");
        $message = "Validation refusee : ce cours est complet.";
        $type_message = "erreur";
    } elseif (conflit_horaire($conn, $inscription['id_etudiant'], $inscription['id_cours'])) {
        $raison = "conflit horaire";
        modifier_statut_inscription($conn, $id_inscription, 'desinscrit');
        creer_notification($conn, $inscription['id_user'], "Votre demande d'inscription au cours " . $inscription['titre'] . " a ete refusee : " . $raison . ".", "inscription");
        $message = "Validation refusee : conflit horaire.";
        $type_message = "erreur";
    } elseif (etudiant_deja_inscrit($conn, $inscription['id_etudiant'], $inscription['id_cours'])) {
        modifier_statut_inscription($conn, $id_inscription, 'desinscrit');
        creer_notification($conn, $inscription['id_user'], "Votre demande d'inscription au cours " . $inscription['titre'] . " a ete refusee : deja inscrit.", "inscription");
        $message = "Validation refusee : l'etudiant est deja inscrit a ce cours.";
        $type_message = "erreur";
    } elseif (modifier_statut_inscription($conn, $id_inscription, 'inscrit')) {
        creer_notification($conn, $inscription['id_user'], "Votre demande d'inscription au cours " . $inscription['titre'] . " a ete acceptee.", "inscription");
        $message = "Demande validee.";
        $type_message = "succes";
    } else {
        $message = "Erreur lors de la validation de la demande.";
        $type_message = "erreur";
    }
}

// Changement manuel de statut depuis la liste des inscriptions.
if (isset($_POST['changer_statut'])) {
    $id_inscription = intval($_POST['id_inscription'] ?? 0);
    $nouveau_statut = $_POST['statut'] ?? '';
    $statuts_autorises = ['inscrit', 'en_attente', 'desinscrit'];
    $inscription = infos_inscription($conn, $id_inscription);

    if (!$inscription || !in_array($nouveau_statut, $statuts_autorises, true)) {
        $message = "Erreur : statut invalide.";
        $type_message = "erreur";
    } elseif ($nouveau_statut === 'inscrit' && $inscription['statut'] !== 'inscrit') {
        if (cours_est_complet($conn, $inscription['id_cours'])) {
            $message = "Erreur : ce cours est complet.";
            $type_message = "erreur";
        } elseif (conflit_horaire($conn, $inscription['id_etudiant'], $inscription['id_cours'])) {
            $message = "Erreur : conflit horaire pour cet etudiant.";
            $type_message = "erreur";
        } elseif (modifier_statut_inscription($conn, $id_inscription, 'inscrit')) {
            creer_notification($conn, $inscription['id_user'], "Votre inscription au cours " . $inscription['titre'] . " est active.", "inscription");
            $message = "Inscription activee avec succes.";
            $type_message = "succes";
        }
    } elseif (modifier_statut_inscription($conn, $id_inscription, $nouveau_statut)) {
        if ($nouveau_statut === 'desinscrit') {
            creer_notification($conn, $inscription['id_user'], "Vous avez ete desinscrit du cours " . $inscription['titre'] . ".", "inscription");
        }
        $message = "Statut modifie avec succes.";
        $type_message = "succes";
    } else {
        $message = "Erreur lors de la modification du statut.";
        $type_message = "erreur";
    }
}

// Inscription directe par l'administrateur, avec les memes controles metier.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inscrire_etudiant'])) {
    $id_etudiant = (int) $_POST['id_etudiant'];
    $id_cours = (int) $_POST['id_cours'];
    $inscription_existante = false;

    if ($id_etudiant <= 0 || $id_cours <= 0) {
        $message = "Erreur : veuillez choisir un etudiant et un cours.";
        $type_message = "erreur";
    } else {
        $inscription_existante = recuperer_inscription($conn, $id_etudiant, $id_cours);
    }

    if ($message === "" && $inscription_existante && $inscription_existante['statut'] === 'inscrit') {
        $message = "Erreur : etudiant deja inscrit.";
        $type_message = "erreur";
    } elseif ($message === "" && cours_est_complet($conn, $id_cours)) {
        $message = "Erreur : ce cours est complet.";
        $type_message = "erreur";
    } elseif ($message === "" && conflit_horaire($conn, $id_etudiant, $id_cours)) {
        $message = "Erreur : cet etudiant a deja un cours sur ce creneau.";
        $type_message = "erreur";
    }

    if ($message === "") {
        $inscription_reussie = false;

        if ($inscription_existante) {
            if (reactiver_inscription($conn, $inscription_existante['id_inscription'])) {
                $message = "Inscription reactivee avec succes.";
                $type_message = "succes";
                $inscription_reussie = true;
            } else {
                $message = "Erreur lors de la reactivation de l'inscription : " . mysqli_error($conn);
                $type_message = "erreur";
            }
        } else {
            $statut = "inscrit";
            $sql_insert = "INSERT INTO inscription (id_etudiant, id_cours, statut)
                           VALUES (?, ?, ?)";
            $requete_insert = mysqli_prepare($conn, $sql_insert);

            if ($requete_insert) {
                mysqli_stmt_bind_param($requete_insert, "iis", $id_etudiant, $id_cours, $statut);

                if (mysqli_stmt_execute($requete_insert)) {
                    $message = "Inscription ajoutee avec succes.";
                    $type_message = "succes";
                    $inscription_reussie = true;
                } else {
                    $message = "Erreur lors de l'inscription : " . mysqli_error($conn);
                    $type_message = "erreur";
                }

                mysqli_stmt_close($requete_insert);
            } else {
                $message = "Erreur de preparation de la requete : " . mysqli_error($conn);
                $type_message = "erreur";
            }
        }

        if ($inscription_reussie) {
            $sql_etudiant = "SELECT id_user FROM etudiant WHERE id_etudiant = ?";
            $requete_etudiant = mysqli_prepare($conn, $sql_etudiant);

            if ($requete_etudiant) {
                mysqli_stmt_bind_param($requete_etudiant, "i", $id_etudiant);
                mysqli_stmt_execute($requete_etudiant);
                $result_etudiant = mysqli_stmt_get_result($requete_etudiant);
                $etudiant = mysqli_fetch_assoc($result_etudiant);
                mysqli_stmt_close($requete_etudiant);

                if ($etudiant) {
                    creer_notification($conn, $etudiant['id_user'], "Vous avez ete inscrit a un cours.", "inscription");
                }
            }
        }
    }
}

$result_etudiants = mysqli_query(
    $conn,
    "SELECT e.id_etudiant, e.numero_etudiant, u.nom, u.prenom
     FROM etudiant e
     INNER JOIN utilisateur u ON e.id_user = u.id_user
     WHERE u.actif = 1
     ORDER BY u.nom, u.prenom"
);

$result_cours = mysqli_query(
    $conn,
    "SELECT id_cours, code_cours, titre, jour, heure_debut, heure_fin
     FROM cours
     ORDER BY code_cours"
);

$where = [];
$types = "";
$params = [];

if ($filtre_cours > 0) {
    $where[] = "i.id_cours = ?";
    $types .= "i";
    $params[] = $filtre_cours;
}
if (in_array($filtre_statut, ['inscrit', 'en_attente', 'desinscrit'], true)) {
    $where[] = "i.statut = ?";
    $types .= "s";
    $params[] = $filtre_statut;
}

$sql_inscriptions = "SELECT i.id_inscription, i.date_inscription, i.statut,
                            u.nom, u.prenom, e.numero_etudiant,
                            c.code_cours, c.titre
                     FROM inscription i
                     INNER JOIN etudiant e ON i.id_etudiant = e.id_etudiant
                     INNER JOIN utilisateur u ON e.id_user = u.id_user
                     INNER JOIN cours c ON i.id_cours = c.id_cours";
if (!empty($where)) {
    $sql_inscriptions .= " WHERE " . implode(" AND ", $where);
}
$sql_inscriptions .= " ORDER BY i.date_inscription DESC";

if (!empty($params)) {
    $stmt_inscriptions = mysqli_prepare($conn, $sql_inscriptions);
    mysqli_stmt_bind_param($stmt_inscriptions, $types, ...$params);
    mysqli_stmt_execute($stmt_inscriptions);
    $result_inscriptions = mysqli_stmt_get_result($stmt_inscriptions);
} else {
    $result_inscriptions = mysqli_query($conn, $sql_inscriptions);
}

$result_demandes = mysqli_query(
    $conn,
    "SELECT i.id_inscription, i.date_inscription,
            u.nom, u.prenom, e.numero_etudiant,
            c.code_cours, c.titre
     FROM inscription i
     INNER JOIN etudiant e ON i.id_etudiant = e.id_etudiant
     INNER JOIN utilisateur u ON e.id_user = u.id_user
     INNER JOIN cours c ON i.id_cours = c.id_cours
     WHERE i.statut = 'en_attente'
     ORDER BY i.date_inscription ASC"
);

include '../includes/header.php';
?>

<section class="container">
<h2>Gestion des inscriptions</h2>
<p class="page-subtitle">Traiter les demandes, inscrire directement un etudiant et suivre les statuts.</p>

<?php if ($message !== "") { ?>
    <p class="<?php echo $type_message === 'succes' ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($message); ?></p>
<?php } ?>

<section class="section-card">
    <h3>Nouvelle inscription administrative</h3>

    <form method="post" action="inscriptions.php">
        <p>
            <label for="id_etudiant">Etudiant</label><br>
            <select id="id_etudiant" name="id_etudiant" required>
                <option value="">-- Choisir un etudiant --</option>
                <?php if ($result_etudiants) { ?>
                    <?php while ($etudiant = mysqli_fetch_assoc($result_etudiants)) { ?>
                        <option value="<?php echo htmlspecialchars($etudiant['id_etudiant']); ?>">
                            <?php echo htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom'] . ' - ' . $etudiant['numero_etudiant']); ?>
                        </option>
                    <?php } ?>
                <?php } ?>
            </select>
        </p>

        <p>
            <label for="id_cours">Cours</label><br>
            <select id="id_cours" name="id_cours" required>
                <option value="">-- Choisir un cours --</option>
                <?php if ($result_cours) { ?>
                    <?php mysqli_data_seek($result_cours, 0); ?>
                    <?php while ($cours = mysqli_fetch_assoc($result_cours)) { ?>
                        <option value="<?php echo htmlspecialchars($cours['id_cours']); ?>">
                            <?php echo htmlspecialchars($cours['code_cours'] . ' - ' . $cours['titre'] . ' (' . $cours['jour'] . ', ' . $cours['heure_debut'] . ' - ' . $cours['heure_fin'] . ')'); ?>
                        </option>
                    <?php } ?>
                <?php } ?>
            </select>
        </p>

        <button type="submit" name="inscrire_etudiant">Inscrire</button>
    </form>
</section>

<section class="section-card">
    <h3>Demandes en attente</h3>
    <p class="alert alert-info">La validation controle automatiquement la capacite, le conflit horaire et la double inscription.</p>

    <table>
        <thead>
            <tr>
                <th>etudiant</th>
                <th>cours</th>
                <th>date_demande</th>
                <th>actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_demandes && mysqli_num_rows($result_demandes) > 0) { ?>
                <?php while ($demande = mysqli_fetch_assoc($result_demandes)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($demande['prenom'] . ' ' . $demande['nom'] . ' - ' . $demande['numero_etudiant']); ?></td>
                        <td><?php echo htmlspecialchars($demande['code_cours'] . ' - ' . $demande['titre']); ?></td>
                        <td><?php echo htmlspecialchars($demande['date_inscription']); ?></td>
                        <td>
                            <form method="post" action="inscriptions.php">
                                <input type="hidden" name="id_inscription" value="<?php echo htmlspecialchars($demande['id_inscription']); ?>">
                                <button type="submit" name="valider_demande">Valider</button>
                                <button type="submit" name="refuser_demande" class="danger js-confirm-delete" data-confirm="Refuser cette demande ?">Refuser</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr><td colspan="4"><span class="empty-state">Aucune demande en attente.</span></td></tr>
            <?php } ?>
        </tbody>
    </table>
</section>

<section class="section-card">
    <h3>Liste des inscriptions</h3>

    <form method="get" action="inscriptions.php" class="toolbar">
        <select name="id_cours">
            <option value="0">Tous les cours</option>
            <?php if ($result_cours) { ?>
                <?php mysqli_data_seek($result_cours, 0); ?>
                <?php while ($cours = mysqli_fetch_assoc($result_cours)) { ?>
                    <option value="<?php echo htmlspecialchars($cours['id_cours']); ?>" <?php if ($filtre_cours === (int) $cours['id_cours']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($cours['code_cours'] . ' - ' . $cours['titre']); ?>
                    </option>
                <?php } ?>
            <?php } ?>
        </select>
        <select name="statut">
            <option value="">Tous les statuts</option>
            <option value="inscrit" <?php if ($filtre_statut === 'inscrit') echo 'selected'; ?>>Inscrit</option>
            <option value="en_attente" <?php if ($filtre_statut === 'en_attente') echo 'selected'; ?>>En attente</option>
            <option value="desinscrit" <?php if ($filtre_statut === 'desinscrit') echo 'selected'; ?>>Desinscrit</option>
        </select>
        <button type="submit">Filtrer</button>
        <a class="button secondary" href="inscriptions.php">Reinitialiser</a>
    </form>

    <table>
        <thead>
            <tr>
                <th>etudiant</th>
                <th>cours</th>
                <th>date_inscription</th>
                <th>statut</th>
                <th>actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_inscriptions && mysqli_num_rows($result_inscriptions) > 0) { ?>
                <?php while ($inscription = mysqli_fetch_assoc($result_inscriptions)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($inscription['prenom'] . ' ' . $inscription['nom'] . ' - ' . $inscription['numero_etudiant']); ?></td>
                        <td><?php echo htmlspecialchars($inscription['code_cours'] . ' - ' . $inscription['titre']); ?></td>
                        <td><?php echo htmlspecialchars($inscription['date_inscription']); ?></td>
                        <td>
                            <span class="badge <?php
                                if ($inscription['statut'] === 'inscrit') {
                                    echo 'badge-success';
                                } elseif ($inscription['statut'] === 'en_attente') {
                                    echo 'badge-warning';
                                } else {
                                    echo 'badge-muted';
                                }
                            ?>"><?php echo htmlspecialchars($inscription['statut']); ?></span>
                            <form method="post" action="inscriptions.php">
                                <input type="hidden" name="id_inscription" value="<?php echo htmlspecialchars($inscription['id_inscription']); ?>">
                                <select name="statut">
                                    <option value="inscrit" <?php if ($inscription['statut'] === 'inscrit') echo 'selected'; ?>>inscrit</option>
                                    <option value="en_attente" <?php if ($inscription['statut'] === 'en_attente') echo 'selected'; ?>>en_attente</option>
                                    <option value="desinscrit" <?php if ($inscription['statut'] === 'desinscrit') echo 'selected'; ?>>desinscrit</option>
                                </select>
                                <button type="submit" name="changer_statut">Changer</button>
                            </form>
                        </td>
                        <td>
                            <?php if ($inscription['statut'] === 'inscrit') { ?>
                                <a class="button danger js-confirm-delete" href="inscriptions.php?desinscrire=<?php echo htmlspecialchars($inscription['id_inscription']); ?>" data-confirm="Desinscrire cet etudiant ?">Desinscrire</a>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr><td colspan="5"><span class="empty-state">Aucune inscription trouvee.</span></td></tr>
            <?php } ?>
        </tbody>
    </table>
</section>

</section>
<?php include '../includes/footer.php'; ?>
