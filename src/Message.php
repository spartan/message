<?php

namespace Spartan\Message;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Spartan\Message\Definition\ProviderInterface;
use Spartan\Message\Provider\Alert\Ntfy;
use Spartan\Message\Provider\Mail\Mailgun;
use Spartan\Message\Provider\Mail\Mailjet;
use Spartan\Message\Provider\Mail\Mailtrap;

/**
 * Message Facade
 *
 * @package Spartan\Message
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Message
{
    const PROVIDERS = [
        'mailgun'  => Mailgun::class,
        'mailtrap' => Mailtrap::class,
        'mailjet'  => Mailjet::class,
        'ntfy'     => Ntfy::class,
    ];

    /**
     * @param string|null $name
     *
     * @return ProviderInterface
     */
    public static function provider($name = null, ContainerInterface $container = null): ProviderInterface
    {
        $config        = require './config/message.php';
        $adapter       = $name ?: $config['adapter'];
        $adapterConfig = $config[$adapter];

        $providerClass = self::PROVIDERS[$adapter];

        if (!$container && function_exists('container')) {
            $container = container();
        } else {
            throw new \InvalidArgumentException('Missing container!');
        }

        return $providerClass::create($adapterConfig, $container);
    }

    /**
     * @param MessageInterface $message
     * @param mixed            $provider
     *
     * @return ResponseInterface
     */
    public static function send(MessageInterface $message, $provider = null): ResponseInterface
    {
        return self::provider($provider)->send($message);
    }
}
