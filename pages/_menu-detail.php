<?php
require_once __DIR__ . '/../config/database.php';

$menuId = (int) ($menuId ?? 0);
$menuStmt = $pdo->prepare(
    'SELECT id, nom, description, nb_personnes_min, prix_minimum
     FROM menus
     WHERE id = :id AND actif = TRUE
     LIMIT 1'
);
$menuStmt->execute(['id' => $menuId]);
$menu = $menuStmt->fetch(PDO::FETCH_ASSOC);

if (!$menu) {
    http_response_code(404);
    exit('Menu introuvable.');
}

$platsQuery = 'SELECT nom, type, allergenes, stock
               FROM plats
               WHERE menu_id = :menu_id
               ORDER BY CASE type
                  WHEN \'entree\' THEN 1
                  WHEN \'plat\' THEN 2
                  WHEN \'dessert\' THEN 3
                  WHEN \'boisson\' THEN 4
                  ELSE 5
               END, nom';

try {
    $platsStmt = $pdo->prepare($platsQuery);
    $platsStmt->execute(['menu_id' => $menuId]);
    $plats = $platsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    // Compatibilité avec une base existante avant l'ajout des colonnes détail.
    $platsStmt = $pdo->prepare(
        'SELECT nom, type
         FROM plats
         WHERE menu_id = :menu_id
         ORDER BY CASE type
            WHEN \'entree\' THEN 1
            WHEN \'plat\' THEN 2
            WHEN \'dessert\' THEN 3
            WHEN \'boisson\' THEN 4
            ELSE 5
         END, nom'
    );
    $platsStmt->execute(['menu_id' => $menuId]);
    $plats = $platsStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($plats as &$plat) {
        $plat['allergenes'] = null;
        $plat['stock'] = null;
    }
    unset($plat);
}

$avis = [];
try {
    $avisStmt = $pdo->prepare(
        'SELECT a.note, a.commentaire, a.created_at, u.prenom
         FROM avis a
         INNER JOIN commandes c ON c.id = a.commande_id
         INNER JOIN utilisateurs u ON u.id = a.utilisateur_id
         WHERE c.menu_id = :menu_id AND a.statut = :statut
         ORDER BY a.created_at DESC
         LIMIT 10'
    );
    $avisStmt->execute(['menu_id' => $menuId, 'statut' => 'valide']);
    $avis = $avisStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    error_log('Avis indisponibles sur le détail du menu : ' . $exception->getMessage());
}
$noteMoyenne = $avis
    ? array_sum(array_column($avis, 'note')) / count($avis)
    : null;
$avisDemo = [
    [
        'note' => 5,
        'commentaire' => 'Une composition soignée et adaptée aux repas de fête.',
        'prenom' => 'Sophie Martin',
    ],
    [
        'note' => 4,
        'commentaire' => 'Des plats variés et une formule facile à organiser.',
        'prenom' => 'Thomas Bernard',
    ],
    [
        'note' => 5,
        'commentaire' => 'Un menu gourmand et une organisation simple pour recevoir nos invités.',
        'prenom' => 'Claire Dubois',
    ],
];

