<?php
$driver   = strtolower(getenv('DB_DRIVER') ?: 'mysql');
$host     = getenv('DB_HOST') ?: '127.0.0.1';
$port     = getenv('DB_PORT') ?: ($driver === 'pgsql' ? '5432' : '3306');
$dbname   = getenv('DB_NAME') ?: 'vite&gourmande';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

if ($driver === 'pgsql') {
  $sslmode = getenv('DB_SSLMODE') ?: 'require';
  $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=$sslmode";
} else {
  $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
}

try {
  $pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (PDOException $e) {
  die('Erreur connexion: ' . $e->getMessage());
}
?>