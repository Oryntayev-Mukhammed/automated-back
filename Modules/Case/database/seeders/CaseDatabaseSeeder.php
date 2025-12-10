<?php

namespace Modules\Case\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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

        DB::table('case_translations')->delete();
        CaseModel::query()->delete();
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement("DELETE FROM sqlite_sequence WHERE name in ('cases','case_translations')");
        }

        $targetLangs = ['en', 'de'];
        $grouped = collect($payload)->groupBy(fn ($item) => $item['slug'] ?? Str::slug(($item['property_title'] ?? 'case') . '-' . Str::random(4)));

        foreach ($grouped as $slug => $items) {
            $base = $items->firstWhere('language', 'en') ?? $items->first();
            $case = CaseModel::create([
                'property_title' => $base['property_title'] ?? 'Case',
                'property_img' => $base['property_img'] ?? null,
                'property_price' => $base['property_price'] ?? null,
                'category' => $base['category'] ?? null,
                'location' => $base['location'] ?? null,
                'livingArea' => $base['livingArea'] ?? null,
                'tag' => $base['tag'] ?? null,
                'status' => $base['status'] ?? null,
                'type' => $base['type'] ?? null,
                'slug' => $slug,
                'language' => $base['language'] ?? 'en',
            ]);

            $translations = [];
            foreach ($items as $item) {
                $lang = strtolower($item['language'] ?? 'en');
                $translations[$lang] = $item;
            }

            foreach ($targetLangs as $lang) {
                $source = $translations[$lang] ?? $translations['en'] ?? $base;
                DB::table('case_translations')->insert([
                    'case_id' => $case->id,
                    'lang' => $lang,
                    'slug' => $source['slug'] ?? $slug,
                    'property_title' => $source['property_title'] ?? $case->property_title,
                    'property_price' => $source['property_price'] ?? $case->property_price,
                    'location' => $source['location'] ?? $case->location,
                    'livingArea' => $source['livingArea'] ?? $case->livingArea,
                    'tag' => $source['tag'] ?? $case->tag,
                    'status' => $source['status'] ?? $case->status,
                    'type' => $source['type'] ?? $case->type,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
