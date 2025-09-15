# 🧠 Synapse

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dgtlss/synapse.svg?style=flat-square)](https://packagist.org/packages/dgtlss/synapse)
[![Total Downloads](https://img.shields.io/packagist/dt/dgtlss/synapse.svg?style=flat-square)](https://packagist.org/packages/dgtlss/synapse)

Seamlessly integrate AI capabilities directly into your Laravel Eloquent models. Generate vector embeddings, perform semantic search, and augment data on-the-fly using AI.

## Features

- 🤖 **Multiple AI Providers**: Support for OpenAI, Anthropic (Claude), Google Gemini, Ollama (local LLMs), and custom providers
- 🔍 **Semantic Search**: Find records based on conceptual similarity
- 📊 **Automatic Embeddings**: Generate and store vector embeddings automatically
- ⚡ **On-the-Fly Augmentation**: Generate summaries, keywords, and more dynamically
- 🏭 **AI-Enhanced Factories**: Generate realistic test data with AI
- 🎯 **Trait-Based Integration**: Add AI capabilities with a single trait
- 🔧 **Extensible Architecture**: Easily add support for any LLM provider

## Installation

You can install the package via Composer:

```bash
composer require dgtlss/synapse
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Dgtlss\Synapse\SynapseServiceProvider" --tag="synapse-config"
```

## Configuration

Configure your AI services in the published `config/synapse.php` file:

```php
// .env
SYNAPSE_DEFAULT_SERVICE=openai

# OpenAI Configuration
OPENAI_API_KEY=your_openai_api_key
OPENAI_EMBEDDING_MODEL=text-embedding-3-small
OPENAI_CHAT_MODEL=gpt-4o-mini

# Anthropic Configuration
ANTHROPIC_API_KEY=your_anthropic_api_key
ANTHROPIC_CHAT_MODEL=claude-3-sonnet-20240229

# Google Gemini Configuration
GEMINI_API_KEY=your_gemini_api_key
GEMINI_EMBEDDING_MODEL=text-embedding-004
GEMINI_CHAT_MODEL=gemini-1.5-flash

# Ollama Configuration (for local LLMs)
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_EMBEDDING_MODEL=nomic-embed-text
OLLAMA_CHAT_MODEL=llama3.1
```

## Quick Start

### 1. Add the Trait to Your Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Dgtlss\Synapse\Traits\HasAiFeatures;

class Post extends Model
{
    use HasAiFeatures;

    protected $fillable = ['title', 'content'];
}
```

### 2. Configure Embeddings

```php
// In your Post model
protected $aiEmbeddable = [
    'column' => 'content_embedding', // Database column for the vector
    'source' => 'content',           // Source attribute to embed
];
```

### 3. Create Migration with Vector Column

```php
// In your migration
use Dgtlss\Synapse\Database\MigrationHelper;

Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('content');
    
    // Add vector column for embeddings
    MigrationHelper::addVectorColumn($table, 'content_embedding', 1536);
    
    $table->timestamps();
});
```

### 4. Use Semantic Search

```php
// Find posts similar to a query
$similarPosts = Post::searchSimilar('artificial intelligence trends', 5)->get();

// Find posts similar to an existing post
$post = Post::find(1);
$similarPosts = $post->searchSimilar(3);
```

## Advanced Features

### On-the-Fly Attribute Augmentation

Generate additional attributes dynamically without storing them in the database:

```php
// In your Post model
protected string $aiAppendableSource = 'content';

protected array $aiAppendable = [
    'summary' => 'Summarize the following article in two sentences: {self}',
    'keywords' => 'Extract the 5 most important keywords from the following text as a comma-separated list: {self}',
    'sentiment' => 'What is the sentiment of the following text (positive, neutral, negative)?: {self}',
];
```

Usage:

```php
$post = Post::find(1);

echo $post->summary;   // "AI is transforming Laravel development..."
echo $post->keywords;  // "laravel, ai, packages, eloquent, development"
echo $post->sentiment; // "positive"
```

### AI-Enhanced Model Factories

Generate realistic test data with AI:

```php
// In database/factories/PostFactory.php
use Dgtlss\Synapse\Facades\AIFactory;

class PostFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(6);

        return [
            'title' => $title,
            'content' => AIFactory::generate("Write a 200-word blog post about '{$title}'"),
        ];
    }
}
```

## Database Support

### PostgreSQL (Recommended)

For optimal performance with vector operations, use PostgreSQL with the `pgvector` extension:

```bash
# Install pgvector extension
CREATE EXTENSION vector;

