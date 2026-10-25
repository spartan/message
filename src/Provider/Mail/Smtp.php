<?php

namespace Spartan\Message\Provider\Mail;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Spartan\Http\Header\Attachment;
use Spartan\Http\Http;
use Spartan\Message\Definition\ProviderInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Smtp Provider
 *
 * Requires symfony/mailer
 *
 * @package Spartan\Message
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Smtp implements ProviderInterface
{
    /**
     * @var mixed[]
     */
    protected array $config = [
        'host' => '',
        'port' => '',
        'user' => '',
        'pass' => '',
        'tls'  => null, // null: SMTPS on port 465, STARTTLS when offered otherwise
    ];

    protected ResponseInterface $response;

    /**
     * Smtp constructor.
     *
     * @param mixed[]           $config
     * @param ResponseInterface $response
     */
    public function __construct(array $config, ResponseInterface $response)
    {
        $this->config   = $config + $this->config;
        $this->response = $response;
    }

    /**
     * @param mixed[]            $config
     * @param ContainerInterface $container
     *
     * @return static
     */
    public static function create(array $config, ContainerInterface $container)
    {
        return new static(
            $config,
            $container->get(ResponseInterface::class)
        );
    }

    /**
     * @return EsmtpTransport
     */
    public function transport(): EsmtpTransport
    {
        // subclasses (Mailtrap) override $config with their own defaults => keys may be missing
        $transport = new EsmtpTransport(
            (string)($this->config['host'] ?? ''),
            (int)($this->config['port'] ?? 0),
            $this->config['tls'] ?? null
        );

        if ($this->config['user'] ?? null) {
            $transport->setUsername((string)$this->config['user']);
            $transport->setPassword((string)($this->config['pass'] ?? ''));
        }

        return $transport;
    }

    /**
     * @param MessageInterface $message
     *
     * @return Email
     */
    public function email(MessageInterface $message): Email
    {
        $email = new Email();

        // addresses: "John Doe <john.doe@gmail.com>" or "john.doe@gmail.com"
        foreach (['from', 'to', 'cc', 'bcc'] as $name) {
            $addresses = array_map([Address::class, 'create'], $message->getHeader($name));

            if ($addresses) {
                $email->{$name}(...$addresses);
            }
        }

        if ($message->hasHeader('Subject')) {
            $email->subject($message->getHeaderLine('Subject'));
        }

        // attachments
        foreach ($message->getHeader('Attachment') as $attachment) {
            /** @var Attachment $attachment */
            $email->attachFromPath(
                (string)$attachment->file()->getRealPath(),
                $attachment->name(),
                $attachment->mime()
            );
        }

        // inline image
        foreach ($message->getHeader('Inline') as $inline) {
            /** @var Attachment $inline */
            $email->embedFromPath((string)$inline->file()->getRealPath(), $inline->name(), $inline->mime());
        }

        // body
        if ((string)$message->getBody()) {
            $email->html((string)$message->getBody());
        } else {
            $email->text($message->getHeaderLine('Text'));
        }

        return $email;
    }

    /**
     * Failures throw \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     *
     * @param MessageInterface $message
     *
     * @return ResponseInterface
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function send(MessageInterface $message): ResponseInterface
    {
        $sent = $this->transport()->send($this->email($message));

        return Http::response($this->response)
                   ->withJsonBody(
                       [
                           'id'     => $sent ? $sent->getMessageId() : null,
                           'result' => $sent ? 1 : 0,
                       ]
                   )
                   ->withStatus($sent ? 200 : 500);
    }
}
