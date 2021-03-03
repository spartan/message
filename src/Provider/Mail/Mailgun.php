<?php

namespace Spartan\Message\Provider\Mail;

use Laminas\Diactoros\Stream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Spartan\Http\Header\Attachment;
use Spartan\Http\Header\ContentDisposition;
use Spartan\Http\Header\ContentLength;
use Spartan\Http\Header\ContentType;
use Spartan\Http\Message;
use Spartan\Message\Provider\Http;

/**
 * Mailgun Mail
 *
 * @package Spartan\Message
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Mailgun extends Http
{
    /**
     * @var mixed[]
     */
    protected array $config = [
        'url'     => 'https://api.mailgun.net/v3',
        'api_key' => '',
        'domain'  => '',
    ];

    /**
     * @param MessageInterface $message
     *
     * @return RequestInterface
     */
    public function request(MessageInterface $message): RequestInterface
    {
        $request = $this->factory
            ->createRequest('POST', "{$this->config['url']}/{$this->config['domain']}/messages")
            ->withHeader('Authorization', 'Basic ' . base64_encode("api:{$this->config['api_key']}"));

        /*
         * @see https://documentation.mailgun.com/en/latest/api-sending.html#sending
         */
        $params = [
            'from'       => $message->getHeaderLine('From'),
            'to'         => $message->getHeaderLine('To'),
            'subject'    => $message->getHeaderLine('Subject'),
            'html'       => $message->getHeaderLine('Html') ?: (string)$message->getBody(),
            'text'       => $message->getHeaderLine('Text'),
            'attachment' => $message->getHeader('Attachment'),
        ];
        /*
         * Custom mailgun headers
         */
        $params = array_replace_recursive($params, $message->getHeader('Mailgun'));

        $boundary = uniqid('', true);
        $body     = $request->getBody();
        $body->write(trim(implode(PHP_EOL . "--{$boundary}" . PHP_EOL, $this->body($params))) . '--');

        return $request
            ->withHeader('Content-Type', "multipart/form-data; boundary=\"{$boundary}\"")
            ->withBody($body);
    }

    /**
     * @param mixed[] $params
     *
     * @return string[]
     */
    protected function body(array $params): array
    {
        $body = [''];

        foreach ($params as $param => $value) {
            if ($param != 'attachment' && strlen($value)) {
                $stream = $this->stream();
                $stream->write($value);
                $message = (new Message())
                    ->withHeaders(
                        [
                            new ContentDisposition(['form-data', 'name' => $param]),
                            new ContentLength(strlen($value)),
                        ]
                    )
                    ->withBody($stream);
                $body[]  = (string)$message;
            } elseif ($param == 'attachment') {
                /** @var Attachment $attachment */
                foreach ($value as $attachment) {
                    $contents = $attachment->value();
                    $stream   = $this->stream();
                    $stream->write($contents);
                    $message = (new Message())
                        ->withHeaders(
                            [
                                new ContentDisposition(['form-data', 'name' => $param, 'filename' => $attachment->name()]),
                                new ContentLength(strlen($contents)),
                                new ContentType($attachment->mime()),
                            ]
                        )
                        ->withBody($stream);
                    $body[]  = (string)$message;
                }
            }
        }

        $body[] = '';

        return $body;
    }

    /**
     * @return Stream
     * @throws \InvalidArgumentException
     */
    protected function stream()
    {
        return new Stream('php://memory', 'r+');
    }
}
