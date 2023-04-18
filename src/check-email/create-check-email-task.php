<?php

/** @var PDO $pdo */
$pdo = require __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../log/log.php';

$limit = getenv('LIMIT') ?: 1000;

while (true) {
    $stmt = $pdo->prepare("
        SELECT u.email FROM users u
        LEFT JOIN emails_to_check etc ON u.email = etc.email
        WHERE etc.email IS NULL AND u.checked = false
        LIMIT :limit
    ");

    $stmt->execute(['limit' => $limit]);

    foreach ($stmt->fetchAll() as $row) {
        $s = $pdo->prepare("INSERT INTO emails_to_check (email, valid, checked) VALUES (?, ?, ?)");
        $s->execute([$row['email'], 0, 0]);
    }

    logM("Inserted tasks to check ${limit}");
}