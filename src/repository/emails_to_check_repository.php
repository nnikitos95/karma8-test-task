<?php

declare(strict_types=1);

require_once __DIR__ . '/../log/log.php';

function addEmailToCheck(PDO $pdo, string $email, bool $valid = false, bool $checked = false): string
{
    $s = $pdo->prepare("INSERT INTO emails_to_check (email, valid, checked) VALUES (?, ?, ?)");
    $s->execute([$email, (int)$valid, (int)$checked]);

    return $email;
}

function getUserEmailsWithNonCheckedEmails(PDO $pdo, int $limit = 1000): array
{
    $stmt = $pdo->prepare("
        SELECT u.email FROM users u
        LEFT JOIN emails_to_check etc ON u.email = etc.email
        WHERE etc.email IS NULL AND u.checked = false
        LIMIT :limit
    ");

    $stmt->execute(['limit' => $limit]);

    return $stmt->fetchAll();
}

function getEmailCheckTaskByEmail(PDO $pdo, string $email): array
{
    $stmt = $pdo->prepare("SELECT etc.* FROM emails_to_check etc WHERE etc.email = :email");

    $stmt->execute(['email' => $email]);

    return $stmt->fetchAll()[0] ?? [];
}

function getCheckTaskForWorker(PDO $pdo, int $workerId): ?string
{
    $s = $pdo->prepare("
        UPDATE emails_to_check
        SET worker = :worker
        WHERE email = (
            SELECT email
            FROM   emails_to_check
            WHERE  checked = FALSE AND worker = 0
            LIMIT  1
            FOR UPDATE SKIP LOCKED
        )
        RETURNING email"
    );

    $pdo->beginTransaction();
    try {
        $s->execute(['worker' => $workerId]);
        $pdo->commit();
        return $s->fetchAll()[0]['email'] ?? null;
    } catch (PDOException $exception) {
        logM("Exception: " . $exception->getMessage());
        $pdo->rollBack();
        throw $exception;
    }
}
