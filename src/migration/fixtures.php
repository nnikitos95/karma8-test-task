<?php

require_once __DIR__ . '/../log/log.php';
$pdo = require __DIR__ . '/../db/db.php';

$cnt_users = getenv('MIGRATION_USERS') ?: 10000;

/**
 * @var PDO $pdo
 */


$pdo->exec("TRUNCATE users");
//$pdo->exec("TRUNCATE emails_to_send");

for ($i = 1; $i <= $cnt_users; $i++) {
    $username  = sprintf('user_%s', $i);
    $email     = sprintf('email_%s@test.com', $i);
    $confirmed = random_int(0, 1);
    // так как в задании не указано, предположим, что если $confirmed = 1, то нет смысла проверять на валидность
    if ($confirmed) {
        $checked = 1;
        $valid = 1;
    } else {
        $valid = random_int(0, 1);
        // если $valid = 1, очевидно, что нет смысла его проверять
        $checked = $valid ?: random_int(0, 1);
    }

    $currentTimestamp = time();
    $minTimestamp     = strtotime('-1 month', $currentTimestamp);
    $maxTimestamp     = strtotime('+1 month', $currentTimestamp);
    $validts          = random_int($minTimestamp, $maxTimestamp);

    $stmt = $pdo->prepare('INSERT INTO users (username, email, validts, confirmed, valid, checked) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$username, $email, $validts, $confirmed, $valid, $checked]);

    logM(sprintf("User %s#d added", $email, $pdo->lastInsertId('users_id_seq')));
}
