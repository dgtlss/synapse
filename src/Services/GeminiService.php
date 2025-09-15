<?php

namespace Dgtlss\Synapse\Services;

use Dgtlss\Synapse\Contracts\AIServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GeminiService implements AIServiceInterface
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
                'Content-Type' => 'application/json',
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

        $response = $this->client->post("/v1beta/models/{$this->config['embedding_model']}:embedContent", [
            'json' => [
                'content' => [
                    'parts' => [
                        ['text' => $text]
                    ]
                ]
            ],
            'query' => [
                'key' => $this->config['api_key']
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['embedding']['values'];
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
        $response = $this->client->post("/v1beta/models/{$this->config['chat_model']}:generateContent", [
            'json' => array_merge([
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => $options['max_tokens'] ?? 1000,
                    'temperature' => $options['temperature'] ?? 0.7,
                ]
            ], $options),
            'query' => [
                'key' => $this->config['api_key']
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['candidates'][0]['content']['parts'][0]['text'];
    }

    /**
     * Get the service name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'gemini';
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