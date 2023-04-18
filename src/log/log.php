<?php

function logM($message): void
{
    $data = date('d.m.Y H:i:s') . ': ';
    echo $data . $message . "\n";
}