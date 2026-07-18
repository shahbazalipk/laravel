<?php

namespace App\Submissions\Models;

class CommunicationLog extends SubmissionModel
{
    protected $table = 'submission_communication_logs';

    protected $casts = ['metadata' => 'array', 'sent_at' => 'datetime'];
}
