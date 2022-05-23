<?php

namespace Spartan\Message\Provider\Sms;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Spartan\Http\Response;
use Spartan\Message\Definition\ProviderInterface;

/**
 * Local SMS for testing purposes
 *
 * @package Spartan\Message
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class LocalSms implements ProviderInterface
{
    /**
     * @var mixed[]
     */
    protected array $config = [
        'path' => './text_messages',
    ];

    /**
     * Http constructor.
     *
     * @param array $config
     */
    public final function __construct(array $config = [])
    {
        $this->config = $config + $this->config;
    }

    public function send(MessageInterface $message): ResponseInterface
    {
        file_put_contents($this->config['path'], json_encode($message->getHeaders()) . PHP_EOL, FILE_APPEND);

        return (new Response())->withStatus(200);
    }
}
