<?php

namespace Examples;

use Illuminate\Database\Eloquent\Model;
use Dgtlss\Synapse\Traits\HasAiFeatures;

/**
 * Example Post model demonstrating Synapse features
 */
class Post extends Model
{
    use HasAiFeatures;

    protected $fillable = ['title', 'content', 'author'];

    /**
     * Configure automatic embedding generation
     * 
     * Format 1: Array of arrays (recommended for multiple embeddings)
     */
    protected $aiEmbeddable = [
        [
            'column' => 'content_embedding', // Database column for the vector
            'source' => 'content',          // Source attribute to embed
        ],
        // You can add more embeddings like this:
        // [
        //     'column' => 'title_embedding',
        //     'source' => 'title',
        // ],
    ];

    /**
     * Alternative format: Flat array (backward compatible)
     * Uncomment this and comment out the above if you prefer the flat format
     */
    // protected $aiEmbeddable = [
    //     'column' => 'content_embedding',
    //     'source' => 'content',
    // ];

    /**
     * Configure on-the-fly attribute augmentation
     */
    protected string $aiAppendableSource = 'content';

    protected array $aiAppendable = [
        'summary' => 'Summarize the following article in two sentences: {self}',
        'keywords' => 'Extract the 5 most important keywords from the following text as a comma-separated list: {self}',
        'sentiment' => 'What is the sentiment of the following text (positive, neutral, negative)?: {self}',
        'reading_time' => 'Estimate the reading time in minutes for the following text: {self}',
    ];

    /**
     * Example usage in a controller
     */
    public static function exampleUsage()
    {
        // Create a new post (embedding will be generated automatically)
        $post = Post::create([
            'title' => 'The Future of AI in Web Development',
            'content' => 'Artificial intelligence is revolutionizing web development...',
            'author' => 'John Doe',
        ]);

        // Semantic search - find similar posts
        $similarPosts = Post::searchSimilar('machine learning trends', 5)->get();

        // Find posts similar to this specific post
        $relatedPosts = $post->searchSimilar(3);

        // Access augmented attributes (generated on-the-fly)
        echo $post->summary;     // "AI is transforming web development..."
        echo $post->keywords;    // "ai, web development, machine learning..."
        echo $post->sentiment;   // "positive"
        echo $post->reading_time; // "3 minutes"

        return $post;
    }
}