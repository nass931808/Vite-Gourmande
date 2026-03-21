<?php
require_once __DIR__ . '/../config/database.php';

$stmt = $pdo->query(
    'SELECT id, nom, description, nb_personnes_min, prix_minimum,
            CASE id
                WHEN 1 THEN ''Noël''
                WHEN 2 THEN ''Pâques''
                WHEN 3 THEN ''Événement''
                ELSE ''Classique''
            END AS theme,
            ''Classique'' AS regime,
            CASE id
                WHEN 1 THEN ''/images/noel.avif''
                WHEN 2 THEN ''/images/paque.avif''
                WHEN 3 THEN ''/images/evenement.avif''
                ELSE ''/images/classique.avif''
            END AS image_path
     FROM menus
     WHERE actif = TRUE
     ORDER BY prix_minimum ASC'
);
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getMenuDetailLink(int $menuId): string
{
    if ($menuId === 1) {
        return '/pages/menu-noel.php';
    }
    if ($menuId === 2) {
        return '/pages/menu-paque.php';
    }
    if ($menuId === 3) {
        return '/pages/menu-evenement.php';
    }
    return '/pages/menu-classique.php';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menus - Vite&Gourmande</title>
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

<main class="container py-5">
    <section class="bg-white rounded shadow-sm p-4 mb-4">
        <h2 class="mb-3">Vue globale des menus</h2>
        <p class="text-muted mb-4">Utilisez les filtres ci-dessous. Les résultats se mettent à jour sans rechargement.</p>

        <div class="row g-3" id="menu-filters">
            <div class="col-12 col-md-3">
                <label class="form-label" for="prix-max">Prix maximum</label>
                <input id="prix-max" type="number" min="0" class="form-control" placeholder="ex: 200">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="prix-min-range">Prix min</label>
                <input id="prix-min-range" type="number" min="0" class="form-control" placeholder="0">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="prix-max-range">Prix max</label>
                <input id="prix-max-range" type="number" min="0" class="form-control" placeholder="500">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label" for="theme">Thème</label>
                <select id="theme" class="form-select">
                    <option value="">Tous</option>
                    <option value="Noël">Noël</option>
                    <option value="Pâques">Pâques</option>
                    <option value="Événement">Événement</option>
                    <option value="Classique">Classique</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label" for="regime">Régime</label>
                <select id="regime" class="form-select">
                    <option value="">Tous</option>
                    <option value="Classique">Classique</option>
                </select>
            </div>
            <div class="col-12 col-md-1">
                <label class="form-label" for="nb-pers">Pers. min</label>
                <input id="nb-pers" type="number" min="1" class="form-control" placeholder="6">
            </div>
        </div>
    </section>

    <section>
        <div class="row g-4" id="menus-grid">
            <?php foreach ($menus as $menu): ?>
                <div class="col-12 col-md-6 col-lg-4 menu-card"
                     data-price="<?php echo htmlspecialchars((string) $menu['prix_minimum']); ?>"
                     data-theme="<?php echo htmlspecialchars($menu['theme']); ?>"
                     data-regime="<?php echo htmlspecialchars($menu['regime']); ?>"
                     data-min-persons="<?php echo (int) $menu['nb_personnes_min']; ?>">
                    <article class="card h-100">
                        <img src="<?php echo htmlspecialchars($menu['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($menu['nom']); ?>">
                        <div class="card-body d-flex flex-column">
                            <h3 class="h5 card-title"><?php echo htmlspecialchars($menu['nom']); ?></h3>
                            <p class="card-text flex-grow-1"><?php echo htmlspecialchars($menu['description'] ?? 'Menu sans description.'); ?></p>
                            <p class="mb-1"><strong>Thème :</strong> <?php echo htmlspecialchars($menu['theme']); ?></p>
                            <p class="mb-2"><strong>Régime :</strong> <?php echo htmlspecialchars($menu['regime']); ?></p>
                            <p class="fw-bold"><?php echo (int) $menu['nb_personnes_min']; ?> pers min - <?php echo number_format((float) $menu['prix_minimum'], 2, ',', ' '); ?> EUR</p>
                            <a href="<?php echo htmlspecialchars(getMenuDetailLink((int) $menu['id'])); ?>" class="btn btn-primary w-100">Voir le détail</a>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
        <p id="no-result" class="text-center mt-4 d-none">Aucun menu ne correspond à vos filtres.</p>
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
<script>
(() => {
    const cards = Array.from(document.querySelectorAll('.menu-card'));
    const noResult = document.getElementById('no-result');

    const fields = {
        prixMax: document.getElementById('prix-max'),
        prixMinRange: document.getElementById('prix-min-range'),
        prixMaxRange: document.getElementById('prix-max-range'),
        theme: document.getElementById('theme'),
        regime: document.getElementById('regime'),
        nbPers: document.getElementById('nb-pers')
    };

    const toNumber = (v) => {
        const n = Number(v);
        return Number.isFinite(n) ? n : null;
    };

    function applyFilters() {
        const prixMax = toNumber(fields.prixMax.value);
        const prixMinRange = toNumber(fields.prixMinRange.value);
        const prixMaxRange = toNumber(fields.prixMaxRange.value);
        const theme = fields.theme.value;
        const regime = fields.regime.value;
        const nbPers = toNumber(fields.nbPers.value);

        let visibleCount = 0;

        cards.forEach((card) => {
            const price = Number(card.dataset.price);
            const cardTheme = card.dataset.theme;
            const cardRegime = card.dataset.regime;
            const minPersons = Number(card.dataset.minPersons);

            let visible = true;

            if (prixMax !== null && price > prixMax) visible = false;
            if (prixMinRange !== null && price < prixMinRange) visible = false;
            if (prixMaxRange !== null && price > prixMaxRange) visible = false;
            if (theme && cardTheme !== theme) visible = false;
            if (regime && cardRegime !== regime) visible = false;
            if (nbPers !== null && minPersons > nbPers) visible = false;

            card.classList.toggle('d-none', !visible);
            if (visible) visibleCount += 1;
        });

        noResult.classList.toggle('d-none', visibleCount > 0);
    }

    Object.values(fields).forEach((field) => {
        field.addEventListener('input', applyFilters);
        field.addEventListener('change', applyFilters);
    });
})();
</script>
</body>
</html>




