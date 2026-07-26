<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class HouseChefSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(['email' => 'admin@housechef.local'], ['name' => 'House Chef Yöneticisi', 'password' => 'ChangeMe123!', 'email_verified_at' => now(), 'role' => 'admin']);
        $admin->update(['role' => 'admin']);
        $category = Category::query()->firstOrCreate(['slug' => 'ana-yemek'], ['name' => 'Ana Yemek', 'description' => 'Doyurucu tarifler']);
        $cuisines = collect(['Kıbrıs Mutfağı' => 'Kıbrıs', 'Türk Mutfağı' => 'Türkiye', 'İtalyan Mutfağı' => 'İtalya', 'Japon Mutfağı' => 'Japonya', 'Meksika Mutfağı' => 'Meksika', 'Lübnan Mutfağı' => 'Lübnan'])->mapWithKeys(fn (string $country, string $name) => [$name => Cuisine::query()->firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'country' => $country])]);
        $ingredientNames = ['tavuk', 'soğan', 'sarımsak', 'domates', 'zeytinyağı', 'pirinç', 'limon', 'yoğurt', 'patates', 'maydanoz', 'nohut', 'makarna'];
        $ingredients = collect($ingredientNames)->mapWithKeys(fn (string $name) => [$name => Ingredient::query()->firstOrCreate(['slug' => Str::slug($name)], ['name' => Str::headline($name), 'default_unit' => 'adet'])]);
        $recipes = [['Kıbrıs Usulü Fırın Tavuk', 'Kıbrıs Mutfağı', 20, 45], ['Zeytinyağlı Nohutlu Pilav', 'Türk Mutfağı', 15, 30], ['Limonlu Akdeniz Makarnası', 'İtalyan Mutfağı', 10, 20], ['Sebzeli Teriyaki Tavuk', 'Japon Mutfağı', 20, 18], ['Fırın Sebzeli Taco Kasesi', 'Meksika Mutfağı', 20, 25], ['Yoğurtlu Nohut Salatası', 'Lübnan Mutfağı', 15, 0], ['Domatesli Tavuk Sote', 'Türk Mutfağı', 15, 25], ['Limonlu Otlu Patates', 'Kıbrıs Mutfağı', 15, 35], ['Sarımsaklı Zeytinyağlı Makarna', 'İtalyan Mutfağı', 10, 15], ['Pirinçli Tavuk Çorbası', 'Türk Mutfağı', 15, 35], ['Nohutlu Akdeniz Kasesi', 'Lübnan Mutfağı', 20, 10], ['Fırında Baharatlı Tavuk', 'Meksika Mutfağı', 15, 40]];
        foreach ($recipes as $index => [$title, $cuisine, $prep, $cook]) {
            $recipe = Recipe::query()->firstOrCreate(['slug' => Str::slug($title)], ['title' => $title, 'cuisine_id' => $cuisines[$cuisine]->id, 'category_id' => $category->id, 'excerpt' => 'Mevsim malzemeleriyle hazırlanan, ev mutfağına uygun özgün demo tarifi.', 'description' => 'Bu tarif House Chef Cyprus için hazırlanmış örnek içeriktir. Damak tadınıza göre baharat oranını ayarlayabilirsiniz.', 'prep_minutes' => $prep, 'cook_minutes' => $cook, 'servings' => 4, 'difficulty' => $index % 3 === 0 ? 'Orta' : 'Kolay', 'dietary_tags' => $index % 2 === 0 ? ['Yüksek protein'] : ['Akdeniz tipi'], 'allergens' => $index % 2 === 0 ? [] : ['Gluten'], 'nutrition' => ['kalori' => 420, 'protein' => 28, 'karbonhidrat' => 44, 'yağ' => 14], 'storage_notes' => ['buzdolabi_gun' => 2, 'uyari' => 'Soğuduktan sonra kapalı kapta saklayın.'], 'source_name' => 'House Chef Cyprus özgün demo', 'reliability_score' => 100, 'status' => 'published', 'is_featured' => $index < 4]);
            foreach (array_values(array_slice($ingredients->all(), $index % 4, 4, true)) as $position => $ingredient) {
                $recipe->ingredients()->syncWithoutDetaching([$ingredient->id => ['quantity' => $position + 1, 'unit' => 'adet', 'is_optional' => false, 'sort_order' => $position + 1]]);
            }
            $recipe->steps()->firstOrCreate(['step_number' => 1], ['title' => 'Hazırlık', 'instructions' => 'Malzemeleri yıkayın, doğrayın ve ölçüleri hazırlayın.', 'duration_minutes' => $prep]);
            $recipe->steps()->firstOrCreate(['step_number' => 2], ['title' => 'Pişirme', 'instructions' => 'Malzemeleri tarifin gerektirdiği sırayla pişirin ve kıvamını kontrol edin.', 'duration_minutes' => $cook]);
            $recipe->steps()->firstOrCreate(['step_number' => 3], ['title' => 'Servis', 'instructions' => 'Tarifi dinlendirin ve sıcak servis edin.', 'duration_minutes' => 5, 'tip' => 'Taze yeşillikle servis edebilirsiniz.']);
        }
    }
}
