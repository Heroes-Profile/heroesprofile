<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollVote;
use App\Models\PollVoteOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PollController extends Controller
{
    public function show(string $slug): View
    {
        $poll = Poll::with('options')->where('slug', $slug)->firstOrFail();
        $accountId = Auth::user()?->battlenet_accounts_id;
        $myVote = $accountId ? $this->voteFor($poll, $accountId) : null;

        return view('Poll.poll')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'poll' => $this->pollData($poll),
            'loggedIn' => (bool) $accountId,
            'myVote' => $myVote,
            // Results only once you've voted, or for everyone after it closes
            'results' => ($myVote || ! $poll->isOpen()) ? $poll->results() : null,
        ]);
    }

    public function vote(Request $request, string $slug): JsonResponse
    {
        // Always the logged-in account; never an id from the request.
        $accountId = Auth::user()?->battlenet_accounts_id;
        if (! $accountId) {
            return response()->json(['status' => 'unauthenticated'], 401);
        }

        $poll = Poll::with('options')->where('slug', $slug)->firstOrFail();
        if (! $poll->isOpen()) {
            return response()->json(['status' => 'closed', 'message' => 'This poll is closed.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'options' => 'required|array|min:1',
            'options.*' => 'integer',
            'comment' => 'nullable|string|max:2000',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'invalid', 'message' => $validator->errors()->first()], 422);
        }

        $optionIds = collect($request->input('options'))->map(fn ($id) => (int) $id)->unique()->values();
        if ($optionIds->diff($poll->options->pluck('poll_option_id'))->isNotEmpty()) {
            return response()->json(['status' => 'invalid', 'message' => 'Unknown option.'], 422);
        }
        if (! $poll->allow_multiple && $optionIds->count() > 1) {
            return response()->json(['status' => 'invalid', 'message' => 'Pick one option.'], 422);
        }

        $comment = trim((string) $request->input('comment')) ?: null;

        DB::connection('heroesprofile_polls')->transaction(function () use ($poll, $accountId, $optionIds, $comment) {
            $vote = PollVote::updateOrCreate(
                ['poll_id' => $poll->poll_id, 'battlenet_accounts_id' => $accountId],
                ['comment' => $comment]
            );

            PollVoteOption::where('poll_vote_id', $vote->poll_vote_id)->delete();
            PollVoteOption::insert($optionIds->map(fn ($id) => [
                'poll_vote_id' => $vote->poll_vote_id,
                'poll_option_id' => $id,
            ])->all());
        });

        return response()->json([
            'status' => 'success',
            'myVote' => $this->voteFor($poll, $accountId),
            'results' => $poll->results(),
        ]);
    }

    private function voteFor(Poll $poll, int $accountId): ?array
    {
        $vote = PollVote::with('options')
            ->where('poll_id', $poll->poll_id)
            ->where('battlenet_accounts_id', $accountId)
            ->first();

        return $vote ? [
            'options' => $vote->options->pluck('poll_option_id')->map(fn ($id) => (int) $id)->all(),
            'comment' => $vote->comment,
        ] : null;
    }

    private function pollData(Poll $poll): array
    {
        return [
            'slug' => $poll->slug,
            'title' => $poll->title,
            'description' => $poll->description,
            'allow_multiple' => $poll->allow_multiple,
            'closes_at' => $poll->closes_at?->toIso8601String(),
            'is_open' => $poll->isOpen(),
            'options' => $poll->options->map(fn ($option) => [
                'id' => $option->poll_option_id,
                'label' => $option->label,
                'description' => $option->description,
            ])->all(),
        ];
    }
}
