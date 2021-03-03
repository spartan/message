<?php

namespace Spartan\Message\Test;

use Laminas\Diactoros\Response;
use Laminas\Mail\Address;
use PHPUnit\Framework\TestCase;
use Spartan\Message\Message\Email;
use Spartan\Message\Provider\Mail\Smtp;

class SwiftTest extends TestCase
{
    public function testAddress()
    {
        $address = Address::fromString('John Doe <john.doe@gmail.com>');

        $this->assertSame('John Doe', $address->getName());
        $this->assertSame('john.doe@gmail.com', $address->getEmail());

        $smtp = new Smtp([], new Response());

        $message = (new Email())
            ->from('John Doe <john.doe@gmail.com>')
            ->html('Hello world!');

        $swiftMessage = $smtp->swiftMessage($message);

        $this->assertSame(['john.doe@gmail.com' => 'John Doe'], $swiftMessage->getFrom());
    }
}
