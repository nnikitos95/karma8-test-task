<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../../log/log.php';
function functionalTest(PDO $pdo, callable $testFunc): array
{
    try {
        $pdo->beginTransaction();

        $result = $testFunc($pdo);

        $pdo->rollBack();

        return $result;

    } catch (Throwable $exception) {
        $pdo->rollBack();
        return makeResult(false, "Exception: {$exception->getMessage()}");
    }
}
