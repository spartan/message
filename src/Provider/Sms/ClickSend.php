<?php

namespace Spartan\Message\Provider\Sms;

use Laminas\Diactoros\Stream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Spartan\Message\Provider\Http;

/**
 * ClickSend
 *
 * @package Spartan\Message
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class ClickSend extends Http
{
    /**
     * @var mixed[]
     */
    protected array $config = [
        'url'     => 'https://rest.clicksend.com/v3/sms/send',
        'app_id'  => '',
        'app_key' => '',
    ];

    /**
     * @see https://developers.clicksend.com/docs/rest/v3/?shell#ClickSend-v3-API-SMS
     *
     * @param  MessageInterface  $message
     *
     * @return RequestInterface
     */
    public function request(MessageInterface $message): RequestInterface
    {
        $request = $this->factory
            ->createRequest('POST', $this->config['url'])
            ->withHeader(
                'Authorization',
                'Basic ' . base64_encode("{$this->config['app_id']}:{$this->config['app_key']}")
            );

        $params = [
            'messages' => [
                [
                    'to'     => $message->getHeaderLine('to'),
                    'source' => 'php',
                    'body'   => $message->getHeaderLine('body') ?: (string)$message->getBody(),
                ],
            ],
        ];

        $body = new Stream('php://memory', 'r+');
        $body->write((string)json_encode($params));

        return $request
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);
    }
}
