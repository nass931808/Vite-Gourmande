<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin('/pages/mes-commandes.php');

$stmt = $pdo->prepare(
    'SELECT c.id, c.created_at, c.date_prestation, c.heure_livraison, c.lieu_livraison, c.ville_livraison,
            c.nb_personnes, c.prix_total, c.statut, m.nom AS menu_nom
     FROM commandes c
     INNER JOIN menus m ON m.id = c.menu_id
     WHERE c.utilisateur_id = :utilisateur_id
     ORDER BY c.created_at DESC'
);
$stmt->execute(['utilisateur_id' => (int) $_SESSION['user_id']]);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes commandes - Vite&Gourmande</title>
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
                    <li class="nav-item mt-2 mt-lg-0"><a class="btn btn-light btn-sm ms-lg-2" href="/pages/logout.php">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<main class="container py-5">
    <section class="bg-white rounded shadow-sm p-4">
        <h2 class="mb-4">Mes commandes</h2>

        <?php if (empty($commandes)): ?>
            <p class="mb-0">Aucune commande pour le moment.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Menu</th>
                            <th>Prestation</th>
                            <th>Lieu</th>
                            <th>Personnes</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Date commande</th>
                            <th>Suivi</th>
                            <th>Avis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commandes as $commande): ?>
                            <tr>
                                <td><?php echo (int) $commande['id']; ?></td>
                                <td><?php echo htmlspecialchars($commande['menu_nom']); ?></td>
                                <td><?php echo htmlspecialchars($commande['date_prestation'] . ' ' . $commande['heure_livraison']); ?></td>
                                <td><?php echo htmlspecialchars($commande['lieu_livraison'] . ', ' . $commande['ville_livraison']); ?></td>
                                <td><?php echo (int) $commande['nb_personnes']; ?></td>
                                <td><?php echo number_format((float) $commande['prix_total'], 2, ',', ' '); ?> EUR</td>
                                <td><?php echo htmlspecialchars($commande['statut']); ?></td>
                                <td><?php echo htmlspecialchars($commande['created_at']); ?></td>
                                <td><a class="btn btn-sm btn-outline-primary" href="/pages/suivi-commande.php?id=<?php echo (int) $commande['id']; ?>">Voir</a></td>
                                <td>
                                    <?php if (in_array($commande['statut'], ['livre', 'terminee'], true)): ?>
                                        <a class="btn btn-sm btn-outline-secondary" href="/pages/donner-avis.php?id=<?php echo (int) $commande['id']; ?>">Donner un avis</a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<footer class="bg-bordeaux text-white text-center p-4">
    <p class="mb-1">Horaires : Du lundi à dimanche de 11H à 23H</p>
    <small>
        <a href="/pages/mentions-legales.php" class="text-white text-decoration-none">Mentions légales</a> |
        <a href="/pages/mentions-legales.php" class="text-white text-decoration-none">Politique de confidentialité</a>
    </small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>




