<?php

return [
    'driver' => env('FCM_PROTOCOL', 'http'),
    'log_enabled' => false,

    'http' => [
        'server_key' => 'AAAA1B2IfV4:APA91bE383c41DwhZDStwEnwXGf5RYmAVvBti8wShlvZie_BjnJI6AloXCIdeGgyaS6Us-Kc_-_pLVcemgORb8Br-jL3VQK4FRGboGAH7K-mvsnKI_z5PHnkJwsJJDOqAv1jjwYjpPsN',
        'sender_id' => '911028551006',
        // 'server_key' => env('FCM_SERVER_KEY', 'Your FCM server key'),
        // 'sender_id' => env('FCM_SENDER_ID', 'Your sender id'),
        'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
        'server_group_url' => 'https://android.googleapis.com/gcm/notification',
        'timeout' => 30.0, // in second
    ],
];
