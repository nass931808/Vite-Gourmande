<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function getUserRole(): string
{
    return $_SESSION['user_role'] ?? 'utilisateur';
}

function requireLogin(string $redirectAfterLogin): void
{
    if (!isLoggedIn()) {
        header('Location: /pages/login.php?redirect=' . urlencode($redirectAfterLogin));
        exit;
    }
}

function requireRole(array $allowedRoles): void
{
    if (!in_array(getUserRole(), $allowedRoles, true)) {
        header('Location: /pages/index.html');
        exit;
    }
}

function getCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function isValidCsrfToken(?string $token): bool
{
    if ($token === null || $token === '' || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
