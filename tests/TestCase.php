<?php

namespace HeshamFouda\AgGrid\Tests;

use HeshamFouda\AgGrid\AgGridServiceProvider;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Maatwebsite\Excel\ExcelServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use DatabaseMigrations;

    protected function getPackageProviders($app): array
    {
        return [
            AgGridServiceProvider::class,
            ExcelServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessModelNamesUsing(
            fn (Factory $factory) => 'HeshamFouda\\AgGrid\\Tests\\TestClasses\\Models\\'.Str::before(class_basename($factory::class), 'Factory')
        );

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'HeshamFouda\\AgGrid\\Tests\\TestClasses\\Factories\\'.class_basename($modelName).'Factory'
        );

        /** @var DatabaseManager $db */
        $db = $this->app->get('db');

        $db->connection()->getSchemaBuilder()->create('zoos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->jsonb('address');
            $table->timestamps();
        });

        $db->connection()->getSchemaBuilder()->create('keepers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('zoo_id')->index()->constrained();
            $table->timestamps();
        });

        $db->connection()->getSchemaBuilder()->create('flamingos', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('species');
            $table->float('weight');
            $table->jsonb('preferred_food_types');
            $table->jsonb('custom_properties')->nullable();
            $table->date('last_vaccinated_on')->nullable();
            $table->boolean('is_hungry')->default(false);
            $table->softDeletes();
            $table->foreignId('keeper_id')->index()->constrained();
        });

    }
}
