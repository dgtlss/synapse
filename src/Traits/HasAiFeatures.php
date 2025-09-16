<?php

namespace Dgtlss\Synapse\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Dgtlss\Synapse\Facades\Synapse;
use Dgtlss\Synapse\Casts\Vector;

trait HasAiFeatures
{
    /**
     * Boot the trait.
     */
    protected static function bootHasAiFeatures(): void
    {
        static::saving(function (Model $model) {
            $model->generateEmbeddings();
        });

        static::retrieved(function (Model $model) {
            $model->generateAppendableAttributes();
        });
    }

    /**
     * Generate embeddings for configured attributes.
     */
    protected function generateEmbeddings(): void
    {
        if (!property_exists($this, 'aiEmbeddable')) {
            return;
        }

        $configs = $this->normalizeEmbeddableConfig();

        foreach ($configs as $config) {
            $sourceAttribute = $config['source'];
            $columnAttribute = $config['column'];

            if ($this->isDirty($sourceAttribute) && !empty($this->getAttribute($sourceAttribute))) {
                try {
                    $embedding = Synapse::generateEmbedding($this->getAttribute($sourceAttribute));
                    $this->setAttribute($columnAttribute, $embedding);
                } catch (\Exception $e) {
                    \Log::warning('Failed to generate embedding for ' . $sourceAttribute . ': ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Normalize embeddable configuration to handle both flat and nested array formats.
     *
     * @return array
     */
    protected function normalizeEmbeddableConfig(): array
    {
        $config = $this->aiEmbeddable;

        // Handle flat array format (backward compatibility)
        if (isset($config['column']) && isset($config['source'])) {
            return [$config];
        }

        // Handle array of arrays format (preferred)
        if (is_array($config) && !empty($config) && is_array($config[0])) {
            return $config;
        }

        return [];
    }

    /**
     * Generate appendable attributes on model retrieval.
     */
    protected function generateAppendableAttributes(): void
    {
        if (!property_exists($this, 'aiAppendable') || !property_exists($this, 'aiAppendableSource')) {
            return;
        }

        $sourceValue = $this->getAttribute($this->aiAppendableSource);
        
        if (empty($sourceValue)) {
            return;
        }

        foreach ($this->aiAppendable as $attribute => $prompt) {
            if (!$this->hasAppended($attribute)) {
                try {
                    $promptWithContent = str_replace('{self}', $sourceValue, $prompt);
                    $result = Synapse::generateCompletion($promptWithContent);
                    
                    $this->setAttribute($attribute, $result);
                    $this->append($attribute);
                } catch (\Exception $e) {
                    \Log::warning('Failed to generate appendable attribute ' . $attribute . ': ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Search for similar records using semantic search.
     *
     * @param Builder $query
     * @param string $searchQuery
     * @param int $count
     * @return Builder
     */
    public function scopeSearchSimilar(Builder $query, string $searchQuery, int $count = 5): Builder
    {
        if (!property_exists($this, 'aiEmbeddable') || empty($this->aiEmbeddable)) {
            throw new \Exception('No embeddable attributes configured for semantic search');
        }

        try {
            // Generate embedding for the search query
            $queryEmbedding = Synapse::generateEmbedding($searchQuery);
            
            // Get the first embeddable configuration
            $configs = $this->normalizeEmbeddableConfig();
            if (empty($configs)) {
                throw new \Exception('Invalid embeddable configuration');
            }
            
            $config = $configs[0];
            $embeddingColumn = $config['column'];
            
            // Convert embedding to JSON for database query
            $queryEmbeddingJson = json_encode($queryEmbedding);
            
            // Use raw SQL for vector similarity search
            // This assumes you're using a database that supports vector operations
            return $query->selectRaw("*, 
                CASE 
                    WHEN {$embeddingColumn} IS NOT NULL 
                    THEN 1 - (
                        SELECT cosine_similarity(
                            {$embeddingColumn}::vector, 
                            '{$queryEmbeddingJson}'::vector
                        )
                    )
                    ELSE 1 
                END as similarity_score")
                ->whereNotNull($embeddingColumn)
                ->orderBy('similarity_score', 'asc')
                ->limit($count);
                
        } catch (\Exception $e) {
            \Log::warning('Semantic search failed: ' . $e->getMessage());
            return $query->whereRaw('1 = 0'); // Return empty result
        }
    }

    /**
     * Search for records similar to this model instance.
     *
     * @param int $count
     * @return Builder
     */
    public function searchSimilar(int $count = 5): Builder
    {
        if (!property_exists($this, 'aiEmbeddable') || empty($this->aiEmbeddable)) {
            throw new \Exception('No embeddable attributes configured for semantic search');
        }

        $configs = $this->normalizeEmbeddableConfig();
        if (empty($configs)) {
            throw new \Exception('Invalid embeddable configuration');
        }

        $config = $configs[0];
        $sourceAttribute = $config['source'];
        
        $sourceValue = $this->getAttribute($sourceAttribute);
        
        if (empty($sourceValue)) {
            throw new \Exception('Source attribute is empty for similarity search');
        }

        return static::searchSimilar($sourceValue, $count);
    }

    /**
     * Get the casts array with vector casts.
     *
     * @return array
     */
    public function getCasts(): array
    {
        $casts = parent::getCasts();
        
        if (property_exists($this, 'aiEmbeddable')) {
            $configs = $this->normalizeEmbeddableConfig();
            
            foreach ($configs as $config) {
                $column = $config['column'];
                $source = $config['source'];
                
                if (!isset($casts[$column])) {
                    $casts[$column] = Vector::class . ':' . $source . ',' . $column;
                }
            }
        }
        
        return $casts;
    }
}