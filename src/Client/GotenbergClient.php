<?php

declare(strict_types=1);

namespace GotenbergPHP\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\MultipartStream;
use Psr\Http\Message\ResponseInterface;
use GotenbergPHP\Exception\GotenbergException;
use GotenbergPHP\Exception\ConversionException;

/**
 * Base HTTP client for communicating with Gotenberg API
 */
class GotenbergClient
{
    private Client $httpClient;
    private string $baseUrl;
    private int $timeout;

    public function __construct(
        string $baseUrl = 'http://gotenberg:3000',
        int $timeout = 30,
        array $httpOptions = []
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;

        $defaultOptions = [
            'timeout' => $timeout,
            'connect_timeout' => 10,
            'headers' => [
                'User-Agent' => 'GotenbergPHP/1.0',
            ],
        ];

        $this->httpClient = new Client(array_merge($defaultOptions, $httpOptions));
    }

    /**
     * Send a multipart form request to Gotenberg
     */
    public function sendMultipartRequest(string $endpoint, array $formData, array $files = []): ResponseInterface
    {
        try {
            $multipart = $this->buildMultipartData($formData, $files);

            $response = $this->httpClient->post($this->baseUrl . $endpoint, [
                'multipart' => $multipart,
                'timeout' => $this->timeout,
            ]);

            $this->validateResponse($response);

            return $response;
        } catch (RequestException $e) {
            throw $this->handleRequestException($e);
        } catch (GuzzleException $e) {
            throw new GotenbergException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Send a simple GET request
     */
    public function sendGetRequest(string $endpoint): ResponseInterface
    {
        try {
            $response = $this->httpClient->get($this->baseUrl . $endpoint);
            $this->validateResponse($response);
            return $response;
        } catch (RequestException $e) {
            throw $this->handleRequestException($e);
        } catch (GuzzleException $e) {
            throw new GotenbergException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Check if Gotenberg service is healthy
     */
    public function isHealthy(): bool
    {
        try {
            $response = $this->sendGetRequest('/health');
            $data = json_decode($response->getBody()->getContents(), true);
            return isset($data['status']) && $data['status'] === 'up';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get Gotenberg version
     */
    public function getVersion(): string
    {
        try {
            $response = $this->sendGetRequest('/version');
            return trim($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw new GotenbergException('Failed to get Gotenberg version: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Build multipart form data for file uploads
     */
    private function buildMultipartData(array $formData, array $files): array
    {
        $multipart = [];

        // Add form fields
        foreach ($formData as $name => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $multipart[] = [
                'name' => $name,
                'contents' => (string) $value,
            ];
        }

        // Add files
        foreach ($files as $name => $file) {
            if (is_string($file)) {
                // File path
                $multipart[] = [
                    'name' => $name,
                    'contents' => fopen($file, 'r'),
                    'filename' => basename($file),
                ];
            } elseif (is_array($file) && isset($file['content'], $file['filename'])) {
                // File content with filename
                $multipart[] = [
                    'name' => $name,
                    'contents' => $file['content'],
                    'filename' => $file['filename'],
                ];
            }
        }

        return $multipart;
    }

    /**
     * Validate HTTP response
     */
    private function validateResponse(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 400) {
            $body = $response->getBody()->getContents();
            $message = "HTTP {$statusCode}: " . ($body ?: 'Unknown error');
            
            if ($statusCode === 400) {
                throw new ConversionException('Bad request: ' . $message);
            } elseif ($statusCode === 403) {
                throw new ConversionException('Forbidden: ' . $message);
            } elseif ($statusCode === 409) {
                throw new ConversionException('Conflict: ' . $message);
            } elseif ($statusCode === 503) {
                throw new ConversionException('Service unavailable: ' . $message);
            } else {
                throw new GotenbergException($message);
            }
        }
    }

    /**
     * Handle Guzzle request exceptions
     */
    private function handleRequestException(RequestException $e): GotenbergException
    {
        if ($e->hasResponse()) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $message = "HTTP {$statusCode}: " . ($body ?: $e->getMessage());
        } else {
            $message = 'Connection failed: ' . $e->getMessage();
        }

        return new GotenbergException($message, 0, $e);
    }

    /**
     * Get the base URL
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Get the timeout
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }
}
