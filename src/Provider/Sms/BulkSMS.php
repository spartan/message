<?php

namespace Spartan\Message\Provider\Sms;

use Laminas\Diactoros\Stream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Spartan\Message\Message\Sms;
use Spartan\Message\Provider\Http;

/**
 * Mailjet Mail
 *
 * @package Spartan\Message
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class BulkSMS extends Http
{
    /**
     * @var mixed[]
     */
    protected array $config = [
        'url'     => 'https://api.bulksms.com/v1/messages',
        'app_id'  => '',
        'app_key' => '',
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
        $request = $this->factory
            ->createRequest('POST', $this->config['url'])
            ->withHeader(
                'Authorization',
                'Basic ' . base64_encode("{$this->config['app_id']}:{$this->config['app_key']}")
            );

        $params = [
                'from'         => $message->getHeaderLine('from'),
                'to'           => $message->getHeader('to'), // array
                'body'         => $message->getHeaderLine('body'),
                'routingGroup' => $message->getHeaderLine('route') ?: Sms::ROUTE_STANDARD,
                'encoding'     => $message->getHeaderLine('encoding') ?: Sms::ENCODING_TEXT,
            ] + $message->getHeaders();

        foreach ($params['to'] as &$datum) {
            if (is_string($datum)) {
                $datum = [
                    'type'    => 'INTERNATIONAL',
                    'address' => $datum,
                ];
            }
        }

        $body = new Stream('php://memory', 'r+');
        $body->write((string)json_encode($params));

        return $request
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);
    }
}
