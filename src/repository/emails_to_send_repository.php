<?php

declare(strict_types=1);

function getEmailsToSend(PDO $pdo, int $limit, int $tsStart, int $tsEnd): array
{
    $stmt = $pdo->prepare("
        SELECT u.email, u.validts FROM users u
        LEFT JOIN emails_to_send ets ON u.email = ets.email AND u.validts = ets.validts
        WHERE ets.email IS NULL AND u.valid = true AND u.validts BETWEEN :start AND :end
        LIMIT :limit
    ");

    $stmt->execute(['limit' => $limit, 'start' => $tsStart, 'end' => $tsEnd]);

    return $stmt->fetchAll();
}

function addEmailToSendTask(PDO $pdo, string $email, int $validts): int
{
    $s = $pdo->prepare("INSERT INTO emails_to_send (email, validts) VALUES (?, ?)");
    $s->execute([$email, $validts]);

    return (int)$pdo->lastInsertId('emails_to_send_id_seq');
}

function getEmailSendTaskById(PDO $pdo, int $id): array
{
    $stmt = $pdo->prepare("SELECT ets.* FROM emails_to_send ets WHERE ets.id = :id");

    $stmt->execute(['id' => $id]);

    return $stmt->fetchAll()[0] ?? [];
}

function getEmailSendTaskByEmailAndValidts(PDO $pdo, string $email, int $validts): array
{
    $stmt = $pdo->prepare("SELECT ets.* FROM emails_to_send ets WHERE ets.email = :email AND ets.validts = :validts LIMIT 1");

    $stmt->execute(['email' => $email, 'validts' => $validts]);

    return $stmt->fetchAll()[0] ?? [];
}

function getSendTaskForWorker(PDO $pdo, int $workerId): array
{
    $s = $pdo->prepare("
        UPDATE emails_to_send
        SET worker = :worker
        WHERE email = (
            SELECT email
            FROM   emails_to_send
            WHERE  sent = FALSE AND worker = 0
            LIMIT  1
            FOR UPDATE SKIP LOCKED
        )
        RETURNING id, email"
    );

    $pdo->beginTransaction();
    try {
        $s->execute(['worker' => $workerId]);
        $pdo->commit();

        return $s->fetchAll()[0] ?? [];
    } catch (PDOException $exception) {
        logM("Exception: " . $exception->getMessage());
        $pdo->rollBack();
        throw $exception;
    }
}

function updateSendTaskResult(PDO $pdo, int $taskId, bool $isSent): void
{
    try {
        $pdo->beginTransaction();
        $s = $pdo->prepare("
            UPDATE emails_to_send
            SET worker = 0, sent = :isSent
            WHERE id = :id"
        );
        $s->execute(['id' => $taskId, 'isSent' => (int)$isSent]);
        $pdo->commit();
        return;
    } catch (PDOException $exception) {
        $pdo->rollBack();
        logM("Exception: " . $exception->getMessage());
        throw $exception;
    }
}
