<?php

namespace Dgtlss\Synapse\Facades;

use Illuminate\Support\Facades\Facade;
use Dgtlss\Synapse\Services\AIServiceManager;

/**
 * @method static array generateEmbedding($text)
 * @method static string generateCompletion(string $prompt, array $options = [])
 * @method static AIServiceManager service(string $service = null)
 * @method static void registerCustomService(string $name, string $serviceClass)
 * @method static array getAvailableServices()
 * 
 * @see \Dgtlss\Synapse\Services\AIServiceManager
 */
class Synapse extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return AIServiceManager::class;
    }
}