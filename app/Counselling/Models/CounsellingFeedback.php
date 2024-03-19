<?php

namespace App\Counselling\Models;

use App\Models\FeedbackType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CounsellingFeedback extends Model
{
    protected $fillable = [
        'accepted_at',
        'received_at',
        'feedback_text',
        'ai_feedback_properties',
        'counselling_id',
        'user_id',
        'status_feedback_id',
        'feedback_source_id',
        'feedback_types_id'
    ];

    protected $table = 'counselling_feedbacks';

    protected $casts = [
        'ai_feedback_properties' => 'json',
    ];


    public function counselling() {
        return $this->belongsTo(Counselling::class);
    }

    public function feedbackType() {
        return $this->hasOne(FeedbackType::class);
    }
}
