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

$platsQuery = 'SELECT id, nom, type, allergenes
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

    try {
        $allergenesStmt = $pdo->prepare(
            'SELECT pa.plat_id, a.nom
             FROM plat_allergenes pa
             INNER JOIN allergenes a ON a.id = pa.allergene_id
             INNER JOIN plats p ON p.id = pa.plat_id
             WHERE p.menu_id = :menu_id
             ORDER BY a.nom'
        );
        $allergenesStmt->execute(['menu_id' => $menuId]);
        $allergenesParPlat = [];
        foreach ($allergenesStmt->fetchAll(PDO::FETCH_ASSOC) as $allergene) {
            $allergenesParPlat[(int) $allergene['plat_id']][] = $allergene['nom'];
        }
        foreach ($plats as &$plat) {
            $platAllergenes = $allergenesParPlat[(int) $plat['id']] ?? [];
            if ($platAllergenes) {
                $plat['allergenes'] = implode(', ', $platAllergenes);
            }
        }
        unset($plat);
    } catch (PDOException $exception) {
        error_log('Table des allergènes indisponible : ' . $exception->getMessage());
    }
} catch (PDOException $exception) {
    // Compatibilité avec une base existante avant l'ajout des colonnes détail.
    $platsStmt = $pdo->prepare(
        'SELECT id, nom, type, allergenes
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
    }
    unset($plat);
}

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
        </div>
    </section>

    <section class="bg-white rounded shadow-sm p-4 mb-4">
        <h2 class="h4 mb-3">Composition et allergènes</h2>
        <?php foreach ($types as $type => $label): ?>
            <?php if (!empty($platsParType[$type])): ?>
                <h3 class="h5 mt-3"><?php echo $label; ?></h3>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Produit</th><th>Allergènes</th></tr></thead>
                        <tbody>
                        <?php foreach ($platsParType[$type] as $plat): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($plat['nom']); ?></td>
                                <td><?php echo htmlspecialchars($plat['allergenes'] ?: 'Aucun allergène renseigné'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        <p class="small text-muted mb-0">Les allergènes indiqués correspondent aux recettes prévues et peuvent varier selon les ingrédients ou la préparation. En cas d’allergie, contactez-nous avant toute commande.</p>
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
