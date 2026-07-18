<?php

namespace App\Submissions\Services;

use App\Submissions\Models\SubmissionType;
use Illuminate\Support\Facades\DB;

final class SubmissionNumberService
{
    public function next(SubmissionType $type): string
    {
        return DB::transaction(function () use ($type): string {
            $locked = SubmissionType::query()->lockForUpdate()->findOrFail($type->getKey());
            $sequence = ((int) $locked->number_sequence) + 1;
            $locked->forceFill(['number_sequence' => $sequence])->save();

            $pattern = $locked->number_pattern ?: '{PREFIX}-{YEAR}-{NUMBER:4}';
            $prefix = $locked->number_prefix ?: strtoupper(substr($locked->code ?: 'SUB', 0, 4));

            $value = str_replace(
                ['{PREFIX}', '{YEAR}'],
                [$prefix, now()->format('Y')],
                $pattern,
            );

            return preg_replace_callback(
                '/\{NUMBER(?::(\d+))?\}/',
                fn (array $match): string => str_pad((string) $sequence, (int) ($match[1] ?? 4), '0', STR_PAD_LEFT),
                $value,
            ) ?? "{$prefix}-{$sequence}";
        }, 3);
    }
}
