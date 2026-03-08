<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin('/pages/espace-employe.php');
requireRole(['employe', 'administrateur']);

$message = '';
$messageType = '';
$allowedStatuts = ['en_attente', 'accepte', 'en_preparation', 'en_cours_de_livraison', 'livre', 'terminee', 'annulee'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = trim($_POST['form_type'] ?? 'commande');

    if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $message = 'Session expirée. Merci de recharger la page.';
        $messageType = 'danger';
    } elseif ($formType === 'avis') {
        $avisId = (int) ($_POST['avis_id'] ?? 0);
        $avisDecision = trim($_POST['avis_decision'] ?? '');

        if ($avisId <= 0 || !in_array($avisDecision, ['valide', 'refuse'], true)) {
            $message = 'Mise à jour d\'avis invalide.';
            $messageType = 'danger';
        } else {
            $avisUpdate = $pdo->prepare('UPDATE avis SET statut = :statut WHERE id = :id');
            $avisUpdate->execute([
                'statut' => $avisDecision,
                'id' => $avisId,
            ]);

            $message = 'Avis mis à jour.';
            $messageType = 'success';
        }
    } else {
        $commandeId = (int) ($_POST['commande_id'] ?? 0);
        $newStatut = trim($_POST['new_statut'] ?? '');

        if ($commandeId <= 0 || !in_array($newStatut, $allowedStatuts, true)) {
            $message = 'Mise à jour invalide.';
            $messageType = 'danger';
        } else {
            $updateStmt = $pdo->prepare('UPDATE commandes SET statut = :statut WHERE id = :id');
            $updateStmt->execute([
                'statut' => $newStatut,
                'id' => $commandeId,
            ]);

            $suiviStmt = $pdo->prepare(
                'INSERT INTO commande_suivis (commande_id, statut, commentaire) VALUES (:commande_id, :statut, :commentaire)'
            );
            $suiviStmt->execute([
                'commande_id' => $commandeId,
                'statut' => $newStatut,
                'commentaire' => 'Statut changé par employé',
            ]);

            $message = 'Statut mis à jour.';
            $messageType = 'success';
        }
    }
}

$statutFiltre = trim($_GET['statut'] ?? '');

$query = 'SELECT c.id, c.created_at, c.statut, c.date_prestation, c.nb_personnes, c.prix_total,
                 c.nom_client, c.prenom_client, m.nom AS menu_nom
          FROM commandes c
          INNER JOIN menus m ON m.id = c.menu_id';

$params = [];
if ($statutFiltre !== '' && in_array($statutFiltre, $allowedStatuts, true)) {
    $query .= ' WHERE c.statut = :statut';
    $params['statut'] = $statutFiltre;
}

$query .= ' ORDER BY c.created_at DESC';

$listStmt = $pdo->prepare($query);
$listStmt->execute($params);
$commandes = $listStmt->fetchAll(PDO::FETCH_ASSOC);

$avisStmt = $pdo->query(
    'SELECT a.id, a.note, a.commentaire, a.created_at, a.statut,
            u.prenom, u.nom, c.id AS commande_id, m.nom AS menu_nom
     FROM avis a
     INNER JOIN utilisateurs u ON u.id = a.utilisateur_id
     INNER JOIN commandes c ON c.id = a.commande_id
     INNER JOIN menus m ON m.id = c.menu_id
     WHERE a.statut = "en_attente"
     ORDER BY a.created_at DESC'
);
$avisEnAttente = $avisStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace employé - Vite&Gourmande</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/styles/style.css">
</head>
<body>
<main class="container py-5" style="max-width: 700px;">
    <section class="bg-white rounded shadow-sm p-4">
        <h2 class="mb-3">Espace employé</h2>
        <p>Accès réservé aux employés et administrateurs.</p>

        <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form class="row g-2 mb-4" method="get" action="/pages/espace-employe.php">
            <div class="col-12 col-md-8">
                <select class="form-select" name="statut">
                    <option value="">Tous les statuts</option>
                    <?php foreach ($allowedStatuts as $statut): ?>
                        <option value="<?php echo htmlspecialchars($statut); ?>" <?php echo ($statut === $statutFiltre) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($statut); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <button class="btn btn-outline-primary w-100" type="submit">Filtrer</button>
            </div>
        </form>

        <?php if (empty($commandes)): ?>
            <p class="mb-4">Aucune commande trouvée.</p>
        <?php else: ?>
            <div class="table-responsive mb-4">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Client</th>
                            <th>Menu</th>
                            <th>Prestation</th>
                            <th>Prix</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commandes as $commande): ?>
                            <tr>
                                <td><?php echo (int) $commande['id']; ?></td>
                                <td><?php echo htmlspecialchars($commande['prenom_client'] . ' ' . $commande['nom_client']); ?></td>
                                <td><?php echo htmlspecialchars($commande['menu_nom']); ?></td>
                                <td><?php echo htmlspecialchars($commande['date_prestation']); ?> (<?php echo (int) $commande['nb_personnes']; ?> pers)</td>
                                <td><?php echo number_format((float) $commande['prix_total'], 2, ',', ' '); ?> EUR</td>
                                <td><?php echo htmlspecialchars($commande['statut']); ?></td>
                                <td>
                                    <form method="post" action="/pages/espace-employe.php<?php echo ($statutFiltre !== '' ? '?statut=' . urlencode($statutFiltre) : ''); ?>" class="d-flex gap-2">
                                        <input type="hidden" name="form_type" value="commande">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                                        <input type="hidden" name="commande_id" value="<?php echo (int) $commande['id']; ?>">
                                        <select name="new_statut" class="form-select form-select-sm" required>
                                            <?php foreach ($allowedStatuts as $statut): ?>
                                                <option value="<?php echo htmlspecialchars($statut); ?>" <?php echo ($statut === $commande['statut']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($statut); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">OK</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <hr>
        <h3 class="h5">Avis en attente de validation</h3>
        <?php if (empty($avisEnAttente)): ?>
            <p class="mb-4">Aucun avis en attente.</p>
        <?php else: ?>
            <div class="table-responsive mb-4">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Commande</th>
                            <th>Client</th>
                            <th>Menu</th>
                            <th>Note</th>
                            <th>Commentaire</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($avisEnAttente as $avis): ?>
                            <tr>
                                <td>#<?php echo (int) $avis['commande_id']; ?></td>
                                <td><?php echo htmlspecialchars($avis['prenom'] . ' ' . $avis['nom']); ?></td>
                                <td><?php echo htmlspecialchars($avis['menu_nom']); ?></td>
                                <td><?php echo (int) $avis['note']; ?>/5</td>
                                <td><?php echo htmlspecialchars($avis['commentaire']); ?></td>
                                <td><?php echo htmlspecialchars($avis['created_at']); ?></td>
                                <td>
                                    <form method="post" action="/pages/espace-employe.php" class="d-flex gap-2">
                                        <input type="hidden" name="form_type" value="avis">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                                        <input type="hidden" name="avis_id" value="<?php echo (int) $avis['id']; ?>">
                                        <button name="avis_decision" value="valide" type="submit" class="btn btn-sm btn-success">Valider</button>
                                        <button name="avis_decision" value="refuse" type="submit" class="btn btn-sm btn-outline-danger">Refuser</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <a class="btn btn-primary" href="/pages/index.html">Retour à l'accueil</a>
    </section>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
