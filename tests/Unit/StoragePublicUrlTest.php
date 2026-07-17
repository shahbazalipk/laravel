<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoragePublicUrlTest extends TestCase
{
    public function test_storage_public_url_delegates_to_public_disk(): void
    {
        Storage::shouldReceive('disk')
            ->once()
            ->with('public')
            ->andReturnSelf();

        Storage::shouldReceive('url')
            ->once()
            ->with('event/logos/test.png')
            ->andReturn('https://glimzo-events-hub.s3.eu-west-1.amazonaws.com/event/logos/test.png');

        $this->assertSame(
            'https://glimzo-events-hub.s3.eu-west-1.amazonaws.com/event/logos/test.png',
            storage_public_url('event/logos/test.png')
        );
    }

    public function test_storage_public_url_returns_null_for_empty_values(): void
    {
        $this->assertNull(storage_public_url(null));
        $this->assertNull(storage_public_url(''));
        $this->assertNull(storage_public_url('0'));
    }

    public function test_storage_public_url_returns_absolute_urls_unchanged(): void
    {
        $url = 'https://glimzo-events-hub.s3.eu-west-1.amazonaws.com/event/logos/test.png';

        $this->assertSame($url, storage_public_url($url));
    }
}
