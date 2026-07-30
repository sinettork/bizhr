<?php

namespace App\Services;

use App\Models\JobApplicant;
use App\Models\JobInterview;
use App\Models\JobOffer;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class RecruitmentWorkflowService
{
    private const TRANSITIONS = [
        'applied' => ['screening', 'rejected'],
        'screening' => ['shortlisted', 'rejected'],
        'shortlisted' => ['interview', 'rejected'],
        'interview' => ['offer_pending', 'rejected'],
        'offer_pending' => ['offered', 'rejected'],
        'offered' => ['accepted', 'declined'],
        'accepted' => ['hired'],
    ];

    public function moveApplicant(JobApplicant $applicant, string $status, ?string $note = null): JobApplicant
    {
        if (! in_array($status, self::TRANSITIONS[$applicant->status] ?? [], true)) {
            throw new DomainException("Invalid recruitment transition: {$applicant->status} → {$status}.");
        }
        $applicant->update(['status' => $status, 'hr_note' => trim((string) $note) ?: $applicant->hr_note]);
        return $applicant->refresh();
    }

    public function completeInterview(JobInterview $interview, int $score, string $feedback): JobInterview
    {
        if ($interview->status !== 'scheduled' || $score < 0 || $score > 100 || mb_strlen(trim($feedback)) < 10) {
            throw new DomainException('A scheduled interview requires a 0–100 score and meaningful feedback.');
        }
        $interview->update(['score' => $score, 'feedback' => trim($feedback), 'status' => 'completed', 'completed_at' => now()]);
        return $interview->refresh();
    }

    public function approveOffer(JobOffer $offer, User $actor): JobOffer
    {
        return DB::transaction(function () use ($offer, $actor) {
            $offer = JobOffer::query()->lockForUpdate()->findOrFail($offer->id);
            if ($offer->status !== 'draft') throw new DomainException('Only a draft offer can be approved.');
            if ((int) $offer->created_by === (int) $actor->id && ! $actor->hasRole('Super Admin')) {
                throw new DomainException('Offer creator cannot approve the same offer.');
            }
            if ($offer->expires_at->isPast()) throw new DomainException('An expired offer cannot be approved.');
            $offer->update(['status' => 'approved', 'approved_by' => $actor->id, 'approved_at' => now()]);
            return $offer->refresh();
        });
    }

    public function respondToOffer(JobOffer $offer, bool $accepted): JobOffer
    {
        if (! in_array($offer->status, ['approved', 'sent'], true) || $offer->expires_at->isPast()) {
            throw new DomainException('This offer is unavailable or expired.');
        }
        $offer->update(['status' => $accepted ? 'accepted' : 'declined', 'responded_at' => now()]);
        $offer->applicant->update(['status' => $accepted ? 'accepted' : 'declined']);
        return $offer->refresh();
    }
}
