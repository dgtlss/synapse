<?php

namespace Dgtlss\Synapse\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Dgtlss\Synapse\SynapseServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            SynapseServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Setup Synapse configuration
        $app['config']->set('synapse.default', 'openai');
        $app['config']->set('synapse.openai.api_key', 'test-key');
        $app['config']->set('synapse.openai.embedding_model', 'text-embedding-3-small');
        $app['config']->set('synapse.openai.chat_model', 'gpt-4o-mini');
    }
}