<?php
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'vite&gourmande';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

// Support common add-on URL env vars (JAWSDB/ClearDB/standard DATABASE_URL).
$databaseUrl = getenv('JAWSDB_URL') ?: getenv('CLEARDB_DATABASE_URL') ?: getenv('DATABASE_URL');
if ($databaseUrl) {
  $parts = parse_url($databaseUrl);
  if ($parts !== false) {
    $host = $parts['host'] ?? $host;
    $port = isset($parts['port']) ? (string) $parts['port'] : $port;
    $dbname = isset($parts['path']) ? ltrim($parts['path'], '/') : $dbname;
    $username = $parts['user'] ?? $username;
    $password = $parts['pass'] ?? $password;
  }
}

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
  $pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (PDOException $e) {
  die('Erreur connexion: ' . $e->getMessage());
}
?>