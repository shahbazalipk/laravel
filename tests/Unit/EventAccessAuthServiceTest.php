<?php

namespace Tests\Unit;

use App\Services\EventAccessAuthService;
use App\Services\EventContextService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EventAccessAuthServiceTest extends TestCase
{
    public function test_user_is_assigned_to_event_checks_event_user_pivot(): void
    {
        DB::shouldReceive('table')
            ->once()
            ->with('event_user')
            ->andReturnSelf();

        DB::shouldReceive('where')
            ->once()
            ->with('event_id', 30)
            ->andReturnSelf();

        DB::shouldReceive('where')
            ->once()
            ->with('organization_user_id', 14)
            ->andReturnSelf();

        DB::shouldReceive('exists')
            ->once()
            ->andReturnTrue();

        $service = new EventAccessAuthService(new EventContextService());

        $this->assertTrue($service->userIsAssignedToEvent(14, 30));
    }
}
