<?php

declare(strict_types=1);

function runCreateSendEmailTasks(PDO $pdo, int $limit, int $daysBefore = 3): void
{
    $startTime = time();
    while (true) {
        processCreateSendEmailTask($pdo, $limit, $startTime, $daysBefore);
    }
}

function processCreateSendEmailTask(PDO $pdo, int $limit, int $endTime, int $daysBefore = 3): void
{
    $tsStart = $endTime - $daysBefore * 24 * 60 * 60;

    $emails = getEmailsToSend($pdo, $limit, $tsStart, $endTime);

    foreach ($emails as $row) {
        addEmailToSendTask($pdo, $row['email'], $row['validts']);
    }

    logM("Inserted tasks to check " . count($emails));
}

function runSendEmailWorker(PDO $pdo, int $workerId, int $retryCount): void
{
    while (true) {
        processSendEmail($pdo, $workerId, $retryCount);
    }
}

function processSendEmail(PDO $pdo, int $workerId, int $retryCount): bool
{
    logM("Trying to get task for worker");
    $result = getSendTaskForWorker($pdo, $workerId);

    if (empty($result)) {
        return false;
    }

    ['email' => $email, 'id' => $id] = $result;

    logM("Task gotten {$email}");
    $isSent = sendEmailWithRetries($retryCount, $email);

    if ($isSent) {
        logM("Sending success {$email}");
        updateSendTaskResult($pdo, $id, true);
        logM("Task finished {$email}");
        return true;
    }

    updateSendTaskResult($pdo, $id, false);
    logM("Task finished {$email} with failed, all retry exceeded");
    return false;
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