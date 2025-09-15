<?php

namespace Dgtlss\Synapse\Services;

use Dgtlss\Synapse\Facades\Synapse;

class AIFactoryService
{
    /**
     * Generate content using AI.
     *
     * @param string $prompt
     * @param array $options
     * @return string
     */
    public function generate(string $prompt, array $options = []): string
    {
        return Synapse::generateCompletion($prompt, $options);
    }
}