# Your migrations will automatically use vector columns
MigrationHelper::addVectorColumn($table, 'content_embedding', 1536);
```

### MySQL/SQLite Fallback

For MySQL and SQLite, the package automatically falls back to JSON/TEXT columns:

```php
// Automatically handled by MigrationHelper
MigrationHelper::addVectorColumn($table, 'content_embedding', 1536);
```

## AI Service Providers

### OpenAI

```php
// .env
SYNAPSE_DEFAULT_SERVICE=openai
OPENAI_API_KEY=your_api_key
OPENAI_EMBEDDING_MODEL=text-embedding-3-small
OPENAI_CHAT_MODEL=gpt-4o-mini
```

### Anthropic (Claude)

```php
// .env
SYNAPSE_DEFAULT_SERVICE=anthropic
ANTHROPIC_API_KEY=your_api_key
ANTHROPIC_CHAT_MODEL=claude-3-sonnet-20240229
```

### Google Gemini

```php
// .env
SYNAPSE_DEFAULT_SERVICE=gemini
GEMINI_API_KEY=your_api_key
GEMINI_EMBEDDING_MODEL=text-embedding-004
GEMINI_CHAT_MODEL=gemini-1.5-flash
```

### Ollama (Local LLMs)

```php
// .env
SYNAPSE_DEFAULT_SERVICE=ollama
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_EMBEDDING_MODEL=nomic-embed-text
OLLAMA_CHAT_MODEL=llama3.1
```

### Custom AI Services

You can easily add support for any LLM provider by implementing the `CustomAIServiceInterface`:

```php
use Dgtlss\Synapse\Contracts\CustomAIServiceInterface;

class MyCustomAIService implements CustomAIServiceInterface
{
    public function generateEmbedding($text): array
    {
        // Your embedding logic here
    }

    public function generateCompletion(string $prompt, array $options = []): string
    {
        // Your completion logic here
    }

    public function getName(): string
    {
        return 'my-custom-service';
    }

    public function isConfigured(): bool
    {
        // Check if your service is properly configured
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

// Register your custom service
app(\Dgtlss\Synapse\Services\AIServiceManager::class)
    ->registerCustomService('my-custom', MyCustomAIService::class);

// Use it like any other service
$result = Synapse::service('my-custom')->generateCompletion('Hello!');
```

## API Reference

### HasAiFeatures Trait

#### Properties

- `$aiEmbeddable`: Array of embedding configurations
- `$aiAppendable`: Array of appendable attribute configurations
- `$aiAppendableSource`: Source attribute for appendable features

#### Methods

- `searchSimilar(string $query, int $count = 5)`: Semantic search scope
- `searchSimilar(int $count = 5)`: Find similar records to current model

### Synapse Facade

```php
use Dgtlss\Synapse\Facades\Synapse;

// Generate embeddings
$embedding = Synapse::generateEmbedding('Your text here');

// Generate completions
$result = Synapse::generateCompletion('Your prompt here');

// Use specific service
$result = Synapse::service('ollama')->generateCompletion('Your prompt');
```

### AIFactory Facade

```php
use Dgtlss\Synapse\Facades\AIFactory;

// Generate content for factories
$content = AIFactory::generate('Write a blog post about Laravel');
```

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
