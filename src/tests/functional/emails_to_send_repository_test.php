<?php

declare(strict_types=1);

$pdo = require __DIR__ . '/../../db/db.php';
require_once __DIR__ . '/base-test.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../../repository/emails_to_send_repository.php';
require_once __DIR__ . '/../../repository/users_repository.php';

function addEmailToSendTest(PDO $db): array {
    $id = addEmailToSendTask($db, 'test@mail.com', time());

    $rowFromDb = getEmailSendTaskById($db, $id);

    if (empty($rowFromDb)) {
        return makeFailedResult("Returned empty array");
    }

    if ($rowFromDb['worker'] !== 0) {
        return makeFailedResult("Expected worker 0, actual ${rowFromDb['worker']}");
    }

    if ($rowFromDb['sent'] !== false) {
        return makeFailedResult("Expected sent = false, actual ${rowFromDb['worker']}");
    }

    return makeSuccessResult();
}

function getEmailToSendTest(PDO $db): array
{
    $email = 'test@mail.com';
    $time = time();

    addUser($db, 'test', $email, $time, false, true);

    $emailsToSend = getEmailsToSend($db, 1, $time - 100, $time + 100);

    if (count($emailsToSend) !== 1) {
        return makeFailedResult("Emails to send is empty");
    }

    $emailsToSend = $emailsToSend[0];

    if ($emailsToSend['email'] !== $email) {
        return makeFailedResult("Expected email = $email, actual ${emailsToSend['email']}");
    }

    if ($emailsToSend['validts'] !== $time) {
        return makeFailedResult("Expected validts = ${time}, actual ${emailsToSend['validts']}");
    }

    return makeSuccessResult();
}

function getEmailToSendTaskByEmailAndValidtsTest(PDO $db): array
{
    $email = 'test@mail.com';
    $time = time();
    $taskId = addEmailToSendTask($db, $email, $time);

    $task = getEmailSendTaskByEmailAndValidts($db, $email, $time);
    if (empty($task)) {
        return makeFailedResult("Task by email ${email} and validts ${time} is not found");
    }

    if ($task['id'] !== $taskId) {
        return makeFailedResult("Expected task id = ${taskId}, actual ${task['id']}");
    }

    if ($task['email'] !== $email) {
        return makeFailedResult("Expected task email = ${email}, actual ${task['email']}");
    }

    if ($task['validts'] !== $time) {
        return makeFailedResult("Expected task validts = ${time}, actual ${task['validts']}");
    }

    return makeSuccessResult();
}

function getEmailSendTaskForWorkerWhenNoTasksTest(PDO $db): array
{
    $workerId = 1;
    $task = getSendTaskForWorker($db, $workerId);

    if (!empty($task)) {
        return makeFailedResult("Array is not empty");
    }

    return makeSuccessResult();
}

function getEmailSendTaskForWorkerWhenHaveFreeTasksTest(PDO $db): array
{
    $email = 'test@mail.com';
    $time = time();
    $id = addEmailToSendTask($db, $email, $time);

    $workerId = 1;
    $task = getSendTaskForWorker($db, $workerId);

    if (empty($task)) {
        return makeFailedResult("Array is empty");
    }

    if ($task['id'] !== $id) {
        return makeFailedResult("Expected id ${id}, actual ${task['id']}");
    }

    if ($task['email'] !== $email) {
        return makeFailedResult("Expected email ${email}, actual ${task['email']}");
    }

    return makeSuccessResult();
}