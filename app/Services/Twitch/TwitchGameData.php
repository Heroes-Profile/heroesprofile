<?php

namespace App\Services\Twitch;

use App\Services\GlobalDataService;
use Illuminate\Support\Collection;

/**
 * Hero and talent lookups for live snapshots, and the static data file the
 * extension renders them from.
 *
 * Snapshots carry ids only. Titles, descriptions and icons would not fit in Twitch's
 * 5KB message limit for a full lobby, and they never change mid-game anyway.
 */
class TwitchGameData
{
    /** @var array<string, int>|null */
    private ?array $heroIdsByAlias = null;

    /** @var array<string, int>|null */
    private ?array $talentIds = null;

    public function __construct(private readonly GlobalDataService $globalDataService) {}

    /**
     * The hero id for whatever the uploader sent: the attribute id from the game
     * files (e.g. "Abat"), or a hero name.
     */
    public function heroId(?string $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $this->heroAliases()[mb_strtolower($value)] ?? null;
    }

    public function heroName(int $heroId): ?string
    {
        return $this->heroes()->firstWhere('id', $heroId)?->name;
    }

    /** Talent id from the internal talent name the game's tracker events use. */
    public function talentId(string $heroName, string $talentName): ?int
    {
        if ($this->talentIds === null) {
            $this->talentIds = [];

            // Playable rows written last, so they win when a retired talent shares
            // a name with its replacement.
            $talents = $this->globalDataService->getAllTalentsKeyed()
                ->sortBy(fn ($talent) => $talent->status === 'playable' ? 1 : 0);

            foreach ($talents as $talent) {
                $this->talentIds[$talent->hero_name.'|'.$talent->talent_name] = (int) $talent->talent_id;
            }
        }

        return $this->talentIds[$heroName.'|'.$talentName] ?? null;
    }

    /**
     * Everything the extension needs to draw a lobby, keyed by id. Kept compact:
     * it ships inside the extension bundle.
     *
     * @return array<string, mixed>
     */
    public function export(): array
    {
        $heroes = [];

        foreach ($this->heroes() as $hero) {
            $heroes[$hero->id] = [
                'n' => $hero->name,
                's' => $hero->short_name,
                'b' => $hero->build_copy_name,
            ];
        }

        $heroIdsByName = $this->heroes()->pluck('id', 'name');

        $talents = [];

        foreach ($this->globalDataService->getAllTalentsKeyed() as $talent) {
            $talents[$talent->talent_id] = [
                'e' => $heroIdsByName[$talent->hero_name] ?? null,
                't' => $talent->title,
                'd' => $talent->description,
                'i' => $talent->icon,
                'l' => (int) $talent->level,
                's' => (int) $talent->sort,
                'h' => $talent->hotkey,
            ];
        }

        return [
            'generated_at' => now()->toIso8601String(),
            'heroes' => $heroes,
            'talents' => $talents,
        ];
    }

    private function heroes(): Collection
    {
        return $this->globalDataService->getHeroes();
    }

    /** @return array<string, int> */
    private function heroAliases(): array
    {
        if ($this->heroIdsByAlias === null) {
            $this->heroIdsByAlias = [];

            foreach ($this->heroes() as $hero) {
                foreach ([$hero->attribute_id, $hero->name, $hero->short_name, $hero->alt_name] as $alias) {
                    if ($alias !== null && $alias !== '') {
                        $this->heroIdsByAlias[mb_strtolower((string) $alias)] ??= (int) $hero->id;
                    }
                }
            }
        }

        return $this->heroIdsByAlias;
    }
}
