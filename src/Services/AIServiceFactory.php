<?php

namespace Dgtlss\Synapse\Services;

use Dgtlss\Synapse\Contracts\AIServiceInterface;
use Dgtlss\Synapse\Contracts\CustomAIServiceInterface;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class AIServiceFactory
{
    protected Container $container;
    protected array $customServices = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register a custom AI service.
     *
     * @param string $name
     * @param string $serviceClass
     * @return void
     */
    public function registerCustomService(string $name, string $serviceClass): void
    {
        if (!is_subclass_of($serviceClass, CustomAIServiceInterface::class)) {
            throw new InvalidArgumentException("Service class must implement CustomAIServiceInterface");
        }

        $this->customServices[$name] = $serviceClass;
    }

    /**
     * Create an AI service instance.
     *
     * @param string $serviceName
     * @param array $config
     * @return AIServiceInterface
     */
    public function create(string $serviceName, array $config = []): AIServiceInterface
    {
        // Check if it's a custom service
        if (isset($this->customServices[$serviceName])) {
            $serviceClass = $this->customServices[$serviceName];
            $service = new $serviceClass();
            
            if (!empty($config)) {
                $service->setConfig($config);
            }
            
            return $service;
        }

        // Check if it's a built-in service
        $serviceClass = match ($serviceName) {
            'openai' => OpenAIService::class,
            'anthropic' => AnthropicService::class,
            'ollama' => OllamaService::class,
            'gemini' => GeminiService::class,
            default => throw new InvalidArgumentException("Unsupported AI service: {$serviceName}")
        };

        return $this->container->make($serviceClass, ['config' => $config]);
    }

    /**
     * Get all available service names.
     *
     * @return array
     */
    public function getAvailableServices(): array
    {
        return array_merge(
            ['openai', 'anthropic', 'ollama', 'gemini'],
            array_keys($this->customServices)
        );
    }

    /**
     * Check if a service is available.
     *
     * @param string $serviceName
     * @return bool
     */
    public function isServiceAvailable(string $serviceName): bool
    {
        return in_array($serviceName, $this->getAvailableServices());
    }
}