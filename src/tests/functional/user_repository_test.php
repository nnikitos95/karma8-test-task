<?php

declare(strict_types=1);

$pdo = require __DIR__ . '/../../db/db.php';
require_once __DIR__ . '/base-test.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../../repository/users_repository.php';

function addUserTest(PDO $db): array {
    $email = 'test@mail.com';

    $userId = addUser(
        $db,
        'test',
        $email,
        time(),
    );

    $user = getUserById($db, $userId);

    if (empty($user)) {
        return makeFailedResult("Returned array is not empty");
    }

    return makeSuccessResult();
}