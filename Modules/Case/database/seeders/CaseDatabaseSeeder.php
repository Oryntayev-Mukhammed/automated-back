<?php

namespace Modules\Case\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Case\Entities\CaseModel;

class CaseDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = module_path('Case', 'resources/data/cases.json');
        if (!File::exists($path)) {
            $this->command?->warn('cases.json not found, skipping CaseDatabaseSeeder');
            return;
        }

        $payload = json_decode(File::get($path), true) ?: [];
        $rows = collect($payload)->map(function ($item) {
            return [
                'property_title' => $item['property_title'] ?? 'Case',
                'property_img' => $item['property_img'] ?? null,
                'property_price' => $item['property_price'] ?? null,
                'category' => $item['category'] ?? null,
                'location' => $item['location'] ?? null,
                'livingArea' => $item['livingArea'] ?? null,
                'tag' => $item['tag'] ?? null,
                'status' => $item['status'] ?? null,
                'type' => $item['type'] ?? null,
                'slug' => $item['slug'] ?? Str::slug(($item['property_title'] ?? 'case') . '-' . Str::random(4)),
                'language' => $item['language'] ?? 'en',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->all();

        CaseModel::query()->truncate();
        CaseModel::query()->insert($rows);
    }
}
