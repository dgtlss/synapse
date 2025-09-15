<?php

namespace Dgtlss\Synapse\Facades;

use Illuminate\Support\Facades\Facade;
use Dgtlss\Synapse\Services\AIFactoryService;

/**
 * @method static string generate(string $prompt, array $options = [])
 * 
 * @see \Dgtlss\Synapse\Services\AIFactoryService
 */
class AIFactory extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return AIFactoryService::class;
    }
}