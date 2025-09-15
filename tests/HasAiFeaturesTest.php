<?php

namespace Dgtlss\Synapse\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Dgtlss\Synapse\Traits\HasAiFeatures;
use Dgtlss\Synapse\Database\MigrationHelper;

class HasAiFeaturesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create test table
        Schema::create('test_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            MigrationHelper::addVectorColumn($table, 'content_embedding', 1536);
            $table->timestamps();
        });
    }

    public function test_trait_can_be_added_to_model()
    {
        $post = new TestPost();
        $this->assertTrue(method_exists($post, 'scopeSearchSimilar'));
    }

    public function test_embeddable_configuration()
    {
        $post = new TestPost([
            'title' => 'Test Post',
            'content' => 'This is a test post content.',
        ]);

        $this->assertTrue(property_exists($post, 'aiEmbeddable'));
        $this->assertIsArray($post->aiEmbeddable);
    }
}

class TestPost extends Model
{
    use HasAiFeatures;

    protected $table = 'test_posts';
    protected $fillable = ['title', 'content'];

    protected $aiEmbeddable = [
        'column' => 'content_embedding',
        'source' => 'content',
    ];
}