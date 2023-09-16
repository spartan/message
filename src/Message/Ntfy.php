<?php

namespace Spartan\Message\Message;

use Spartan\Http\Message;

class Ntfy extends Message
{
    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_HIGH   = 'high';
    const PRIORITY_LOW    = 'low';

    public static function create(array $headers)
    {
        return (new self())->withHeaders($headers);
    }

    /**
     * @param string $topic
     *
     * @return $this
     */
    public function withTopic(string $topic): self
    {
        return $this->withHeader('topic', $topic);
    }

    /**
     * @param string $priority
     *
     * @return $this
     */
    public function withPriority(string $priority): self
    {
        return $this->withHeader('priority', $priority);
    }

    /**
     * @param string $message
     *
     * @return $this
     */
    public function withMessage(string $message): self
    {
        return $this->withHeader('message', $message);
    }

    /**
     * @param array $tags
     *
     * @return $this
     */
    public function withTags(array $tags): self
    {
        $this->withAddedHeader('tags', $tags);

        return clone $this;
    }

    /**
     * @param array $attributes
     *
     * @return $this
     */
    public function withAttributes(array $attributes): self
    {
        $this->attributes = $attributes + $this->attributes;

        return clone $this;
    }

    /**
     * @return array
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
