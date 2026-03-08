<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/database.php';

$message = '';
$messageType = '';
$redirectTarget = '/pages/index.html';

if (isset($_GET['redirect']) && is_string($_GET['redirect']) && str_starts_with($_GET['redirect'], '/pages/')) {
    $redirectTarget = $_GET['redirect'];
}

if (isset($_GET['logout']) && $_GET['logout'] === '1') {
    $message = 'Vous êtes maintenant déconnecté.';
    $messageType = 'success';
}

if (isset($_SESSION['user_id'])) {
    header('Location: ' . $redirectTarget);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $message = 'Session expirée. Merci de recharger la page.';
        $messageType = 'danger';
    } elseif ($email === '' || $password === '') {
        $message = 'Veuillez remplir tous les champs.';
        $messageType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Adresse email invalide.';
        $messageType = 'danger';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id, nom, prenom, gsm, email, adresse_postale, role, mot_de_passe FROM utilisateurs WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['mot_de_passe'])) {
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['user_name'] = $user['nom'];
                $_SESSION['user_firstname'] = $user['prenom'];
                $_SESSION['user_phone'] = $user['gsm'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_address'] = $user['adresse_postale'];
                $_SESSION['user_role'] = $user['role'];

                if (!isset($_GET['redirect']) || $_GET['redirect'] === '') {
                    if ($user['role'] === 'administrateur') {
                        $redirectTarget = '/pages/espace-admin.php';
                    } elseif ($user['role'] === 'employe') {
                        $redirectTarget = '/pages/espace-employe.php';
                    } else {
                        $redirectTarget = '/pages/espace-utilisateur.php';
                    }
                }

                header('Location: ' . $redirectTarget);
                exit;
            }

            $message = 'Email ou mot de passe incorrect.';
            $messageType = 'danger';
        } catch (PDOException $e) {
            $message = 'Erreur lors de la connexion: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Vite&Gourmande</title>
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
                        <li class="nav-item"><a class="nav-link" href="/pages/contact.php">Contact</a></li>
                        <li class="nav-item mt-2 mt-lg-0">
                            <a class="btn btn-light btn-sm ms-lg-2" href="/pages/register.php">Inscription</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container py-5" style="max-width: 500px;">
        <section class="bg-white rounded shadow-sm p-4">
            <h2 class="text-center mb-4">Connexion</h2>

            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/pages/login.php?redirect=<?php echo urlencode($redirectTarget); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" name="email" type="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input id="password" name="password" type="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Se connecter</button>
            </form>
            <p class="text-center mt-3 mb-0">
                Pas encore de compte ?
                <a href="/pages/register.php" class="fw-semibold">Inscrivez-vous</a>
            </p>
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




