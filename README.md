# 🧠 Synapse

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dgtlss/synapse.svg?style=flat-square)](https://packagist.org/packages/dgtlss/synapse)
[![Total Downloads](https://img.shields.io/packagist/dt/dgtlss/synapse.svg?style=flat-square)](https://packagist.org/packages/dgtlss/synapse)

Seamlessly integrate AI capabilities directly into your Laravel Eloquent models. Generate vector embeddings, perform semantic search, and augment data on-the-fly using AI.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
  - [1. Add the Trait to Your Model](#1-add-the-trait-to-your-model)
  - [2. Configure Embeddings](#2-configure-embeddings)
  - [3. Create Migration with Vector Column](#3-create-migration-with-vector-column)
  - [4. Use Semantic Search](#4-use-semantic-search)
- [Advanced Features](#advanced-features)
  - [On-the-Fly Attribute Augmentation](#on-the-fly-attribute-augmentation)
  - [AI-Enhanced Model Factories](#ai-enhanced-model-factories)
- [Database Support](#database-support)
  - [PostgreSQL (Recommended)](#postgresql-recommended)
  - [MySQL/SQLite Fallback](#mysqlsqlite-fallback)
- [AI Service Providers](#ai-service-providers)
  - [OpenAI](#openai)
  - [Anthropic (Claude)](#anthropic-claude)
  - [Google Gemini](#google-gemini)
  - [Ollama (Local LLMs)](#ollama-local-llms)
  - [Custom AI Services](#custom-ai-services)
- [API Reference](#api-reference)
  - [HasAiFeatures Trait](#hasaifeatures-trait)
  - [Synapse Facade](#synapse-facade)
  - [AIFactory Facade](#aifactory-facade)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

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

After publishing the configuration file, you can configure your AI services by setting environment variables in your `.env` file:

```bash
# Set your default AI service
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

> **Note**: You only need to configure the environment variables for the AI service(s) you plan to use. See the [AI Service Providers](#ai-service-providers) section for detailed configuration options.

## Quick Start

Follow these steps to get started with Synapse in your Laravel application:

### 1. Add the Trait to Your Model

Add the `HasAiFeatures` trait to any Eloquent model you want to enhance with AI capabilities:

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

Define which attributes should be embedded and where to store the vector data:

```php
// In your Post model
protected $aiEmbeddable = [
    'column' => 'content_embedding', // Database column for the vector
    'source' => 'content',           // Source attribute to embed
];
```

### 3. Create Migration with Vector Column

Create a migration to add the vector column for storing embeddings:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Dgtlss\Synapse\Database\MigrationHelper;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            
            // Add vector column for embeddings (1536 dimensions for OpenAI)
            MigrationHelper::addVectorColumn($table, 'content_embedding', 1536);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

### 4. Use Semantic Search

Now you can perform semantic searches on your model:

```php
// Find posts similar to a text query
$similarPosts = Post::searchSimilar('artificial intelligence trends', 5)->get();

// Find posts similar to an existing post
$post = Post::find(1);
$similarPosts = $post->searchSimilar(3);
```

> **Tip**: The embeddings will be automatically generated when you save or update your model records.

## Advanced Features

### On-the-Fly Attribute Augmentation

Generate additional attributes dynamically without storing them in the database. This is perfect for creating summaries, extracting keywords, or analyzing sentiment on-demand.

**Configuration:**

```php
// In your Post model
protected string $aiAppendableSource = 'content';

protected array $aiAppendable = [
    'summary' => 'Summarize the following article in two sentences: {self}',
    'keywords' => 'Extract the 5 most important keywords from the following text as a comma-separated list: {self}',
    'sentiment' => 'What is the sentiment of the following text (positive, neutral, negative)?: {self}',
];
```

**Usage:**

```php
$post = Post::find(1);

echo $post->summary;   // "AI is transforming Laravel development..."
echo $post->keywords;  // "laravel, ai, packages, eloquent, development"
echo $post->sentiment; // "positive"
```

> **Note**: The `{self}` placeholder will be replaced with the content from the `$aiAppendableSource` attribute.

### AI-Enhanced Model Factories

Generate realistic test data using AI for your model factories:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
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

**Usage in Tests:**

```php
// Generate a single post with AI content
$post = Post::factory()->create();

// Generate multiple posts
$posts = Post::factory()->count(10)->create();
```

## Database Support

### PostgreSQL (Recommended)

For optimal performance with vector operations, use PostgreSQL with the `pgvector` extension:

```sql
-- Install pgvector extension
CREATE EXTENSION vector;
```

The `MigrationHelper` will automatically create proper vector columns:

```php
// This creates a proper vector column in PostgreSQL
MigrationHelper::addVectorColumn($table, 'content_embedding', 1536);
```

### MySQL/SQLite Fallback

For MySQL and SQLite, the package automatically falls back to JSON/TEXT columns for storing vector data:

```php
// Automatically handled by MigrationHelper
// Creates JSON column in MySQL, TEXT column in SQLite
MigrationHelper::addVectorColumn($table, 'content_embedding', 1536);
```

> **Performance Note**: While PostgreSQL with pgvector provides the best performance for vector operations, MySQL and SQLite implementations will work for smaller datasets and development environments.

## AI Service Providers

Synapse supports multiple AI providers. Configure your preferred service in the `.env` file:

### OpenAI

```bash
# .env
SYNAPSE_DEFAULT_SERVICE=openai
OPENAI_API_KEY=your_api_key
OPENAI_EMBEDDING_MODEL=text-embedding-3-small
OPENAI_CHAT_MODEL=gpt-4o-mini
```

**Models:**
- Embeddings: `text-embedding-3-small`, `text-embedding-3-large`, `text-embedding-ada-002`
- Chat: `gpt-4o`, `gpt-4o-mini`, `gpt-3.5-turbo`

### Anthropic (Claude)

```bash
# .env
SYNAPSE_DEFAULT_SERVICE=anthropic
ANTHROPIC_API_KEY=your_api_key
ANTHROPIC_CHAT_MODEL=claude-3-sonnet-20240229
```

**Models:**
- Chat: `claude-3-opus-20240229`, `claude-3-sonnet-20240229`, `claude-3-haiku-20240307`

### Google Gemini

```bash
# .env
SYNAPSE_DEFAULT_SERVICE=gemini
GEMINI_API_KEY=your_api_key
GEMINI_EMBEDDING_MODEL=text-embedding-004
GEMINI_CHAT_MODEL=gemini-1.5-flash
```

**Models:**
- Embeddings: `text-embedding-004`
- Chat: `gemini-1.5-pro`, `gemini-1.5-flash`, `gemini-pro`

### Ollama (Local LLMs)

```bash
# .env
SYNAPSE_DEFAULT_SERVICE=ollama
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_EMBEDDING_MODEL=nomic-embed-text
OLLAMA_CHAT_MODEL=llama3.1
```

**Popular Models:**
- Embeddings: `nomic-embed-text`, `mxbai-embed-large`
- Chat: `llama3.1`, `llama3.2`, `mistral`, `codellama`

### Custom AI Services

You can easily add support for any LLM provider by implementing the `CustomAIServiceInterface`:

```php
<?php

namespace App\Services;

use Dgtlss\Synapse\Contracts\CustomAIServiceInterface;

class MyCustomAIService implements CustomAIServiceInterface
{
    private array $config = [];

    public function generateEmbedding($text): array
    {
        // Your embedding logic here
        // Return an array of float values
    }

    public function generateCompletion(string $prompt, array $options = []): string
    {
        // Your completion logic here
        // Return the generated text
    }

    public function getName(): string
    {
        return 'my-custom-service';
    }

    public function isConfigured(): bool
    {
        // Check if your service is properly configured
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
```

**Register and Use:**

```php
// Register your custom service
app(\Dgtlss\Synapse\Services\AIServiceManager::class)
    ->registerCustomService('my-custom', MyCustomAIService::class);

// Use it like any other service
$result = Synapse::service('my-custom')->generateCompletion('Hello!');
```

## API Reference

### HasAiFeatures Trait

The `HasAiFeatures` trait adds AI capabilities to your Eloquent models.

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$aiEmbeddable` | `array` | Configuration for automatic embedding generation |
| `$aiAppendable` | `array` | Configuration for on-the-fly attribute augmentation |
| `$aiAppendableSource` | `string` | Source attribute for appendable features |

#### Methods

| Method | Description | Parameters |
|--------|-------------|------------|
| `searchSimilar(string $query, int $count = 5)` | Static scope for semantic search | `$query`: Search text, `$count`: Number of results |
| `searchSimilar(int $count = 5)` | Instance method to find similar records | `$count`: Number of results |

**Example:**

```php
// Static search
$posts = Post::searchSimilar('artificial intelligence', 10)->get();

// Instance search
$post = Post::find(1);
$similarPosts = $post->searchSimilar(5);
```

### Synapse Facade

The main facade for interacting with AI services:

```php
use Dgtlss\Synapse\Facades\Synapse;

// Generate embeddings
$embedding = Synapse::generateEmbedding('Your text here');

// Generate completions
$result = Synapse::generateCompletion('Your prompt here');

// Use specific service
$result = Synapse::service('ollama')->generateCompletion('Your prompt');

// Check if service is configured
$isConfigured = Synapse::service('openai')->isConfigured();
```

### AIFactory Facade

Generate AI content for your model factories:

```php
use Dgtlss\Synapse\Facades\AIFactory;

// Generate content for factories
$content = AIFactory::generate('Write a blog post about Laravel');

// Generate with specific service
$content = AIFactory::service('anthropic')->generate('Write a product description');
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.