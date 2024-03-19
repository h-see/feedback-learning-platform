<?php

namespace App\Course\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Counselling\Models\CounsellingSetup;

class Course extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'enrollment_key',
        'start_date',
        'end_date',
        'settings',
        'creator_id',
    ];

    public function counsellingSetups()
    {
        return $this->hasMany(CounsellingSetup::class);
    }

    public function courseMembers()
    {
        return $this->hasMany(CourseMember::class);
    }

    public function exerciseSetup()
    {
        return $this->counsellingSetups()->where('mandatory', false);
    }

    public function tasksSetups()
    {
        return $this->counsellingSetups()->where('mandatory', true);
    }
}
