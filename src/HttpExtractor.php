<?php

declare(strict_types=1);

namespace Keboola\HttpExtractor;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use Keboola\Component\UserException;
use Psr\Http\Message\UriInterface;
use function in_array;
use const CURLE_COULDNT_RESOLVE_HOST;

class HttpExtractor
{
    /** @var Client */
    private $client;

    /** @var array */
    private $clientOptions;

    public function __construct(
        Client $client,
        array $clientOptions
    ) {
        $this->client = $client;
        $this->clientOptions = $clientOptions;
    }

    public function extract(UriInterface $httpSource, string $filesystemDestination): void
    {
        try {
            $requestOptions = $this->getRequestOptions();
            $requestOptions['sink'] = $filesystemDestination;
            $this->client->get($httpSource, $requestOptions);
        } catch (ClientException|ServerException $e) {
            throw new UserException(sprintf(
                'Server returned HTTP %s for "%s"',
                $e->getCode(),
                (string) $httpSource
            ), 0, $e);
        } catch (TooManyRedirectsException $e) {
            throw new UserException(sprintf(
                'Too many redirects requesting "%s": %s',
                (string) $httpSource,
                $e->getMessage()
            ), 0, $e);
        } catch (RequestException $e) {
            $userErrors = [
                CURLE_COULDNT_RESOLVE_HOST,
                CURLE_COULDNT_RESOLVE_PROXY,
                CURLE_COULDNT_CONNECT,
                CURLE_OPERATION_TIMEOUTED,
                CURLE_SSL_CONNECT_ERROR,
                CURLE_GOT_NOTHING,
                CURLE_RECV_ERROR,
            ];
            $context = $e->getHandlerContext();
            if (!isset($context['errno'])) {
                throw $e;
            }

            $curlErrorNumber = $context['errno'];
            if (!in_array($curlErrorNumber, $userErrors)) {
                throw $e;
            }
            $curlErrorMessage = $context['error'];
            throw new UserException(sprintf(
                'Error requesting "%s": cURL error %s: %s',
                (string) $httpSource,
                $curlErrorNumber,
                $curlErrorMessage
            ), 0, $e);
        } catch (GuzzleException $e) {
            throw new UserException(sprintf(
                'Error requesting "%s": Guzzle error: %s',
                (string) $httpSource,
                $e->getMessage()
            ), 0, $e);
        }
        // will throw exception for HTTP errors, no need to signal back
    }

    /**
     * @return mixed[]
     */
    private function getRequestOptions(): array
    {
        $requestOptions = [];
        if (isset($this->clientOptions['maxRedirects'])) {
            $requestOptions['allow_redirects'] = [
                'max' => $this->clientOptions['maxRedirects'],
            ];
        }
        return $requestOptions;
    }
}
