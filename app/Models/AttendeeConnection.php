<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;

class AttendeeConnection extends Model
{
    use HasEventScope;

    protected $fillable = [
        'event_id',
        'org_id',
        'sender_id',
        'receiver_id',
        'status',
        'message',
        'accepted_at',
        'rejected_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(Registration::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(Registration::class, 'receiver_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    // Check if two users are connected
    public static function areConnected($userId1, $userId2)
    {
        return self::where(function($query) use ($userId1, $userId2) {
            $query->where('sender_id', $userId1)->where('receiver_id', $userId2);
        })->orWhere(function($query) use ($userId1, $userId2) {
            $query->where('sender_id', $userId2)->where('receiver_id', $userId1);
        })->where('status', 'accepted')->exists();
    }

    // Get connection status between two users
    public static function getConnectionStatus($userId1, $userId2)
    {
        $connection = self::where(function($query) use ($userId1, $userId2) {
            $query->where('sender_id', $userId1)->where('receiver_id', $userId2);
        })->orWhere(function($query) use ($userId1, $userId2) {
            $query->where('sender_id', $userId2)->where('receiver_id', $userId1);
        })->first();

        return $connection;
    }
}
