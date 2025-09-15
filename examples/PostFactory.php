<?php

namespace Examples;

use Illuminate\Database\Eloquent\Factories\Factory;
use Dgtlss\Synapse\Facades\AIFactory;

/**
 * Example Post factory demonstrating AI-enhanced data generation
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = fake()->sentence(6);
        $topic = fake()->randomElement([
            'artificial intelligence',
            'web development',
            'machine learning',
            'data science',
            'cybersecurity',
            'cloud computing',
        ]);

        return [
            'title' => $title,
            'content' => AIFactory::generate("Write a 300-word blog post about {$topic}. Make it informative and engaging."),
            'author' => fake()->name(),
        ];
    }

    /**
     * Create a post about a specific topic
     */
    public function aboutTopic(string $topic): static
    {
        return $this->state(function (array $attributes) use ($topic) {
            return [
                'content' => AIFactory::generate("Write a detailed blog post about {$topic}. Include practical examples and insights."),
            ];
        });
    }

    /**
     * Create a technical post
     */
    public function technical(): static
    {
        return $this->state(function (array $attributes) {
            $tech = fake()->randomElement(['Laravel', 'React', 'Python', 'Docker', 'Kubernetes']);
            
            return [
                'title' => "Advanced {$tech} Techniques",
                'content' => AIFactory::generate("Write a technical blog post about advanced {$tech} techniques. Include code examples and best practices."),
            ];
        });
    }
}