<?php

return [
    /*
     * Message providers:
     * - mailgun
     * - mailtrap
     * - mailjet
     */
    'adapter'  => getenv('MESSAGE_ADAPTER') ?: 'mailtrap',

    /*
     *
     * MAIL providers
     *
     */

    /*
     * Mailtrap provider
     */
    'mailtrap' => [
        'host' => 'smtp.mailtrap.io',
        'port' => 2525,
        'user' => getenv('MAILTRAP_USER'),
        'pass' => getenv('MAILTRAP_PASS'),
    ],

    /*
     * Mailgun provider
     */
    'mailgun'  => [
        'url'     => 'https://api.mailgun.net/v3',
        'api_key' => getenv('MAILGUN_API_KEY'),
        'domain'  => getenv('MAILGUN_DOMAIN'),
    ],

    /*
     * Mailjet provider
     */
    'mailjet'  => [
        'url'             => 'https://api.mailjet.com/v3/send',
        'api_key_public'  => getenv('MAILJET_PUBLIC_KEY'),
        'api_key_private' => getenv('MAILJET_PRIVATE_KEY'),
    ],

    /*
     *
     * SMS providers
     *
     */

    /*
     * BulkSMS provider
     */
    'bulksms'  => [
        'url'     => 'https://api.bulksms.com/v1/messages',
        'app_id'  => getenv('BULKSMS_APP_ID'),
        'app_key' => getenv('BULKSMS_APP_KEY'),
    ],
];
