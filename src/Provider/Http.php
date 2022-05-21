<?php

namespace Spartan\Message\Provider;

use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Spartan\Message\Definition\ProviderInterface;

class Http implements ProviderInterface
{
    /**
     * @var mixed[]
     */
    protected array $config = [];

    protected RequestFactoryInterface $factory;

    protected ClientInterface $client;

    /**
     * Http constructor.
     *
     * @param mixed[]                 $config
     * @param RequestFactoryInterface $factory
     * @param ClientInterface         $client
     */
    public final function __construct(array $config, RequestFactoryInterface $factory, ClientInterface $client)
    {
        $this->config  = $config + $this->config;
        $this->factory = $factory;
        $this->client  = $client;
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
            $container->get(RequestFactoryInterface::class),
            $container->get(ClientInterface::class)
        );
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function withConfig(array $config): self
    {
        $this->config = $config + $this->config;

        return $this;
    }

    /**
     * @param MessageInterface $message
     *
     * @return RequestInterface
     */
    public function request(MessageInterface $message): RequestInterface
    {
        $request = $this->factory->createRequest(
            $this->config['method'] ?? 'GET',
            $this->config['uri'] ?? 'http://localhost'
        );

        foreach ($message->getHeaders() as $headerName => $headerValue) {
            $request = $request->withHeader($headerName, $headerValue);
        }

        return $request;
    }

    /**
     * @param MessageInterface $message
     *
     * @return ResponseInterface
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function send(MessageInterface $message): ResponseInterface
    {
        return $this->client->sendRequest($this->request($message));
    }
}
