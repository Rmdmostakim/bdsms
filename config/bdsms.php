<?php
return [
    'drivers' => [
        'mimsms' => [
            'api_key' => env('MIM_SMS_API_KEY'),
            'user_name' => env('MIM_SMS_USER_NAME'),
            'sender_name' => env('MIM_SMS_SENDER_NAME'),
            'api_url' => env('MIM_SMS_API_URL', 'https://api.mimsms.com/api/SmsSending/SMS')
        ],
        'sslwireless' => [
            'api_token' => env('SSL_WIRELESS_API_TOKEN'),
            'sid' => env('SSL_WIRELESS_SID'),
            'api_url' => env('SSL_WIRELESS_API_URL', 'https://smsplus.sslwireless.com/api/v3/send-sms')
        ],
        'twilio' => [
            'sid' => env('TWILIO_ACCOUNT_SID'),
            'number' => env('TWILIO_NUMBER'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
        ],
        'infobip' => [
            'api_key' => env('INFOBIP_API_KEY'),
            'number' => env('INFOBIP_NUMBER'),
            'api_url' => env('INFOBIP_API_URL', 'https://api.infobip.com/sms/2/text/advanced')
        ],
    ]
];
