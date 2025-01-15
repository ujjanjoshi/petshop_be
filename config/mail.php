<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => config('app.mail_mailer', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "log", "array", "failover", "roundrobin"
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'url' => env('MAIL_URL'),
            'host' => config('app.mail_host', '127.0.0.1'),
            'port' => config('app.mail_port', 2525),
            'encryption' => config('app.mail_encryption', 'tls'),
            'username' => config('app.mail_username'),
            'password' => config('app.mail_password'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
            'from' => [
                'address' => config('app.mail_from_address', 'hello@example.com'),
                'name' => config('app.mail_from_name', 'Example'),
            ],
        ],

        'petShop_smtp' => [
            'transport' => 'smtp',
            'url' => env('MAIL_URL'),
            'host' => config('app.petshop_mail_host', '127.0.0.1'),
            'port' => config('app.petshop_mail_port', 2525),
            'encryption' => config('app.petshop_mail_encryption', 'tls'),
            'username' => config('app.petshop_mail_username'),
            'password' => config('app.petshop_mail_password'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
            'from' => [
                'address' => config('app.petshop_mail_from_address', 'hello@example.com'),
                'name' => config('app.petshop_mail_from_name', 'Example'),
            ],
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    'from' => [
        'address' => config('app.mail_from_address', 'hello@example.com'),
        'name' => config('app.mail_from_name', 'Example'),
    ],

];
