<?php

declare(strict_types=1);

namespace TRSTD\COT;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Client\ClientExceptionInterface;
use GuzzleHttp\Client as GuzzleHttpClient;

final class HttpClientWrapper implements ClientInterface
{
    /**
     * @var GuzzleHttpClient;
     */
    private $client;

    /**
     * HttpClientWrapper constructor.
     *
     * @param GuzzleHttpClient $client The client to wrap
     */
    public function __construct(GuzzleHttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * Sends a PSR-7 request and returns a PSR-7 response.
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws ClientExceptionInterface If an error happens while processing the request.
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->sendRequest($request);
    }
}
