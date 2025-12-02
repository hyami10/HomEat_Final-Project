<?php

return [
    'seed' => [
        'name' => env('ADMIN_NAME', 'Super Admin'),
        'email' => env('ADMIN_EMAIL', 'admin@homeat.com'),
        'password' => env('ADMIN_PASSWORD'), 
        'phone' => env('ADMIN_PHONE'),
        'address' => env('ADMIN_ADDRESS'),
        'profile_photo' => env('ADMIN_PROFILE_PHOTO'),
        'verify_email' => env('ADMIN_VERIFY_EMAIL', true),
    ],

    'user_seed' => [
        'email' => env('USER_DEFAULT_EMAIL', 'user@homeat.com'),
        'name' => env('USER_DEFAULT_NAME', 'User Biasa'),
        'password' => env('USER_DEFAULT_PASSWORD'), 
    ],
];
