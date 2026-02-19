<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;

class AttendeeMessage extends Model
{
    use HasEventScope;

    protected $fillable = [
        'event_id',
        'org_id',
        'sender_id',
        'receiver_id',
        'message',
        'attachment',
        'attachment_type',
        'is_read',
        'read_at',
        'is_deleted_by_sender',
        'is_deleted_by_receiver',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'is_deleted_by_sender' => 'boolean',
        'is_deleted_by_receiver' => 'boolean',
    ];

    public function sender()
    {
        return $this->belongsTo(Registration::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(Registration::class, 'receiver_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeBetween($query, $userId1, $userId2)
    {
        return $query->where(function($q) use ($userId1, $userId2) {
            $q->where('sender_id', $userId1)->where('receiver_id', $userId2);
        })->orWhere(function($q) use ($userId1, $userId2) {
            $q->where('sender_id', $userId2)->where('receiver_id', $userId1);
        });
    }

    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }
}
