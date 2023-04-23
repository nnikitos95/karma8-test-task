<?php

$pdo = require __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../log/log.php';
require_once __DIR__ . '/service.php';

$limit = getenv('LIMIT') ?: 1000;
$DAYS_BEFORE = 3;

runCreateSendEmailTasks($pdo, $limit, $DAYS_BEFORE);