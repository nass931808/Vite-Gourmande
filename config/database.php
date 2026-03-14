<?php
$hostEnv = getenv('DB_HOST');
$portEnv = getenv('DB_PORT');
$nameEnv = getenv('DB_NAME');
$userEnv = getenv('DB_USER');
$passEnv = getenv('DB_PASS');

$host = $hostEnv ?: '127.0.0.1';
$port = $portEnv ?: '3306';
$dbname = $nameEnv ?: 'vite&gourmande';
$username = $userEnv ?: 'root';
$password = $passEnv ?: '';

$hasExplicitDbConfig = $hostEnv && $nameEnv && $userEnv;

// Only use URL-based config if DB_* vars are not fully provided.
if (!$hasExplicitDbConfig) {
  $databaseUrl = getenv('JAWSDB_URL') ?: getenv('CLEARDB_DATABASE_URL') ?: getenv('DATABASE_URL');
  if ($databaseUrl) {
    $parts = parse_url($databaseUrl);
    $scheme = $parts['scheme'] ?? '';
    if ($parts !== false && ($scheme === 'mysql' || $scheme === 'mysqli')) {
      $host = $parts['host'] ?? $host;
      $port = isset($parts['port']) ? (string) $parts['port'] : $port;
      $dbname = isset($parts['path']) ? ltrim($parts['path'], '/') : $dbname;
      $username = $parts['user'] ?? $username;
      $password = $parts['pass'] ?? $password;
    }
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