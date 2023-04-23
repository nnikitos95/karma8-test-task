<?php

/** @var PDO $pdo */
$pdo = require __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../repository/emails_to_check_repository.php';

$limit = (int)getenv('LIMIT') ?: 1000;

runCreateCheckEmailTasks($pdo, $limit);