<?php

namespace Dgtlss\Synapse\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Dgtlss\Synapse\Facades\Synapse;

class Vector implements CastsAttributes
{
    protected string $sourceAttribute;
    protected string $columnName;

    public function __construct(string $sourceAttribute, string $columnName)
    {
        $this->sourceAttribute = $sourceAttribute;
        $this->columnName = $columnName;
    }

    /**
     * Cast the given value.
     *
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return array
     */
    public function get($model, $key, $value, $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        // If it's already an array, return it
        if (is_array($value)) {
            return $value;
        }

        // If it's a JSON string, decode it
        if (is_string($value)) {
            return json_decode($value, true);
        }

        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return string
     */
    public function set($model, $key, $value, $attributes)
    {
        // If the value is already an array (vector), store it as JSON
        if (is_array($value)) {
            return json_encode($value);
        }

        // If the value is null, return null
        if (is_null($value)) {
            return null;
        }

        // If we have a source attribute, generate the embedding
        if (isset($attributes[$this->sourceAttribute])) {
            $sourceValue = $attributes[$this->sourceAttribute];
            
            if (!empty($sourceValue)) {
                try {
                    $embedding = Synapse::generateEmbedding($sourceValue);
                    return json_encode($embedding);
                } catch (\Exception $e) {
                    // Log the error but don't fail the model save
                    \Log::warning('Failed to generate embedding for ' . $this->sourceAttribute . ': ' . $e->getMessage());
                    return null;
                }
            }
        }

        return null;
    }
}