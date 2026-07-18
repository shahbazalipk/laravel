<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class CommunicationTemplate extends SubmissionModel
{
    use SoftDeletes;

    protected $table = 'submission_communication_templates';

    protected $casts = ['is_active' => 'boolean', 'settings' => 'array'];

    public function getEventKeyAttribute(): ?string
    {
        return $this->trigger;
    }

    public function getBodyHtmlAttribute(): string
    {
        return $this->body;
    }

    public function getBodyTextAttribute(): ?string
    {
        return $this->settings['body_text'] ?? null;
    }

    public function getVersionAttribute(): int
    {
        return (int) ($this->settings['version'] ?? 1);
    }
}
