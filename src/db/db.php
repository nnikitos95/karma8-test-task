<?php

require_once __DIR__ . '/PdoWithSavePoint.php';

['host' => $host, 'db' => $db, 'user' => $user, 'password' => $password] = require __DIR__ . '/config.php';

try {
    $dsn = "pgsql:host={$host};port=5432;dbname={$db};";
    // make a database connection
    $pdo = new PdoWithSavePoint($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    return $pdo;
} catch (PDOException $e) {
    die($e->getMessage());
}
