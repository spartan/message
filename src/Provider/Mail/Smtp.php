<?php

namespace Spartan\Message\Provider\Mail;

use Laminas\Mail\Address;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Spartan\Http\Header\Attachment;
use Spartan\Http\Header\MessageId;
use Spartan\Http\Http;
use Spartan\Message\Definition\ProviderInterface;

/**
 * Smtp Provider
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
     * @return Smtp
     */
    public static function create(array $config, ContainerInterface $container)
    {
        return new self(
            $config,
            $container->get(ResponseInterface::class)
        );
    }

    /**
     * @return \Swift_Mailer
     */
    public function mailer(): \Swift_Mailer
    {
        return new \Swift_Mailer($this->transport());
    }

    /**
     * @return \Swift_Transport
     */
    public function transport(): \Swift_Transport
    {
        return (new \Swift_SmtpTransport($this->config['host'], $this->config['port']))
            ->setUsername($this->config['user'])
            ->setPassword($this->config['pass']);
    }

    /**
     * @param MessageInterface $message
     *
     * @return \Swift_Message
     */
    public function swiftMessage(MessageInterface $message): \Swift_Message
    {
        $swiftMessage = new \Swift_Message();

        // attachments
        foreach ($message->getHeader('Attachment') as $attachment) {
            /** @var Attachment $attachment */
            $swiftMessage->attach(
                \Swift_Attachment::fromPath((string)$attachment->file()->getRealPath())
                                 ->setFilename($attachment->name())
                                 ->setContentType($attachment->mime())
            );
        }

        // inline image
        foreach ($message->getHeader('Inline') as $inline) {
            /** @var Attachment $inline */
            $swiftMessage->embed(
                \Swift_Image::fromPath((string)$inline->file()->getRealPath())
            );
        }

        // body
        if ((string)$message->getBody()) {
            $swiftMessage->setBody((string)$message->getBody(), 'text/html');
        } else {
            $swiftMessage->setBody($message->getHeaderLine('Text'), 'text/plain');
        }

        // headers
        foreach ($message->getHeaders() as $name => $value) {
            // fix From, To, Cc, Bcc
            if (in_array(strtolower($name), ['from', 'to', 'cc', 'bcc'])) {
                $addresses = [];
                foreach ($message->getHeader($name) as $address) {
                    $address = Address::fromString($address);
                    if ($address->getName()) {
                        $addresses[$address->getEmail()] = $address->getName();
                    } else {
                        $addresses[] = $address->getEmail();
                    }
                }

                $swiftMessage->{"set{$name}"}($addresses);
            } elseif (method_exists($swiftMessage, "set{$name}")) {
                $swiftMessage->{"set{$name}"}($message->getHeaderLine($name));
            }
        }

        return $swiftMessage;
    }

    /**
     * @param MessageInterface $message
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function send(MessageInterface $message): ResponseInterface
    {
        $result = $this->mailer()->send($this->swiftMessage($message));

        $response = $this->response;

        if ($result > 0) {
            return Http::response($response)
                       ->withJsonBody(
                           [
                               'id'     => MessageId::generateId(),
                               'result' => $result,
                           ]
                       )
                       ->withStatus(200);
        } else {
            return Http::response($response)
                       ->withJsonBody(
                           [
                               'result' => $result,
                           ]
                       )
                       ->withStatus(500);
        }
    }
}