$types = [
    'entree' => 'Entrées',
    'plat' => 'Plats',
    'dessert' => 'Desserts',
    'boisson' => 'Boissons',
];
$platsParType = [];
foreach ($plats as $plat) {
    $platsParType[$plat['type']][] = $plat;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($menu['nom']); ?> - Vite&Gourmande</title>
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
                    <li class="nav-item"><a class="nav-link active" href="/pages/menus.php">Menus</a></li>
                    <li class="nav-item"><a class="nav-link" href="/pages/contact.php">Contact</a></li>
                    <li class="nav-item mt-2 mt-lg-0"><a class="btn btn-light btn-sm ms-lg-2" href="/pages/login.php">Connexion</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<main class="container py-5 menu-detail">
    <section class="bg-white rounded shadow-sm p-4 mb-4">
        <h1 class="h2"><?php echo htmlspecialchars($menu['nom']); ?></h1>
        <p><?php echo htmlspecialchars($menu['description'] ?? ''); ?></p>
        <div class="row g-3 menu-conditions">
            <div class="col-12 col-md-4"><strong>Conditions</strong><br>Minimum <?php echo (int) $menu['nb_personnes_min']; ?> personnes</div>
            <div class="col-12 col-md-4"><strong>Tarif</strong><br>À partir de <?php echo number_format((float) $menu['prix_minimum'], 2, ',', ' '); ?> €</div>
            <div class="col-12 col-md-4">
                <strong>Avis clients</strong><br>
                <?php if ($noteMoyenne !== null): ?>
                    <?php echo number_format($noteMoyenne, 1, ',', ' '); ?>/5 (<?php echo count($avis); ?> avis)
                <?php else: ?>
                    Aucun avis publié pour le moment
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="bg-white rounded shadow-sm p-4 mb-4">
        <h2 class="h4 mb-3">Composition, allergènes et disponibilité</h2>
        <?php foreach ($types as $type => $label): ?>
            <?php if (!empty($platsParType[$type])): ?>
                <h3 class="h5 mt-3"><?php echo $label; ?></h3>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Produit</th><th>Allergènes</th><th>Stock</th></tr></thead>
                        <tbody>
                        <?php foreach ($platsParType[$type] as $plat): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($plat['nom']); ?></td>
                                <td><?php echo htmlspecialchars($plat['allergenes'] ?: 'Aucun allergène renseigné'); ?></td>
                                <td>
                                    <?php if ($plat['stock'] === null): ?>
                                        <span class="text-muted">Stock à confirmer</span>
                                    <?php elseif ((int) $plat['stock'] > 0): ?>
                                        <span class="text-success"><?php echo (int) $plat['stock']; ?> disponible(s)</span>
                                    <?php else: ?>
                                        <span class="text-danger">Rupture de stock</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        <p class="small text-muted mb-0">Les allergènes indiqués correspondent aux recettes prévues et peuvent varier selon les ingrédients ou la préparation. En cas d’allergie, contactez-nous avant toute commande.</p>
    </section>

    <section class="bg-white rounded shadow-sm p-4 mb-4">
        <h2 class="h4 mb-3">Avis clients</h2>
        <p class="mb-3">Vous avez déjà commandé ce menu ? Partagez votre expérience avec nos futurs clients.</p>
        <a href="/pages/ajouter-avis.php" class="btn btn-primary mb-4">Ajouter un avis</a>
        <?php if (!$avis): ?>
            <p class="alert alert-info">
                Exemples d'avis affichés pour présenter le rendu de la page.
            </p>
            <div class="row g-3">
                <?php foreach ($avisDemo as $avisClient): ?>
                    <article class="col-12 col-md-6">
                        <div class="border rounded p-3 h-100">
                            <p class="mb-1" aria-label="Note de démonstration : <?php echo (int) $avisClient['note']; ?> sur 5">
                                <strong><?php echo str_repeat('★', (int) $avisClient['note']); ?></strong>/5
                            </p>
                            <p class="mb-2"><?php echo htmlspecialchars($avisClient['commentaire']); ?></p>
                            <small class="text-muted"><?php echo htmlspecialchars($avisClient['prenom']); ?></small>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($avis as $avisClient): ?>
                    <article class="col-12 col-md-6">
                        <div class="border rounded p-3 h-100">
                            <p class="mb-1" aria-label="Note : <?php echo (int) $avisClient['note']; ?> sur 5">
                                <strong><?php echo str_repeat('★', (int) $avisClient['note']); ?></strong>/5
                            </p>
                            <p class="mb-2"><?php echo htmlspecialchars($avisClient['commentaire']); ?></p>
                            <small class="text-muted">Par <?php echo htmlspecialchars($avisClient['prenom']); ?>, le <?php echo date('d/m/Y', strtotime($avisClient['created_at'])); ?></small>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <div class="text-center">
        <a href="/pages/menus.php" class="menu-action-btn menu-action-btn-retour">Retour aux menus</a>
        <a href="/pages/commande.php?menu_id=<?php echo $menuId; ?>" class="menu-action-btn menu-action-btn-commander">Commander ce menu</a>
    </div>
</main>

<footer class="bg-bordeaux text-white text-center p-4">
    <p class="mb-1">Horaires : Du lundi à dimanche de 11H à 23H</p>
    <small><a href="/pages/mentions-legales.php" class="text-white text-decoration-none">Mentions légales</a> | <a href="/pages/mentions-legales.php" class="text-white text-decoration-none">Politique de confidentialité</a></small>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
