<?php

namespace App\Persona\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'counselling_field_id',
        'properties'
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'properties' => 'json',
    ];

    public function counsellingField()
    {
        return $this->belongsTo(CounsellingField::class); 
    }
}
