<?php

namespace App\Counselling\Models;

use Illuminate\Database\Eloquent\Model;
use App\Course\Models\Course;

class CounsellingSetup extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mandatory',
        'due_date',
        'course_id',
        'settings',
    ];

    protected $casts = [
        'mandatory' => 'boolean',
        'settings' => 'json',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class); 
    }
}
