<?php

$host = getenv('DB_HOST');
$db = getenv('DB');
$user = 'postgres';
$password = 'password';

return ['host' => $host, 'db' => $db, 'user' => $user, 'password' => $password];