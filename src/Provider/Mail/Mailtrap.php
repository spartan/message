<?php

namespace Spartan\Message\Provider\Mail;

/**
 * Mailtrap Mail
 *
 * @package Spartan\Message
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Mailtrap extends Smtp
{
    protected array $config = [
        'host' => 'smtp.mailtrap.io',
        'port' => 2525,
    ];
}
