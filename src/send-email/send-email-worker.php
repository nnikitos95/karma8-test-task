<?php

$RETRY_COUNT = 10;

/** @var PDO $pdo */
$pdo = require __DIR__ .  '/../db/db.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../log/log.php';

$workerId = (int)getenv("WORKER_ID");

if ($workerId <= 0) {
    logM("Wrong worker ${workerId}");
    exit(1);
}

while (true) {
    logM("Trying to get task for worker");
    $result = getNextTask($workerId, $pdo);

    if (empty($result)) {
        continue;
    }

    ['email' => $email, 'id' => $id] = $result;

    logM("Task gotten {$email}");
    $isSent = sendEmailWithRetries($RETRY_COUNT, $email);

    if ($isSent) {
        logM("Sending success {$email}");
        try {
            $pdo->beginTransaction();
            $s = $pdo->prepare("
                UPDATE emails_to_send
                SET worker = 0, sent = true
                WHERE id = :id"
            );
            $s->execute(['id' => $id]);
            $pdo->commit();
            logM("Task finished {$email} with success");
            continue;
        } catch (PDOException $exception) {
            $pdo->rollBack();
            logM("Exception: " . $exception->getMessage());
            throw $exception;
        }
    }

    logM("Task finished {$email} with failed, all retry exceeded");
    try {
        $pdo->beginTransaction();
        $s = $pdo->prepare("
            UPDATE emails_to_send
            SET worker = 0
            WHERE id = :id"
        );
        $s->execute(['id' => $id]);
        $pdo->commit();
        logM("Task finished {$email} with failed, all retry exceeded");
    } catch (PDOException $exception) {
        $pdo->rollBack();
        logM("Exception: " . $exception->getMessage());
        throw $exception;
    }
}

function getNextTask(int $workerId, PDO $pdo): array
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

        return $s->fetchAll()[0];
    } catch (PDOException $exception) {
        logM("Exception: " . $exception->getMessage());
        $pdo->rollBack();
        throw $exception;
    }
}

function sendEmailWithRetries(int $retryCount, string $email): bool
{
    for ($i = 1; $i <= $retryCount + 1; $i++) {
        logM("Start sending {$email} retry ${i}");
        $sent = send_email($email, "from@mail.ru", "to@mail.ru", "subject", "body");
        if (!$sent) {
            logM("Sending {$email} retry ${i} failed");
            sleep(1);
            continue;
        }

        return true;
    }

    return false;
}