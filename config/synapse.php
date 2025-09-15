<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Service
    |--------------------------------------------------------------------------
    |
    | This option controls the default AI service that will be used by Synapse.
    | Supported services: openai, anthropic, ollama, gemini
    | You can also register custom services using the AIServiceManager
    |
    */
    'default' => env('SYNAPSE_DEFAULT_SERVICE', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenAI services (GPT models)
    |
    */
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'chat_model' => env('OPENAI_CHAT_MODEL', 'gpt-4o-mini'),
        'timeout' => env('OPENAI_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Anthropic Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Anthropic services (Claude models)
    |
    */
    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com'),
        'embedding_model' => env('ANTHROPIC_EMBEDDING_MODEL', 'claude-3-sonnet-20240229'),
        'chat_model' => env('ANTHROPIC_CHAT_MODEL', 'claude-3-sonnet-20240229'),
        'timeout' => env('ANTHROPIC_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ollama Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Ollama (local LLM models)
    |
    */
    'ollama' => [
        'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
        'embedding_model' => env('OLLAMA_EMBEDDING_MODEL', 'nomic-embed-text'),
        'chat_model' => env('OLLAMA_CHAT_MODEL', 'llama3.1'),
        'timeout' => env('OLLAMA_TIMEOUT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Gemini Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Google Gemini services
    |
    */
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com'),
        'embedding_model' => env('GEMINI_EMBEDDING_MODEL', 'text-embedding-004'),
        'chat_model' => env('GEMINI_CHAT_MODEL', 'gemini-1.5-flash'),
        'timeout' => env('GEMINI_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Vector Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for vector operations
    |
    */
    'vector' => [
        'dimensions' => env('SYNAPSE_VECTOR_DIMENSIONS', 1536),
        'distance_metric' => env('SYNAPSE_DISTANCE_METRIC', 'cosine'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for caching AI responses
    |
    */
    'cache' => [
        'enabled' => env('SYNAPSE_CACHE_ENABLED', true),
        'ttl' => env('SYNAPSE_CACHE_TTL', 3600), // 1 hour
    ],
];