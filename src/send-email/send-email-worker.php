<?php

$RETRY_COUNT = 10;

/** @var PDO $pdo */
$pdo = require __DIR__ .  '/../db/db.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../log/log.php';
require_once __DIR__ . '/../send-email/service.php';

$workerId = (int)getenv("WORKER_ID");

if ($workerId <= 0) {
    logM("Wrong worker ${workerId}");
    exit(1);
}

runSendEmailWorker($pdo, $workerId, 10);