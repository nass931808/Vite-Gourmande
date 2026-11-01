<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin('/pages/ajouter-avis.php');

$commandesStmt = $pdo->prepare(
    'SELECT c.id, c.statut, c.date_prestation, m.nom AS menu_nom, a.id AS avis_id
     FROM commandes c
     INNER JOIN menus m ON m.id = c.menu_id
     LEFT JOIN avis a ON a.commande_id = c.id
     WHERE c.utilisateur_id = :utilisateur_id
       AND c.statut IN (\'livre\', \'terminee\')
     ORDER BY c.date_prestation DESC, c.id DESC'
);
$commandesStmt->execute(['utilisateur_id' => (int) $_SESSION['user_id']]);
$commandes = $commandesStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un avis - Vite&Gourmande</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/styles/style.css">
</head>
<body>
<main class="container py-5" style="max-width: 760px;">
    <section class="bg-white rounded shadow-sm p-4">
        <h1 class="h2 mb-3">Ajouter un avis</h1>
        <p class="mb-4">Sélectionnez une commande livrée ou terminée pour partager votre expérience.</p>

        <?php if (!$commandes): ?>
            <div class="alert alert-info">
                Vous pourrez laisser un avis après la livraison ou la fin d'une commande.
            </div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($commandes as $commande): ?>
                    <div class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <strong>Commande #<?php echo (int) $commande['id']; ?></strong>
                            <br>
                            <span><?php echo htmlspecialchars($commande['menu_nom']); ?></span>
                            <small class="d-block text-muted">
                                Prestation du <?php echo date('d/m/Y', strtotime($commande['date_prestation'])); ?>
                            </small>
                        </div>
                        <?php if ($commande['avis_id']): ?>
                            <span class="badge text-bg-secondary">Avis déjà envoyé</span>
                        <?php else: ?>
                            <a class="btn btn-primary" href="/pages/donner-avis.php?id=<?php echo (int) $commande['id']; ?>">
                                Écrire un avis
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a class="btn btn-outline-secondary mt-4" href="/pages/menus.php">Retour aux menus</a>
    </section>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
