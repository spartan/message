<?php

namespace Spartan\Message\Provider\Alert;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Spartan\Message\Provider\Http;

/**
 * NTFY
 *
 * @see     https://ntfy.sh/
 * @package Spartan\Message
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Ntfy extends Http
{
    /**
     * @var mixed[]
     */
    protected array $config = [
        'url'  => '',
        'user' => '',
        'pass' => '',
    ];

    /**
     * @param MessageInterface $message
     *
     * @return RequestInterface
     */
    public function request(MessageInterface $message): RequestInterface
    {
        $request = $this->factory
            ->createRequest('POST', "{$this->config['url']}/{$message->getHeaderLine('topic')}")
            ->withHeader('Content-type', 'application/json')
            ->withHeader('Authorization', 'Basic ' . base64_encode("{$this->config['user']}:{$this->config['pass']}"));

        foreach ($message->getHeaders() as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $request;
    }
}
