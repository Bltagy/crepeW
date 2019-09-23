<?php

return [
  'gcm' => [
      'priority' => 'normal',
      'dry_run' => false,
      'apiKey' => 'My_ApiKey',
  ],
  'fcm' => [
        'priority' => 'normal',
        'dry_run' => false,
        'apiKey' => 'AAAA1B2IfV4:APA91bE383c41DwhZDStwEnwXGf5RYmAVvBti8wShlvZie_BjnJI6AloXCIdeGgyaS6Us-Kc_-_pLVcemgORb8Br-jL3VQK4FRGboGAH7K-mvsnKI_z5PHnkJwsJJDOqAv1jjwYjpPsN',
  ],
  'apn' => [
      'certificate' => __DIR__ . '/iosCertificates/Certificates_dev_7.pem',
      // 'passPhrase' => '1234', //Optional
      // 'passFile' => __DIR__ . '/iosCertificates/yourKey.pem', //Optional
      'dry_run' => true
  ]
];