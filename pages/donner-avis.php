<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin('/pages/mes-commandes.php');

$commandeId = (int) ($_GET['id'] ?? $_POST['commande_id'] ?? 0);
if ($commandeId <= 0) {
    header('Location: /pages/mes-commandes.php');
    exit;
}

$commandeStmt = $pdo->prepare(
    'SELECT c.id, c.statut, m.nom AS menu_nom
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

$avisExistantStmt = $pdo->prepare('SELECT id, statut FROM avis WHERE commande_id = :commande_id LIMIT 1');
$avisExistantStmt->execute(['commande_id' => $commandeId]);
$avisExistant = $avisExistantStmt->fetch(PDO::FETCH_ASSOC);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note = (int) ($_POST['note'] ?? 0);
    $commentaire = trim($_POST['commentaire'] ?? '');

    if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $message = 'Session expirée. Merci de recharger la page.';
        $messageType = 'danger';
    } elseif (!in_array($commande['statut'], ['livre', 'terminee'], true)) {
        $message = 'Vous pouvez laisser un avis seulement quand la commande est livrée ou terminée.';
        $messageType = 'danger';
    } elseif ($avisExistant) {
        $message = 'Un avis existe déjà pour cette commande.';
        $messageType = 'warning';
    } elseif ($note < 1 || $note > 5 || $commentaire === '') {
        $message = 'Merci de saisir une note entre 1 et 5 et un commentaire.';
        $messageType = 'danger';
    } else {
        $insertAvis = $pdo->prepare(
            'INSERT INTO avis (commande_id, utilisateur_id, note, commentaire, statut)
             VALUES (:commande_id, :utilisateur_id, :note, :commentaire, :statut)'
        );
        $insertAvis->execute([
            'commande_id' => $commandeId,
            'utilisateur_id' => (int) $_SESSION['user_id'],
            'note' => $note,
            'commentaire' => $commentaire,
            'statut' => 'en_attente',
        ]);

        $message = 'Merci, votre avis a été envoyé et sera visible après validation.';
        $messageType = 'success';

        $avisExistantStmt->execute(['commande_id' => $commandeId]);
        $avisExistant = $avisExistantStmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donner un avis - Vite&Gourmande</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/styles/style.css">
</head>
<body>
<main class="container py-5" style="max-width: 760px;">
    <section class="bg-white rounded shadow-sm p-4">
        <h2 class="mb-3">Donner un avis</h2>
        <p class="mb-1"><strong>Commande:</strong> #<?php echo (int) $commande['id']; ?></p>
        <p class="mb-4"><strong>Menu:</strong> <?php echo htmlspecialchars($commande['menu_nom']); ?></p>

        <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($avisExistant): ?>
            <p class="mb-4">Votre avis a déjà été envoyé (statut : <strong><?php echo htmlspecialchars($avisExistant['statut']); ?></strong>).</p>
        <?php else: ?>
            <form method="post" action="/pages/donner-avis.php">
                <input type="hidden" name="commande_id" value="<?php echo (int) $commandeId; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">

                <div class="mb-3">
                    <label class="form-label" for="note">Note (1 à 5)</label>
                    <select id="note" name="note" class="form-select" required>
                        <option value="">Choisir</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="commentaire">Commentaire</label>
                    <textarea id="commentaire" name="commentaire" class="form-control" rows="4" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Envoyer mon avis</button>
            </form>
        <?php endif; ?>

        <a class="btn btn-outline-secondary mt-3" href="/pages/mes-commandes.php">Retour à mes commandes</a>
    </section>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
