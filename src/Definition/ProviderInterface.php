<?php

namespace Spartan\Message\Definition;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * ProviderInterface
 *
 * @package Spartan\Message
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
interface ProviderInterface
{
    public function send(MessageInterface $message): ResponseInterface;
}
