<?php
require_once __DIR__ . '/_auth.php';

requireLogin('/pages/espace-admin.php');
requireRole(['administrateur']);
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
<main class="container py-5" style="max-width: 700px;">
    <section class="bg-white rounded shadow-sm p-4">
        <h2 class="mb-3">Espace administrateur</h2>
        <p>Accès réservé aux administrateurs.</p>
        <p class="mb-4">Zone prête pour la création d'employés et les statistiques.</p>
        <a class="btn btn-primary" href="/pages/index.html">Retour à l'accueil</a>
    </section>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
