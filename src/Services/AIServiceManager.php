<?php

namespace Dgtlss\Synapse\Services;

use Dgtlss\Synapse\Contracts\AIServiceInterface;
use Illuminate\Contracts\Container\Container;

class AIServiceManager
{
    protected Container $container;
    protected AIServiceFactory $factory;
    protected array $services = [];
    protected string $defaultService;

    public function __construct(Container $container, AIServiceFactory $factory)
    {
        $this->container = $container;
        $this->factory = $factory;
        $this->defaultService = config('synapse.default', 'openai');
    }

    /**
     * Get the default AI service.
     *
     * @return AIServiceInterface
     */
    public function service(): AIServiceInterface
    {
        return $this->service($this->defaultService);
    }

    /**
     * Get a specific AI service.
     *
     * @param string $service
     * @return AIServiceInterface
     */
    public function service(string $service): AIServiceInterface
    {
        if (!isset($this->services[$service])) {
            $config = config("synapse.{$service}", []);
            $this->services[$service] = $this->factory->create($service, $config);
        }

        return $this->services[$service];
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
        $this->factory->registerCustomService($name, $serviceClass);
    }

    /**
     * Get all available services.
     *
     * @return array
     */
    public function getAvailableServices(): array
    {
        return $this->factory->getAvailableServices();
    }

    /**
     * Generate embeddings using the default service.
     *
     * @param string|array $text
     * @return array
     */
    public function generateEmbedding($text): array
    {
        return $this->service()->generateEmbedding($text);
    }

    /**
     * Generate a completion using the default service.
     *
     * @param string $prompt
     * @param array $options
     * @return string
     */
    public function generateCompletion(string $prompt, array $options = []): string
    {
        return $this->service()->generateCompletion($prompt, $options);
    }
}