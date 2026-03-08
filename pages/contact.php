<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/database.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $message = 'Session expirée. Merci de recharger la page.';
        $messageType = 'danger';
    } elseif ($nom === '' || $email === '' || $titre === '' || $description === '') {
        $message = 'Veuillez remplir tous les champs.';
        $messageType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Adresse email invalide.';
        $messageType = 'danger';
    } else {
        $insert = $pdo->prepare(
            'INSERT INTO contacts (nom, email, titre, description) VALUES (:nom, :email, :titre, :description)'
        );
        $insert->execute([
            'nom' => $nom,
            'email' => $email,
            'titre' => $titre,
            'description' => $description,
        ]);

        $to = 'contact@viteetgourmande.fr';
        $subject = '[Contact Site] ' . $titre;
        $body = "Nom: $nom\nEmail: $email\n\nMessage:\n$description";
        $headers = "From: $email\r\nReply-To: $email\r\n";

        $mailSent = @mail($to, $subject, $body, $headers);

        if ($mailSent) {
            $message = 'Votre message a bien été envoyé.';
        } else {
            $message = 'Message enregistré. Email non envoyé sur cet environnement local.';
        }
        $messageType = 'success';

        $_POST = [];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Vite&Gourmande</title>
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
                <li class="nav-item"><a class="nav-link" href="/pages/menus.php">Menus</a></li>
                <li class="nav-item"><a class="nav-link active" href="/pages/contact.php">Contact</a></li>
                <li class="nav-item mt-2 mt-lg-0">
                    <a class="btn btn-light btn-sm ms-lg-2" href="/pages/login.php">Connexion</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
</header>

<main class="container py-5" style="max-width: 760px;">
    <section class="bg-white rounded shadow-sm p-4">
        <h2 class="mb-3">Nous contacter</h2>
        <p>Pour toute question ou réservation, utilisez ce formulaire.</p>

        <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/pages/contact.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
            <div class="mb-3">
                <label for="nom" class="form-label">Votre nom</label>
                <input id="nom" name="nom" type="text" class="form-control" required value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Votre email</label>
                <input id="email" name="email" type="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="mb-3">
                <label for="titre" class="form-label">Titre</label>
                <input id="titre" name="titre" type="text" class="form-control" required value="<?php echo htmlspecialchars($_POST['titre'] ?? ''); ?>">
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Envoyer</button>
        </form>
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




