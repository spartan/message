<?php

namespace Spartan\Message\Provider\Mail;

use Laminas\Diactoros\Stream;
use Laminas\Mail\Address;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Spartan\Http\Header\Attachment;
use Spartan\Message\Provider\Http;

/**
 * Mailjet Mail
 *
 * @package Spartan\Message
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Mailjet extends Http
{
    const TRACK_DEFAULT = 0;
    const TRACK_OFF     = 1;
    const TRACK_ON      = 2;

    /**
     * @var mixed[]
     */
    protected array $config = [
        'url'             => 'https://api.mailjet.com/v3/send',
        'api_key_public'  => '',
        'api_key_private' => '',
    ];

    /**
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
                'Basic ' . base64_encode("{$this->config['api_key_public']}:{$this->config['api_key_private']}")
            );

        $attachments = [];
        /** @var Attachment $attachment */
        foreach ($message->getHeader('Attachment') as $attachment) {
            $attachments[] = [
                'Filename'     => $attachment->name(),
                'Content-type' => $attachment->mime(),
                'Content'      => base64_encode($attachment->value()),
            ];
        }

        $inline = [];
        /** @var Attachment $attachment */
        foreach ($message->getHeader('Inline') as $attachment) {
            /** @var Attachment $attachment */
            $inline[] = [
                'Filename'     => $attachment->name(),
                'Content-type' => $attachment->mime(),
                'Content'      => base64_encode($attachment->value()),
            ];
        }

        $from = Address::fromString($message->getHeaderLine('From'));

        /*
         * @see https://dev.mailjet.com/email/reference/send-emails/
         */
        $params = [
            'FromEmail'          => $from->getEmail(),
            'FromName'           => $from->getName(),
            'To'                 => $message->getHeaderLine('To'),
            'Cc'                 => $message->getHeaderLine('Cc'),
            'Bcc'                => $message->getHeaderLine('Bcc'),
            'Subject'            => $message->getHeaderLine('Subject'),
            'Html-part'          => $message->getHeaderLine('Html') ?: (string)$message->getBody(),
            'Text-part'          => $message->getHeaderLine('Text'),
            'Attachments'        => $attachments,
            'Inline_attachments' => $inline,
            'Mj-trackopen'       => $message->getHeaderLine('Tracking') == 0
                ? self::TRACK_OFF
                : self::TRACK_ON,
        ];
        // overwrite custom headers
        $params = array_filter(array_replace_recursive($params, $message->getHeader('Mailjet')));

        $body = new Stream('php://memory', 'r+');
        $body->write((string)json_encode($params));

        return $request
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);
    }
}
