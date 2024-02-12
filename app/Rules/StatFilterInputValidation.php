<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StatFilterInputValidation implements Rule
{
    protected $validStats = [
        'win_rate',
        'game_time',
        'kills',
        'takedowns',
        'deaths',
        'siege_damage',
        'hero_damage',
        'healing',
        'damage_taken',
        'experience_contribution',
        'assists',
        'highest_kill_streak',
        'structure_damage',
        'minion_damage',
        'creep_damage',
        'summon_damage',
        'self_healing',
        'town_kills',
        'time_spent_dead',
        'merc_camp_captures',
        'watch_tower_captures',
        'protection_Allies',
        'silencing_enemies',
        'rooting_enemies',
        'stunning_enemies',
        'clutch_heals',
        'escapes',
        'vengeance',
        'outnumbered_deaths',
        'teamfight_escapes',
        'teamfight_healing',
        'teamfight_damage_taken',
        'teamfight_hero_damage',
        'multikill',
        'physical_damage',
        'spell_damage',
        'regen_globes',
    ];

    protected $timeframeType;

    protected $timeframe;

    public function __construct($timeframeType, $timeframe)
    {
        $this->timeframeType = $timeframeType;
        $this->timeframe = $timeframe;

        if (! is_array($this->timeframe)) {
            $this->timeframe = explode(',', $this->timeframe);
        }

    }

    public function passes($attribute, $value)
    {
        if ($this->timeframeType == 'major' || count($this->timeframe) > 5) {
            return false;
        }
        if (! in_array($value, $this->validStats)) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return 'The :attribute must be a valid stat code.';
    }
}
