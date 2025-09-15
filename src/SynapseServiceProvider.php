<?php

namespace Dgtlss\Synapse;

use Illuminate\Support\ServiceProvider;
use Dgtlss\Synapse\Services\AIServiceManager;
use Dgtlss\Synapse\Services\AIServiceFactory;
use Dgtlss\Synapse\Services\OpenAIService;
use Dgtlss\Synapse\Services\AnthropicService;
use Dgtlss\Synapse\Services\OllamaService;
use Dgtlss\Synapse\Services\GeminiService;
use Dgtlss\Synapse\Services\AIFactoryService;

class SynapseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/synapse.php', 'synapse');

        $this->app->singleton(AIServiceFactory::class, function ($app) {
            return new AIServiceFactory($app);
        });

        $this->app->singleton(AIServiceManager::class, function ($app) {
            return new AIServiceManager($app, $app->make(AIServiceFactory::class));
        });

        // Register AI services
        $this->app->bind(OpenAIService::class, function ($app) {
            return new OpenAIService($app['config']['synapse.openai']);
        });

        $this->app->bind(AnthropicService::class, function ($app) {
            return new AnthropicService($app['config']['synapse.anthropic']);
        });

        $this->app->bind(OllamaService::class, function ($app) {
            return new OllamaService($app['config']['synapse.ollama']);
        });

        $this->app->bind(GeminiService::class, function ($app) {
            return new GeminiService($app['config']['synapse.gemini']);
        });

        $this->app->singleton(AIFactoryService::class, function ($app) {
            return new AIFactoryService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/synapse.php' => config_path('synapse.php'),
            ], 'synapse-config');
        }
    }
}