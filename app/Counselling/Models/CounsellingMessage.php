<?php

namespace App\Counselling\Models;

use App\Counselling\AdditionsCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CounsellingMessage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'counselling_id',
        'content',
        'author',
        'additions',
    ];

    protected $primaryKey = ['counselling_id', 'message_number'];
    public $incrementing = false;

    protected $attributes = [
        'additions' => '{}',
    ];

    protected $casts = [
        'additions' => AdditionsCast::class,
    ];

    public static function boot() {
        parent::boot();

        static::creating(function ($counsellingMessage) {
            $counsellingMessage->message_number = static::where('counselling_id', $counsellingMessage->counselling_id)->max('message_number') + 1;
        });
    }

    public function counselling() {
        return $this->belongsTo(Counselling::class);
    }

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('counselling_id', $this->getAttribute('counselling_id'))
            ->where('message_number', $this->getAttribute('message_number'));
    }
}
