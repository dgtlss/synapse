<?php

namespace Dgtlss\Synapse\Services;

use Dgtlss\Synapse\Contracts\AIServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class OllamaService implements AIServiceInterface
{
    protected Client $client;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client([
            'base_uri' => $this->config['base_url'],
            'timeout' => $this->config['timeout'],
        ]);
    }

    /**
     * Generate embeddings for the given text.
     *
     * @param string|array $text
     * @return array
     * @throws GuzzleException
     */
    public function generateEmbedding($text): array
    {
        if (is_array($text)) {
            $text = implode(' ', $text);
        }

        $response = $this->client->post('/api/embeddings', [
            'json' => [
                'model' => $this->config['embedding_model'],
                'prompt' => $text,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['embedding'];
    }

    /**
     * Generate a chat completion.
     *
     * @param string $prompt
     * @param array $options
     * @return string
     * @throws GuzzleException
     */
    public function generateCompletion(string $prompt, array $options = []): string
    {
        $response = $this->client->post('/api/generate', [
            'json' => array_merge([
                'model' => $this->config['chat_model'],
                'prompt' => $prompt,
                'stream' => false,
            ], $options),
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['response'];
    }

    /**
     * Get the service name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'ollama';
    }

    /**
     * Check if the service is properly configured.
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        try {
            $response = $this->client->get('/api/tags');
            return $response->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            return false;
        }
    }
}