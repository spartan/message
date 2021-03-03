<?php

namespace Spartan\Message\Message;

use Laminas\Diactoros\Stream;
use Laminas\Mail\Address\AddressInterface;
use Laminas\Mail\Header\HeaderInterface;
use Psr\Http\Message\MessageInterface;
use Spartan\Http\Message;

class Email extends Message
{
    const TRACK_OFF   = 0;
    const TRACK_OPEN  = 1;
    const TRACK_CLICK = 2;
    const TRACK_SUBS  = 4;
    const TRACK_ON    = 7;

    /**
     * @var mixed[]
     */
    protected array $attachment = [];

    /**
     * @var mixed[]
     */
    protected array $inline = [];

    /**
     * @var mixed[]
     */
    protected array $substitutions = [];

    /**
     * @param string      $header
     * @param mixed       $address
     * @param string|null $name
     *
     * @return MessageInterface|Email
     */
    public function address(string $header, $address, string $name = null): MessageInterface
    {
        if ($address instanceof HeaderInterface) {
            return $this->withHeader($header, $address->getFieldValue());
        } elseif ($address instanceof AddressInterface) {
            return $this->withHeader($header, sprintf('%s <%s>', $address->getName(), $address->getEmail()));
        } elseif ($name) {
            return $this->withHeader($header, sprintf('%s <%s>', $name, $address));
        }

        return $this->withHeader($header, (string)$address);
    }

    /**
     * @param mixed       $address
     * @param string|null $name
     *
     * @return MessageInterface|Email
     */
    public function from($address, string $name = null): MessageInterface
    {
        return $this->address('From', $address, $name);
    }

    /**
     * @param mixed       $address
     * @param string|null $name
     *
     * @return MessageInterface|Email
     */
    public function to($address, string $name = null): MessageInterface
    {
        return $this->address('To', $address, $name);
    }

    /**
     * @param mixed       $address
     * @param string|null $name
     *
     * @return MessageInterface|Email
     */
    public function cc($address, string $name = null): MessageInterface
    {
        return $this->address('Cc', $address, $name);
    }

    /**
     * @param mixed       $address
     * @param string|null $name
     *
     * @return \Psr\Http\Message\MessageInterface|Email
     */
    public function bcc($address, string $name = null): MessageInterface
    {
        return $this->address('Cc', $address, $name);
    }

    /**
     * @param string $subject
     *
     * @return MessageInterface|Email
     */
    public function subject(string $subject): MessageInterface
    {
        return $this->withHeader('Subject', $subject);
    }

    /**
     * @param string $html
     *
     * @return MessageInterface|Email
     */
    public function html(string $html): MessageInterface
    {
        $stream = new Stream('php://memory', 'r+');
        $stream->write($html);

        return $this->withBody($stream);
    }

    /**
     * @param string $text
     *
     * @return MessageInterface|Email
     */
    public function text(string $text): MessageInterface
    {
        return $this->withHeader('Text', $text);
    }

    /**
     * @param mixed[] $attachments
     *
     * @return MessageInterface|Email
     */
    public function attachment(array $attachments): MessageInterface
    {
        $clone = clone $this;

        $clone->attachment = $attachments;

        return $clone;
    }

    /**
     * @param mixed[] $inline
     *
     * @return MessageInterface|Email
     */
    public function inline(array $inline): MessageInterface
    {
        $clone = clone $this;

        $clone->inline = $inline;

        return $clone;
    }

    /**
     * @param int $tracking
     *
     * @return MessageInterface|Email
     */
    public function tracking(int $tracking): MessageInterface
    {
        return $this->withHeader('Tracking', (string)$tracking);
    }

    /**
     * @param string $name
     *
     * @return \Psr\Http\Message\MessageInterface|Email
     */
    public function template(string $name): MessageInterface
    {
        return $this->withHeader('Template', $name);
    }

    /**
     * @param mixed[] $substitutions
     *
     * @return MessageInterface|Email
     */
    public function substitutions(array $substitutions): MessageInterface
    {
        $clone = clone $this;

        $clone->substitutions = $substitutions;

        return $clone;
    }

    /**
     * @param mixed $at
     *
     * @return MessageInterface|Email
     */
    public function scheduled($at): MessageInterface
    {
        if ($at instanceof \DateTime) {
            $at = $at->format('Y-m-d H:i:s');
        } elseif (is_numeric($at)) {
            $at = (new \DateTime())->setTimestamp((int)$at)->format('Y-m-d H:i:s');
        }

        return $this->withHeader('Scheduled-At', $at);
    }

    /**
     * Diactoros cannot store objects as headers so we need this "hack"
     *
     * @param string $header
     *
     * @return mixed[]
     */
    public function getHeader($header): array
    {
        $headerName = strtolower($header);

        if (property_exists($this, $headerName)) {
            return $this->{$headerName};
        }

        return parent::getHeader($header);
    }
}
