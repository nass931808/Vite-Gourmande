<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/database.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $gsm = trim($_POST['gsm'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $adressePostale = trim($_POST['adresse_postale'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $message = 'Session expirée. Merci de recharger la page.';
        $messageType = 'danger';
    } elseif ($nom === '' || $prenom === '' || $gsm === '' || $email === '' || $adressePostale === '' || $password === '' || $confirmPassword === '') {
        $message = 'Veuillez remplir tous les champs.';
        $messageType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Adresse email invalide.';
        $messageType = 'danger';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{10,}$/', $password)) {
        $message = 'Mot de passe invalide : 10 caractères minimum avec majuscule, minuscule, chiffre et caractère spécial.';
        $messageType = 'danger';
    } elseif ($password !== $confirmPassword) {
        $message = 'Les mots de passe ne correspondent pas.';
        $messageType = 'danger';
    } else {
        try {
            $checkStmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = :email LIMIT 1');
            $checkStmt->execute(['email' => $email]);

            if ($checkStmt->fetch()) {
                $message = 'Un compte existe déjà avec cet email.';
                $messageType = 'warning';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $insertStmt = $pdo->prepare(
                    'INSERT INTO utilisateurs (nom, prenom, gsm, email, adresse_postale, mot_de_passe, role, created_at)
                     VALUES (:nom, :prenom, :gsm, :email, :adresse_postale, :mot_de_passe, :role, NOW())'
                );
                $insertStmt->execute([
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'gsm' => $gsm,
                    'email' => $email,
                    'adresse_postale' => $adressePostale,
                    'mot_de_passe' => $hashedPassword,
                    'role' => 'utilisateur',
                ]);

                $message = 'Inscription réussie. Vous pouvez maintenant vous connecter.';
                $messageType = 'success';
            }
        } catch (PDOException $e) {
            $message = 'Erreur lors de l\'inscription : ' . $e->getMessage();
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
    <title>Inscription - Vite&Gourmande</title>
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
                            <a class="btn btn-light btn-sm ms-lg-2" href="/pages/login.php">Connexion</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container py-5" style="max-width: 560px;">
        <section class="bg-white rounded shadow-sm p-4">
            <h2 class="text-center mb-4">Créer un compte</h2>

            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/pages/register.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom complet</label>
                    <input id="nom" name="nom" type="text" class="form-control" required value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="prenom" class="form-label">Prénom</label>
                    <input id="prenom" name="prenom" type="text" class="form-control" required value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="gsm" class="form-label">Numéro GSM</label>
                    <input id="gsm" name="gsm" type="text" class="form-control" required value="<?php echo htmlspecialchars($_POST['gsm'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" name="email" type="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="adresse_postale" class="form-label">Adresse postale</label>
                    <input id="adresse_postale" name="adresse_postale" type="text" class="form-control" required value="<?php echo htmlspecialchars($_POST['adresse_postale'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input id="password" name="password" type="password" class="form-control" minlength="10" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{10,}" required>
                    <div class="form-text">10 caractères minimum, avec majuscule, minuscule, chiffre et caractère spécial.</div>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                    <input id="confirm_password" name="confirm_password" type="password" class="form-control" minlength="10" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
            </form>

            <p class="text-center mt-3 mb-0">
                Vous avez déjà un compte ?
                <a href="/pages/login.php" class="fw-semibold">Connectez-vous</a>
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




