<?php

return [
    
    'frame_options' => env('SECURITY_FRAME_OPTIONS', 'SAMEORIGIN'),
    'xss_protection' => env('SECURITY_XSS_PROTECTION', '1; mode=block'),
    'content_type_options' => env('SECURITY_CONTENT_TYPE_OPTIONS', 'nosniff'),
    'referrer_policy' => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
    'permissions_policy' => env('SECURITY_PERMISSIONS_POLICY', "geolocation=(), microphone=(), camera=(), payment=(), usb=()"),
];
