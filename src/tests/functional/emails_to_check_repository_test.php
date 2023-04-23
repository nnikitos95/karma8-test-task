<?php

declare(strict_types=1);

$pdo = require __DIR__ . '/../../db/db.php';
require_once __DIR__ . '/base-test.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../../repository/emails_to_check_repository.php';

function addEmailToCheckTest(PDO $db): array {
    $insertedEmail = addEmailToCheck($db, 'test@mail.com');

    $rowFromDb = getEmailCheckTaskByEmail($db, $insertedEmail);

    if (empty($rowFromDb)) {
        return makeFailedResult("Empty array from db");
    }

    return makeSuccessResult();
}

function getEmailCheckTaskForWorkerWhenNoTasksTest(PDO $db): array
{
    $workerId = 1;
    $email = getCheckTaskForWorker($db, $workerId);

    if ($email !== null) {
        return makeFailedResult("Expected null, actual ${email}");
    }

    return makeSuccessResult();
}

function getEmailCheckTaskForWorkerWhenHaveFreeTasksTest(PDO $db): array
{
    $insertedEmail = addEmailToCheck($db, 'test@mail.com');

    $workerId = 1;
    $email = getCheckTaskForWorker($db, $workerId);

    if ($email !== $insertedEmail) {
        return makeFailedResult("Expected mail = ${insertedEmail}, actual ${email}");
    }

    return makeSuccessResult();
}