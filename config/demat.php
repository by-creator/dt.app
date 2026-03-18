<?php

return [
    'recipients' => array_values(array_filter(array_map(
        static fn (string $email) => trim($email),
        explode(',', env('DEMAT_RECIPIENTS', 'iosid242@gmail.com,noreplysitedt@gmail.com'))
    ))),

    'director_email' => env('DEMAT_DIRECTOR_EMAIL', 'bongoyebamarcdamien@yahoo.fr'),
];
