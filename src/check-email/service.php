<?php

declare(strict_types=1);

require_once __DIR__ . '/../repository/emails_to_check_repository.php';
require_once __DIR__ . '/../log/log.php';
require_once __DIR__ . '/../functions.php';

function runCreateCheckEmailTasks(PDO $pdo, int $limit): void
{
    while (true) {
        processCreateCheckEmailTasks($pdo, $limit);
    }
}

function processCreateCheckEmailTasks(PDO $pdo, int $limit): void
{
    $users = getUserEmailsWithNonCheckedEmails($pdo, $limit);

    foreach ($users as $row) {
        addEmailToCheck($pdo, $row['email']);
    }

    logM("Inserted tasks to check " . count($users));
}

function runCheckEmailWorker(PDO $pdo, int $workerId): void
{
    while (true) {
        processCheckEmail($pdo, $workerId);
    }
}

function processCheckEmail(PDO $pdo, int $workerId): bool
{
    logM("Trying to get task for worker");
    $email = getCheckTaskForWorker($pdo, $workerId);

    if ($email === null) {
        return false;
    }

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
        return (bool)$valid;
    } catch (PDOException $exception) {
        $pdo->rollBack();
        logM("Exception: " . $exception->getMessage());
        throw $exception;
    }
}
