<?php

namespace Spartan\Message\Test;

use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Spartan\Http\Header\Attachment;
use Spartan\Message\Message\Email;
use Spartan\Message\Provider\Mail\Mailtrap;
use Spartan\Message\Provider\Mail\Smtp;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class SmtpTest extends TestCase
{
    public function testAddresses()
    {
        $email = (new Smtp([], new Response()))->email(
            (new Email())
                ->from('John Doe <john.doe@gmail.com>')
                ->to('jane@example.com', 'Jane')
                ->cc('cc@example.com')
                ->html('Hello world!')
        );

        $this->assertSame('john.doe@gmail.com', $email->getFrom()[0]->getAddress());
        $this->assertSame('John Doe', $email->getFrom()[0]->getName());
        $this->assertSame('jane@example.com', $email->getTo()[0]->getAddress());
        $this->assertSame('Jane', $email->getTo()[0]->getName());
        $this->assertSame('cc@example.com', $email->getCc()[0]->getAddress());
        $this->assertSame([], $email->getBcc());
    }

    public function testSubjectBodyAndAttachments()
    {
        $smtp = new Smtp([], new Response());

        $html = $smtp->email(
            (new Email())
                ->from('a@example.com')
                ->subject('Hello')
                ->html('<h1>Hi</h1>')
                ->attachment([Attachment::fromContents('attach.txt', 'Attached')])
        );

        $this->assertSame('Hello', $html->getSubject());
        $this->assertSame('<h1>Hi</h1>', $html->getHtmlBody());
        $this->assertNull($html->getTextBody());
        $this->assertCount(1, $html->getAttachments());
        $this->assertSame('attach.txt', $html->getAttachments()[0]->getFilename());

        $text = $smtp->email((new Email())->from('a@example.com')->text('Plain'));

        $this->assertSame('Plain', $text->getTextBody());
        $this->assertNull($text->getHtmlBody());
    }

    public function testTransport()
    {
        $transport = (new Smtp(['host' => 'smtp.example.com', 'port' => '2525', 'user' => 'u', 'pass' => 'p'], new Response()))
            ->transport();

        $this->assertInstanceOf(EsmtpTransport::class, $transport);
        $this->assertSame('u', $transport->getUsername());
        $this->assertSame('p', $transport->getPassword());
        $this->assertSame('smtp://smtp.example.com:2525', (string)$transport);
    }

    public function testTransportRaisesNoDeprecation()
    {
        // apps turn every notice into an exception (Swift_SmtpTransport raised a deprecation on PHP 8.2+)
        set_error_handler(
            function (int $severity, string $message, string $file = '', int $line = 0) {
                throw new \ErrorException($message, 0, $severity, $file, $line);
            }
        );

        try {
            $transport = (new Smtp(['host' => 'smtp.example.com', 'port' => 2525], new Response()))->transport();
        } finally {
            restore_error_handler();
        }

        $this->assertInstanceOf(EsmtpTransport::class, $transport);
    }

    public function testCreateKeepsSubclass()
    {
        $container = $this->createMock(\Psr\Container\ContainerInterface::class);
        $container->method('get')->willReturn(new Response());

        $this->assertInstanceOf(Mailtrap::class, Mailtrap::create(['host' => 'smtp.mailtrap.io'], $container));
    }
}
