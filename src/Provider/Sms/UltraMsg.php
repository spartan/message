<?php

namespace Spartan\Message\Provider\Sms;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Spartan\Message\Provider\Http;

/**
 * UltraMsg Mail
 *
 * @package Spartan\Message
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class UltraMsg extends Http
{
    /**
     * @var mixed[]
     */
    protected array $config = [
        'url'     => 'https://api.ultramsg.com/%s/messages/chat',
        'app_id'  => '', // instance #
        'app_key' => '', // token
    ];

    /**
     * @see https://www.bulksms.com/developer/json/v1/#tag/Message%2Fpaths%2F~1messages%2Fpost
     *
     * @param MessageInterface $message
     *
     * @return RequestInterface
     */
    public function request(MessageInterface $message): RequestInterface
    {
        $params = [
            'token' => $this->config['app_key'],
            'to'    => $message->getHeaderLine('to'),
            'body'  => $message->getHeaderLine('body') ?: (string)$message->getBody(),
        ];

        return $this->factory
            ->createRequest(
                'GET',
                sprintf(
                    $this->config['url'],
                    $this->config['app_id']
                ) . '?' . http_build_query($params)
            );
    }
}
