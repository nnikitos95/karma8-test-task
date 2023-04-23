<?php

declare(strict_types=1);

const INFO = 'i';
const ERROR = 'e';
const SUCCESS = 's';

function wrapStrWithColor(string $str, string $type = INFO): string {
    return match ($type) {
        INFO => "\033[36m$str \033[0m",
        ERROR => "\033[31m$str \033[0m",
        SUCCESS => "\033[32m$str \033[0m",
        default => $str,
    };
}

function makeAssert(callable $fn, string $errorText): array
{
    $result = $fn();

    return makeResult($result, $result ? null : $errorText);
}

function makeResult(bool $result, ?string $errorText = null): array
{
    return [
        'result' => $result,
        'error' => $errorText,
    ];
}

function makeSuccessResult(): array
{
    return makeResult(true);
}

function makeFailedResult(string $errorText): array
{
    return makeResult(false, $errorText);
}