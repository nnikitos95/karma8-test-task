<?php

declare(strict_types=1);

$pdo = require __DIR__ . '/../../db/db.php';
require_once __DIR__ . '/../../send-email/service.php';
require_once __DIR__ . '/../../repository/emails_to_check_repository.php';
require_once __DIR__ . '/../../repository/users_repository.php';
require_once __DIR__ . '/base-test.php';
require_once __DIR__ . '/helpers.php';

function sendEmailTaskTest(PDO $db): array {
    $email = 'test@mail.com';
    $time = time() - 100;

    addUser(
        $db,
        'test',
        $email,
        $time,
        false,
        true
    );

    processCreateSendEmailTask($db, 1, time(), 1);

    $emailSendTask = getEmailSendTaskByEmailAndValidts($db, $email, $time);
    if (empty($emailSendTask)) {
        return makeFailedResult("Empty array from db");
    }

    $workerId = 1;

    $sent = processSendEmail($db, $workerId, 10);

    $emailSendTask = getEmailSendTaskByEmailAndValidts($db, $email, $time);
    if ($emailSendTask['sent'] !== $sent) {
        return makeFailedResult("Expected email_to_send.sent = ${sent}, actual ${emailSendTask['sent']}");
    }

    if ($emailSendTask['worker'] !== 0) {
        return makeFailedResult("Expected email_to_send.worker = true, actual ${emailSendTask['worker']}");
    }

    return makeSuccessResult();
}