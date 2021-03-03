<?php

namespace Spartan\Message\Test;

use Http\Adapter\Guzzle6\Client;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Spartan\Http\Header\Attachment;
use Spartan\Message\Provider\Mail\Mailjet;
use Spartan\Message\Provider\Mail\Mailtrap;
use Spartan\Message\Message\Email;
use Spartan\Message\Provider\Mail\Mailgun;

class MailProviderTest extends TestCase
{
    public function setUp(): void
    {
        $env = explode("\n", trim(file_get_contents(__DIR__ . '/.env')));
        foreach ($env as $line) {
            putenv($line);
        }
    }

    public function testMailgun()
    {
        $provider = new Mailgun(
            [
                'api_key' => getenv('MAILGUN_API_KEY'),
                'domain'  => getenv('MAILGUN_DOMAIN'),
            ],
            new RequestFactory(),
            new Client()
        );

        $message = (new Email())
            ->from(getenv('MAILGUN_SENDER'))
            ->to(getenv('MAIL_RECIPIENT'))
            ->subject('Test Mailgun')
            ->html("<h1>Test Mailgun</h1>")
            ->attachment([
                Attachment::fromContents('attach.txt', 'Test Mailgun')
            ]);

        $response = $provider->send($message);

        $body = (string)$response->getBody();

        echo "\n{$body}\n";

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testMailtrap()
    {
        $provider = new Mailtrap(
            [
                'user' => getenv('MAILTRAP_USER'),
                'pass'  => getenv('MAILTRAP_PASS'),
            ],
            new Response()
        );

        $message = (new Email())
            ->from(getenv('MAILTRAP_SENDER'))
            ->to(getenv('MAIL_RECIPIENT'))
            ->subject('Test Mailtrap')
            ->html("<h1>Test Mailtrap</h1>")
            ->attachment(
                [
                    Attachment::fromContents('attach.txt', 'Test Mailtrap')
                ]
            );

        $response = $provider->send($message);

        $body = (string)$response->getBody();

        echo "\n{$body}\n";

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testMailjet()
    {
        $provider = new Mailjet(
            [
                'api_key_public'  => getenv('MAILJET_PUBLIC_KEY'),
                'api_key_private' => getenv('MAILJET_PRIVATE_KEY'),
            ],
            new RequestFactory(),
            new Client()
        );

        $message = (new Email())
            ->from(getenv('MAILJET_SENDER'))
            ->to(getenv('MAIL_RECIPIENT'))
            ->subject('Test Mailjet')
            ->html("<h1>Test Mailjet</h1>")
            ->attachment(
                [
                    Attachment::fromContents('attach.txt', 'Test Mailjet')
                ]
            );

        $response = $provider->send($message);

        $body = (string)$response->getBody();

        echo "\n{$body}\n";

        $this->assertSame(200, $response->getStatusCode());
    }
}
