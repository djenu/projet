<?php
// Configuration de la connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "djenu2005";
$dbname = "etudiantsDB";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

// Traitement des requêtes POST pour ajouter, modifier ou supprimer des étudiants
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM etudiants WHERE id='$id'";
        if ($conn->query($sql) === TRUE) {
            echo "Record supprimé avec succès";
        } else {
            echo "Erreur de suppression: " . $conn->error;
        }
    } else {
        $id = $_POST['id'] ?? null;
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $matiere = $_POST['matiere'];
        $tp = $_POST['tp'] ?? null;
        $cc = $_POST['cc'] ?? null;
        $sn = $_POST['sn'] ?? null;

        $column_tp = "matiere_${matiere}_tp";
        $column_cc = "matiere_${matiere}_cc";
        $column_sn = "matiere_${matiere}_sn";

        if ($id) {
            $sql = "UPDATE etudiants SET nom='$nom', prenom='$prenom', $column_tp='$tp', $column_cc='$cc', $column_sn='$sn' WHERE id='$id'";
        } else {
            $sql = "INSERT INTO etudiants (nom, prenom, $column_tp, $column_cc, $column_sn) VALUES ('$nom', '$prenom', '$tp', '$cc', '$sn')";
        }

        if ($conn->query($sql) === TRUE) {
            echo $id ? "Record mis à jour avec succès" : "Nouveau record créé avec succès";
        } else {
            echo "Erreur: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Récupération des données pour l'affichage
$matiere = $_GET['matiere'] ?? '104';
$sql = "SELECT * FROM etudiants";
$result = $conn->query($sql);

$etudiants = array();
while ($row = $result->fetch_assoc()) {
    $etudiants[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Notes - Matière <?php echo htmlspecialchars($matiere); ?></title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { width: 80%; margin: auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
        input[type="text"], input[type="number"] { width: 100%; padding: 8px; margin: 4px 0; }
        button { padding: 8px 16px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #45a049; }
        .hidden { display: none; }
        .search { margin: 20px 0; }
    </style>
</head>
<body>

    <div class="container">
        <h1>Gestion des Notes - Matière <?php echo htmlspecialchars($matiere); ?></h1>
        
        <!-- Menu principal -->
        <a href="index.php">Retour au Menu Principal</a>

        <!-- Formulaire de recherche -->
        <form method="GET" class="search">
            <input type="hidden" name="matiere" value="<?php echo htmlspecialchars($matiere); ?>">
            <input type="text" name="search" placeholder="Rechercher un étudiant...">
            <button type="submit">Rechercher</button>
        </form>

        <!-- Table des étudiants -->
        <h2>Liste des Étudiants</h2>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Note TP</th>
                    <th>Note CC</th>
                    <th>Note SN</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($etudiants as $student): ?>
                    <?php if (empty($_GET['search']) || strpos(strtolower($student['nom']), strtolower($_GET['search'])) !== false || strpos(strtolower($student['prenom']), strtolower($_GET['search'])) !== false): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['nom']); ?></td>
                            <td><?php echo htmlspecialchars($student['prenom']); ?></td>
                            <td><?php echo htmlspecialchars($student["matiere_${matiere}_tp"] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($student["matiere_${matiere}_cc"] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($student["matiere_${matiere}_sn"] ?? ''); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($student['id']); ?>">
                                    <input type="hidden" name="matiere" value="<?php echo htmlspecialchars($matiere); ?>">
                                    <input type="text" name="nom" value="<?php echo htmlspecialchars($student['nom']); ?>" required>
                                    <input type="text" name="prenom" value="<?php echo htmlspecialchars($student['prenom']); ?>" required>
                                    <input type="number" name="tp" value="<?php echo htmlspecialchars($student["matiere_${matiere}_tp"] ?? ''); ?>">
                                    <input type="number" name="cc" value="<?php echo htmlspecialchars($student["matiere_${matiere}_cc"] ?? ''); ?>">
                                    <input type="number" name="sn" value="<?php echo htmlspecialchars($student["matiere_${matiere}_sn"] ?? ''); ?>">
                                    <button type="submit">Modifier</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($student['id']); ?>">
                                    <button type="submit" name="delete">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Formulaire d'ajout -->
        <h2>Ajouter un Étudiant</h2>
        <form method="POST">
            <input type="hidden" name="id">
            <input type="hidden" name="matiere" value="<?php echo htmlspecialchars($matiere); ?>">
            <input type="text" name="nom" placeholder="Nom" required>
            <input type="text" name="prenom" placeholder="Prénom" required>
            <input type="number" name="tp" placeholder="Note TP">
            <input type="number" name="cc" placeholder="Note CC">
            <input type="number" name="sn" placeholder="Note SN">
            <button type="submit">Ajouter</button>
        </form>
    </div>

</body>
</html>

