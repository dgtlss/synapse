<?php

namespace Examples;

use Dgtlss\Synapse\Contracts\CustomAIServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Example custom AI service implementation
 * This shows how to create a custom AI service for any LLM provider
 */
class CustomAIService implements CustomAIServiceInterface
{
    protected Client $client;
    protected array $config = [];

    public function __construct()
    {
        $this->client = new Client();
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

        // Example: Call your custom LLM API for embeddings
        $response = $this->client->post($this->config['base_url'] . '/embeddings', [
            'json' => [
                'text' => $text,
                'model' => $this->config['embedding_model'],
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'Content-Type' => 'application/json',
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
        // Example: Call your custom LLM API for completions
        $response = $this->client->post($this->config['base_url'] . '/completions', [
            'json' => array_merge([
                'prompt' => $prompt,
                'model' => $this->config['chat_model'],
                'max_tokens' => $options['max_tokens'] ?? 1000,
                'temperature' => $options['temperature'] ?? 0.7,
            ], $options),
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'Content-Type' => 'application/json',
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['choices'][0]['text'];
    }

    /**
     * Get the service name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'custom';
    }

    /**
     * Check if the service is properly configured.
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->config['api_key']) && !empty($this->config['base_url']);
    }

    /**
     * Get the service configuration.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set the service configuration.
     *
     * @param array $config
     * @return void
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }
}

/**
 * Example of how to register a custom service
 */
class CustomServiceRegistration
{
    public static function register(): void
    {
        // Register your custom service
        app(\Dgtlss\Synapse\Services\AIServiceManager::class)
            ->registerCustomService('custom', CustomAIService::class);

        // Now you can use it like any other service
        $result = app(\Dgtlss\Synapse\Facades\Synapse::class)
            ->service('custom')
            ->generateCompletion('Hello, world!');
    }
}