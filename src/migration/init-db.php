<?php

$pdo = require __DIR__ . '/../db/db.php';

$pdo->exec("DROP TABLE IF EXISTS users");
$pdo->exec("DROP TABLE IF EXISTS emails_to_check");
$pdo->exec("DROP TABLE IF EXISTS emails_to_send");

$pdo->exec("CREATE TABLE users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            validts INTEGER NOT NULL,
            confirmed BOOLEAN NOT NULL DEFAULT FALSE,
            checked BOOLEAN NOT NULL DEFAULT FALSE,
            valid BOOLEAN NOT NULL DEFAULT FALSE
)");
$pdo->exec("CREATE INDEX valid_idx ON users(valid)");

$pdo->exec("CREATE TABLE emails_to_check (
            email VARCHAR(255) PRIMARY KEY,
            valid BOOLEAN NOT NULL DEFAULT FALSE,
            checked BOOLEAN NOT NULL DEFAULT FALSE,
            worker INTEGER NOT NULL DEFAULT 0
)");

$pdo->exec("CREATE TABLE emails_to_send (
          id SERIAL PRIMARY KEY,
          email VARCHAR(255) NOT NULL,
          validts INTEGER NOT NULL,
          worker INTEGER NOT NULL DEFAULT 0,
          sent BOOLEAN NOT NULL DEFAULT FALSE
)");

$pdo->exec("CREATE UNIQUE INDEX validts_email_unique ON emails_to_send(email, validts)");