<?php

namespace App\Counselling\Models;

use App\Persona\Models\Persona;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Course\Models\CourseMember;

class Counselling extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'course_member_id',
        'counselling_setup_id',
        'persona_id',
        'status_chat_id',
        'start_date',
        'postprocessing_process'
    ];

    protected $casts = [
        'postprocessing_process' => 'json',
    ];

    protected $attributes = [
        'postprocessing_process' => '{"note": {"done": 0, "text": ""},"vikl_rating": 0,"ki_feedback": 0,"teacher_feedback": 0,"peer_review": 0}'
    ];

    public function courseMember()
    {
        return $this->belongsTo(CourseMember::class);
    }

    public function counsellingSetup() {
        return $this->belongsTo(CounsellingSetup::class);
    }

    public function counsellingMessages() {
        return $this->hasMany(CounsellingMessage::class);
    }

    public function counsellingFeedbacks() {
        return $this->hasMany(CounsellingFeedback::class);
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }
}
