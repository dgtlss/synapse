<?php

namespace Dgtlss\Synapse\Contracts;

interface CustomAIServiceInterface
{
    /**
     * Generate embeddings for the given text.
     *
     * @param string|array $text
     * @return array
     */
    public function generateEmbedding($text): array;

    /**
     * Generate a chat completion.
     *
     * @param string $prompt
     * @param array $options
     * @return string
     */
    public function generateCompletion(string $prompt, array $options = []): string;

    /**
     * Get the service name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Check if the service is properly configured.
     *
     * @return bool
     */
    public function isConfigured(): bool;

    /**
     * Get the service configuration.
     *
     * @return array
     */
    public function getConfig(): array;

    /**
     * Set the service configuration.
     *
     * @param array $config
     * @return void
     */
    public function setConfig(array $config): void;
}