<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),
    'frontend_url'=>env('FRONTEND_URL'),
    'petapikey' => env('PETAPIKEY'),
    'petid'     => env('PETID'),
    'peturl'    => env('PETURL'),
    'mail_mailer' => env('MAIL_MAILER'),
    'mail_host' => env('MAIL_HOST'),
    'mail_port' => env('MAIL_PORT'),
    'mail_username' => env('MAIL_USERNAME'),
    'mail_password' => env('MAIL_PASSWORD'),
    'mail_encryption' => env('MAIL_ENCRYPTION'),
    'mail_from_address' => env('MAIL_FROM_ADDRESS'),
    'mail_from_name' => env('MAIL_FROM_NAME'),
    'mail_support_address' => env('MAIL_SUPPORT_ADDRESS'),
    'mail_support_name' => env('MAIL_SUPPORT_NAME'),

    'petshop_mail_host' => env('PETSHOP_MAIL_HOST') ?? env('MAIL_HOST'),
    'petshop_mail_port' => env('PETSHOP_MAIL_PORT') ?? env('MAIL_PORT'),
    'petshop_mail_username' => env('PETSHOP_MAIL_USERNAME') ?? env('MAIL_USERNAME'),
    'petshop_mail_password' => env('PETSHOP_MAIL_PASSWORD') ?? env('MAIL_PASSWORD'),
    'petshop_mail_encryption' => env('PETSHOP_MAIL_ENCRYPTION') ?? env('MAIL_ENCRYPTION'),
    'petshop_mail_from_address' => env('PETSHOP_MAIL_FROM_ADDRESS') ?? env('MAIL_FROM_ADDRESS'),
    'petshop_mail_from_name' => env('PETSHOP_MAIL_FROM_NAME') ?? env('MAIL_FROM_NAME'),
    'petshop_mail_support_address' => env('PETSHOP_MAIL_SUPPORT_ADDRESS') ?? env('MAIL_SUPPORT_ADDRESS'),
    'petshop_mail_support_name' => env('PETSHOP_MAIL_SUPPORT_NAME') ?? env('MAIL_SUPPORT_NAME'),

    'google_place_api'=> env('GOOGLE_PLACE_API'),
    'google_place_url'=> env('GOOGLE_PLACE_URL'),
    
    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    'stripe_key' => env('STRIPE_KEY'),
    'stripe_secret' => env('STRIPE_SECRET'),

    'petshop_stripe_key' => env('PETSHOP_STRIPE_KEY') ?? env('STRIPE_KEY'),
    'petshop_stripe_secret' => env('PETSHOP_STRIPE_SECRET') ?? env('STRIPE_SECRET'),
];
