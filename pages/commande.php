<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/database.php';

if (!isLoggedIn()) {
    $target = '/pages/commande.php';
    if (isset($_GET['menu_id'])) {
        $target .= '?menu_id=' . urlencode((string) $_GET['menu_id']);
    }
    requireLogin($target);
}

$message = '';
$messageType = '';
$commandeRecap = null;

$menuId = isset($_GET['menu_id']) ? (int) $_GET['menu_id'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menuId = (int) ($_POST['menu_id'] ?? 0);
}

$menusStmt = $pdo->query('SELECT id, nom, nb_personnes_min, prix_minimum FROM menus WHERE actif = 1 ORDER BY nom');
$menus = $menusStmt->fetchAll(PDO::FETCH_ASSOC);

$menuSelectionne = null;
foreach ($menus as $menu) {
    if ((int) $menu['id'] === $menuId) {
        $menuSelectionne = $menu;
        break;
    }
}

$userStmt = $pdo->prepare('SELECT nom, prenom, gsm, email, adresse_postale FROM utilisateurs WHERE id = :id LIMIT 1');
$userStmt->execute(['id' => (int) $_SESSION['user_id']]);
$userData = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
    header('Location: /pages/logout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datePrestation = trim($_POST['date_prestation'] ?? '');
    $heureLivraison = trim($_POST['heure_livraison'] ?? '');
    $lieuLivraison = trim($_POST['lieu_livraison'] ?? '');
    $villeLivraison = trim($_POST['ville_livraison'] ?? '');
    $distanceKm = (float) ($_POST['distance_km'] ?? 0);
    $nbPersonnes = (int) ($_POST['nb_personnes'] ?? 0);

    if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $message = 'Session expirée. Merci de recharger la page.';
        $messageType = 'danger';
    } elseif (!$menuSelectionne) {
        $message = 'Merci de choisir un menu valide.';
        $messageType = 'danger';
    } elseif ($datePrestation === '' || $heureLivraison === '' || $lieuLivraison === '' || $villeLivraison === '') {
        $message = 'Veuillez remplir tous les champs de la prestation.';
        $messageType = 'danger';
    } elseif ($nbPersonnes < (int) $menuSelectionne['nb_personnes_min']) {
        $message = 'Le nombre de personnes doit être au moins ' . (int) $menuSelectionne['nb_personnes_min'] . '.';
        $messageType = 'danger';
    } else {
        $minPersonnes = (int) $menuSelectionne['nb_personnes_min'];
        $prixBase = (float) $menuSelectionne['prix_minimum'];
        $prixParPersonne = $minPersonnes > 0 ? ($prixBase / $minPersonnes) : $prixBase;

        $prixMenu = $prixBase;
        if ($nbPersonnes > $minPersonnes) {
            $prixMenu += ($nbPersonnes - $minPersonnes) * $prixParPersonne;
        }

        $remise = 0.0;
        if ($nbPersonnes >= ($minPersonnes + 5)) {
            $remise = $prixMenu * 0.10;
        }

        $villeNormalisee = strtolower(trim($villeLivraison));
        $fraisLivraison = 0.0;
        if ($villeNormalisee !== 'bordeaux') {
            $fraisLivraison = 5.00 + (0.59 * max($distanceKm, 0));
        }

        $prixTotal = $prixMenu - $remise + $fraisLivraison;

        $insertCommande = $pdo->prepare(
            'INSERT INTO commandes (
                utilisateur_id, menu_id, nom_client, prenom_client, email_client, gsm_client, adresse_client,
                date_prestation, heure_livraison, lieu_livraison, ville_livraison, distance_km,
                nb_personnes, prix_menu, remise, frais_livraison, prix_total, statut
            ) VALUES (
                :utilisateur_id, :menu_id, :nom_client, :prenom_client, :email_client, :gsm_client, :adresse_client,
                :date_prestation, :heure_livraison, :lieu_livraison, :ville_livraison, :distance_km,
                :nb_personnes, :prix_menu, :remise, :frais_livraison, :prix_total, :statut
            )'
        );

        $insertCommande->execute([
            'utilisateur_id' => (int) $_SESSION['user_id'],
            'menu_id' => $menuId,
            'nom_client' => $userData['nom'],
            'prenom_client' => $userData['prenom'],
            'email_client' => $userData['email'],
            'gsm_client' => $userData['gsm'] ?? '',
            'adresse_client' => $userData['adresse_postale'],
            'date_prestation' => $datePrestation,
            'heure_livraison' => $heureLivraison,
            'lieu_livraison' => $lieuLivraison,
            'ville_livraison' => $villeLivraison,
            'distance_km' => max($distanceKm, 0),
            'nb_personnes' => $nbPersonnes,
            'prix_menu' => round($prixMenu, 2),
            'remise' => round($remise, 2),
            'frais_livraison' => round($fraisLivraison, 2),
            'prix_total' => round($prixTotal, 2),
            'statut' => 'en_attente',
        ]);

        $commandeId = (int) $pdo->lastInsertId();
        $suiviStmt = $pdo->prepare(
            'INSERT INTO commande_suivis (commande_id, statut, commentaire) VALUES (:commande_id, :statut, :commentaire)'
        );
        $suiviStmt->execute([
            'commande_id' => $commandeId,
            'statut' => 'en_attente',
            'commentaire' => 'Commande créée par le client',
        ]);

        $commandeRecap = [
            'menu' => $menuSelectionne['nom'],
            'nb_personnes' => $nbPersonnes,
            'prix_menu' => round($prixMenu, 2),
            'remise' => round($remise, 2),
            'frais_livraison' => round($fraisLivraison, 2),
            'prix_total' => round($prixTotal, 2),
        ];

        $message = 'Commande enregistrée avec succès.';
        $messageType = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande - Vite&Gourmande</title>
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
                    <li class="nav-item"><a class="nav-link" href="/pages/mes-commandes.php">Mes commandes</a></li>
                    <li class="nav-item"><a class="nav-link" href="/pages/contact.php">Contact</a></li>
                    <li class="nav-item mt-2 mt-lg-0"><a class="btn btn-light btn-sm ms-lg-2" href="/pages/logout.php">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<main class="container py-5" style="max-width: 760px;">
    <section class="bg-white rounded shadow-sm p-4">
        <h2 class="mb-4">Commander un menu</h2>

        <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/pages/commande.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label" for="nom_client">Nom</label>
                    <input id="nom_client" class="form-control" value="<?php echo htmlspecialchars($userData['nom']); ?>" disabled>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="prenom_client">Prénom</label>
                    <input id="prenom_client" class="form-control" value="<?php echo htmlspecialchars($userData['prenom']); ?>" disabled>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="email_client">Email</label>
                    <input id="email_client" class="form-control" value="<?php echo htmlspecialchars($userData['email']); ?>" disabled>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="gsm_client">GSM</label>
                    <input id="gsm_client" class="form-control" value="<?php echo htmlspecialchars($userData['gsm']); ?>" disabled>
                </div>
                <div class="col-12">
                    <label class="form-label" for="adresse_client">Adresse</label>
                    <input id="adresse_client" class="form-control" value="<?php echo htmlspecialchars($userData['adresse_postale']); ?>" disabled>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="menu_id">Menu</label>
                    <select id="menu_id" name="menu_id" class="form-select" required>
                        <option value="">Choisir un menu</option>
                        <?php foreach ($menus as $menu): ?>
                            <option value="<?php echo (int) $menu['id']; ?>" <?php echo ($menuSelectionne && (int) $menuSelectionne['id'] === (int) $menu['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($menu['nom']); ?> (min <?php echo (int) $menu['nb_personnes_min']; ?> pers)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="nb_personnes">Nombre de personnes</label>
                    <input id="nb_personnes" name="nb_personnes" type="number" class="form-control" min="1" required value="<?php echo htmlspecialchars($_POST['nb_personnes'] ?? ''); ?>">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="date_prestation">Date de prestation</label>
                    <input id="date_prestation" name="date_prestation" type="date" class="form-control" required value="<?php echo htmlspecialchars($_POST['date_prestation'] ?? ''); ?>">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="heure_livraison">Heure de livraison</label>
                    <input id="heure_livraison" name="heure_livraison" type="time" class="form-control" required value="<?php echo htmlspecialchars($_POST['heure_livraison'] ?? ''); ?>">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="lieu_livraison">Lieu de livraison</label>
                    <input id="lieu_livraison" name="lieu_livraison" type="text" class="form-control" required value="<?php echo htmlspecialchars($_POST['lieu_livraison'] ?? ''); ?>">
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="ville_livraison">Ville</label>
                    <input id="ville_livraison" name="ville_livraison" type="text" class="form-control" required value="<?php echo htmlspecialchars($_POST['ville_livraison'] ?? 'Bordeaux'); ?>">
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="distance_km">Distance (km)</label>
                    <input id="distance_km" name="distance_km" type="number" step="0.1" min="0" class="form-control" value="<?php echo htmlspecialchars($_POST['distance_km'] ?? '0'); ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-4">Valider la commande</button>
        </form>

        <?php if ($commandeRecap !== null): ?>
            <hr>
            <h3 class="h5">Récapitulatif des prix</h3>
            <p class="mb-1">Menu : <?php echo htmlspecialchars($commandeRecap['menu']); ?></p>
            <p class="mb-1">Personnes : <?php echo (int) $commandeRecap['nb_personnes']; ?></p>
            <p class="mb-1">Prix du menu : <?php echo number_format($commandeRecap['prix_menu'], 2, ',', ' '); ?> EUR</p>
            <p class="mb-1">Remise : -<?php echo number_format($commandeRecap['remise'], 2, ',', ' '); ?> EUR</p>
            <p class="mb-1">Livraison : <?php echo number_format($commandeRecap['frais_livraison'], 2, ',', ' '); ?> EUR</p>
            <p class="fw-bold mb-0">Total : <?php echo number_format($commandeRecap['prix_total'], 2, ',', ' '); ?> EUR</p>
            <a class="btn btn-outline-secondary mt-3" href="/pages/mes-commandes.php">Voir mes commandes</a>
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


