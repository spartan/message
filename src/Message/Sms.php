<?php

namespace Spartan\Message\Message;

use Psr\Http\Message\MessageInterface;
use Spartan\Http\Message;

class Sms extends Message
{
    const ROUTE_ECONOMY  = 'ECONOMY';
    const ROUTE_STANDARD = 'STANDARD';
    const ROUTE_PREMIUM  = 'PREMIUM';

    const ENCODING_TEXT    = 'TEXT';
    const ENCODING_UNICODE = 'UNICODE';
    const ENCODING_BINARY  = 'BINARY';

    /**
     * @param $sender
     *
     * @return MessageInterface|Email
     */
    public function from($sender): MessageInterface
    {
        return $this->withHeader('from', $sender);
    }

    /**
     * @param $number
     *
     * @return MessageInterface|Email
     */
    public function to($number): MessageInterface
    {
        return $this->withAddedHeader('to', $number);
    }

    /**
     * @param string $text
     *
     * @return MessageInterface|Email
     */
    public function body(string $text): MessageInterface
    {
        return $this->withHeader('body', $text);
    }

    public function encoding(string $encoding = self::ENCODING_TEXT)
    {
        return $this->withHeader('encoding', $encoding);
    }

    public function route(string $route = self::ROUTE_STANDARD)
    {
        return $this->withHeader('route', $route);
    }
}
