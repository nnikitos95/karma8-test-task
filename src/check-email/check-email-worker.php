<?php

/** @var PDO $pdo */
$pdo = require __DIR__ .  '/../db/db.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../log/log.php';

$workerId = (int)getenv("WORKER_ID");

if ($workerId <= 0) {
    logM("Wrong worker ${workerId}");
    exit(1);
}

runCheckEmailWorker($pdo, $workerId);
