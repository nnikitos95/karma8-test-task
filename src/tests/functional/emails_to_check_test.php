<?php

declare(strict_types=1);

$pdo = require __DIR__ . '/../../db/db.php';
require_once __DIR__ . '/../../check-email/service.php';
require_once __DIR__ . '/../../repository/emails_to_check_repository.php';
require_once __DIR__ . '/../../repository/users_repository.php';
require_once __DIR__ . '/base-test.php';
require_once __DIR__ . '/helpers.php';

function checkEmailTaskTest(PDO $db): array {
    $email = 'test@mail.com';

    $userId = addUser(
        $db,
        'test',
        $email,
        time(),
    );

    processCreateCheckEmailTasks($db, 1);

    $emailCheckTask = getEmailCheckTaskByEmail($db, $email);
    if (empty($emailCheckTask)) {
        return makeFailedResult("Empty array from db");
    }

    if ($emailCheckTask['worker'] !== 0) {
        return makeFailedResult("Expected email_to_check.worker != 0, actual ${emailCheckTask['worker']}");
    }

    $workerId = 1;

    $valid = processCheckEmail($db, $workerId);

    $emailCheckTask = getEmailCheckTaskByEmail($db, $email);
    if ($emailCheckTask['checked'] !== true) {
        return makeFailedResult("Expected email_to_check.checked = true, actual ${emailCheckTask['checked']}");
    }

    if ($emailCheckTask['valid'] !== $valid) {
        return makeFailedResult("Expected email_to_check.valid = ${valid}, actual ${emailCheckTask['valid']}");
    }

    if ($emailCheckTask['worker'] !== 0) {
        return makeFailedResult("Expected email_to_check.worker = 0, actual ${emailCheckTask['worker']}");
    }

    $user = getUserById($db, $userId);
    if ($user['checked'] !== true) {
        return makeFailedResult("Expected users.checked = true, actual ${user['checked']}");
    }

    if ($user['valid'] !== $valid) {
        return makeFailedResult("Expected users.valid = ${valid}, actual ${user['valid']}");
    }

    return makeSuccessResult();
}