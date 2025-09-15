<?php

namespace Examples;

use Dgtlss\Synapse\Facades\Synapse;
use Dgtlss\Synapse\Contracts\CustomAIServiceInterface;

/**
 * Examples showing how to use different LLM providers with Synapse
 */
class LLMProviderExamples
{
    /**
     * Example: Using OpenAI
     */
    public function useOpenAI()
    {
        // Set OpenAI as default in .env: SYNAPSE_DEFAULT_SERVICE=openai
        // Configure API key: OPENAI_API_KEY=your_key
        
        $embedding = Synapse::generateEmbedding('Hello, world!');
        $completion = Synapse::generateCompletion('Write a haiku about programming');
        
        // Or use specific service
        $result = Synapse::service('openai')->generateCompletion('Explain Laravel');
    }

    /**
     * Example: Using Anthropic Claude
     */
    public function useAnthropic()
    {
        // Set Anthropic as default in .env: SYNAPSE_DEFAULT_SERVICE=anthropic
        // Configure API key: ANTHROPIC_API_KEY=your_key
        
        $embedding = Synapse::generateEmbedding('Hello, world!');
        $completion = Synapse::generateCompletion('Write a haiku about programming');
        
        // Or use specific service
        $result = Synapse::service('anthropic')->generateCompletion('Explain Laravel');
    }

    /**
     * Example: Using Google Gemini
     */
    public function useGemini()
    {
        // Set Gemini as default in .env: SYNAPSE_DEFAULT_SERVICE=gemini
        // Configure API key: GEMINI_API_KEY=your_key
        
        $embedding = Synapse::generateEmbedding('Hello, world!');
        $completion = Synapse::generateCompletion('Write a haiku about programming');
        
        // Or use specific service
        $result = Synapse::service('gemini')->generateCompletion('Explain Laravel');
    }

    /**
     * Example: Using Ollama (Local LLM)
     */
    public function useOllama()
    {
        // Set Ollama as default in .env: SYNAPSE_DEFAULT_SERVICE=ollama
        // Configure base URL: OLLAMA_BASE_URL=http://localhost:11434
        
        $embedding = Synapse::generateEmbedding('Hello, world!');
        $completion = Synapse::generateCompletion('Write a haiku about programming');
        
        // Or use specific service
        $result = Synapse::service('ollama')->generateCompletion('Explain Laravel');
    }

    /**
     * Example: Creating a custom LLM service
     */
    public function createCustomService()
    {
        // Create a custom service for any LLM provider
        class CustomLLMService implements CustomAIServiceInterface
        {
            protected array $config = [];

            public function generateEmbedding($text): array
            {
                // Your custom embedding logic
                // This could be for Hugging Face, Cohere, or any other provider
                return [/* your embedding vector */];
            }

            public function generateCompletion(string $prompt, array $options = []): string
            {
                // Your custom completion logic
                return "Generated response for: {$prompt}";
            }

            public function getName(): string
            {
                return 'custom-llm';
            }

            public function isConfigured(): bool
            {
                return !empty($this->config['api_key']);
            }

            public function getConfig(): array
            {
                return $this->config;
            }

            public function setConfig(array $config): void
            {
                $this->config = $config;
            }
        }

        // Register the custom service
        Synapse::registerCustomService('custom-llm', CustomLLMService::class);

        // Use it like any other service
        $result = Synapse::service('custom-llm')->generateCompletion('Hello!');
    }

    /**
     * Example: Switching between providers dynamically
     */
    public function switchProviders()
    {
        $providers = ['openai', 'anthropic', 'gemini', 'ollama'];
        
        foreach ($providers as $provider) {
            try {
                $result = Synapse::service($provider)->generateCompletion('Hello!');
                echo "Provider {$provider}: {$result}\n";
            } catch (\Exception $e) {
                echo "Provider {$provider} failed: {$e->getMessage()}\n";
            }
        }
    }

    /**
     * Example: Using different providers for different tasks
     */
    public function useDifferentProvidersForTasks()
    {
        // Use OpenAI for embeddings (fast and accurate)
        $embedding = Synapse::service('openai')->generateEmbedding('Document content');

        // Use Claude for complex reasoning
        $analysis = Synapse::service('anthropic')->generateCompletion(
            'Analyze this complex business problem...'
        );

        // Use Gemini for creative tasks
        $creative = Synapse::service('gemini')->generateCompletion(
            'Write a creative story about...'
        );

        // Use local Ollama for sensitive data
        $sensitive = Synapse::service('ollama')->generateCompletion(
            'Process this sensitive information...'
        );
    }

    /**
     * Example: Checking available services
     */
    public function checkAvailableServices()
    {
        $availableServices = Synapse::getAvailableServices();
        
        foreach ($availableServices as $service) {
            $serviceInstance = Synapse::service($service);
            $isConfigured = $serviceInstance->isConfigured();
            
            echo "Service: {$service}, Configured: " . ($isConfigured ? 'Yes' : 'No') . "\n";
        }
    }
}