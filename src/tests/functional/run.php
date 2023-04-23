<?php

declare(strict_types=1);

$pdo = require __DIR__ . '/../../db/db.php';
require_once __DIR__ . '/base-test.php';
require_once __DIR__ . '/emails_to_check_repository_test.php';
require_once __DIR__ . '/emails_to_send_repository_test.php';
require_once __DIR__ . '/user_repository_test.php';
require_once __DIR__ . '/emails_to_check_test.php';
require_once __DIR__ . '/emails_to_send_test.php';
require_once __DIR__ . '/../../log/log.php';
require_once __DIR__ . '/helpers.php';

$repositoryTests = [
    'addUserTest',
    'addEmailToCheckTest',
    'getEmailCheckTaskForWorkerWhenNoTasksTest',
    'getEmailCheckTaskForWorkerWhenHaveFreeTasksTest',
    'addEmailToSendTest',
    'getEmailToSendTest',
    'getEmailToSendTaskByEmailAndValidtsTest',
    'getEmailSendTaskForWorkerWhenNoTasksTest',
    'getEmailSendTaskForWorkerWhenHaveFreeTasksTest',
];

$testsToRun = [
    'checkEmailTaskTest',
    'sendEmailTaskTest',
];

$allTests = array_merge($repositoryTests, $testsToRun);
$success = $failed = 0;
$all = count($allTests);

foreach ($allTests as $testFunc) {
    if (!is_callable($testFunc)) {
        $failed++;
        logM(wrapStrWithColor("Test $testFunc is not callable", ERROR));
        continue;
    }

    $result = functionalTest($pdo, $testFunc);
    if ($result['result'] === true) {
        $success++;
        logM(wrapStrWithColor("Test $testFunc passed", SUCCESS));
    } else {
        $failed++;
        logM(wrapStrWithColor("Test $testFunc failed with error: ${result['error']}", ERROR));
    }
}

logM(wrapStrWithColor("Tests finished, all: ${all}, successfully: ${success}, failed: ${failed}"));

exit($all === $success ? 0 : 1);
