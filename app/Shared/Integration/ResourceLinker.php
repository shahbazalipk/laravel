<?php

namespace App\Shared\Integration;

use App\Shared\Integration\Models\ResourceLink;
use InvalidArgumentException;

class ResourceLinker
{
    public function link(
        ResourceReference $source,
        ResourceReference $target,
        string $relationship,
        array $metadata = [],
    ): ResourceLink {
        if ($source->equals($target)) {
            throw new InvalidArgumentException('A resource cannot be linked to itself.');
        }

        return ResourceLink::query()->firstOrCreate([
            'source_module' => $source->module,
            'source_type' => $source->type,
            'source_public_id' => $source->publicId,
            'target_module' => $target->module,
            'target_type' => $target->type,
            'target_public_id' => $target->publicId,
            'relationship' => $relationship,
        ], [
            'metadata' => $metadata,
            'created_by' => session('admin_id'),
        ]);
    }
}
