<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin('/pages/espace-admin.php');
requireRole(['administrateur']);

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
                $insertStmt = $pdo->prepare(
                    'INSERT INTO utilisateurs
                        (nom, prenom, gsm, email, adresse_postale, mot_de_passe, role, created_at)
                     VALUES
                        (:nom, :prenom, :gsm, :email, :adresse_postale, :mot_de_passe, :role, NOW())'
                );
                $insertStmt->execute([
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'gsm' => $gsm,
                    'email' => $email,
                    'adresse_postale' => $adressePostale,
                    'mot_de_passe' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => 'employe',
                ]);

                $message = 'Employé créé avec succès. Il peut maintenant se connecter.';
                $messageType = 'success';
                $_POST = [];
            }
        } catch (PDOException $e) {
            error_log('Erreur lors de la création d\'un employé : ' . $e->getMessage());
            $message = 'Impossible de créer cet employé pour le moment.';
            $messageType = 'danger';
        }
    }
}

$employeesStmt = $pdo->query(
    "SELECT nom, prenom, gsm, email, created_at
     FROM utilisateurs
     WHERE role = 'employe'
     ORDER BY nom, prenom"
);
$employees = $employeesStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace administrateur - Vite&Gourmande</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/styles/style.css">
</head>
<body>
<main class="container py-5" style="max-width: 900px;">
    <section class="bg-white rounded shadow-sm p-4">
        <h2 class="mb-3">Espace administrateur</h2>
        <p>Accès réservé aux administrateurs.</p>

        <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <h3 class="h5 mt-4">Créer un compte employé</h3>
        <p class="text-muted">Le compte sera enregistré dans <code>utilisateurs</code> avec le rôle <code>employe</code>.</p>
        <form method="post" action="/pages/espace-admin.php" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
            <div class="col-md-6">
                <label for="nom" class="form-label">Nom</label>
                <input id="nom" name="nom" type="text" class="form-control" required value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label for="prenom" class="form-label">Prénom</label>
                <input id="prenom" name="prenom" type="text" class="form-control" required value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label for="gsm" class="form-label">Téléphone</label>
                <input id="gsm" name="gsm" type="tel" class="form-control" required value="<?php echo htmlspecialchars($_POST['gsm'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email professionnel</label>
                <input id="email" name="email" type="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="col-12">
                <label for="adresse_postale" class="form-label">Adresse</label>
                <input id="adresse_postale" name="adresse_postale" type="text" class="form-control" required value="<?php echo htmlspecialchars($_POST['adresse_postale'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label for="password" class="form-label">Mot de passe</label>
                <input id="password" name="password" type="password" class="form-control" required>
                <div class="form-text">10 caractères minimum, avec majuscule, minuscule, chiffre et caractère spécial.</div>
            </div>
            <div class="col-md-6">
                <label for="confirm_password" class="form-label">Confirmation du mot de passe</label>
                <input id="confirm_password" name="confirm_password" type="password" class="form-control" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Créer l'employé</button>
            </div>
        </form>

        <h3 class="h5 mt-5">Employés enregistrés</h3>
        <?php if ($employees === []): ?>
            <p class="text-muted">Aucun employé n'est encore enregistré.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Créé le</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($employee['prenom'] . ' ' . $employee['nom']); ?></td>
                                <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                <td><?php echo htmlspecialchars($employee['gsm']); ?></td>
                                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($employee['created_at']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-wrap gap-2 mt-3">
            <a class="btn btn-primary" href="/pages/espace-employe.php">Ouvrir l'espace employé</a>
            <a class="btn btn-outline-secondary" href="/pages/index.html">Retour à l'accueil</a>
        </div>
    </section>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
