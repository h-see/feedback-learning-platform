<?php

namespace App\Policies;

use App\Counselling\Models\Counselling;
use App\Counselling\Models\CounsellingFeedback;
use App\Models\User;


class FeedbackPolicy
{
    /** checks if current user alrady submitted feedback for this counselling
     *
     * @param  \App\Models\User  $user
     * @param  \App\Counselling\Models\Counselling  $counselling
     * @return bool
     */
    public function userAlreadySubmittedFeedback(User $user, Counselling $counselling) {

        foreach ($counselling->counsellingFeedbacks as $feedbackRequest) {
            // if in progress --> don't lock to continue
            if ($feedbackRequest->user_id === $user->id && $feedbackRequest->status_feedback_id !== 2) {
                return false; // User already submitted Feedback for this counselling --> refuse access
            }
        }
        return true;
    }

    /** checks if current user is locked as peer reviewer for this counselling
     *
     * @param  \App\Models\User  $user
     * @param  \App\Counselling\Models\CounsellingFeedback  $counsellingFeedback
     * @return bool
     */
    public function userIsLockedPeerReviewer(User $user, CounsellingFeedback $counsellingFeedback) {

        if($counsellingFeedback->user_id === $user->id && ($counsellingFeedback->status_feedback_id === 1 || $counsellingFeedback->status_feedback_id === 2)){
            return true;
        }
            return false;
    }
}
