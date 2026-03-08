<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin('/pages/espace-utilisateur.php');

$userStmt = $pdo->prepare('SELECT nom, prenom, gsm, email, adresse_postale, role FROM utilisateurs WHERE id = :id LIMIT 1');
$userStmt->execute(['id' => (int) $_SESSION['user_id']]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: /pages/logout.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace utilisateur - Vite&Gourmande</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/styles/style.css">
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-bordeaux fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/pages/index.html">Vite&Gourmande</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-lg-auto text-center">
                    <li class="nav-item"><a class="nav-link" href="/pages/index.html">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="/pages/commande.php">Commander</a></li>
                    <li class="nav-item"><a class="nav-link" href="/pages/mes-commandes.php">Mes commandes</a></li>
                    <li class="nav-item mt-2 mt-lg-0"><a class="btn btn-light btn-sm ms-lg-2" href="/pages/logout.php">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<main class="container py-5" style="max-width: 760px;">
    <section class="bg-white rounded shadow-sm p-4">
        <h2 class="mb-4">Mon espace utilisateur</h2>
        <p class="mb-3">Bienvenue <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>.</p>

        <ul class="list-group mb-4">
            <li class="list-group-item"><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></li>
            <li class="list-group-item"><strong>GSM :</strong> <?php echo htmlspecialchars($user['gsm']); ?></li>
            <li class="list-group-item"><strong>Adresse :</strong> <?php echo htmlspecialchars($user['adresse_postale']); ?></li>
            <li class="list-group-item"><strong>Rôle :</strong> <?php echo htmlspecialchars($user['role']); ?></li>
        </ul>

        <a class="btn btn-primary" href="/pages/commande.php">Nouvelle commande</a>
        <a class="btn btn-outline-secondary" href="/pages/mes-commandes.php">Voir mes commandes</a>
    </section>
</main>

<footer class="bg-bordeaux text-white text-center p-4">
    <p class="mb-1">Horaires : Du lundi à dimanche de 11H à 23H</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
