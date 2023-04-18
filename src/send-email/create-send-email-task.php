<?php

$pdo = require __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../log/log.php';

$limit = getenv('LIMIT') ?: 1000;
$DAYS_BEFORE = 3;

while (true) {
    $tsStart = time();
    $tsEnd = $tsStart + $DAYS_BEFORE * 24 * 60 * 60;
    $stmt = $pdo->prepare("
        SELECT u.email, u.validts FROM users u
        LEFT JOIN emails_to_send ets ON u.email = ets.email AND u.validts = ets.validts
        WHERE ets.email IS NULL AND u.valid = true AND u.validts BETWEEN :start AND :end
        LIMIT :limit
    ");

    $stmt->execute(['limit' => $limit, 'start' => $tsStart, 'end' => $tsEnd]);

    foreach ($stmt->fetchAll() as $row) {
        $s = $pdo->prepare("INSERT INTO emails_to_send (email, validts) VALUES (?, ?)");
        $s->execute([$row['email'], $row['validts']]);
    }

    logM("Inserted tasks to check ${limit}");
}
