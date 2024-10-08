<?php

namespace HeshamFouda\AgGrid\Tests\TestClasses\Factories;

use HeshamFouda\AgGrid\Tests\TestClasses\Models\Zoo;
use Illuminate\Database\Eloquent\Factories\Factory;

class KeeperFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'zoo_id' => Zoo::factory(),
        ];
    }
}
