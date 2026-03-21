<?php
function env_first(array $keys)
{
  foreach ($keys as $key) {
    $value = getenv($key);
    if ($value !== false && $value !== '') {
      return $value;
    }
  }

  return null;
}

// Heroku JawsDB/ClearDB usually exposes credentials via a single URL.
$databaseUrl = env_first(['JAWSDB_URL', 'CLEARDB_DATABASE_URL', 'DATABASE_URL']);

if ($databaseUrl !== null) {
  $parts = parse_url($databaseUrl);

  if ($parts === false) {
    die('Erreur connexion: URL de base de donnees invalide');
  }

  $host = $parts['host'] ?? '127.0.0.1';
  $port = isset($parts['port']) ? (string) $parts['port'] : '3306';
  $dbname = isset($parts['path']) ? ltrim($parts['path'], '/') : '';
  $username = isset($parts['user']) ? rawurldecode($parts['user']) : '';
  $password = isset($parts['pass']) ? rawurldecode($parts['pass']) : '';
} else {
  $host = env_first(['DB_HOST']) ?: '127.0.0.1';
  $port = env_first(['DB_PORT']) ?: '3306';
  $dbname = env_first(['DB_NAME']) ?: 'vite&gourmande';
  $username = env_first(['DB_USER', 'DB_USERNAME']) ?: 'root';
  $password = env_first(['DB_PASS', 'DB_PASSWORD']) ?: '';
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