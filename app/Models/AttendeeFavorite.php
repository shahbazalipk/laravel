<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendeeFavorite extends Model
{
    protected $fillable = [
        'registration_id',
        'favoritable_type',
        'favoritable_id',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function favoritable()
    {
        return $this->morphTo();
    }
}
