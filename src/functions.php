<?php

function check_mail($mail): bool
{
    sleep(random_int(1, 60));

    return random_int(0, 1);
}

function send_email($email, $from, $to, $subj, $body): bool
{
    sleep(random_int(1, 10));

    return random_int(0, 1);
}