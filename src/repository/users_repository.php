<?php

declare(strict_types=1);

function getUserById(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare("SELECT u.* FROM users u WHERE u.id = :id");

    $stmt->execute(['id' => $userId]);

    return $stmt->fetchAll()[0] ?? [];
}

function addUser(PDO $pdo, string $username, string $email, int $validts, bool $confirmed = false, bool $valid = false, bool $checked = false): int
{
    $stmt = $pdo->prepare('INSERT INTO users (username, email, validts, confirmed, valid, checked) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$username, $email, $validts, (int)$confirmed, (int)$valid, (int)$checked]);

    return (int)$pdo->lastInsertId('users_id_seq');
}
