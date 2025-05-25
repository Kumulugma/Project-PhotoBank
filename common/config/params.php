<?php
return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Zasobnik B',
    'user.passwordResetTokenExpire' => 3600,
    'user.passwordMinLength' => 8,
    
    // Katalogi przesyłanych plików
    'uploadsPath' => dirname(__DIR__, 2) . '/public_html/uploads',
    'uploadsUrl' => '/uploads',
    
    // Limity plików
    'maxUploadSize' => 100 * 1024 * 1024, // 100MB
    
    // Konfiguracja miniatur
    'thumbnails' => [
        'defaultSizes' => [
            'thumb' => ['width' => 150, 'height' => 150, 'crop' => true, 'watermark' => false],
            'medium' => ['width' => 400, 'height' => 300, 'crop' => false, 'watermark' => false],
            'large' => ['width' => 800, 'height' => 600, 'crop' => false, 'watermark' => true],
        ],
    ],
    
    // Typy MIME
    'allowedImageTypes' => [
        'image/jpeg',
        'image/png',
        'image/gif',
    ],
];