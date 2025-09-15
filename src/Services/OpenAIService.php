<?php

namespace Dgtlss\Synapse\Services;

use Dgtlss\Synapse\Contracts\AIServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class OpenAIService implements AIServiceInterface
{
    protected Client $client;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client([
            'base_uri' => $this->config['base_url'],
            'timeout' => $this->config['timeout'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'Content-Type' => 'application/json',
                'OpenAI-Organization' => $this->config['organization'] ?? null,
            ],
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

        $response = $this->client->post('/embeddings', [
            'json' => [
                'input' => $text,
                'model' => $this->config['embedding_model'],
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['data'][0]['embedding'];
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
        $messages = [
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ];

        $response = $this->client->post('/chat/completions', [
            'json' => array_merge([
                'model' => $this->config['chat_model'],
                'messages' => $messages,
                'max_tokens' => $options['max_tokens'] ?? 1000,
                'temperature' => $options['temperature'] ?? 0.7,
            ], $options),
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['choices'][0]['message']['content'];
    }

    /**
     * Get the service name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'openai';
    }

    /**
     * Check if the service is properly configured.
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->config['api_key']);
    }
}