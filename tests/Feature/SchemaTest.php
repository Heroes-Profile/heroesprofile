<?php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

/** The migrations build a table for every model, on the model's own connection. */
class SchemaTest extends FeatureTestCase
{
    public static function models(): array
    {
        $models = [];

        foreach (Finder::create()->files()->in(__DIR__.'/../../app/Models')->name('*.php') as $file) {
            $class = 'App\\Models\\'.str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());

            if (class_exists($class) && is_subclass_of($class, Model::class) && ! (new ReflectionClass($class))->isAbstract()) {
                $models[$class] = [$class];
            }
        }

        ksort($models);

        return $models;
    }

    #[DataProvider('models')]
    public function test_model_table_exists(string $class): void
    {
        /** @var Model $model */
        $model = new $class;
        $table = $model->getTable();
        $connection = $model->getConnectionName() ?? config('database.default');

        $this->assertTrue(
            Schema::connection($connection)->hasTable($table),
            "{$class}: table {$table} missing on connection {$connection}"
        );
    }
}
