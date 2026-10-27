<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BattlenetAccount;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Admin console: community polls.
 *
 * Once a poll has votes its options are locked to their wording: adding or
 * removing one would skew the counts already cast.
 */
class PollAdminController extends Controller
{
    public function index(): JsonResponse
    {
        $polls = Poll::withCount('votes')
            ->orderByDesc('poll_id')
            ->get()
            ->map(fn (Poll $poll) => [
                'id' => $poll->poll_id,
                'slug' => $poll->slug,
                'title' => $poll->title,
                'closes_at' => $poll->closes_at?->toDateTimeString(),
                'is_open' => $poll->isOpen(),
                'votes' => $poll->votes_count,
            ])
            ->all();

        return response()->json(['polls' => $polls]);
    }

    public function show(int $id): JsonResponse
    {
        $poll = Poll::with('options')->findOrFail($id);

        $votes = PollVote::with('options')
            ->where('poll_id', $poll->poll_id)
            ->orderByDesc('updated_at')
            ->get();

        // pluck, not get: BattlenetAccount eager-loads Patreon and settings on every model
        $battletags = BattlenetAccount::whereIn('battlenet_accounts_id', $votes->pluck('battlenet_accounts_id')->unique())
            ->pluck('battletag', 'battlenet_accounts_id');

        $labels = $poll->options->pluck('label', 'poll_option_id');

        return response()->json([
            'poll' => $this->present($poll),
            'results' => $poll->results(),
            'comments' => $votes->filter(fn (PollVote $vote) => $vote->comment !== null)
                ->map(fn (PollVote $vote) => [
                    'battletag' => $battletags[$vote->battlenet_accounts_id] ?? 'id '.$vote->battlenet_accounts_id,
                    'picks' => $vote->options->map(fn ($pick) => $labels[$pick->poll_option_id] ?? '?')->values()->all(),
                    'comment' => $vote->comment,
                    'updated_at' => $vote->updated_at?->toDateTimeString(),
                ])
                ->values()
                ->all(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatePoll($request);

        $poll = DB::connection('heroesprofile_polls')->transaction(function () use ($validated) {
            $poll = Poll::create($this->pollFields($validated));
            $this->createOptions($poll, $validated['options']);

            return $poll;
        });

        return response()->json(['poll' => $this->present($poll->load('options'))]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $poll = Poll::with('options')->findOrFail($id);
        $validated = $this->validatePoll($request, $poll);
        $hasVotes = $poll->votes()->exists();

        if ($hasVotes) {
            $sent = collect($validated['options'])->pluck('id')->map(fn ($id) => (int) $id)->sort()->values();
            if ($sent->all() !== $poll->options->pluck('poll_option_id')->sort()->values()->all()) {
                throw ValidationException::withMessages(['options' => 'This poll has votes, so options can be reworded but not added or removed.']);
            }
        }

        DB::connection('heroesprofile_polls')->transaction(function () use ($poll, $validated, $hasVotes) {
            $poll->update($this->pollFields($validated));

            if ($hasVotes) {
                foreach ($validated['options'] as $index => $option) {
                    PollOption::where('poll_option_id', $option['id'])->update([
                        'label' => $option['label'],
                        'description' => $option['description'] ?? null,
                        'sort_order' => $index + 1,
                    ]);
                }
            } else {
                PollOption::where('poll_id', $poll->poll_id)->delete();
                $this->createOptions($poll, $validated['options']);
            }
        });

        return response()->json(['poll' => $this->present($poll->fresh('options'))]);
    }

    public function close(int $id): JsonResponse
    {
        $poll = Poll::findOrFail($id);
        $poll->update(['closes_at' => now()]);

        return response()->json(['poll' => $this->present($poll->load('options'))]);
    }

    private function validatePoll(Request $request, ?Poll $poll = null): array
    {
        return $request->validate([
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/',
                Rule::unique('heroesprofile_polls.polls', 'slug')->ignore($poll?->poll_id, 'poll_id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'allow_multiple' => ['required', 'boolean'],
            'closes_at' => ['nullable', 'date'],
            'options' => ['required', 'array', 'min:2', 'max:20'],
            'options.*.id' => ['nullable', 'integer'],
            'options.*.label' => ['required', 'string', 'max:100'],
            'options.*.description' => ['nullable', 'string', 'max:1000'],
        ], [
            'slug.regex' => 'The slug can only use lowercase letters, numbers and dashes.',
        ]);
    }

    private function pollFields(array $validated): array
    {
        return [
            'slug' => $validated['slug'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'allow_multiple' => $validated['allow_multiple'],
            'closes_at' => $validated['closes_at'] ?? null,
        ];
    }

    private function createOptions(Poll $poll, array $options): void
    {
        foreach ($options as $index => $option) {
            PollOption::create([
                'poll_id' => $poll->poll_id,
                'label' => $option['label'],
                'description' => $option['description'] ?? null,
                'sort_order' => $index + 1,
            ]);
        }
    }

    /** @return array<string, mixed> */
    private function present(Poll $poll): array
    {
        return [
            'id' => $poll->poll_id,
            'slug' => $poll->slug,
            'title' => $poll->title,
            'description' => $poll->description,
            'allow_multiple' => $poll->allow_multiple,
            // In the shape a datetime-local input takes; UTC like the rest of the app
            'closes_at' => $poll->closes_at?->format('Y-m-d\TH:i'),
            'is_open' => $poll->isOpen(),
            'has_votes' => $poll->votes()->exists(),
            'options' => $poll->options->map(fn (PollOption $option) => [
                'id' => $option->poll_option_id,
                'label' => $option->label,
                'description' => $option->description,
            ])->all(),
        ];
    }
}
