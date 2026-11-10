<?php

namespace App\Services;

use App\Models\Hero;
use App\Models\HeroesDataAbility;
use App\Models\HeroesDataTalent;
use App\Models\HeroesDataUnit;
use App\Models\HeroesDataWeapon;
use App\Models\HeroesMetadata;
use Illuminate\Support\Facades\Cache;

/** Game data for the /Heroes pages, written each patch by the decompile tool. */
class HeroPageService
{
    private const CACHE_SECONDS = 3600;

    public function getIndex(): array
    {
        return Cache::remember('hero_pages_index', self::CACHE_SECONDS, function () {
            $metadata = HeroesMetadata::get(['hero_id', 'title', 'franchise', 'difficulty'])->keyBy('hero_id');

            return Hero::orderBy('name')
                ->get(['id', 'name', 'short_name', 'new_role', 'release_date'])
                ->map(fn ($hero) => array_merge($hero->toArray(), [
                    'title' => $metadata[$hero->id]->title ?? null,
                    'franchise' => $metadata[$hero->id]->franchise ?? null,
                    'difficulty' => $metadata[$hero->id]->difficulty ?? null,
                ]))
                ->values()
                ->all();
        });
    }

    public function getHero(Hero $hero): array
    {
        return Cache::remember('hero_page|'.$hero->id, self::CACHE_SECONDS, function () use ($hero) {
            return [
                'hero' => $hero->only(['id', 'name', 'short_name', 'new_role', 'type', 'release_date', 'rework_date']),
                'metadata' => HeroesMetadata::find($hero->id),
                'units' => HeroesDataUnit::where('hero_id', $hero->id)->orderBy('sort')->get()->makeHidden('hero_id'),
                'weapons' => HeroesDataWeapon::where('hero_id', $hero->id)->orderBy('sort')->get()->makeHidden('hero_id'),
                'abilities' => HeroesDataAbility::where('hero_name', $hero->name)
                    ->orderBy('sort')
                    ->get()
                    ->makeHidden(['hero_name', 'short_name', 'attribute_id']),
                'talents' => HeroesDataTalent::where('hero_name', $hero->name)
                    ->where('level', '!=', 0)
                    ->orderBy('level')
                    ->orderBy('sort')
                    ->get(['talent_id', 'title', 'talent_name', 'description', 'short_description', 'raw_description', 'hotkey', 'cooldown', 'mana_cost', 'level', 'sort', 'icon', 'link_id', 'ability_link_id', 'is_quest']),
            ];
        });
    }
}
