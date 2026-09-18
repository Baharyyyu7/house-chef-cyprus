<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use LogicException;

final class RecipeTestKitchenRatingsSeeder extends Seeder
{
    public function run(): void
    {
        throw new LogicException('Synthetic recipe ratings are disabled. Only genuine visitor ratings may be stored.');
    }
}
