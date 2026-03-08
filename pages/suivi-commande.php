<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin('/pages/mes-commandes.php');

$commandeId = (int) ($_GET['id'] ?? 0);
if ($commandeId <= 0) {
    header('Location: /pages/mes-commandes.php');
    exit;
}

$commandeStmt = $pdo->prepare(
    'SELECT c.id, c.statut, c.created_at, m.nom AS menu_nom
     FROM commandes c
     INNER JOIN menus m ON m.id = c.menu_id
     WHERE c.id = :id AND c.utilisateur_id = :utilisateur_id
     LIMIT 1'
);
$commandeStmt->execute([
    'id' => $commandeId,
    'utilisateur_id' => (int) $_SESSION['user_id'],
]);
$commande = $commandeStmt->fetch(PDO::FETCH_ASSOC);

if (!$commande) {
    header('Location: /pages/mes-commandes.php');
    exit;
}

$suivisStmt = $pdo->prepare(
    'SELECT statut, commentaire, created_at
     FROM commande_suivis
     WHERE commande_id = :commande_id
     ORDER BY created_at ASC'
);
$suivisStmt->execute(['commande_id' => $commandeId]);
$suivis = $suivisStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi de commande - Vite&Gourmande</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/styles/style.css">
</head>
<body>
<main class="container py-5" style="max-width: 760px;">
    <section class="bg-white rounded shadow-sm p-4">
        <h2 class="mb-3">Suivi de commande #<?php echo (int) $commande['id']; ?></h2>
        <p class="mb-1"><strong>Menu :</strong> <?php echo htmlspecialchars($commande['menu_nom']); ?></p>
        <p class="mb-4"><strong>Statut actuel :</strong> <?php echo htmlspecialchars($commande['statut']); ?></p>

        <?php if (empty($suivis)): ?>
            <p>Aucun historique disponible.</p>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($suivis as $suivi): ?>
                    <li class="list-group-item">
                        <strong><?php echo htmlspecialchars($suivi['statut']); ?></strong>
                        <span class="text-muted"> - <?php echo htmlspecialchars($suivi['created_at']); ?></span>
                        <?php if (!empty($suivi['commentaire'])): ?>
                            <div><?php echo htmlspecialchars($suivi['commentaire']); ?></div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (in_array($commande['statut'], ['livre', 'terminee'], true)): ?>
            <a class="btn btn-primary mt-4" href="/pages/donner-avis.php?id=<?php echo (int) $commande['id']; ?>">Donner un avis</a>
        <?php endif; ?>

        <a class="btn btn-outline-secondary mt-4" href="/pages/mes-commandes.php">Retour à mes commandes</a>
    </section>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
