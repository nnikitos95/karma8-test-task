<?php

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
    } catch (PDOException $exception) {
        logM("Exception: " . $exception->getMessage());
        $pdo->rollBack();
        throw $exception;
    }

    if ($s->rowCount()) {
        $email = $s->fetchAll()[0]['email'];
        logM("Task gotten {$email}");
        logM("Start checking {$email}");
        $valid = (int)check_mail($email);
        logM("Checking finished {$email}");
        try {
            $pdo->beginTransaction();
            $s = $pdo->prepare("
                UPDATE emails_to_check
                SET worker = 0, checked = :checked, valid = :valid
                WHERE email = :email AND worker = :worker"
            );
            $s->execute(['checked' => 1, 'valid' => $valid, 'email' => $email, 'worker' => $workerId]);
            $s = $pdo->prepare("
                UPDATE users
                SET checked = :checked, valid = :valid
                WHERE email = :email"
            );
            $s->execute(['checked' => 1, 'valid' => $valid, 'email' => $email]);
            $pdo->commit();
            logM("Task finished {$email}, valid " . ($valid ? 'true' : 'false'));
        } catch (PDOException $exception) {
            $pdo->rollBack();
            logM("Exception: " . $exception->getMessage());
            throw $exception;
        }
    }
}

