<?php
// Connexion à la base de données
require_once __DIR__ . '/../config/database.php';

// ID du menu de Noël
$menuId = 1;

// Récupération des plats
$sql = "SELECT nom, type FROM plats WHERE menu_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$menuId]);
$plats = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vite&Gourmande</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/styles/style.css">
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-bordeaux fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/pages/index.html">Vite&Gourmande</a>
            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-lg-auto text-center">
                    <li class="nav-item"><a class="nav-link" href="/pages/index.html">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="/pages/contact.php">Contact</a></li>
                    <li class="nav-item mt-2 mt-lg-0">
                        <a class="btn btn-light btn-sm ms-lg-2" href="/pages/login.php">Connexion</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
    
    <section class="menu-detail" style="padding: 40px;text-align: center;">
        <h2>Menu de Noël</h2>

        <h3>Entrée</h3>
        <ul>
            <?php foreach ($plats as $plat) {
                $type = strtolower(trim((string) $plat['type']));
                $type = str_replace(array('é', 'è', 'ê', 'ë'), 'e', $type);
                if ($type === 'entree') {
                    echo "<li>{$plat['nom']}</li>";
                }
            } ?>
        </ul>

        <h3>Plat</h3>
        <ul>
            <?php foreach ($plats as $plat) {
                $type = strtolower(trim((string) $plat['type']));
                if ($type === 'plat') {
                    echo "<li>{$plat['nom']}</li>";
                }
            } ?>
        </ul>

        <h3>Dessert</h3>
        <ul>
            <?php foreach ($plats as $plat) {
                $type = strtolower(trim((string) $plat['type']));
                if ($type === 'dessert') {
                    echo "<li>{$plat['nom']}</li>";
                }
            } ?>
        </ul>

        <a href="/pages/index.html" class="menu-action-btn menu-action-btn-retour">Retour à l'accueil</a>
        <a href="/pages/commande.php?menu_id=<?php echo $menuId; ?>" class="menu-action-btn menu-action-btn-commander">Commander ce menu</a>
    </section>
    
<footer class="bg-bordeaux text-white text-center p-4">
           <p class="mb-1">Horaires : Du lundi à dimanche de 11H à 23H</p>
    <small>
       <a href="/pages/mentions-legales.php" class="text-white text-decoration-none">Mentions légales</a> |
       <a href="/pages/mentions-legales.php" class="text-white text-decoration-none">Politique de confidentialité</a>
    </small>
</footer>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>




