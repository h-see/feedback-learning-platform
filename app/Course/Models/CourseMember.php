<?php

namespace App\Course\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Role;
use App\Models\User;
use App\Course\Models\Course;

class CourseMember extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pseudo_first_name',
        'pseudo_last_name',
        'properties',
        'enabled',
        'course_id',
        'user_id',
        'role_id',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function course() {
        return $this->belongsTo(Course::class);
    }

    public function role() {
        return $this->belongsTo(Role::class);
    }
}
