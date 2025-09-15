<?php

namespace Dgtlss\Synapse\Services;

use Dgtlss\Synapse\Contracts\AIServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AnthropicService implements AIServiceInterface
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
                'x-api-key' => $this->config['api_key'],
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01',
            ],
        ]);
    }

    /**
     * Generate embeddings for the given text.
     * Note: Anthropic doesn't have a direct embeddings API, so we'll use their chat model
     * to generate a representation that can be used for similarity.
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

        // For now, we'll use a simple approach with Anthropic's chat model
        // In a real implementation, you might want to use a different approach
        // or integrate with a third-party embedding service
        $prompt = "Convert the following text into a numerical vector representation suitable for similarity comparison. Return only the vector as a JSON array of numbers: {$text}";
        
        $response = $this->generateCompletion($prompt, ['max_tokens' => 2000]);
        
        // Try to parse the response as JSON
        $embedding = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Fallback: create a simple hash-based embedding
            $embedding = $this->createHashEmbedding($text);
        }

        return $embedding;
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
        $response = $this->client->post('/v1/messages', [
            'json' => array_merge([
                'model' => $this->config['chat_model'],
                'max_tokens' => $options['max_tokens'] ?? 1000,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ], $options),
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['content'][0]['text'];
    }

    /**
     * Create a simple hash-based embedding as fallback.
     *
     * @param string $text
     * @return array
     */
    protected function createHashEmbedding(string $text): array
    {
        $words = str_word_count($text, 1);
        $embedding = array_fill(0, 1536, 0);
        
        foreach ($words as $word) {
            $hash = crc32(strtolower($word));
            $index = abs($hash) % 1536;
            $embedding[$index] += 1;
        }
        
        // Normalize the vector
        $magnitude = sqrt(array_sum(array_map(function($x) { return $x * $x; }, $embedding)));
        if ($magnitude > 0) {
            $embedding = array_map(function($x) use ($magnitude) { return $x / $magnitude; }, $embedding);
        }
        
        return $embedding;
    }

    /**
     * Get the service name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'anthropic';
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