<?php

namespace App\Shared\Integration;

use InvalidArgumentException;

final readonly class ResourceReference
{
    public function __construct(
        public string $module,
        public string $type,
        public string $publicId,
    ) {
        if ($module === '' || $type === '' || $publicId === '') {
            throw new InvalidArgumentException('Resource references require a module, type, and public ID.');
        }
    }

    public function equals(self $other): bool
    {
        return $this->module === $other->module
            && $this->type === $other->type
            && $this->publicId === $other->publicId;
    }
}
