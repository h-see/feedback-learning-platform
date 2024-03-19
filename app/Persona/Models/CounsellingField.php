<?php

namespace App\Persona\Models;

use Illuminate\Database\Eloquent\Model;

class CounsellingField extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function personas()
    {
        return $this->hasMany(Persona::class);
    }
}
